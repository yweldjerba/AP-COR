<?php

namespace App\Http\Controllers;

use App\Models\RendezVous;
use App\Support\Gsb;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ClientController extends Controller
{
    /* =========================================================
       ACCUEIL : rendez-vous + prise de rendez-vous
       ========================================================= */

    public function accueil()
    {
        $client = $this->clientConnecte();

        if (!$client) {
            session()->flush();

            return redirect()->route('login')->with('error', 'Client introuvable.');
        }

        $rendezVous = RendezVous::with('praticien')
            ->where('id_client', $client->id)
            ->orderBy('date_rdv')
            ->orderBy('heure_rdv')
            ->get();

        // À venir / historique
        [$aVenir, $historique] = $rendezVous->partition(fn ($rdv) => $rdv->estAVenir());

        $historique = $historique
            ->sortByDesc(fn ($rdv) => optional($rdv->debut())->timestamp)
            ->values();

        $praticiens = $this->praticiensDeLaRegion($client);

        return view('client.accueil', compact(
            'client',
            'rendezVous',
            'aVenir',
            'historique',
            'praticiens'
        ));
    }


    /* =========================================================
       CRÉNEAUX DISPONIBLES (appel JavaScript)
       ========================================================= */

    public function creneaux(Request $request)
    {
        $request->validate([
            'id_praticien' => ['required', 'integer'],
            'date'         => ['nullable', 'date_format:Y-m-d'],
        ]);

        $idPraticien = (int) $request->id_praticien;

        if (!Gsb::praticienDansRegion($idPraticien)) {
            return response()->json([
                'creneaux' => [],
                'jours'    => [],
                'message'  => 'Ce praticien n\'est pas disponible.',
            ], 422);
        }

        $jours = Gsb::joursTravailles($idPraticien);

        if (!$request->filled('date')) {
            return response()->json([
                'creneaux' => [],
                'jours'    => $jours,
                'message'  => '',
            ]);
        }

        $date = $request->date;
        $numeroJour = Carbon::createFromFormat('Y-m-d', $date)->dayOfWeekIso;
        $horaire = Gsb::horaires($idPraticien)[$numeroJour] ?? null;
        $creneaux = Gsb::creneauxDisponibles($idPraticien, $date);

        $message = '';

        if (!$horaire || !$horaire['actif']) {
            $message = 'Le praticien ne consulte pas ce jour-là. Choisissez une autre date.';
        } elseif (empty($creneaux)) {
            $message = 'Plus aucun créneau libre ce jour-là. Choisissez une autre date.';
        }

        return response()->json([
            'creneaux' => $creneaux,
            'jours'    => $jours,
            'message'  => $message,
        ]);
    }


    /* =========================================================
       ENREGISTRER UNE DEMANDE DE RENDEZ-VOUS
       ========================================================= */

    public function store(Request $request)
    {
        $client = $this->clientConnecte();

        if (!$client) {
            return redirect()->route('login');
        }

        $request->validate([
            'id_praticien' => ['required', 'integer', 'exists:praticien,id'],
            'date_rdv'     => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'heure_rdv'    => ['required', 'date_format:H:i'],
            'motif'        => ['required', 'string', 'max:255'],
        ], [
            'id_praticien.required'   => 'Veuillez sélectionner un praticien.',
            'id_praticien.exists'     => 'Le praticien sélectionné est invalide.',
            'date_rdv.required'       => 'Veuillez choisir une date.',
            'date_rdv.after_or_equal' => 'La date doit être aujourd\'hui ou plus tard.',
            'heure_rdv.required'      => 'Veuillez choisir un créneau horaire.',
            'heure_rdv.date_format'   => 'Le créneau horaire est invalide.',
            'motif.required'          => 'Le motif est obligatoire.',
            'motif.max'               => 'Le motif ne doit pas dépasser 255 caractères.',
        ]);

        if (!Gsb::praticienDansRegion($request->id_praticien)) {
            return back()->withInput()->withErrors([
                'id_praticien' => 'Ce praticien ne fait pas partie de la région.',
            ]);
        }

        $creneauxLibres = Gsb::creneauxDisponibles((int) $request->id_praticien, $request->date_rdv);

        if (!in_array($request->heure_rdv, $creneauxLibres, true)) {
            return back()->withInput()->withErrors([
                'heure_rdv' => 'Ce créneau n\'est plus disponible. Choisissez-en un autre.',
            ]);
        }

        RendezVous::create([
            'id_client'    => $client->id,
            'id_praticien' => $request->id_praticien,
            'date_rdv'     => $request->date_rdv,
            'heure_rdv'    => $request->heure_rdv . ':00',
            'motif'        => trim($request->motif),
            'statut'       => 'en_attente',
        ]);

        return redirect()
            ->route('client.accueil')
            ->with('success', 'Votre demande de rendez-vous a été envoyée au praticien.');
    }


    /* =========================================================
       DOSSIER MÉDICAL
       ========================================================= */

    public function dossier()
    {
        $client = $this->clientConnecte();

        if (!$client) {
            return redirect()->route('login');
        }

        $entrees = DB::table('dossier_medical')
            ->leftJoin('praticien', 'praticien.id', '=', 'dossier_medical.id_praticien')
            ->where('dossier_medical.id_client', $client->id)
            ->orderByDesc('dossier_medical.date_consultation')
            ->orderByDesc('dossier_medical.id')
            ->select(
                'dossier_medical.*',
                'praticien.nom as praticien_nom',
                'praticien.prenom as praticien_prenom'
            )
            ->get();

        return view('client.dossier', compact('client', 'entrees'));
    }


    /* =========================================================
       OUTILS
       ========================================================= */

    private function clientConnecte()
    {
        return DB::table('client')
            ->where('id', session('id_client'))
            ->first();
    }

    /** Praticiens de la région, avec indicateurs de proximité. */
    private function praticiensDeLaRegion($client)
    {
        $idVilleClient = (int) ($client->id_ville ?? 0);
        $idDepartementClient = (int) ($client->id_departement ?? 0);

        return DB::table('praticien')
            ->join('ville', 'ville.id', '=', 'praticien.id_ville')
            ->join('departement', 'departement.id', '=', 'ville.id_departement')
            ->join('region', 'region.id', '=', 'departement.id_region')
            ->where('region.slug', Gsb::SLUG_REGION)
            ->select(
                'praticien.id',
                'praticien.nom',
                'praticien.prenom',
                'ville.nom_reel as ville',
                'departement.code as code_departement',
                'departement.nom_reel as departement',
                'region.nom_reel as region'
            )
            ->selectRaw('CASE WHEN ville.id = ? THEN 1 ELSE 0 END AS meme_ville', [$idVilleClient])
            ->selectRaw('CASE WHEN departement.id = ? THEN 1 ELSE 0 END AS meme_departement', [$idDepartementClient])
            ->orderBy('praticien.nom')
            ->orderBy('praticien.prenom')
            ->get();
    }
}

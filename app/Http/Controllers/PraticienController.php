<?php

namespace App\Http\Controllers;

use App\Models\RendezVous;
use App\Support\Gsb;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PraticienController extends Controller
{
    /* =========================================================
       ACCUEIL : demandes + calendrier
       ========================================================= */

    public function accueil()
    {
        $rendezVous = RendezVous::with('client')
            ->where('id_praticien', session('id_prat'))
            ->orderBy('date_rdv')
            ->orderBy('heure_rdv')
            ->get();

        return view('praticien.accueil', compact('rendezVous'));
    }


    /* =========================================================
       ACCEPTER / REFUSER / TERMINER
       ========================================================= */

    public function accepter($id)
    {
        $rdv = $this->rdvDuPraticien($id);

        if ($rdv->statut !== 'en_attente') {
            return back()->with('error', 'Ce rendez-vous a déjà été traité.');
        }

        // Un seul rendez-vous accepté par créneau
        $conflit = RendezVous::where('id_praticien', $rdv->id_praticien)
            ->whereDate('date_rdv', $rdv->date_rdv)
            ->where('heure_rdv', $rdv->heure_rdv)
            ->where('id', '!=', $rdv->id)
            ->whereIn('statut', ['accepte', 'termine'])
            ->exists();

        if ($conflit) {
            return back()->with('error', 'Vous avez déjà un rendez-vous accepté sur ce créneau.');
        }

        $rdv->update(['statut' => 'accepte']);

        return back()->with('success', 'Le rendez-vous a été accepté.');
    }

    public function refuser($id)
    {
        $rdv = $this->rdvDuPraticien($id);

        if ($rdv->statut !== 'en_attente') {
            return back()->with('error', 'Ce rendez-vous a déjà été traité.');
        }

        $rdv->update(['statut' => 'refuse']);

        return back()->with('success', 'Le rendez-vous a été refusé.');
    }

    /** Fin du rendez-vous : saisie du dossier médical (date + symptôme). */
    public function terminer(Request $request, $id)
    {
        $rdv = $this->rdvDuPraticien($id);

        if ($rdv->statut !== 'accepte') {
            return back()->with('error', 'Seul un rendez-vous accepté peut être terminé.');
        }

        $request->validate([
            'date_consultation' => ['required', 'date_format:Y-m-d', 'before_or_equal:today'],
            'symptome'          => ['required', 'string', 'max:1000'],
        ], [
            'date_consultation.required'        => 'La date de consultation est obligatoire.',
            'date_consultation.before_or_equal' => 'La date de consultation ne peut pas être dans le futur.',
            'symptome.required'                 => 'Le symptôme est obligatoire.',
            'symptome.max'                      => 'Le symptôme ne doit pas dépasser 1000 caractères.',
        ]);

        DB::transaction(function () use ($rdv, $request) {
            DB::table('dossier_medical')->insert([
                'id_client'         => $rdv->id_client,
                'id_praticien'      => $rdv->id_praticien,
                'id_rendez_vous'    => $rdv->id,
                'date_consultation' => $request->date_consultation,
                'symptome'          => trim($request->symptome),
            ]);

            $rdv->update(['statut' => 'termine']);
        });

        return back()->with('success', 'Rendez-vous terminé : le dossier médical du patient a été mis à jour.');
    }


    /* =========================================================
       LISTE DES PATIENTS
       ========================================================= */

    public function patients()
    {
        $idPraticien = session('id_prat');

        $patients = DB::table('rendez_vous')
            ->join('client', 'client.id', '=', 'rendez_vous.id_client')
            ->where('rendez_vous.id_praticien', $idPraticien)
            ->whereIn('rendez_vous.statut', ['accepte', 'termine'])
            ->groupBy('client.id', 'client.nom', 'client.prenom', 'client.email', 'client.telephone')
            ->select('client.id', 'client.nom', 'client.prenom', 'client.email', 'client.telephone')
            ->selectRaw('COUNT(rendez_vous.id) AS nb_rdv')
            ->selectRaw('MAX(rendez_vous.date_rdv) AS dernier_rdv')
            ->orderBy('client.nom')
            ->orderBy('client.prenom')
            ->get();

        $dossiers = DB::table('dossier_medical')
            ->where('id_praticien', $idPraticien)
            ->whereIn('id_client', $patients->pluck('id'))
            ->orderByDesc('date_consultation')
            ->get()
            ->groupBy('id_client');

        return view('praticien.patients', compact('patients', 'dossiers'));
    }


    /* =========================================================
       PLAGES HORAIRES
       ========================================================= */

    public function horaires()
    {
        $horaires = Gsb::horaires((int) session('id_prat'));

        return view('praticien.horaires', compact('horaires'));
    }

    public function updateHoraires(Request $request)
    {
        $request->validate([
            'jours'          => ['required', 'array'],
            'jours.*.debut'  => ['required', 'date_format:H:i'],
            'jours.*.fin'    => ['required', 'date_format:H:i'],
        ], [
            'jours.*.debut.required'    => 'Chaque jour doit avoir une heure de début.',
            'jours.*.fin.required'      => 'Chaque jour doit avoir une heure de fin.',
            'jours.*.debut.date_format' => 'Heure de début invalide.',
            'jours.*.fin.date_format'   => 'Heure de fin invalide.',
        ]);

        $idPraticien = (int) session('id_prat');
        $erreurs = [];
        $lignes = [];

        foreach (Gsb::JOURS as $numero => $nom) {
            $jour = $request->input("jours.$numero", []);

            $actif = !empty($jour['actif']);
            $debut = $jour['debut'] ?? '08:00';
            $fin = $jour['fin'] ?? '18:00';

            if ($actif && $debut >= $fin) {
                $erreurs["jours.$numero.fin"] = "$nom : l'heure de fin doit être après l'heure de début.";
            }

            $lignes[$numero] = compact('actif', 'debut', 'fin');
        }

        if (!collect($lignes)->contains(fn ($ligne) => $ligne['actif'])) {
            $erreurs['jours'] = 'Vous devez consulter au moins un jour par semaine.';
        }

        if ($erreurs) {
            return back()->withInput()->withErrors($erreurs);
        }

        foreach ($lignes as $numero => $ligne) {
            DB::table('horaire_praticien')->updateOrInsert(
                ['id_praticien' => $idPraticien, 'jour' => $numero],
                [
                    'actif'       => $ligne['actif'] ? 1 : 0,
                    'heure_debut' => $ligne['debut'] . ':00',
                    'heure_fin'   => $ligne['fin'] . ':00',
                ]
            );
        }

        return redirect()
            ->route('praticien.horaires')
            ->with('success', 'Vos plages horaires ont été enregistrées.');
    }


    /* =========================================================
       OUTILS
       ========================================================= */

    private function rdvDuPraticien($id): RendezVous
    {
        return RendezVous::where('id', $id)
            ->where('id_praticien', session('id_prat'))
            ->firstOrFail();
    }
}

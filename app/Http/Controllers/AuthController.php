<?php

namespace App\Http\Controllers;

use App\Support\Gsb;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /* =========================================================
       CONNEXION
       ========================================================= */

    public function showLogin()
    {
        if ($redirection = $this->redirigerSiConnecte()) {
            return $redirection;
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'login' => ['required', 'string'],
            'mdp'   => ['required', 'string'],
        ], [
            'login.required' => 'Veuillez saisir votre identifiant.',
            'mdp.required'   => 'Veuillez saisir votre mot de passe.',
        ]);

        $login = trim($request->login);
        $mdp = $request->mdp;

        /* ---------- 1. Compte client ---------- */

        $client = DB::table('client')
            ->where('login', $login)
            ->first();

        if ($client && $this->verifierMdp($mdp, $client->mdp)) {
            $request->session()->regenerate();

            session([
                'user_role'      => 'client',
                'user_nom'       => $client->nom,
                'user_prenom'    => $client->prenom,
                'user_login'     => $client->login,
                'id_client'      => $client->id,
                'id_region'      => $client->id_region ?? null,
                'id_departement' => $client->id_departement ?? null,
                'id_prat'        => null,
            ]);

            return redirect()->route('client.accueil');
        }

        /* ---------- 2. Compte praticien (table utilisateur) ---------- */

        $utilisateur = DB::table('utilisateur')
            ->where('login', $login)
            ->first();

        if ($utilisateur && $this->verifierMdp($mdp, $utilisateur->mdp)) {
            $praticien = DB::table('praticien')
                ->where('id', $utilisateur->id_praticien)
                ->first();

            if (!$praticien) {
                return back()
                    ->withInput($request->only('login'))
                    ->with('error', 'Le compte praticien n\'est associé à aucun praticien.');
            }

            $request->session()->regenerate();

            session([
                'user_role'      => 'praticien',
                'user_nom'       => $praticien->nom,
                'user_prenom'    => $praticien->prenom,
                'user_login'     => $utilisateur->login,
                'id_client'      => null,
                'id_region'      => null,
                'id_departement' => null,
                'id_prat'        => $praticien->id,
            ]);

            // Notification à la connexion : demandes en attente
            $nbDemandes = DB::table('rendez_vous')
                ->where('id_praticien', $praticien->id)
                ->where('statut', 'en_attente')
                ->count();

            $redirection = redirect()->route('praticien.accueil');

            if ($nbDemandes > 0) {
                $redirection->with(
                    'notification',
                    $nbDemandes > 1
                        ? "Vous avez {$nbDemandes} nouvelles demandes de rendez-vous à traiter."
                        : 'Vous avez 1 nouvelle demande de rendez-vous à traiter.'
                );
            }

            return $redirection;
        }

        /* ---------- 3. Échec ---------- */

        return back()
            ->withInput($request->only('login'))
            ->with('error', 'Identifiant ou mot de passe incorrect.');
    }

    public function logout(Request $request)
    {
        $request->session()->flush();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('login')
            ->with('success', 'Vous êtes maintenant déconnecté.');
    }


    /* =========================================================
       INSCRIPTION
       ========================================================= */

    public function showInscription()
    {
        if ($redirection = $this->redirigerSiConnecte()) {
            return $redirection;
        }

        $idRegion = Gsb::regionId();

        $region = DB::table('region')
            ->where('id', $idRegion)
            ->first();

        $departements = DB::table('departement')
            ->where('id_region', $idRegion)
            ->orderBy('code')
            ->get(['id', 'code', 'nom_reel']);

        $villes = DB::table('ville')
            ->whereIn('id_departement', $departements->pluck('id'))
            ->orderBy('nom_reel')
            ->get(['id', 'nom_reel', 'id_departement']);

        return view('auth.inscription', compact(
            'region',
            'departements',
            'villes'
        ));
    }

    public function inscription(Request $request)
    {
        $request->validate([
            'nom'            => ['required', 'string', 'max:100'],
            'prenom'         => ['required', 'string', 'max:100'],
            'email'          => ['required', 'email', 'max:150', 'unique:client,email'],
            'telephone'      => ['required', 'regex:/^0[1-9][0-9]{8}$/'],
            'adresse'        => ['required', 'string', 'max:255'],
            'id_departement' => ['required', 'integer', 'exists:departement,id'],
            'id_ville'       => ['required', 'integer', 'exists:ville,id'],
            'mdp'            => ['required', 'string', 'min:3', 'confirmed'],
        ], [
            'nom.required'            => 'Le nom est obligatoire.',
            'prenom.required'         => 'Le prénom est obligatoire.',
            'email.required'          => 'L\'adresse email est obligatoire.',
            'email.email'             => 'L\'adresse email est invalide.',
            'email.unique'            => 'Cette adresse email est déjà utilisée.',
            'telephone.required'      => 'Le numéro de téléphone est obligatoire.',
            'telephone.regex'         => 'Le numéro doit contenir 10 chiffres et commencer par 0.',
            'adresse.required'        => 'L\'adresse postale est obligatoire.',
            'id_departement.required' => 'Veuillez choisir votre département.',
            'id_departement.exists'   => 'Le département sélectionné est invalide.',
            'id_ville.required'       => 'Veuillez choisir votre ville.',
            'id_ville.exists'         => 'La ville sélectionnée est invalide.',
            'mdp.required'            => 'Le mot de passe est obligatoire.',
            'mdp.min'                 => 'Le mot de passe doit contenir au moins 3 caractères.',
            'mdp.confirmed'           => 'Les mots de passe ne correspondent pas.',
        ]);

        $idRegion = Gsb::regionId();

        // Le département doit appartenir à la région
        $departementValide = DB::table('departement')
            ->where('id', $request->id_departement)
            ->where('id_region', $idRegion)
            ->exists();

        if (!$departementValide) {
            return back()->withInput()->withErrors([
                'id_departement' => 'Ce département ne fait pas partie de la région.',
            ]);
        }

        // La ville doit appartenir au département choisi
        $villeValide = DB::table('ville')
            ->where('id', $request->id_ville)
            ->where('id_departement', $request->id_departement)
            ->exists();

        if (!$villeValide) {
            return back()->withInput()->withErrors([
                'id_ville' => 'Cette ville ne fait pas partie du département choisi.',
            ]);
        }

        $nom = trim($request->nom);
        $prenom = trim($request->prenom);

        $login = $this->genererLogin($prenom, $nom);

        DB::table('client')->insert([
            'nom'            => $nom,
            'prenom'         => $prenom,
            'email'          => strtolower(trim($request->email)),
            'telephone'      => $request->telephone,
            'adresse'        => trim($request->adresse),
            'login'          => $login,
            'mdp'            => Hash::make($request->mdp),
            'role'           => 'client',
            'id_region'      => $idRegion,
            'id_departement' => $request->id_departement,
            'id_ville'       => $request->id_ville,
        ]);

        return redirect()
            ->route('login')
            ->with('success', 'Votre compte a été créé. Votre identifiant est : ' . $login);
    }


    /* =========================================================
       OUTILS
       ========================================================= */

    private function redirigerSiConnecte()
    {
        if (session('user_role') === 'client') {
            return redirect()->route('client.accueil');
        }

        if (session('user_role') === 'praticien') {
            return redirect()->route('praticien.accueil');
        }

        return null;
    }

    /** Identifiant = initiale du prénom + nom (ex. : jdupont, jdupont2...). */
    private function genererLogin(string $prenom, string $nom): string
    {
        $base = substr($this->nettoyerTexte($prenom), 0, 1) . $this->nettoyerTexte($nom);
        $login = $base;
        $numero = 2;

        while (
            DB::table('client')->where('login', $login)->exists()
            || DB::table('utilisateur')->where('login', $login)->exists()
        ) {
            $login = $base . $numero;
            $numero++;
        }

        return $login;
    }

    /** Accepte les mots de passe hachés (bcrypt) et les anciens en clair. */
    private function verifierMdp($saisi, $enBase): bool
    {
        if (!$enBase) {
            return false;
        }

        if (str_starts_with($enBase, '$2y$') || str_starts_with($enBase, '$2a$')) {
            return Hash::check($saisi, $enBase);
        }

        return hash_equals((string) $enBase, (string) $saisi);
    }

    private function nettoyerTexte($texte): string
    {
        $texte = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $texte);
        $texte = preg_replace('/[^a-zA-Z0-9]/', '', $texte);

        return strtolower($texte);
    }
}

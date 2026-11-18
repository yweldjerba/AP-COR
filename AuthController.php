<?php

namespace App\Http\Controllers;

use App\Models\Utilisateur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Affichage de la page de connexion.
     */
    public function showLogin()
    {
        // Si l'utilisateur est déjà connecté,
        // on le redirige selon son rôle.
        if (session()->has('user_id')) {
            return $this->redirectByRole(session('user_role'));
        }

        return view('auth.login');
    }

    /**
     * Connexion.
     */
    public function login(Request $request)
    {
        $request->validate([
            'login' => ['required', 'string'],
            'mdp'   => ['required', 'string'],
        ], [
            'login.required' => 'Le login est obligatoire.',
            'mdp.required'   => 'Le mot de passe est obligatoire.',
        ]);

        $utilisateur = Utilisateur::where('login', $request->login)->first();

        if (!$utilisateur) {
            return back()
                ->withInput($request->only('login'))
                ->withErrors([
                    'login' => 'Login ou mot de passe incorrect.',
                ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Vérification du mot de passe
        |--------------------------------------------------------------------------
        |
        | Compatible avec :
        | - ancien mot de passe en clair : 1234
        | - mot de passe hashé avec Laravel
        |
        */

        $motDePasseCorrect = false;

        $mdpStocke = (string) $utilisateur->mdp;

        if (
            str_starts_with($mdpStocke, '$2y$') ||
            str_starts_with($mdpStocke, '$2a$') ||
            str_starts_with($mdpStocke, '$2b$')
        ) {
            $motDePasseCorrect = Hash::check(
                $request->mdp,
                $mdpStocke
            );
        } else {
            $motDePasseCorrect = $request->mdp === $mdpStocke;
        }

        if (!$motDePasseCorrect) {
            return back()
                ->withInput($request->only('login'))
                ->withErrors([
                    'login' => 'Login ou mot de passe incorrect.',
                ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Création de la session
        |--------------------------------------------------------------------------
        */

        $request->session()->regenerate();

        session([
            'user_id'    => $utilisateur->id,
            'user_nom'   => $utilisateur->nom,
            'user_login' => $utilisateur->login,
            'user_role'  => $utilisateur->role,
            'id_prat'    => $utilisateur->id_prat,
        ]);

        /*
        |--------------------------------------------------------------------------
        | Redirection selon le rôle
        |--------------------------------------------------------------------------
        */

        return $this->redirectByRole($utilisateur->role);
    }

    /**
     * Redirection selon le rôle.
     */
    private function redirectByRole(?string $role)
    {
        /*
         * RH
         *
         * IMPORTANT :
         * plus de tableau de bord RH.
         * Le RH arrive directement sur les praticiens.
         */
        if ($role === 'RH') {
            return redirect()->route('praticiens.index');
        }

        /*
         * Responsable des commandes.
         */
        if ($role === 'responsable') {
            return redirect()
                ->route('responsable.commandes.index');
        }

        /*
         * Praticien.
         */
        if ($role === 'praticien') {
            return redirect()
                ->route('medicaments.index');
        }

        /*
         * Rôle inconnu.
         */
        session()->flush();

        return redirect()
            ->route('login')
            ->withErrors([
                'login' => 'Rôle utilisateur inconnu.',
            ]);
    }

    /**
     * Déconnexion.
     */
    public function logout(Request $request)
    {
        $request->session()->flush();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('login')
            ->with('success', 'Vous êtes maintenant déconnecté.');
    }
}
<?php

namespace App\Http\Middleware;

use App\Models\Utilisateur;
use Closure;
use Illuminate\Http\Request;

class ResponsableMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $utilisateur = Utilisateur::find(session('user_id'));

        if (!$utilisateur) {
            return redirect()->route('login');
        }

        if (!$utilisateur->estResponsable()) {
            return redirect()
                ->route($utilisateur->estRH() ? 'praticiens.index' : 'medicaments.index')
                ->with('error', 'Accès réservé au responsable des commandes.');
        }

        return $next($request);
    }
}

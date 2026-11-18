<?php

namespace App\Http\Middleware;

use App\Models\Utilisateur;
use Closure;
use Illuminate\Http\Request;

class RHMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $utilisateur = Utilisateur::find(session('user_id'));

        if (!$utilisateur) {
            return redirect()->route('login');
        }

        if (!$utilisateur->estRH()) {
            return redirect()
                ->route($utilisateur->estResponsable()
                    ? 'responsable.commandes.index'
                    : 'medicaments.index')
                ->with('error', 'Accès réservé au service RH.');
        }

        return $next($request);
    }
}

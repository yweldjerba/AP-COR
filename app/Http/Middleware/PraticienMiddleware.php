<?php

namespace App\Http\Middleware;

use App\Models\Utilisateur;
use Closure;
use Illuminate\Http\Request;

class PraticienMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $utilisateur = Utilisateur::find(session('user_id'));

        if (!$utilisateur) {
            return redirect()->route('login');
        }

        if (!$utilisateur->estPraticien()) {
            return redirect()
                ->route($utilisateur->estRH() ? 'praticiens.index' : 'responsable.commandes.index')
                ->with('error', 'Cette partie est réservée aux praticiens.');
        }

        return $next($request);
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Bloque l'accès aux pages si l'utilisateur n'a pas le bon rôle.
 * Utilisation dans les routes : ->middleware(VerifierRole::class . ':client')
 */
class VerifierRole
{
    public function handle(Request $request, Closure $next, string $role)
    {
        if (session('user_role') !== $role) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Accès refusé.'], 403);
            }

            return redirect()
                ->route('login')
                ->with('error', 'Veuillez vous connecter pour accéder à cette page.');
        }

        return $next($request);
    }
}

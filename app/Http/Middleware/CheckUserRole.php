<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class CheckUserRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
           // Verifica si el usuario está autenticado y es usuario
        if ( Auth::user()->role_id == 2) {
            return $next($request);
        }

        // Si no es administrador, redirige o muestra un mensaje de error
        return response()->json(['message' => 'Acceso denegado aun no es usuario.'], 403);
    }
}

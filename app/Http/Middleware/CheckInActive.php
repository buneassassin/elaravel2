<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

use Illuminate\Http\Request;
use App\Models\User;

class CheckInActive
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
        //sacamos el email del request
        $email = $request->input('email');
        if ($email==null) {
            $email = Auth::user()->email;
        }
        // Verifica si el usuario esta con el email 
        $user = User::where('email', $email)->first();    
        if ($user->is_inactive == true) {
            return $next($request);
        }
    

        // Si no es administrador, redirige o muestra un mensaje de error
        return response()->json(['message' => 'Acceso denegado Cuenta inactiva.'], 403);
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Log;

class RegistrarActividad
{
    /**
     * Maneja la solicitud entrante.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        $log = new Log();
        $log->user_id = auth()->check() ? auth()->user()->id : null;
        $log->verbo = $request->method();
        $log->ruta = $request->path();
        $log->dato = json_encode($request->all());
        $log->newdato = $response->getContent(); // Guarda la respuesta de la API
        $log->save();

        return $response;
    }
}

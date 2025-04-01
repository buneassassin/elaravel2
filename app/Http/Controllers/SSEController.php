<?php

namespace App\Http\Controllers;

use App\Models\Lector;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SSEController extends Controller
{
    public function streams()
    {
        return response()->stream(function () {
            // Un ejemplo simple de bucle infinito.
            while (true) {
                // Prepara los datos que deseas emitir (por ejemplo, una actualización de algún contador o notificación)
                $data = json_encode(['mensaje' => 'Hola, este es un evento SSE', 'tiempo' => now()->toDateTimeString()]);
                
                // Escribe el evento en el formato SSE (data: ... doble salto de línea)
                echo "data: {$data}\n\n";
                
                // Fuerza la liberación del búfer de salida
                @ob_flush();
                flush();

                // Pausa de 1 segundo entre cada evento
                sleep(1);
            }
        }, 200, [
            'Content-Type'  => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'Connection'    => 'keep-alive',
        ]);
    }
   
}

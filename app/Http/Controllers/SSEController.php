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
    public function stream()
    {
        // Set the appropriate headers for SSE
        $response = new StreamedResponse(function () {
            while (true) {
                // Your server-side logic to get data
                $data = json_encode(['message' => 'This is a message']);

                echo "data: $data\n\n";

                // Flush the output buffer
                ob_flush();
                flush();

                // Delay for 1 second
                sleep(1);
            }
        });

        $response->headers->set('Content-Type', 'text/event-stream');
        $response->headers->set('Cache-Control', 'no-cache');
        $response->headers->set('Connection', 'keep-alive');

        return $response;
    }
}

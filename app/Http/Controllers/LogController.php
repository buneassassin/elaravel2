<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Log;
use App\Models\User;

class LogController extends Controller
{
    public function index()
    {
        // Obtén todos los logs ordenados por fecha descendente
        $logs = Log::orderBy('fecha', 'desc')->get();

        return response()->json($logs);
    }

    public function show($id)
    {
        $log = Log::find($id);

        if (!$log) {
            return response()->json(['error' => 'Log no encontrado'], 404);
        }

        return response()->json($log);
    }

    public function obtenerLogs()
    {
        // Obtener todos los registros de MongoDB
        $logs = Log::all();

        // Agregar el nombre del usuario desde MySQL
        $logs->transform(function ($log) {
            $usuario = User::find($log->user_id); // Busca el usuario en MySQL
            $log->name = $usuario ? $usuario->name : 'Desconocido';
            $log->email = $usuario ? $usuario->email : 'Desconocido';
            return $log;
        });

        //json_decode(): Argument #1 ($json) must be of type string, array given
        return response()->json($logs);
    }
    public function obtenerLogs2()
    {
        // Obtener todos los registros de MongoDB
        $logs = Log::paginate(30);

        // Agregar el nombre del usuario desde MySQL
        $logs->transform(function ($log) {
            $usuario = User::find($log->user_id); // Busca el usuario en MySQL
            $log->name = $usuario ? $usuario->name : 'Desconocido';
            $log->email = $usuario ? $usuario->email : 'Desconocido';
            return $log;
        });

        //json_decode(): Argument #1 ($json) must be of type string, array given
        return response()->json([
            'success' => true,
            'message' => 'Logs obtenidos correctamente',
            'data' => $logs->items(),
            'pagination' => [
                'current_page' => $logs->currentPage(),
                'total' => $logs->total(),
                'per_page' => $logs->perPage(),
                'last_page' => $logs->lastPage(),
            ],

        ]);
    }
}

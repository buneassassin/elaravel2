<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Illuminate\Support\Facades\DB;


class AdminController extends Controller
{
    public function index()
    {
        return "admin";
    }
    public function activateUser(Request $request){

        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Los datos proporcionados son inválidos.',
            ], 422);
        }
        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json(['message' => 'El usuario no existe.'], 404);
        }
        if ($user->role_id == 2) {
            return response()->json(['message' => 'Ya es un usuario.'], 400);
        }
        if ($user->role_id == 3) {
            return response()->json(['message' => 'El usuario es admin.'], 400);
        }
        $user->role_id = 2;
        $user->save();

        return response()->json([
            'message' => 'Rol a usuario actualizado.',
        ]);

    }
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Los datos proporcionados son inválidos.',
            ], 422);
        }
        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json(['message' => 'El usuario no existe.'], 404);
        }
        if ($user->role_id == 3) {
            return response()->json(['message' => 'El usuario ya es admin.'], 400);
        }
        $user->role_id = 3;
        $user->save();

        return response()->json([
            'message' => 'Rol de usuario actualizado a admin exitosamente.',
        ]);
    }
    public function baja(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Los datos proporcionados son inválidos.',
            ], 422);
        }
        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json(['message' => 'El usuario no existe.'], 404);
        }
        if ($user->role_id == 3) {
            return response()->json(['message' => 'El usuario es admin.'], 400);
        }
        $user->is_Inactive=false;
        $user->save();

        return response()->json([
            'message' => 'Usuario dado de baja exitosamente.',
        ]);
    }
    public function jugadoresall()
    {
        //bucamos los jugadores con el rol 2
        $jugadores = DB::table('users')->where('role_id', 2)->get();
        return response()->json($jugadores);
    }
}

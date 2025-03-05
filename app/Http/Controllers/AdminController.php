<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;

class AdminController extends Controller
{

    public function index()
    {
        return "admin";
    }
    //isAdmin
    public function isAdmin()
    {
        $user = auth()->user();
        if ($user->role_id == 3) {
            return response()->json(['success' => true, 'message' => 'El usuario es admin.'], 200);
        }
        return response()->json(['message' => 'El usuario no es admin.'], 403);
    }
    public function getUsers()
    {
        $users = User::where('role_id', 2)->get();
        $guest = User::where('role_id',1)->get();
        $admin = User::where('role_id', 3)->get();
        return response()->json([
            'message' => 'Usuarios obtenidos correctamente.',
            'guest' => $guest,
            'users' => $users,
            'admin' => $admin,
        ], 200);
    }

    public function activateUser(Request $request)
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
    //dar de vaja a un usuario
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
        $user->is_Inactive = false;
        $user->save();

        return response()->json([
            'message' => 'Usuario dado de baja exitosamente.',
        ]);
    }
}

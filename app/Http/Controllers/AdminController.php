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
    public function isUserGeneral()
    {
        $user = User::with([])->paginate(10);
        return response()->json(['success' => true, 'message' => 'El usuario es general.', 'data' => $user->items(), 'pagination' => [
            'current_page' => $user->currentPage(),
            'total' => $user->total(),
            'per_page' => $user->perPage(),
            'last_page' => $user->lastPage(),
        ]], 200);
    }
    public function getUsers()
    {
        $users = User::where('role_id', 2)->get();
        $guest = User::where('role_id', 1)->get();
        $admin = User::where('role_id', 3)->get();
        return response()->json([
            'message' => 'Usuarios obtenidos correctamente.',
            'guest' => $guest,
            'users' => $users,
            'admin' => $admin,
        ], 200);
    }
    //obtenerRol
    public function obtenerRol()
    {
        //mosrtrar los roles que hay
        return response()->json([
            'message' => 'Roles obtenidos correctamente.',
            'roles' => ['guest', 'user', 'admin'],
        ], 200);
    }
    public function cambiarRol(Request $request){
        $validator = Validator::make($request->all(), [
            'rol' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Los datos proporcionados son inválidos.',
            ], 422);
        }
        //por si nos mada id de ves de correo
        if ($request->id){
            $user = User::where('id', $request->id)->first();
        }else if ($request->email){
            $user = User::where('email', $request->email)->first();
        }else{
            return response()->json(['message' => 'Proporciona el correo o el id.'], 404);
        }

        if (!$user) {
            return response()->json(['message' => 'El usuario no existe.'], 404);
        }
        if ($user->role_id == 3) {
            return response()->json(['message' => 'El usuario es admin.'], 400);
        }
        $user->role_id = $request->rol;
        if ($user->role_id == 1) {
            $user->is_active = false;
            $activationCode = rand(100000, 999999);

            $user->activation_token = $activationCode;
        }
        if ($user->role_id == 2) {
            $user->is_active = true;
            $user->activation_token = null;
        }
        if($user->role_id == 3) {
            $user->is_active = true;
            $user->activation_token = null;
        }
        $user->save();

        return response()->json([
            'message' => 'Rol actualizado.',
        ]);
    }
    public function showUser($id){
        $user = User::where('id', $id)->first();
        if (!$user) {
            return response()->json(['message' => 'El usuario no existe.'], 404);
        }
        return response()->json([
            'message' => 'Usuario obtenido correctamente.',
            'user' => $user
        ]);
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
        $user->is_inactive = false;
        $user->save();

        return response()->json([
            'message' => 'Usuario dado de baja exitosamente.',
        ]);
    }
    public function desactivarUsuario(Request $request)
    {
        // Validación: solo requerir el formato correcto de email
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()
            ], 400);
        }

        // Buscar el usuario por email
        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json([
                'message' => 'User not found'
            ], 404);
        }
        // Verificar si el usuario ya esta desactivado
        if ($user->is_inactive == false) {
            return response()->json(['message' => 'El usuario ya esta desactivado.'], 400);
        }

        // Desactivar al usuario (cambiar el campo `is_Inactive`)
        $user->is_inactive = false;  // Asumo que quieres desactivar el usuario, se cambió a `true`
        $user->save();

        return response()->json(['message' => 'Usuario desactivado correctamente.'], 200);
    }
    public function activarUsuario(Request $request)
    {
        // Validación: solo requerir el formato correcto de email
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()
            ], 400);
        }

        // Buscar el usuario por email
        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json([
                'message' => 'User not found'
            ], 404);
        }
        // Verificar si el usuario ya esta activado
        if ($user->is_inactive == true) {
            return response()->json(['message' => 'El usuario ya esta activado.'], 400);
        }

        // Activar al usuario (cambiar el campo `is_active`)
        $user->is_inactive = true;  // Asumo que quieres desactivar el usuario, se cambió a `true`
        $user->save();

        return response()->json(['message' => 'Usuario activado correctamente.'], 200);
    }
}

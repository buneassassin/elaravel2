<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;

class AuthController extends Controller
{
    public function register_sanctum(Request $request)
    {
        // Validar los datos de entrada
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'device_name' => 'required|string|max:255',
        ]);

        // Crear un nuevo usuario
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password), // Encriptar la contraseña
        ]);

        // Crear un token de acceso personal
        $token = $user->createToken($request->device_name)->plainTextToken;

        // Devolver la respuesta
        return response()->json([
            'user' => $user,
            'token' => $token,
        ], 201);
    }

    public function login_sanctum(Request $request)
    {
        // Validar los datos de entrada
        $request->validate([
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:6',
            'device_name' => 'required|string|max:255',
        ]);

        // Autenticar al usuario
        $user = User::where('email', $request->email)->first();
        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json(['error' => 'Credenciales inválidas'], 401);
        }        

        // Crear un token de acceso personal        
        $token = $user->createToken($request->device_name)->plainTextToken;

        // Devolver la respuesta
        return response()->json([
            'user' => $user,
            'token' => $token,
        ]);
    } 
    public function me(Request $request){
        $user = $request->user();
        return response()->json($user); 
    }  



    
}

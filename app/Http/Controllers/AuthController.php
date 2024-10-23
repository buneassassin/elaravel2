<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Illuminate\Support\Facades\Http;

class AuthController extends Controller
{
    public function register_sanctum(Request $request)
    {
      
        $register = Http::withOptions([
            'verify' => false,
        ])->post('http://192.168.116.70:5400/register', [                         
            'email' => $request->input('email'),
            'password' => $request->input('password'),
        ]);
        $tobias=$register->json();
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
            'Tobias'=>$tobias
        ], 201);
    }

    public function login_sanctum(Request $request)
    {
        $register = Http::withOptions([
            'verify' => false,
        ])->post('http://192.168.116.70:5400/login', [
            'email' => $request->input('email'),
            'password' => $request->input('password'),
        ]);
        //devolvemos el puro token
        $tokenss = $register->json()['token'];
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
            'token_3' => $token,
            'token_4'=> $tokenss
       
        ], 201);
    } 

    public function me(Request $request){
        $user = $request->user();
        return response()->json($user); 
    }  

    public function getTokensByEmail(Request $request)
    {
        // Validar que el campo 'email' esté presente en el cuerpo
        $request->validate([
            'email' => 'required|email',
        ]);

        // Obtener el correo electrónico del cuerpo de la solicitud
        $email = $request->input('email');

        // Buscar el usuario por correo
        $user = User::where('email', $email)->first();

        // Si no se encuentra el usuario, devolver un error
        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        // Obtener los tokens asociados al usuario
        $tokens = $user->tokens; // Asegúrate de que esto está relacionado correctamente

        // Si no hay tokens asociados, devolver un mensaje adecuado
        if ($tokens->isEmpty()) {
            return response()->json(['message' => 'No tokens found for this user'], 404);
        }

        // Preparar la respuesta en el formato deseado
        $tokensResponse = $tokens->map(function ($token) use ($user) {
            return [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'email_verified_at' => $user->email_verified_at,
                    'created_at' => $user->created_at,
                    'updated_at' => $user->updated_at,
                ],
                'token' => "{$token->id}|{$token->token}", // Asegúrate de que la propiedad token existe en tu modelo
            ];
        });

        // Retornar la respuesta en formato JSON
        return response()->json($tokensResponse);
    }
    
}

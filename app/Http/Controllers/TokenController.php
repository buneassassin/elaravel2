<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use App\Models\Token;


class TokenController extends Controller
{
    public function store(Request $request)
    {
        // Valida los campos
        $request->validate([
            'token3' => 'required|string',
            'token4' => 'required|string',
        ]);

        // Actualiza el token si el correo existe, de lo contrario lo crea
        Token::updateOrCreate(
            ['token3' => $request->token3],  // Condición para buscar el registro
            ['token4' => $request->token4]   // Datos a actualizar o crear
        );

        return response()->json(['message' => 'Token updated or created successfully!']);
    }
    // Método para mostrar el token buscando por correo
    public function show(Request $request)
    {
        // Valida que el correo esté presente
        $request->validate([
            'token3' => 'required|string',
        ]);

        // Busca el token por correo
        $token = Token::where('token3', $request->token3)->first();
        //solo devolvemos el token 
        $token = $token->token4;

        // Si no se encuentra el token
        if (!$token) {
            return response()->json(['message' => 'Token not found for this email'], 404);
        }

        // Retorna el token encontrado
        return response()->json([ 'data' => $token]);
    }
}

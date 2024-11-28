<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class Juego extends Controller
{
    public function game()
    {
        $id_user = auth()->user()->id;

        $response = Http::post(
            env('API_URL') . '/game',
            [
                'userid' => $id_user

            ]
        );

        return $response->json();
    }
    public function join($id)
    {
        $id_user = auth()->user()->id;
        $response = Http::post(
            env('API_URL') . '/join/' . $id,
            [
                'userid' => $id_user
            ]
        );

        return $response->json();
    }
    public function barcos($id, Request $request)
    {
        $id_user = auth()->user()->id;

        // Datos de los barcos (pueden venir también del request si es necesario)
        $ships = $request->input('ships'); // Los barcos enviados en el cuerpo del request

        // Realiza la solicitud POST a la API externa con los datos dinámicos
        $response = Http::post(
            env('API_URL') . '/barcos/' . $id,
            [
                'userid' => $id_user,  // Usamos el id_user enviado en el request
                'ships' => $ships       // Los barcos también provienen del request
            ]
        );

        // Devuelve la respuesta de la API externa como JSON
        return $response->json();
    }

    public function atacar($id, Request $request)
    {
        // Obtén el id_user del usuario autenticado
        $id_user = auth()->user()->id;

        // Obtener las coordenadas 'x' y 'y' del cuerpo de la solicitud
        $x = $request->input('x');
        $y = $request->input('y');

        // Realiza la solicitud POST con los datos apropiados
        $response = Http::post(
            env('API_URL') . '/atacar/' . $id,
            [
                'userid' => $id_user,  // El id del usuario autenticado
                'x' => $x,              // Coordenada x
                'y' => $y               // Coordenada y
            ]
        );

        // Devuelve la respuesta de la API externa como JSON
        return $response->json();
    }

    public function abandonar($id)
    {
        $id_user = auth()->user()->id;
        $response = Http::post(
            env('API_URL') . '/abandonar/' . $id,
            [
                'userid' => $id_user
            ]
        );

        return $response->json();
    }
    public function consultaratakes($id)
    {
        $id_user = auth()->user()->id;
        $response = Http::post(
            env('API_URL') . '/consultaratakes/' . $id,
            [
                'userid' => $id_user
            ]
        );

        return $response->json();
    }
    public function consultar($id)
    {
        $id_user = auth()->user()->id;
        $response = Http::post(
            env('API_URL') . '/consultar/' . $id,
            [
                'userid' => $id_user
            ]
        );

        return $response->json();
    }
    public function partidosjuego()
    {
        $id_user = auth()->user()->id;
        $response = Http::post(
            env('API_URL') . '/partidosjuego',
            [
                'userid' => $id_user
            ]
        );

        return $response->json();
    }
    public function listGames(){
        $id_user = auth()->user()->id;
        $response = Http::get(
            env('API_URL') . '/gamesview',
            [
                'userid' => $id_user
            ]
        );

        return $response->json();
    }
    public function showGame( $id){
        $id_user = auth()->user()->id;
        $response = Http::get(
            env('API_URL') . '/gamesview/' . $id,
            [
                'userid' => $id_user
            ]
        );

        return $response->json();
    }

}

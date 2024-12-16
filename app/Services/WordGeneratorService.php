<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class WordGeneratorService
{
    public function generateRandomWord()
    {
        // Seleccionar aleatoriamente una longitud de palabra entre 4, 5 y 6
        $lengthOptions = [4, 5, 6];
        $randomLength = $lengthOptions[array_rand($lengthOptions)];

        // Construir la URL con el parámetro de longitud aleatoria
        $apiUrl = "https://random-word-api.herokuapp.com/word?lang=es&length={$randomLength}";

        // Hacer la solicitud a la API
        $response = Http::get($apiUrl);

        if ($response->successful()) {
            $words = $response->json();
            $randomWord = $words[0] ?? '';

            if (!empty($randomWord)) {
                // Eliminar acentos y espacios
                $randomWord = $this->removeAccents($randomWord);
                $randomWord = $this->removeSpaces($randomWord);

                return $randomWord;
            } else {
                throw new \Exception('Respuesta de la API no contiene una palabra válida');
            }
        } else {
            throw new \Exception('Error al conectar con la API');
        }
    }

    private function removeAccents($word)
    {
        $accents = [
            'á' => 'a',
            'é' => 'e',
            'í' => 'i',
            'ó' => 'o',
            'ú' => 'u',
            'Á' => 'A',
            'É' => 'E',
            'Í' => 'I',
            'Ó' => 'O',
            'Ú' => 'U',
            'ñ' => 'n',
            'Ñ' => 'N'
        ];
        return str_replace(array_keys($accents), array_values($accents), $word);
    }

    private function removeSpaces($word)
    {
        // Reemplazar todos los espacios (incluidos los múltiples) con una cadena vacía
        return preg_replace('/\s+/', '', $word);
    }
}

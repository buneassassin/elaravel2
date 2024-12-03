<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\User;
use Illuminate\Http\Request;
use Twilio\Rest\Client;
use Twilio\Http\CurlClient;

class GameController extends Controller
{
    public function sendLetterMessage($message)
    {
        $sid    = env('TWILIO_SID');
        $token  = env('TWILIO_AUTH_TOKEN');
        $from   = "whatsapp:+14155238886";
        $to     = "whatsapp:+5218714307468";

        try {
            // Configurar las opciones cURL para ignorar la validación SSL (solo pruebas locales)
            $options = [
                CURLOPT_SSL_VERIFYPEER => false, // Deshabilitar validación del certificado
                CURLOPT_SSL_VERIFYHOST => 0,    // No verificar el nombre del host
            ];
            $httpClient = new CurlClient($options);

            // Crear el cliente de Twilio
            $twilio = new Client($sid, $token);

            // Configurar el cliente HTTP
            $twilio->setHttpClient($httpClient);

            // Enviar el mensaje
            $message = $twilio->messages->create(
                $to,
                [
                    "from" => $from,
                    "body" => "$message"
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Mensaje enviado exitosamente',
                'sid' => $message->sid
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ]);
        }
    }
    public function userHistory()
    {
        // Obtener el ID del usuario autenticado
        $userId = auth()->user()->id;
    
        // Verificar si el usuario está autenticado
        if (!$userId) {
            return response()->json(['message' => 'No tienes un juego activo.'], 400);
        }
    
        // Obtener solo los juegos completados del usuario
        $games = Game::where('user_id', $userId)
                     ->where('is_completed', true) // Filtrar solo juegos completados
                     ->get();
    
        // Si no hay juegos en el historial
        if ($games->isEmpty()) {
            return response()->json(['message' => 'No se encontraron juegos en el historial.'], 404);
        }
    
        // Formatear la respuesta con más detalles de los juegos
        $gamesHistory = $games->map(function ($game) {
            return [
                'game_id' => $game->id,
                'masked_word' => $game->masked_word,  // Solo mostrar la palabra enmascarada
                'attempts' => $game->attempts,
                'max_attempts' => $game->max_attempts,
                'is_completed' => $game->is_completed ? 'Sí' : 'No',
                'is_won' => $game->is_won ? 'Ganado' : 'Perdido',
                'date' => $game->created_at->format('d-m-Y H:i:s'),
            ];
        });
    
        return response()->json([
            'message' => 'Historial de juegos completados del usuario.',
            'games' => $gamesHistory
        ]);
    }
    public function generateRandomWord($length)
    {
        // Array con palabras en español
        $palabras = [
            'amor',
            'feliz',
            'sol',
            'noche',
            'día',
            'cielo',
            'agua',
            'fuego',
            'tierra',
            'luz',
            'viento',
            'alegría',
            'trabajo',
            'familia',
            'amistad',
            'música',
            'paz',
            'esperanza',
            'rojo',
            'verde',
            'azul',
            'amarillo',
            'luna',
            'estrella',
            'risa',
            'tristeza',
            'fuerza',
            'valor',
            'libertad',
            'coraje',
            'pueblo',
            'ciudad',
            'país',
            'comida',
            'bebida',
            'fútbol',
            'balón',
            'coche',
            'camisa',
            'zapato',
            'gato',
            'perro',
            'elefante',
            'león',
            'tigre',
            'pájaro',
            'pez',
            'flor',
            'árbol',
            'montaña',
            'río',
            'carrera',
            'universidad',
            'escuela',
            'profesor',
            'estudiante',
            'clase',
            'examen',
            'sabio',
            'inteligente',
            'tonto',
            'amigo',
            'enemigo',
            'profundo',
            'superficial',
            'rápido',
            'lento',
            'alto',
            'bajo',
            'grande',
            'pequeño',
            'nuevo',
            'viejo',
            'rico',
            'pobre',
            'justo',
            'injusto',
            'honesto',
            'mentiroso',
            'cansado',
            'fresco',
            'caliente',
            'frío',
            'sucio',
            'limpio',
            'oscuro',
            'claro',
            'cerca',
            'lejos',
            'frente',
            'detrás',
            'izquierda',
            'derecha',
            'arriba',
            'abajo',
            'delante',
            'detrás',
            'madre',
            'padre',
            'hermano',
            'hermana',
            'abuelo',
            'abuela',
            'hijo',
            'hija',
            'tío',
            'tía',
            'primo',
            'prima',
            'hijo',
            'hija',
            'esposo',
            'esposa',
            'trabajo',
            'dinero',
            'empresa',
            'negocio',
            'café',
            'té',
            'cerveza',
            'vino',
            'jugo',
            'comida',
            'postre',
            'ensalada',
            'pan',
            'fruta',
            'verdura',
            'pollo',
            'carne',
            'pescado',
            'arroz',
            'pasta',
            'sopa',
            'hamburguesa',
            'pizza',
            'sándwich',
            'tortilla',
            'guiso',
            'asado',
            'hamburguesa',
            'frito',
            'grillado',
            'vapor',
            'salado',
            'dulce',
            'ácido',
            'amargo',
            'suave',
            'fuerte',
            'picante',
            'delicioso',
            'rico'
        ];

        // Generar una palabra aleatoria del array
        $randomWord = $palabras[array_rand($palabras)];

        // Si la longitud de la palabra generada es menor a la deseada, agregamos letras
        while (strlen($randomWord) < $length) {
            $randomWord .= $palabras[array_rand($palabras)];
        }

        // Recortar la palabra si excede la longitud deseada
        return substr($randomWord, 0, $length);
    }
    public function availableGames()
    {
        // Obtener todos los juegos activos (no completados y con palabra asignada)
        $games = Game::where('is_completed', false)
                     ->whereNotNull('word')  // Asegurarse de que el juego tenga una palabra asignada
                     ->get(['id', 'masked_word', 'attempts', 'max_attempts', 'is_completed', 'is_won']);  // Excluimos 'word' de la respuesta
    
        // Si no hay juegos disponibles
        if ($games->isEmpty()) {
            return response()->json([
                'message' => 'No hay juegos disponibles para jugar en este momento.'
            ], 404);
        }
    
        // Devolver los juegos disponibles sin mostrar la palabra original
        return response()->json([
            'message' => 'Juegos disponibles para jugar.',
            'games' => $games
        ]);
    }
    public function createGame()
    {
        $userId = auth()->user()->id;
    
        // Validar si ya tiene un juego activo (con una palabra seleccionada)
        $activeGame = Game::where('user_id', $userId)
                          ->where('is_completed', false)
                          ->whereNotNull('attempted_letters')  // Verificamos que ya haya una palabra seleccionada
                          ->first();
        
        // Si el juego tiene una palabra seleccionada, no se puede crear otro
        if ($activeGame) {
            return response()->json([
                'message' => 'Ya tienes un juego en curso.',
                'game' => [
                    'id' => $activeGame->id,
                    'masked_word' => $activeGame->masked_word,
                    'attempts' => $activeGame->attempts,
                    'max_attempts' => $activeGame->max_attempts,
                    'is_completed' => $activeGame->is_completed,
                    'is_won' => $activeGame->is_won,
                ]
            ], 400);
        }
    
        // Crear un nuevo juego si no existe ninguno activo
        $wordLength = rand(5, 10);  // Longitud aleatoria entre 5 y 10
        $word = $this->generateRandomWord($wordLength);
    
        // Crear el nuevo juego
        $game = Game::create([
            'user_id' => $userId,
            'word' => $word,  // Se asigna una palabra generada
            'masked_word' => str_repeat('_', strlen($word)),
            'attempts' => 0,
            'max_attempts' => env('GAME_MAX_ATTEMPTS'),
        ]);
    
        return response()->json([
            'message' => 'Juego creado',
            'game' => [
                'id' => $game->id,
                'masked_word' => $game->masked_word,
                'attempts' => $game->attempts,
                'max_attempts' => $game->max_attempts,
                'is_completed' => $game->is_completed,
                'is_won' => $game->is_won,
            ]
        ]);
    }
    public function guess(Request $request, $id)
    {
        try {
            $request->validate([
                'letter' => ['required', 'alpha', 'size:1'], // Validar solo una letra
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Debes enviar una sola letra válida.',
                'errors' => $e->errors(),
            ], 400);
        }

        $game = Game::findOrFail($id);

        if ($game->is_completed) {
            return response()->json(['message' => 'El juego ya terminó.'], 400);
        }

        // Obtener el número máximo de intentos desde el archivo .env
        $maxAttempts = env('GAME_MAX_ATTEMPTS', 6); // Valor por defecto de 6 si no está en .env

        $letter = strtolower($request->letter);
        $word = $game->word;
        $maskedWord = $game->masked_word;
        $attemptedLetters = $game->attempted_letters ? explode(',', $game->attempted_letters) : [];

        if (in_array($letter, $attemptedLetters)) {
            return response()->json([
                'message' => 'Ya intentaste esta letra.',
                'attempted_letters' => $attemptedLetters,
            ], 400);
        }

        $attemptedLetters[] = $letter;
        $game->attempted_letters = implode(',', $attemptedLetters);

        if (strpos($word, $letter) !== false) {
            $this->sendLetterMessage( "¡Correcto! La letra '{$letter}' está en la palabra.");

            // Actualizar la palabra enmascarada
            for ($i = 0; $i < strlen($word); $i++) {
                if ($word[$i] === $letter) {
                    $maskedWord[$i] = $letter;
                }
            }
            $game->masked_word = $maskedWord;

            if ($maskedWord === $word) {
                $game->is_completed = true;
                $game->is_won = true;

                $this->sendLetterMessage( "¡Ganaste! La palabra era '{$word}'.");
            }
        } else {
            $game->attempts++;

            if ($game->attempts >= $maxAttempts) {
                $game->is_completed = true;
                $game->is_won = false;

                $this->sendLetterMessage( "¡Perdiste! La palabra era '{$word}'.");
            } else {
                $remainingAttempts = $maxAttempts - $game->attempts;
                $this->sendLetterMessage( "La letra '{$letter}' no está en la palabra. Te quedan {$remainingAttempts} intentos.");
            }
        }

        $game->save();

        return response()->json([
            'message' => $game->is_completed
                ? ($game->is_won ? '¡Ganaste!' : '¡Perdiste!')
                : 'Sigue jugando.',
            'game' => [
                'id' => $game->id,
                'masked_word' => $game->masked_word,
                'attempts' => $game->attempts,
                'max_attempts' => $maxAttempts,  // Usar el valor actualizado desde el .env
                'is_completed' => $game->is_completed,
                'is_won' => $game->is_won,
            ],
            'attempted_letters' => $attemptedLetters,
        ]);
    }
    public function abandonGame($id)
    {
        $userId = auth()->user()->id;
        //validamos si es su juego
        $game = Game::where('user_id', $userId)->where('id', $id)->first();
        if (!$game) {
            return response()->json(['message' => 'No tienes un juego activo.'], 400);
        }
        $game = Game::findOrFail($id);

        if ($game->is_completed) {
            return response()->json(['message' => 'El juego ya terminó.'], 400);
        }

        $game->is_completed = true;
        $game->is_won = false;
        $game->save();

        return response()->json([
            'message' => 'Has abandonado el juego. Se ha marcado como perdido.',
            'game' => $game
        ]);
    }
    public function adminReport()
    {
        $games = Game::all();
        $totalGames = $games->count();
        $wonGames = $games->where('is_won', true)->count();
        $lostGames = $games->where('is_won', false)->count();

        return response()->json([
            'total_games' => $totalGames,
            'won_games' => $wonGames,
            'lost_games' => $lostGames,
        ]);
    }
    public function status($id)
    {
        $game = Game::findOrFail($id);

        // Calcular letras intentadas
        $attemptedLetters = $game->attempted_letters ? explode(',', $game->attempted_letters) : [];

        // Calcular intentos restantes
        $remainingAttempts = $game->max_attempts - $game->attempts;

        // Preparar respuesta clara
        $response = [
            'status' => $game->is_completed ? ($game->is_won ? 'Ganaste' : 'Perdiste') : 'En progreso',
            'masked_word' => $game->masked_word,
            'remaining_attempts' => $remainingAttempts,
            'attempted_letters' => $attemptedLetters,
            'is_completed' => $game->is_completed,
            'is_won' => $game->is_won,
            'original_word' => $game->is_completed ? $game->word : null, // Mostrar solo si el juego terminó
        ];

        return response()->json($response);
    }
}

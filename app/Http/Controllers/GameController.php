<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use App\Models\Game;
use App\Models\User;
use Illuminate\Http\Request;
use Twilio\Rest\Client;
use Twilio\Http\CurlClient;
use App\Jobs\SendGameSummaryToSlack;
use App\Services\TwilioService;
use App\Services\WordGeneratorService;

class GameController extends Controller
{
    protected $twilioService;
    protected $wordGeneratorService;

    public function __construct(TwilioService $twilioService, WordGeneratorService $wordGeneratorService)
    {
        $this->twilioService = $twilioService;
        $this->wordGeneratorService = $wordGeneratorService;
    }
    public function sendLetterMessage($message)
    {
        $to = "whatsapp:+5218714307468";
        $result = $this->twilioService->sendMessage($to, $message);

        return response()->json($result);
    }
    public function generateRandomWord()
    {
        return $this->wordGeneratorService->generateRandomWord();
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
        //  $wordLength = rand(5, 10);  // Longitud aleatoria entre 5 y 10
        $word = $this->generateRandomWord();

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
        // Validar el ID del juego que si existe
        if (!Game::where('id', $id)->exists()) {
            return response()->json(['message' => 'Juego no encontrado.'], 404);
        }
        

        $game = Game::findOrFail($id);
        $userId = auth()->user()->id;

        // Validar si ya tiene un juego activo (con una palabra seleccionada)
        $activeGame = Game::where('user_id', $userId)
            ->where('is_completed', false)
            ->whereNotNull('attempted_letters')  // Verificamos que ya haya una palabra seleccionada
            ->first();

        // Si el juego tiene una palabra seleccionada, no se puede crear otro
        if ($activeGame && $activeGame->id !== $game->id) {
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
        $Juego = Game::where('user_id', $userId)->where('id', $id)->first();
        if (!$Juego) {
            return response()->json(['message' => 'No tienes un juego activo.'], 400);
        }
        if ($game->is_completed) {
            return response()->json(['message' => 'El juego ya terminó.'], 400);
        }

        try {
            $request->validate([
                'letter' => ['required', 'alpha', 'size:1'], // Validar solo una letra
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Datos inválidos.',
                'errors' => $e->errors(),
            ], 400);
        }
        // Verificar si el jugador tiene un juego en curso (activo) sin haberlo completado
        $activeGame = game::where('user_id', $userId)
            ->where('is_completed', false)  // Verifica que el juego no esté completado
            ->whereNotNull('word')  // Verifica que haya una palabra seleccionada
            //verefica que haya attempts_used sea > 0
            ->where('attempts', '>', 0)
            ->first();

        if ($activeGame && $activeGame->id != $id) {
            return response()->json([
                'message' => 'Ya tienes un juego en curso. Termina tu juego actual antes de crear uno nuevo.',
                'juego_id' => $activeGame->id
            ], 400);
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
            $this->sendLetterMessage("¡Correcto! La letra '{$letter}' está en la palabra.");

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

                $summaryMessage = "¡Felicidades! Has ganado el juego de adivinar la palabra. La palabra era: {$word}. Letras intentadas: " . implode(', ', $attemptedLetters);
                SendGameSummaryToSlack::dispatch($game, $summaryMessage);

                $this->sendLetterMessage("¡Ganaste! La palabra era '{$word}'.");
            }
        } else {
            $game->attempts++;

            if ($game->attempts >= $maxAttempts) {
                $game->is_completed = true;
                $game->is_won = false;

                $summaryMessage = "¡Lo siento! Has perdido el juego de adivinar la palabra. La palabra era: {$word}. Letras intentadas: " . implode(', ', $attemptedLetters);
                SendGameSummaryToSlack::dispatch($game, $summaryMessage);

                $this->sendLetterMessage("¡Perdiste! La palabra era {$word}.");
            } else {
                $remainingAttempts = $maxAttempts - $game->attempts;
                $this->sendLetterMessage("La letra '{$letter}' no está en la palabra. Te quedan {$remainingAttempts} intentos.");
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
    public function availableGames()
    {

        $games = Game::where('is_completed', false)
            ->whereNotNull('word')
            ->where('user_id', auth()->user()->id)
            ->get(['id', 'masked_word', 'attempts', 'max_attempts', 'is_completed', 'is_won']);  // Excluimos 'word' de la respuesta

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
        $summaryMessage = "Has abandonado el juego de adivinar la palabra.";
        SendGameSummaryToSlack::dispatch($game, $summaryMessage);

        $this->sendLetterMessage("Has abandonado el juego.");
        return response()->json([
            'message' => 'Has abandonado el juego. Se ha marcado como perdido.',
            'game' => $game
        ]);
    }
    public function status($id)
    {
        if (!Game::where('id', $id)->exists()) {
            return response()->json(['message' => 'Juego no encontrado.'], 404);
        }
        $game = Game::findOrFail($id);

        // Validar si el juego es del mismo usuario
        if ($game->user_id !== auth()->user()->id) {
            return response()->json(['message' => 'No tienes acceso a este juego.'], 403);
        }

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
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Juego;
use App\Models\Attempt;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Twilio\Rest\Client;
use Twilio\Http\CurlClient;
use Illuminate\Support\Facades\Validator;
use App\Jobs\SendGameSummaryToSlack;
use App\Services\TwilioService;
use App\Services\WordGeneratorService;

class WordleController extends Controller
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
    public function createJuego()
    {
        $userId = auth()->user()->id;

        // Verificar si el jugador tiene un juego en curso (activo) sin haberlo completado
        $activeGame = Juego::where('user_id', $userId)
            ->where('is_completed', false)  // Verifica que el juego no esté completado
            ->whereNotNull('word')  // Verifica que haya una palabra seleccionada
            //verefica que haya attempts_used sea > 0
            ->where('attempts_used', '>', 0)
            ->first();

        if ($activeGame) {
            return response()->json([
                'message' => 'Ya tienes un juego en curso. Termina tu juego actual antes de crear uno nuevo.',
                'juego_id' => $activeGame->id
            ], 400);
        }

        // Si no existe un juego activo, permite crear uno nuevo

        $word = $this->generateRandomWord();

        // Crear una nueva partida
        $Juego = Juego::create([
            'user_id' => $userId,
            'word' => $word,  // Palabra seleccionada para la nueva partida
            'is_completed' => false,  // El juego empieza como no completado
            'attempts_used' => 0,  // Número de intentos inicializado en 0
            'is_won' => false,  // El juego no ha sido ganado aún
        ]);

        return response()->json([
            'message' => 'Partida creada con éxito.',
            'juego_id' => $Juego->id
        ], 201);
    }
    public function makeAttempt(Request $request, $id)
    {
        //validamos si el usario le pertenece el juego
        $userId = auth()->user()->id;
        // Verificar si el jugador tiene un juego en curso (activo) sin haberlo completado
        $activeGame = Juego::where('user_id', $userId)
            ->where('is_completed', false)  // Verifica que el juego no esté completado
            ->whereNotNull('word')  // Verifica que haya una palabra seleccionada
            //verefica que haya attempts_used sea > 0
            ->where('attempts_used', '>', 0)
            ->first();

        if ($activeGame && $activeGame->id != $id) {
            return response()->json([
                'message' => 'Ya tienes un juego en curso. Termina tu juego actual antes de crear uno nuevo.',
                'juego_id' => $activeGame->id
            ], 400);
        }
        $Juego = Juego::where('user_id', $userId)->where('id', $id)->first();
        if (!$Juego) {
            return response()->json(['message' => 'No tienes un juego activo.'], 400);
        }
        // Validar la palabra proporcionada
        $validator = Validator::make($request->all(), [
            'word_attempted' => 'required|string', // Se valida que tenga exactamente 5 caracteres
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'La palabra debe tener exactamente 5 letras.', 'errors' => $validator->errors()], 400);
        }


        // Verificar si el jugador tiene un juego en curso (activo) sin haberlo completado
        $activeGame = Juego::where('user_id', $userId)
            ->where('is_completed', false)  // Verifica que el juego no esté completado
            ->whereNotNull('word')  // Verifica que haya una palabra seleccionada
            //verefica que haya attempts_used sea > 0
            ->where('attempts_used', '>', 0)
            ->first();

        if ($activeGame && $activeGame->id != $id) {
            return response()->json([
                'message' => 'Ya tienes un juego en curso. Termina tu juego actual antes de crear uno nuevo.',
                'juego_id' => $activeGame->id
            ], 400);
        }
        $Juego = Juego::findOrFail($id);

        // Verificar si la partida ya está terminada
        if ($Juego->is_completed) {
            $this->sendLetterMessage("La partida ya ha terminado.");
            return response()->json(['message' => 'La partida ya ha terminado.'], 400);
        }

        // Obtener el máximo de intentos desde el archivo .env
        $maxAttempts = env('MAX_ATTEMPTS', 5);

        // Validar si ya se alcanzaron el máximo de intentos
        if ($Juego->attempts_used >= $maxAttempts) {
            $Juego->is_completed = true;
            $Juego->is_won = false;
            $Juego->save();
            $this->sendLetterMessage("Has alcanzado el límite de intentos. La palabra era '{$Juego->word}'.");

            return response()->json(['message' => 'Has alcanzado el límite de intentos. La palabra era: ' . $Juego->word], 400);
        }

        $attempt = strtoupper($request->word_attempted); // Convertir a mayúsculas
        $word = strtoupper($Juego->word); // Asegurar que ambas palabras estén en mayúsculas
        $feedback = [];

        // Validación de longitud para evitar errores
        if (strlen($attempt) !== strlen($word)) {
            return response()->json(['message' => 'Error: Las palabras deben tener ' . strlen($word) . ' caracteres.'], 400);
        }

        // Generar feedback de las letras
        $feedbackMessage = "Estado del intento: ";
        for ($i = 0; $i < strlen($word); $i++) {
            if (isset($attempt[$i]) && $word[$i] === $attempt[$i]) {
                $feedback[] = ['letter' => $attempt[$i], 'status' => 'correcto'];
                $feedbackMessage .= "{$attempt[$i]} correcto, ";
            } elseif (isset($attempt[$i]) && strpos($word, $attempt[$i]) !== false) {
                $feedback[] = ['letter' => $attempt[$i], 'status' => 'mal colocado'];
                $feedbackMessage .= "{$attempt[$i]} mal colocado, ";
            } else {
                $feedback[] = ['letter' => $attempt[$i] ?? '', 'status' => 'incorrecto'];
                $feedbackMessage .= "{$attempt[$i]} incorrecto, ";
            }
        }

        // Eliminar la última coma
        $feedbackMessage = rtrim($feedbackMessage, ', ');

        // Incrementar intentos utilizados
        $Juego->attempts_used++;

        // Calcular intentos restantes después de incrementar
        $attemptsRemaining = $maxAttempts - $Juego->attempts_used;

        // Verificar si el juego termina
        if ($attempt === $word) {
            $Juego->is_completed = true;
            $Juego->is_won = true;

            // Formatear intentos para el usuario
            $attemptsArray = json_decode($Juego->attempts, true);
            $formattedAttempts = array_map(function ($attempt) {
                $feedbackArray = json_decode($attempt['feedback'], true);
                $feedbackString = implode(", ", array_map(function ($feedback) {
                    return "{$feedback['letter']} ({$feedback['status']})";
                }, $feedbackArray));
                return "{$attempt['word_attempted']} => $feedbackString";
            }, $attemptsArray);

            $summaryMessage = "¡Ganaste! La palabra era '{$word}'.\n";
            $summaryMessage .= "Intentos utilizados: {$Juego->attempts_used}\n";
            $summaryMessage .= "Palabras intentadas:\n" . implode("\n", $formattedAttempts);

            SendGameSummaryToSlack::dispatch($Juego, $summaryMessage);
            $this->sendLetterMessage("¡Ganaste! La palabra era '{$word}'.");
        } elseif ($Juego->attempts_used >= $maxAttempts) {
            $Juego->is_completed = true;
            $Juego->is_won = false;

            // Formatear intentos para el usuario
            $attemptsArray = json_decode($Juego->attempts, true);
            $formattedAttempts = array_map(function ($attempt) {
                $feedbackArray = json_decode($attempt['feedback'], true);
                $feedbackString = implode(", ", array_map(function ($feedback) {
                    return "{$feedback['letter']} ({$feedback['status']})";
                }, $feedbackArray));
                return "{$attempt['word_attempted']} => $feedbackString";
            }, $attemptsArray);

            $summaryMessage = "¡Perdiste! La palabra era '{$word}'.\n";
            $summaryMessage .= "Intentos utilizados: {$Juego->attempts_used}\n";
            $summaryMessage .= "Palabras intentadas:\n" . implode("\n", $formattedAttempts);

            SendGameSummaryToSlack::dispatch($Juego, $summaryMessage);
            $this->sendLetterMessage("¡Perdiste! La palabra era '{$word}'.");
        }



        $Juego->save();

        // Registrar el intento en la base de datos
        Attempt::create([
            'game_id' => $Juego->id,
            'word_attempted' => $attempt,
            'feedback' => json_encode($feedback), // Guardar el feedback como JSON
        ]);


        $formattedFeedback = implode("\n", array_map(function ($item) {
            return "→ {$item['letter']} ({$item['status']})"; // Agregar flechas y formato por línea
        }, $feedback));

        // Enviar el mensaje con feedback detallado y mejor formato
        $this->sendLetterMessage("Intento realizado: '{$attempt}'.\nFeedback:\n{$formattedFeedback}\nTe quedan {$attemptsRemaining} intentos.");

        return response()->json([
            'message' => $Juego->is_completed
                ? ($Juego->is_won ? '¡Ganaste!' : '¡Perdiste! La palabra era: ' . $word)
                : 'Sigue jugando.',
            'feedback' => $feedback,
            'remaining_attempts' => $attemptsRemaining, // Devuelve los intentos restantes
        ]);
    }
    public function availableJuego()
    {
        $maxAttempts = env('MAX_ATTEMPTS', 5);

        // Obtener todos los juegos activos (no completados y con palabra asignada) solo del jugador
        $Juego = Juego::where('is_completed', false)
            ->where('user_id', auth()->user()->id)
            ->get();


        // Si no hay juegos disponibles
        if ($Juego->isEmpty()) {
            return response()->json([
                'message' => 'No hay juegos disponibles para jugar en este momento.'
            ], 404);
        }
        $juegos = $Juego->map(function ($juego) use ($maxAttempts) {

            return [
                'id' => $juego->id,
                'is_completed' => $juego->is_completed,
                'is_won' => $juego->is_won,
                'attempts_used' => $juego->attempts_used,
                'max_attempts' => $maxAttempts,
            ];
        });
        // Devolver los juegos disponibles sin mostrar la palabra original
        return response()->json([
            'message' => 'Juegos disponibles para jugar.',
            'Juego' => $juegos
        ]);
    }
    public function abandonJuego($id)
    {
        $userId = auth()->user()->id;

        // Validar si el juego pertenece al usuario
        $Juego = Juego::where('user_id', $userId)->where('id', $id)->first();
        if (!$Juego) {
            return response()->json(['message' => 'No tienes un juego activo.'], 400);
        }

        if ($Juego->is_completed) {
            return response()->json(['message' => 'El juego ya terminó.'], 400);
        }

        // Marcar el juego como abandonado
        $Juego->is_completed = true;
        $Juego->is_won = false;
        $Juego->save();

        // Formatear intentos para un resumen claro
        $attemptsArray = json_decode($Juego->attempts, true);
        $formattedAttempts = array_map(function ($attempt) {
            $feedbackArray = json_decode($attempt['feedback'], true);
            $feedbackString = implode("\n", array_map(function ($feedback) {
                return "→ {$feedback['letter']} ({$feedback['status']})";
            }, $feedbackArray));
            return "{$attempt['word_attempted']}:\n$feedbackString";
        }, $attemptsArray);

        // Crear mensaje de resumen
        $summaryMessage = "El usuario abandonó el juego.\n";
        $summaryMessage .= "La palabra era '{$Juego->word}'.\n";
        $summaryMessage .= "Intentos utilizados: {$Juego->attempts_used}\n";
        $summaryMessage .= "Palabras intentadas:\n" . implode("\n\n", $formattedAttempts);

        // Despachar el mensaje a Slack
        SendGameSummaryToSlack::dispatch($Juego, $summaryMessage);

        // Respuesta al cliente
        return response()->json([
            'message' => 'Has abandonado el juego. Se ha marcado como perdido.',
            'summary' => $summaryMessage,
            'Juego' => $Juego
        ]);
    }

    public function JuegoStatus($id)
    {
        // Validar el ID del juego que existe
        if (!Juego::where('id', $id)->exists()) {
            return response()->json(['message' => 'Juego no encontrado.'], 404);
        }
        // Validar si el juego es del mismo usuario
        $userId = auth()->user()->id;
        $Juego = Juego::where('user_id', $userId)->where('id', $id)->first();
        if (!$Juego) {
            return response()->json(['message' => 'No tienes un juego activo.'], 400);
        }

        $Juego = Juego::with('attempts')->findOrFail($id); // Carga el juego con los intentos relacionados

        $attempts = $Juego->attempts; // Obtener los intentos asociados al juego
        $attemptsReport = $attempts->map(function ($attempt) {
            return [
                'attempt' => $attempt->word_attempted,
                'feedback' => json_decode($attempt->feedback, true), // Decodifica JSON para mayor claridad
            ];
        });

        return response()->json([
            'Juego' => [
                'id' => $Juego->id,
                'word' => $Juego->is_completed ? $Juego->word : 'No revelada',
                'attempts_used' => $Juego->attempts_used,
                'remaining_attempts' => env('MAX_ATTEMPTS', 5) - $Juego->attempts_used,
                'status' => $Juego->is_completed
                    ? ($Juego->is_won ? 'Ganaste' : 'Perdiste')
                    : 'En progreso',
            ],
            'attempts' => $attemptsReport,
        ]);
    }
    public function userHistory()
    {
        // Obtener el ID del usuario autenticado
        $userId = auth()->user()->id;

        // Verificar si el usuario está autenticado
        if (!$userId) {
            return response()->json(['message' => 'No tienes un juegos jugados.'], 400);
        }

        // Obtener solo los juegos completados del usuario
        $Juegos = Juego::where('user_id', $userId)
            ->where('is_completed', true) // Filtrar solo juegos completados
            ->get();

        // Si no hay juegos en el historial
        if ($Juegos->isEmpty()) {
            return response()->json(['message' => 'No se encontraron juegos en el historial.'], 404);
        }

        // Formatear la respuesta con más detalles de los juegos
        $JuegosHistory = $Juegos->map(function ($Juego) {
            return [
                'juego_id' => $Juego->id,
                'original_word' => $Juego->word,  // Solo mostrar la palabra enmascarada
                'attempts' => $Juego->attempts,
                'max_attempts' => $Juego->max_attempts,
                'is_completed' => $Juego->is_completed ? 'Sí' : 'No',
                'is_won' => $Juego->is_won ? 'Ganado' : 'Perdido',
                'date' => $Juego->created_at->format('d-m-Y H:i:s'),
            ];
        });

        return response()->json([
            'message' => 'Historial de juegos completados del usuario.',
            'Juegos' => $JuegosHistory
        ]);
    }
    public function adminReport()
    {
        // Cargar todos los juegos
        $Juegos = Juego::all();

        // Estadísticas básicas
        $totalJuegos = $Juegos->count();
        $wonJuegos = $Juegos->where('is_won', true)->count();
        $lostJuegos = $Juegos->where('is_won', false)->where('is_completed', true)->count();
        $inProgressJuegos = $Juegos->where('is_completed', false)->count();

        // Porcentaje de victorias y derrotas
        $porcentajeGanados = $totalJuegos > 0 ? ($wonJuegos / $totalJuegos) * 100 : 0;
        $porcentajePerdidos = $totalJuegos > 0 ? ($lostJuegos / $totalJuegos) * 100 : 0;


        // Informe detallado de cada juego
        $reporteDetallado = $Juegos->map(function ($Juego) {
            $user = User::find($Juego->user_id);
            return [
                'usuario' => $user->name,
                'id' => $Juego->id,
                'palabra' => $Juego->word,
                'intentos_usados' => $Juego->attempts_used,
                'intentos_restantes' => env('MAX_ATTEMPTS', 5) - $Juego->attempts_used,
                'estado' => $Juego->is_completed
                    ? ($Juego->is_won ? 'Ganado' : 'Perdido')
                    : 'En progreso',
            ];
        });

        // Retornar los datos como respuesta JSON
        return response()->json([
            'total_juegos' => $totalJuegos,
            'juegos_ganados' => $wonJuegos,
            'juegos_perdidos' => $lostJuegos,
            'juegos_en_progreso' => $inProgressJuegos,
            'porcentaje_ganados' => round($porcentajeGanados, 2),
            'porcentaje_perdidos' => round($porcentajePerdidos, 2),
            'reporte_detallado' => $reporteDetallado,
        ]);
    }
}

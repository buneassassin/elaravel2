<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Juego;
use App\Models\Attempt;
use Twilio\Rest\Client;
use Twilio\Http\CurlClient;
use Illuminate\Support\Facades\Validator;

class WordleController extends Controller
{
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

        $word = $this->generateRandomWord(5);  // Generar palabra aleatoria de 5 caracteres

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
        $Juego = Juego::where('user_id', $userId)->where('id', $id)->first();
        if (!$Juego) {
            return response()->json(['message' => 'No tienes un juego activo.'], 400);
        }
        // Validar la palabra proporcionada
        $validator = Validator::make($request->all(), [
            'word_attempted' => 'required|string', // Se valida que tenga exactamente 5 caracteres
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Los datos proporcionados son inválidos.'], 422);
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
            $this->sendLetterMessage("¡Ganaste! La palabra era '{$word}'.");
        } elseif ($Juego->attempts_used >= $maxAttempts) {
            $Juego->is_completed = true;
            $Juego->is_won = false;
            $this->sendLetterMessage("¡Perdiste! La palabra era '{$word}'.");
        }

        $Juego->save();

        // Registrar el intento en la base de datos
        Attempt::create([
            'game_id' => $Juego->id,
            'word_attempted' => $attempt,
            'feedback' => json_encode($feedback), // Guardar el feedback como JSON
        ]);

        // Enviar el mensaje con un estado más comprensible
        $this->sendLetterMessage("Intento realizado: '{$attempt}'. " . $feedbackMessage . ". Te quedan {$attemptsRemaining} intentos.");

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

        // Obtener todos los juegos activos (no completados y con palabra asignada)
        $Juego = Juego::where('is_completed', false)
            ->whereNotNull('word')  // Asegurarse de que el juego tenga una palabra asignada
            ->get(['id', 'word', 'is_completed', 'is_won']);  // Excluimos 'word' de la respuesta


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
    public function JuegoStatus($id)
    {
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
    public function abandonJuego($id)
    {
        $userId = auth()->user()->id;
        //validamos si es su juego
        $Juego = Juego::where('user_id', $userId)->where('id', $id)->first();
        if (!$Juego) {
            return response()->json(['message' => 'No tienes un juego activo.'], 400);
        }
        $Juego = Juego::findOrFail($id);

        if ($Juego->is_completed) {
            return response()->json(['message' => 'El juego ya terminó.'], 400);
        }

        $Juego->is_completed = true;
        $Juego->is_won = false;
        $Juego->save();

        return response()->json([
            'message' => 'Has abandonado el juego. Se ha marcado como perdido.',
            'Juego' => $Juego
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
            return [
                'id' => $Juego->id,
                'usuario_id' => $Juego->user_id,
                'palabra' => $Juego->is_completed ? $Juego->word : 'No revelada',
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
                'masked_word' => $Juego->masked_word,  // Solo mostrar la palabra enmascarada
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
    public function generateRandomWord($length)
    {
        // Array con palabras en español
        $palabras = [
            'feliz',
            'noche',
            'cielo',
            'fuego',
            'valor',
            'rojo',
            'verde',
            'azul',
            'luna',
            'risa',
            'fuerza',
            'pueblo',
            'ciudad',
            'comida',
            'bebida',
            'balón',
            'coche',
            'camisa',
            'gato',
            'perro',
            'león',
            'flor',
            'árbol',
            'sabio',
            'tonto',
            'amigo',
            'justo',
            'rico',
            'nuevo',
            'viejo',
            'frío',
            'cerca',
            'madre',
            'padre',
            'hermano',
            'hija',
            'tío',
            'primo',
            'dinero',
            'empresa',
            'café',
            'té',
            'vino',
            'jugo',
            'pan',
            'pollo',
            'carne',
            'arroz',
            'pasta',
            'sopa',
            'pizza',
            'guiso',
            'frito',
            'vapor',
            'dulce',
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
}

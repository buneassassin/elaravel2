<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use App\Models\User;
use App\Mail\AccountActivationMail;
use App\Mail\AdminNotificationMail;
use Illuminate\Support\Facades\Validator;
use Twilio\Rest\Client;
use Twilio\Http\CurlClient;

class AuthController extends Controller
{
    public function register_sanctum(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Los datos proporcionados son inválidos.',
                'errors' => $validator->errors()
            ], 422);
        }

        // Verificar si el correo ya está registrado
        $existingUser = User::where('email', $request->email)->first();
        if ($existingUser) {
            return response()->json(['message' => 'El usuario ya existe.'], 400);
        }

        // Crear un código de activación aleatorio
        $activationCode = random_int(100000, 999999);

        // Crear el usuario
        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role_id' => 1, // Rol predeterminado
            'is_active' => false, // Cuenta desactivada inicialmente
            'activation_token' => $activationCode, // Guardar el código de activación
        ]);

        // Enviar el código de activación por WhatsApp
        $this->sendActivationCode( $activationCode);

        return response()->json(['message' => 'Usuario registrado. Por favor, revisa tu WhatsApp para activar tu cuenta.'], 201);
    }

    private function sendActivationCode( $activationCode)
    {
        $sid = env('TWILIO_SID'); // Tu Twilio SID
        $token = env('TWILIO_AUTH_TOKEN'); // Tu Twilio Auth Token
        $from = "whatsapp:+14155238886"; // Número de Twilio habilitado para WhatsApp
        $to = "whatsapp:+5218714307468"; // Destino en formato internacional

        try {
            // Configurar opciones cURL para ignorar SSL (solo pruebas locales)
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
            $twilio->messages->create(
                $to,
                [
                    "from" => $from,
                    "body" => "Tu código de activación es: ". $activationCode.". Por favor, úsalo para activar tu cuenta. 🚀"
                ]
            );
        } catch (\Exception $e) {
            // Manejar errores
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    public function resendActivationCode(Request $request)
    {
        // Validar que se proporcione el correo
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|max:255',
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                'message' => 'El correo proporcionado no es válido.',
                'errors' => $validator->errors()
            ], 422);
        }
    
        // Buscar al usuario por correo
        $user = User::where('email', $request->email)->first();
    
        if (!$user) {
            return response()->json(['message' => 'El usuario no existe.'], 404);
        }
    
        // Verificar si el usuario ya está activo
        if ($user->is_active) {
            return response()->json(['message' => 'El usuario ya está activado.'], 400);
        }
    
        // Reenviar el código de activación al teléfono del usuario
        $this->sendActivationCode($user->phone, $user->activation_token);
    
        return response()->json(['message' => 'El código de activación ha sido reenviado. Revisa tu WhatsApp.'], 200);
    }
    

    private function sendmessage( $message)
    {
        $sid = env('TWILIO_SID'); // Tu Twilio SID
        $token = env('TWILIO_AUTH_TOKEN'); // Tu Twilio Auth Token
        $from = "whatsapp:+14155238886"; // Número de Twilio habilitado para WhatsApp
        $to = "whatsapp:+5218714307468"; // Destino en formato internacional

        try {
            // Configurar opciones cURL para ignorar SSL (solo pruebas locales)
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
            $twilio->messages->create(
                $to,
                [
                    "from" => $from,
                    "body" => "Nuevo usruario registrado: $message. 🚀"
                ]
            );
        } catch (\Exception $e) {
            // Manejar errores
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    public function activateAccountWas(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'code' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Los datos proporcionados son inválidos.',
                'errors' => $validator->errors()
            ], 422);
        }

        // Verificar el usuario y el código de activación
        $user = User::where('email', $request->email)
            ->where('activation_token', $request->code)
            ->first();

        if (!$user) {
            return response()->json(['message' => 'Código de activación inválido.'], 400);
        }

        // Activar la cuenta
        $user->is_active = true;
        $user->activation_token = null; // Eliminar el token una vez activado
        $user->save();
        //enviamos un mesaje de notificacion al admin por WhatsApp de usario activado
        //buscmos el telefono del admin
        $admin = User::where('role_id', 3)->first();
        if ($admin) {
            $this->sendmessage('Cuenta activada' . $user->name . ' ' . $user->email . ' ' . $user->phone);
        }

        return response()->json(['message' => 'Cuenta activada con éxito.'], 200);
    }

    public function login_sanctum(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Los datos proporcionados son inválidos.'], 422);
        }
        // Verificar las que los datos no sean null
      
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['error' => 'Credenciales inválidas'], 401);
        }

        $token = $user->createToken("Mi_dispositivo")->plainTextToken;

        return response()->json(['user' => $user, 'token' => $token], 201);
    }

    public function activateAccount(Request $request)
    {
        $user = User::find($request->user);

        if ($user->is_active) {
            return response()->json(['message' => 'La cuenta ya está activada.'], 400);
        }

        $admin = User::where('role_id', 3)->first();

        if ($admin) {
            Mail::to($admin->email)->send(new AdminNotificationMail($user));
        }

        $user->is_active = true;
        $user->save();

        return response()->json(['message' => 'La cuenta ha sido activada.'], 200);
    }

    public function resendActivationLink(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Los datos proporcionados son inválidos.'], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'El usuario no existe.'], 404);
        }

        if ($user->is_active) {
            return response()->json(['message' => 'La cuenta ya está activada.'], 400);
        }

        $activationLink = URL::temporarySignedRoute('user.activate', now()->addMinutes(5), ['user' => $user->id]);
        Mail::to($request->email)->send(new AccountActivationMail($activationLink));

        return response()->json(['message' => 'Se ha enviado un nuevo enlace de activación a tu correo electrónico.'], 200);
    }
}

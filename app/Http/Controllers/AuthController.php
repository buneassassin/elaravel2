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
use App\Mail\ResetPassword;

class AuthController extends Controller
{
    public function register_sanctum(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Los datos proporcionados son inválidos.',
                'errors'  => $validator->errors()
            ], 422);
        }

        $user = User::where('email', $request->email)->first();
        if ($user) {
            return response()->json(['message' => 'El usuario ya existe.'], 400);
        }

        $activationCode = rand(100000, 999999);

        $user = User::create([
            'name'              => $request->name,
            'email'             => $request->email,
            'password'          => Hash::make($request->password),
            'role_id'           => 1,
            'is_active'         => false,
            'is_inactive'       => true,
            'profile_picture'   => "https://ui-avatars.com/api/?name=" . urlencode($request->name) . "&color=7F9CF5&background=EBF4FF",
            'activation_token'  => $activationCode,
        ]);

        $activationLink = URL::temporarySignedRoute('user.activate.form', now()->addMinutes(10), ['user' => $user->id]);
        Mail::to($request->email)->send(new AccountActivationMail($activationLink, $activationCode));

        return response()->json([
            'success' => true,
            'message' => 'Usuario registrado. Por favor, revisa tu correo para activar la cuenta.'
        ], 201);
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

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['error' => 'Credenciales inválidas'], 401);
        }

        if (!$user->is_active) {
            return response()->json(['error' => 'Cuenta no activada. Por favor, revisa tu correo para activarla.'], 401);
        }

        $token = $user->createToken("Mi_dispositivo")->plainTextToken;

        return response()->json(['user' => $user, 'token' => $token], 201);
    }
    public function me()
    {
        return response()->json(['success' => true, 'user' => auth()->user()]);
    }
    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json(['message' => 'Saliendo...'], 200);
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
        $user->role_id = 2;
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
        Mail::to($request->email)->send(new AccountActivationMail($activationLink, $user->activation_token));

        return response()->json(['message' => 'Se ha enviado un nuevo enlace de activación a tu correo electrónico.'], 200);
    }
    public function showActivationForm()
    {
        return view('auth.activate');
    }
    public function activateAccount22(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'activation_code' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Los datos proporcionados son inválidos.',
                'errors' => $validator->errors()
            ], 422);
        }

        // Buscar al usuario por el código de activación
        $user = User::where('activation_token', $request->activation_code)->first();

        if (!$user) {
            return response()->json(['message' => 'El código de activación es incorrecto.'], 404);
        }

        if ($user->is_active) {
            return response()->json(['message' => 'La cuenta ya está activada.'], 400);
        }

        // Activar la cuenta y limpiar el token
        $user->is_active = true;
        $user->activation_token = null;
        $user->role_id = 2;
        $user->save();

        return response()->json(['message' => 'La cuenta ha sido activada.'], 200);
    }
    public function activateAccount2(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'activation_code' => 'required|string',
        ]);
    
        if ($validator->fails()) {
            return view('errors.activation', [
                'message' => 'Los datos proporcionados son inválidos.',
                'errors' => $validator->errors()
            ]);
        }
    
        // Buscar al usuario por el código de activación
        $user = User::where('activation_token', $request->activation_code)->first();
    
        if (!$user) {
            return view('errors.activation', [
                'message' => 'El código de activación es incorrecto.'
            ]);
        }
    
        if ($user->is_active) {
            return view('errors.activation', [
                'message' => 'La cuenta ya está activada.'
            ]);
        }
    
        // Activar la cuenta y limpiar el token
        $user->is_active = true;
        $user->activation_token = null;
        $user->role_id = 2;
        $user->save();
    
        // Aquí enviamos un mensaje de éxito y mostramos un botón para ir al login
        return view('success.activation', [
            'message' => 'La cuenta ha sido activada correctamente.'
        ]);
    }
    public function recuperarPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()
            ], 400);
        }
        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json([
                'message' => 'Email not found'
            ], 404);
        }
        $url = URL::temporarySignedRoute('reset-password', now(), ['user' => $user->id]);
        $activarCuenta = new ResetPassword($user, $url);

        Mail::to($user->email)->send($activarCuenta);

        return response()->json([
            'message' => 'Correo enviado correctamente, revisa tu correo',
            'email' => $user->email,
            'url' => $url
        ], 200);
    }
    public function showResetForm($userId)
    {
        // Verifica si el enlace es válido
        $user = User::findOrFail($userId);
        return view('auth.reset_password_form', ['user' => $user]);
    }
    public function resetPassworddd(Request $request, $userId)
    {
        $user = User::findOrFail($userId);
        $user->password = bcrypt($request->password);
        $user->save();
    
        return redirect()->back()->with('success', '¡Contraseña cambiada correctamente!');
    }
    
}

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
        
        //verificar si el correo ya esta registrado

        $user = User::where('email', $request->email)->first();
        if ($user) {
            return response()->json(['message' => 'El usuario ya existe.'], 400);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role_id' => 1,
            'is_active' => false,
            'is_inactive' => true,
            'profile_picture' => null,
            'activation_token' => null
        ]);

        $activationLink = URL::temporarySignedRoute('user.activate', now()->addMinutes(1), ['user' => $user->id]);
        Mail::to($request->email)->send(new AccountActivationMail($activationLink));

        return response()->json(['message' => 'Usuario registrado. Por favor, revisa tu correo para activar la cuenta.'], 201);
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
            return response()->json(['error' => 'Cuenta no activada. Por favor, revisa tu correo para activarla.'], 403);
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

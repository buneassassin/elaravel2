<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use Illuminate\Support\Facades\Validator;

class ImageController extends Controller
{
    public function subirImagen(Request $request)
    {
        // Validar la imagen
        $validator = Validator::make($request->all(), [
            'profile_picture' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Inamgen no valida para subir la imagen solo aceptamos jpeg, png, jpg, gif, svg.'], 400);
        }

        $imagen = $request->file('profile_picture');
        $carpeta = '23170153';
        $path = $imagen->store($carpeta, 'public');

        $user = $request->user();
        if ($user instanceof User) {
            $user->profile_picture = $path;
            $user->save();

            return response()->json(['message' => 'Imagen subida exitosamente', 'image_url' => $path]);
        }

        return response()->json(['message' => 'Error al subir la imagen.'], 500);
    }
    public function obtenerImagen()
    {
        $user = auth()->user();
        $path = $user->profile_picture;

        if (!$path) {
            return response()->json(['message' => 'No hay imagen'], 404);
        }

        $content = Storage::disk('public')->get($path);

        return response($content)->header('Content-Type', 'image/png');
    }


    public function uploadProfilePicture(Request $request)
    {
        $user = auth()->user();
        // Validar la imagen
        $validator = Validator::make($request->all(), [
            'archivo' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Inamgen no valida para subir la imagen.'], 400);
        }

        try {
            $user = auth()->user();


            $imagen = $request->file('archivo');

            $path = Storage::disk('s3')->put('23170153', $imagen);

            $user->profile_picture = $path;

            return response()->json(['message' => 'Imagen subida exitosamente', 'image_url' => $imagen]);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Error al subir la imagen.'], 500);
        }
    }


    public function getProfilePicture(Request $request)
    {
        $user = auth()->user();
        $path = $user->imagen;

        if (!$path) {
            return response()->json([
                'message' => 'No hay imagen',
            ]);
        }

        $content = Storage::disk('s3')->get($path);

        return response($content)->header('content-type', 'image/png');
    }
}

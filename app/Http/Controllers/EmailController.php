<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\DynamicEmail;
use Illuminate\Support\Facades\Storage;



class EmailController extends Controller
{
    public function index(Request $request)
    {
       // $content = Storage::disk('public')->get('images/mxy19CBaQOJE8FTFkjzM2ORLGfi0r2oADZwOsIHE.jpg');
       // return response($content, 200)->header('Content-Type', 'image/jpeg');
        $path = storage_path('app').'/public/images/6qp0KpUp4z3fAImpv5i79QI8TW4NywB2yxRgR4Kw.jpg';
        Mail::to($request->user()->email)->send(new DynamicEmail($request->user(), $path));
        return "ok";
    }


    public function archivo(Request $request)
    {
        $file = $request->file('archivo');
        $path = Storage::disk('public')->put('images', $file);
        return $path;
    }

    public function sendEmail(Request $request)
    {
        $user = $request->user();
        $name = $user->name;
        $email = $user->email;

        Mail::to($request->user()->email)->send(new DynamicEmail($name, $email, $request->input('archivo'))); // Pasa el nombre y el correo al Mailable

        return response()->json(['message' => 'Email sent successfully!']);
    }
}

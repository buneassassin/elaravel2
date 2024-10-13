<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Libro;
use Database\Seeders\DatabaseSeeder;
use Dflydev\DotAccessData\Data;
use Illuminate\Http\Request;
use Illuminate\Database\Seeder;

class LibroController extends Controller
{
    // Mostrar una lista de libros
    public function index()
    {
        $seeder = new DatabaseSeeder();
        // Ejecutar el seeder 'DatabaseSeeder'
       $seeder->run('db:seed', [
           '--class' => 'Database\\Seeders\\DatabaseSeeder',
       ]);

        $libros = Libro::all();
        return response()->json([
            'success' => true,
            'message' => 'Lista de libros',
            'data' => $libros
        ]);
    }

    // Almacenar un nuevo libro en la base de datos
    public function store(Request $request)
    {
        $request->validate([
            'titulo' => 'required|string|max:255',
            'genero' => 'nullable|string|max:255',
            'autor_id' => 'required|exists:autores,id',
        ]);

        $libro = Libro::create($request->all());

        return response()->json($libro, 201);
    }

    // Mostrar un libro específico
    public function show(Libro $libro)
    {
        return response()->json($libro);
    }

    // Actualizar un libro en la base de datos
    public function update(Request $request, Libro $libro)
    {
        $request->validate([
            'titulo' => 'required|string|max:255',
            'genero' => 'nullable|string|max:255',
            'autor_id' => 'required|exists:autores,id',
        ]);

        $libro->update($request->all());

        return response()->json($libro);
    }

    // Eliminar un libro de la base de datos
    public function destroy(Libro $libro)
    {
        $libro->delete();

        return response()->json(null, 204);
    }
}

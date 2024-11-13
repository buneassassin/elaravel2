<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Autor;
use App\Models\Editorial;
use App\Models\EventoLiterario;
use App\Models\Lector;
use App\Models\Libreria;
use App\Models\Libro;
use App\Models\ParticipacionEvento;
use App\Models\Prestamo;
use App\Models\Publicacion;
use App\Models\Resena;
use App\Models\Inventario;
use App\Models\Token;

use Database\Seeders\DatabaseSeeder;
use Dflydev\DotAccessData\Data;
use Illuminate\Http\Request;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use Faker\Factory;
use Faker\Factory as Faker;

class LibroController extends Controller
{
    // Mostrar una lista de libros
    public function index()
    {



        $libros = Libro::with([
            'autor', // Información del autor
            'publicaciones.editorial', // Información de las publicaciones y editoriales
            'resenas.lector', // Información de las reseñas y los lectores que las escribieron
            'inventarios.libreria' // Información de los inventarios y las librerías
        ])->get();
        return response()->json([
            'success' => true,
            'message' => 'Lista de libros',
            'data' => $libros,

        ]);
    }
    // Almacenar un nuevo libro 
    public function store(Request $request)
    {

        $request->validate([
            'titulo' => 'required|string|max:255',
            'genero' => 'required|string|max:255',
            'nombre' => 'required|string|max:255',
            'nacionalidad' => 'required|string|max:255',
        ]);

        // Crear el autor
        $autor = Autor::create([
            'nombre' => $request->input('nombre'),
            'nacionalidad' => $request->input('nacionalidad'),
        ]);

        // Crear el libro y asignar el id del autor automáticamente
        $libro = Libro::create([
            'titulo' => $request->input('titulo'),
            'genero' => $request->input('genero'),
            'autor_id' => $autor->id,  // Asignar el id del autor recién creado
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Libro creado exitosamente',
            'data' => $libro,
            'autor' => $autor,
        ]);
    }
    // Mostrar un libro específico
    public function show($id)
    {


        // Buscar el libro por su ID y cargar las relaciones
        $libro = Libro::with([
            'autor', // Información del autor
            'publicaciones.editorial', // Información de las publicaciones y editoriales
            'resenas.lector', // Información de las reseñas y los lectores que las escribieron
            'inventarios.libreria' // Información de los inventarios y las librerías
        ])->find($id);

        // Verificar si el libro existe
        if (!$libro) {
            return response()->json([
                'success' => false,
                'message' => 'Libro no encontrado'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detalles del libro',
            'title' => $libro->titulo, // Incluir el título del libro
            'data' => $libro,
        ]);
    }
    // Actualizar un libro 
    public function update(Request $request, Libro $libro, Autor $autor)
    {



        $request->validate([
            'titulo' => 'required|string|max:255',
            'genero' => 'required|string|max:255',
            'nombre' => 'required|string|max:255',
            'nacionalidad' => 'required|string|max:255',
        ]);
        $libro->update([
            'titulo' => $request->input('titulo'),
            'genero' => $request->input('genero'),
            'autor_id' => $libro->autor_id,  // Asignar el id del autor reciente creado
        ]);
        $autor->update([
            'nacionalidad' => $request->input('nacionalidad'),
            'nombre' => $request->input('nombre'),
        ]);


        // Devuelve la respuesta JSON con los datos actualizados
        return response()->json([
            'libro' => $libro,
            'autor' => $autor,
        ]);
    }
    // Eliminar un libro de la base de datos
    public function destroy($id)
    {

        // Cargar el libro con las relaciones necesarias
        $libro = Libro::with(['resenas', 'publicaciones', 'inventarios', 'autor',])->find($id);


        if (!$libro) {
            return response()->json([
                'success' => false,
                'message' => 'Libro no encontrado'
            ], 404);
        }

        try {

            // Eliminar las reseñas relacionadas
            $libro->resenas()->delete();
            // Eliminar las publicaciones relacionadas
            $libro->publicaciones()->delete();

            // Eliminar los inventarios relacionados
            $libro->inventarios()->delete(); // Asegúrate de que 'inventarios' esté definido en el modelo

            // Ahora eliminar el libro
            $libro->delete();
            return response()->json(['message' => 'Libro eliminado exitosamente'], 204);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el libro',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    ///////////////////////////////////////////////////////////////////////////
    public function indexAutor()
    {


        $autor = Autor::all();
        return response()->json([
            'autor' => $autor,
        ]);
    }
    public function storeAutor(Request $request)
    {


        $autor = Autor::create([
            'nombre' => $request->input('nombre'),
            'nacionalidad' => $request->input('nacionalidad'),
        ]);

        return response()->json([
            'autor' => $autor,
        ]);
    }
    public function showAutor($id)
    {

        $autor = Autor::with(['libros'])->find($id);
        return response()->json([
            'autor' => $autor,
        ]);
    }
    public function updateAutor(Request $request, Autor $autor)
    {

        $autor->update([
            'nacionalidad' => $request->input('nacionalidad'),
            'nombre' => $request->input('nombre'),
        ]);
        return response()->json([
            'autor' => $autor,
        ]);
    }
    public function destroyAutor($id)
    {

        Autor::destroy($id);
        return response()->json([
            'message' => 'Autor eliminado exitosamente',
        ], 204);
    }
    ///////////////////////////////////////////////////////////////////////////////
    public function indexEditorials()
    {
        $editorial = Editorial::all();
        return response()->json([
            'editorial' => $editorial,
        ]);
    }
    public function storeEditorials(Request $request)
    {

        // Crear el editorial
        $editorial = Editorial::create([
            'nombre' => $request->input('nombre'),
            'pais' => $request->input('pais'),
        ]);

        return response()->json([
            'editorial' => $editorial,
        ]);
    }
    public function showEditorials($id)
    {
        // buscar el editorial especifico
        $editorial = Editorial::find($id);
        return response()->json([
            'editorial' => $editorial,
        ]);
    }
    public function updateEditorials(Request $request, Editorial $editorial)
    {

        $editorial->update([
            'pais' => $request->input('pais'),
            'nombre' => $request->input('nombre'),
        ]);
        return response()->json([
            'editorial' => $editorial,
        ]);
    }
    public function destroyEditorials($id)
    {

        Editorial::destroy($id);
        return response()->json([
            'message' => 'Editorial eliminado exitosamente',
        ], 204);
    }
    ///////////////////////////////////////////////////////////////////////////////
    public function indexEntos_literarios()
    {



        $Evento_literario = Eventoliterario::all();
        return response()->json([
            'Evento_literario' => $Evento_literario,
        ]);
    }
    public function storeEntos_literarios(Request $request)
    {
        // Crear el Evento_literario
        $Evento_literario = Eventoliterario::create([
            'nombre' => $request->input('nombre'),
            'fecha' => $request->input('fecha'),
            'ubicacion' => $request->input('ubicacion'),
        ]);

        return response()->json([
            'Evento_literario' => $Evento_literario,
        ]);
    }
    public function showEntos_literarios($id)
    {
        $Evento_literario = Eventoliterario::find($id);
        return response()->json([
            'Evento_literario' => $Evento_literario,
        ]);
    }
    public function updateEntos_literarios(Request $request,  Eventoliterario $eventoliterario)
    {

        $eventoliterario->update([
            'nombre' => $request->input('nombre'),
            'fecha' => $request->input('fecha'),
            'ubicacion' => $request->input('ubicacion'),
        ]);
        return response()->json([
            'Entos_literarios' => $eventoliterario,
        ]);
    }
    public function destroyEntos_literarios($id)
    {
        Eventoliterario::destroy($id);
        return response()->json([
            'message' => 'Entos_literarios eliminado exitosamente',
        ], 204);
    }
    ///////////////////////////////////////////////////////////////////////////////
    public function indexLectores()
    {

        $Lectores = Lector::all();
        return response()->json([
            'Lectores' => $Lectores,
        ]);
    }
    public function storeLectores(Request $request)
    {
        // Crear el Lector
        $Lector = Lector::create([
            'nombre' => $request->input('nombre'),
            'email' => $request->input('email'),
        ]);

        return response()->json([
            'Lector' => $Lector,
        ]);
    }
    public function showLectores($id)
    {
        $Lector = Lector::find($id);
        return response()->json([
            'Lector' => $Lector,
        ]);
    }
    public function updateLectores(Request $request, Lector $lector)
    {
        $lector->update([
            'email' => $request->input('email'),
            'nombre' => $request->input('nombre'),
        ]);
        return response()->json([
            'lector' => $lector,
        ]);
    }
    public function destroyLectores($id)
    {
        Lector::destroy($id);
        return response()->json([
            'message' => 'Lector eliminado exitosamente',
        ], 204);
    }
    ///////////////////////////////////////////////////////////////////////////////
    public function indexLibrerías()
    {
        $Librerias = Libreria::all();
        return response()->json([
            'Librerias' => $Librerias,
        ]);
    }
    public function storeLibrerías(Request $request)
    {
        $Libreria = Libreria::create([
            'nombre' => $request->input('nombre'),
            'ubucacion' => $request->input('ubucacion'),
        ]);
        return response()->json([
            'Libreria' => $Libreria,
        ]);
    }
    public function showLibrerías($id)
    {
        $Libreria = Libreria::find($id);
        return response()->json([
            'Libreria' => $Libreria,
        ]);
    }
    public function updateLibrerías(Request $request, Libreria $libreria)
    {

        $libreria->update([
            'nombre' => $request->input('nombre'),
            'ubucacion' => $request->input('ubucacion'),
        ]);
        return response()->json([
            'Libreria' => $libreria,
        ]);
    }
    public function destroyLibrerías($id)
    {

        Libreria::destroy($id);
        return response()->json([
            'message' => 'Librería eliminado exitosamente',
        ], 204);
    }
    ///////////////////////////////////////////////////////////////////////////////
    public function indexParticipacion_evento()
    {



        $ParticipacionEvento = ParticipacionEvento::with(['autor', 'evento'])->get();
        return response()->json([
            'ParticipacionEvento' => $ParticipacionEvento,
        ]);
    }
    public function storeParticipacion_evento(Request $request)
    {
        $faker = Faker::create();

        // Crear el un evento
        $Evento = EventoLiterario::create([
            'nombre' => $request->input('nombre'),
            'fecha' => $request->input('fecha'),
            'ubicacion' => $request->input('ubicacion'),
        ]);
        // Crear el autor
        $Autor = Autor::create([
            'nombre' => $faker->name,
            'nacionalidad' => $request->input('nacionalidad'),
        ]);
        // Crear el ParticipacionEvento
        $participacionEvento = ParticipacionEvento::create([
            'autor_id' => $Autor->id,
            'evento_id' => $Evento->id,
        ]);
        return response()->json([
            'ParticipacionEvento' => $participacionEvento,
        ]);
    }
    public function showParticipacion_evento($id)
    {

        $participacionEvento = ParticipacionEvento::with(['autor', 'evento'])->find($id);
        return response()->json([
            'ParticipacionEvento' => $participacionEvento,
        ]);
    }
    public function updateParticipacion_evento(Request $request, ParticipacionEvento $participacionEvento)
    {

        $participacionEvento->update([
            'autor_id' => $request->input('autor_id'),
            'evento_id' => $request->input('evento_id'),
        ]);

        return response()->json([
            'ParticipacionEvento' => $participacionEvento,
        ]);
    }
    public function destroyParticipacion_evento($id)
    {
        ParticipacionEvento::destroy($id);
        return response()->json([
            'message' => 'Participacion_evento eliminado exitosamente',
        ], 204);
    }
    ///////////////////////////////////////////////////////////////////////////////
    public function indexPrestamos()
    {
        $Prestamos = Prestamo::all();
        return response()->json([
            'Prestamos' => $Prestamos,
        ]);
    }
    public function storePrestamos(Request $request)
    {
        $faker = Faker::create();

        // Crear el Lector
        $Prestamos = Prestamo::create([
            'name' => $faker->name,
            'email' => $faker->email,
        ]);
        // Crear el Autor
        $Autor = Autor::create([
            'nombre' => $faker->name,
            'nacionalidad' => $faker->name,
        ]);
        //Crear el libro
        $libro = Libro::create([
            'titulo' => $faker->name,
            'genero' => $faker->name,
            'autor_id' => $Autor->id,
        ]);

        // Crear el Prestamo
        $prestamo = Prestamo::create([
            'libro_id' => $libro->id,
            'lector_id' => $Prestamos->id,
            'fecha_prestamo' => $request->input('fecha_prestamo'),
            'fecha_devolucion' => $request->input('fecha_devolucion'),
        ]);
        return response()->json([
            'Prestamos' => $prestamo,
        ]);
    }
    public function showPrestamos($id)
    {
        $prestamo = Prestamo::find($id);
        return response()->json([
            'Prestamos' => $prestamo,
        ]);
    }
    public function updatePrestamos(Request $request, Prestamo $prestamo)
    {
        $prestamo = $prestamo->update([
            'fecha_prestamo' => $request->input('fecha_prestamo'),
            'fecha_devolucion' => $request->input('fecha_devolucion'),
        ]);
        return response()->json([
            'Prestamos' => $prestamo,
        ]);
    }
    public function destroyPrestamos($id)
    {
        Prestamo::destroy($id);
        return response()->json([
            'message' => 'Prestamo eliminado exitosamente',
        ], 204);
    }
    ///////////////////////////////////////////////////////////////////////////////
    public function indexPublicaciones()
    {

        $publicaciones = Publicacion::all();
        return response()->json([
            'publicaciones' => $publicaciones,
        ]);
    }

    public function storePublicaciones(Request $request)
    {


        // Crear el editorial
        $editorial = Editorial::create([
            'nombre' => $request->input('nombre'),
            'pais' => $request->input('pais'),
        ]);
        $publicaciones = Publicacion::create([
            'editorial_id' => $editorial->id,
            'autor_id' => 1,
            'fecha_publicacion' => $request->input('fecha_publicacion'),
        ]);

        return response()->json([
            'publicaciones' => $publicaciones,
        ]);
    }
    public function showPublicaciones($id)
    {

        $publicaciones = Publicacion::find($id);
        return response()->json([
            'publicaciones' => $publicaciones,
        ]);
    }
    public function updatePublicaciones(Request $request, Publicacion $publicacion)
    {


        $publicacion->update([
            'fecha_publicacion' => $request->input('fecha_publicacion'),
        ]);
        return response()->json([
            'publicaciones' => $publicacion,
        ]);
    }
    public function destroyPublicaciones($id)
    {
        Publicacion::destroy($id);
        return response()->json([
            'message' => 'Publicaciones eliminado exitosamente',
        ], 204);
    }
    ///////////////////////////////////////////////////////////////////////////////
    public function indexResena()
    {


        $resena = Resena::all();
        return response()->json([
            'resena' => $resena,
        ]);
    }
    public function storeResena(Request $request)
    {

        $resena = Resena::create([
            'calificacion' => $request->input('calificacion'),
            'comentario' => $request->input('comentarios'),
        ]);


        return response()->json([
            'resena' => $resena,
        ]);
    }
    public function showResena($id)
    {

        $resena = Resena::find($id);
        return response()->json([
            'resena' => $resena,
        ]);
    }
    public function updateResena(Request $request,  Resena $resena)
    {


        $resena->update([
            'calificacion' => $request->input('calificacion'),
            'comentario' => $request->input('comentarios'),

        ]);
        return response()->json([
            'resena' => $resena,
        ]);
    }
    public function destroyResena($id)
    {
        Resena::destroy($id);
        return response()->json([
            'message' => 'Resena eliminado exitosamente',
        ], 204);
    }
    ///////////////////////////////////////////////////////////////////////////////

}

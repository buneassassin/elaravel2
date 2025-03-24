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
use App\Models\Categoria;
use App\Models\Token;

use App\Events\Resenas;

//Validaciones
use Illuminate\Support\Facades\Validator;
use Database\Seeders\DatabaseSeeder;
use Dflydev\DotAccessData\Data;
use Illuminate\Http\Request;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use Faker\Factory;
use Faker\Factory as Faker;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LibroController extends Controller
{
    public function index()
    {
        //vereficar si hay libros
        $libros = Libro::all();
        if ($libros->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No hay libros en la base de datos',
            ], 404);
        }
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
    public function index2()
    {
        // Verificar si hay libros
        if (!Libro::exists()) {
            return response()->json([
                'success' => false,
                'message' => 'No hay libros en la base de datos',
            ], 404);
        }

        // Obtener libros con relaciones y paginación
        $libros = Libro::with([
            'autor',
            'publicaciones.editorial',
            'resenas.lector',
            'inventarios.libreria'
        ])->paginate(10); // Cambia 10 por el número de elementos por página

        return response()->json([
            'success' => true,
            'message' => 'Lista de libros paginada',
            'data' => $libros->items(), // Solo los libros de la página actual
            'pagination' => [
                'current_page' => $libros->currentPage(),
                'total' => $libros->total(),
                'per_page' => $libros->perPage(),
                'last_page' => $libros->lastPage(),
                'next_page_url' => $libros->nextPageUrl(),
                'prev_page_url' => $libros->previousPageUrl()
            ]
        ]);
    }
    public function store(Request $request)
    {
        // Verificar la validación de los datos
        $validator = Validator::make($request->all(), [
            'titulo' => 'required|string|max:255',
            'genero' => 'required|string|max:255',
            'nombre' => 'required|string|max:255',
            'nacionalidad' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Datos inválidos',
                'errors' => $validator->errors(),
            ], 400);
        }

        // Verificar si el libro ya existe
        $libro = Libro::where('titulo', $request->input('titulo'))->first();
        if ($libro) {
            return response()->json([
                'success' => false,
                'message' => 'El libro ya existe',
            ], 400);
        }

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
    public function update(Request $request, $id)
    {
        // Buscar el libro por ID
        $libro = Libro::find($id);

        // Si no se encuentra el libro, devolver un mensaje personalizado
        if (!$libro) {
            return response()->json([
                'success' => false,
                'message' => 'El libro con el ID especificado no existe.',
            ], 404);
        }

        // Continuar con la lógica normal si el libro existe
        $validator = Validator::make($request->all(), [
            'titulo' => 'required|string|max:255',
            'genero' => 'required|string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Datos inválidos',
                'errors' => $validator->errors(),
            ], 400);
        }

        // Actualizar el libro
        $libro->update([
            'titulo' => $request->input('titulo'),
            'genero' => $request->input('genero'),
        ]);

        // Respuesta de éxito
        return response()->json([
            'success' => true,
            'message' => 'Libro actualizados correctamente.',
            'libro' => $libro,
        ], 200);
    }
    public function destroy($id)
    {
        // Cargar el libro con las relaciones necesarias
        $libro = Libro::with(['resenas', 'publicaciones', 'inventarios', 'autor'])->find($id);

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
            $libro->inventarios()->delete();

            // Ahora eliminar el libro
            $libro->delete();

            // Respuesta con código 200 para incluir un mensaje JSON
            return response()->json([
                'success' => true,
                'message' => 'Libro eliminado exitosamente'
            ], 200);
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
        ], 200);
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
        ], 200);
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
        ], 200);
    }
    ///////////////////////////////////////////////////////////////////////////////
    public function indexLectores()
    {

        $Lectores = Lector::all();
        return response()->json([
            'Lectores' => $Lectores,
        ]);
    }
    public function indexLectores2()
    {
        //asemos la paginacion a la consulta
        $Lectores = Lector::paginate(10);
        return response()->json([
            'success' => true,
            'message' => 'Lista de lectores paginada',
            'data' => $Lectores->items(),
            'pagination' => [
                'current_page' => $Lectores->currentPage(),
                'total' => $Lectores->total(),
                'per_page' => $Lectores->perPage(),
                'last_page' => $Lectores->lastPage(),
            ],

        ]);
    }
    public function streamLectores()
    {
        return response()->stream(function () {
            while (true) {
                $Lectores = Lector::paginate(10);
                $data = json_encode([
                    'success'    => true,
                    'message'    => 'Lista de lectores paginada',
                    'data'       => $Lectores->items(),
                    'pagination' => [
                        'current_page' => $Lectores->currentPage(),
                        'total'        => $Lectores->total(),
                        'per_page'     => $Lectores->perPage(),
                        'last_page'    => $Lectores->lastPage(),
                    ],
                ]);
                $Lectores = Lector::paginate(10);
                $data = json_encode([
                    'success' => true,
                    'message' => 'Lista de lectores paginada',
                    'data' => $Lectores->items(),
                    'pagination' => [
                        'current_page' => $Lectores->currentPage(),
                        'total' => $Lectores->total(),
                        'per_page' => $Lectores->perPage(),
                        'last_page' => $Lectores->lastPage(),
                    ],

                ]);
                echo "data: {$data}\n\n";
                @ob_flush();
                flush();
                sleep(20);
            }
        }, 200, [
            'Content-Type'  => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'Connection'    => 'keep-alive',
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
        ], 200);
    }
    ///////////////////////////////////////////////////////////////////////////////
    public function indexLibrerías()
    {
        $Librerias = Libreria::all();
        return response()->json([
            'Librerias' => $Librerias,
        ]);
    }
    public function indexLibrerías2()
    {
        //asemos la paginacion a la consulta
        $Librerias = Libreria::paginate(10);
        return response()->json([
            'success' => true,
            'message' => 'Lista de Librerias paginada',
            'data' => $Librerias->items(),
            'pagination' => [
                'current_page' => $Librerias->currentPage(),
                'total' => $Librerias->total(),
                'per_page' => $Librerias->perPage(),
                'last_page' => $Librerias->lastPage(),
            ]
        ]);
    }
    public function streamLectoresWithPage2(Request $request)
    {
        //set_time_limit(0);

        return response()->stream(function () {
            
            while (true) {  // Verifica si el cliente se desconectó
                $mesaje = "Conexión a SSE|";
                $data = json_encode([
                    'message' => $mesaje
                ]);
                echo "data: {$data}\n\n";
                @ob_flush();
                flush();
                sleep(10);
            }
        }, 200, [
            'Content-Type'  => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'Connection'    => 'keep-alive',
            'X-Accel-Buffering' => 'no',

        ]);
    }
    public function streamLectoresWithPage(Request $request)
    {
        //set_time_limit(0);
        $page = $request->get('page', 1);

        return response()->stream(function () use ($page) {
        
            while (true) {
                $lectores = Lector::paginate(10, ['*'], 'page', $page);

                $data = json_encode([
                    'success'    => true,
                    'message'    => 'Lista de lectores paginada',
                    'data'       => $lectores->items(),
                    'pagination' => [
                        'current_page' => $lectores->currentPage(),
                        'total'        => $lectores->total(),
                        'per_page'     => $lectores->perPage(),
                        'last_page'    => $lectores->lastPage(),
                    ],
                ]);
                

                echo "data: {$data}\n\n";
                @ob_flush();
                flush();
                sleep(10);
            }
        }, 200, [
            'Content-Type'  => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'Connection'    => 'keep-alive',
            'X-Accel-Buffering' => 'no',

        ]);
    }
    public function streamLectoresWithPage1(Request $request)
    {
        $page = $request->get('page', 1);

        $response = new StreamedResponse(function () use ($page) {
            while (true) {
                $lectores = Lector::paginate(10, ['*'], 'page', $page);

                $data = json_encode([
                    'success'    => true,
                    'message'    => 'Lista de lectores paginada',
                    'data'       => $lectores->items(),
                    'pagination' => [
                        'current_page' => $lectores->currentPage(),
                        'total'        => $lectores->total(),
                        'per_page'     => $lectores->perPage(),
                        'last_page'    => $lectores->lastPage(),
                    ],
                ]);

                echo "data: {$data}\n\n";
                @ob_flush();
                flush();
                sleep(10);
            }
        });

        $response->headers->set('Content-Type', 'text/event-stream');
        $response->headers->set('Cache-Control', 'no-cache');
        $response->headers->set('Connection', 'keep-alive');

        return $response;
    }
    public function storeLibrerías(Request $request)
    {
        // Crear la Libreria
        $Libreria = Libreria::create([
            'nombre' => $request->input('nombre'),
            'ubicacion' => $request->input('ubicacion'),
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
    public function updateLibrerías(Request $request, $id)
    {
        //validar los datos
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255',
            'ubicacion' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $validator->errors(),
            ], 422);
        }

        //buscar la libreria
        $libreria = Libreria::find($id);

        if (!$libreria) {
            return response()->json([
                'success' => false,
                'message' => 'Libreria no encontrada',
            ], 404);
        }

        //actualizar la libreria
        $libreria->update([
            'nombre' => $request->input('nombre'),
            'ubicacion' => $request->input('ubicacion'),
        ]);
        return response()->json([
            'Libreria' => $libreria,
        ]);
    }
    public function destroyLibrerías($id)
    {
        //eliminamos todo el inventario de la libreria
        Inventario::where('libreria_id', $id)->delete();
        Libreria::destroy($id);
        return response()->json([
            'success' => true,
            'message' => 'Librería eliminado exitosamente',
            'data' => null
        ], 200);
    }
    ///////////////////////////////////////////////////////////////////////////////
    public function indexParticipacion_evento()
    {
        $ParticipacionEvento = ParticipacionEvento::with(['autor', 'evento'])->get();
        return response()->json([
            'ParticipacionEvento' => $ParticipacionEvento,
        ]);
    }
    public function indexParticipacion_evento2()
    {
        //asemos la paginacion a la consulta
        $ParticipacionEvento = ParticipacionEvento::with(['evento', 'autor'])->paginate(10);
        return response()->json([
            'success' => true,
            'message' => 'Lista de ParticipacionEvento paginada',
            'data' => $ParticipacionEvento->items(),
            'pagination' => [
                'current_page' => $ParticipacionEvento->currentPage(),
                'total' => $ParticipacionEvento->total(),
                'per_page' => $ParticipacionEvento->perPage(),
                'last_page' => $ParticipacionEvento->lastPage(),
            ]
        ]);
    }
    public function storeParticipacion_evento(Request $request)
    {
        //validar los datos
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255',
            'ubicacion' => 'required|string|max:255',
            'nacionalidad' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $validator->errors(),
            ]);
        }
        $faker = Faker::create();

        // Crear el un evento
        $Evento = EventoLiterario::create([
            'nombre' => $request->input('nombre'),
            'fecha' => $request->input('fecha') ?? date('Y-m-d'),
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
        //validar los datos
        $validator = Validator::make($request->all(), [
            'autor_id' => 'required|exists:autors,id',
            'evento_id' => 'required|exists:eventos_literarios,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $validator->errors(),
            ], 422);
        }

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
        ], 200);
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
        ], 200);
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
        ], 200);
    }
    ///////////////////////////////////////////////////////////////////////////////
    public function indexResena()
    {


        $resena = Resena::all();
        return response()->json([
            'resena' => $resena,
        ]);
    }
    public function indexResenas2()
    {

        $resena = Resena::with([])->paginate(10);
        return response()->json([
            'success' => true,
            'resena' => $resena->items(),
            'pagination' => [
                'current_page' => $resena->currentPage(),
                'total' => $resena->total(),
                'per_page' => $resena->perPage(),
                'last_page' => $resena->lastPage(),
            ],
        ]);
    }
    public function storeResena(Request $request)
    {
        // Validaciones
        $validator = Validator::make($request->all(), [
            'calificacion' => 'required|integer|min:1|max:5',
            'comentario' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
            ], 422);
        }

        $resena = Resena::create([
            'lector_id' => 2, // Cambia según tu lógica
            'libro_id' => 8,  // Cambia según tu lógica
            'calificacion' => $request->input('calificacion'),
            'comentario' => $request->input('comentario'),
        ]);

        // Emite la reseña completa para que el front pueda actualizar la lista
        broadcast(new Resenas($resena));

        return response()->json([
            'resena' => $resena,
            'message' => 'Resena creada exitosamente',
        ], 200);
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
        //validaciones
        $validator = Validator::make($request->all(), [
            'calificacion' => 'required|integer|min:1|max:5',
            'comentario' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
            ], 422);
        }
        $resena->update([
            'calificacion' => $request->input('calificacion'),
            'comentario' => $request->input('comentario'),

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
        ], 200);
    }
    //////////////////////////////////////////////////////////////////////////////
    public function indexCategorias()
    {
        $categorias = Categoria::all();
        return response()->json([
            'categorias' => $categorias,
        ]);
    }
    public function indexCategorias2()
    {
        $categorias = Categoria::with([])->paginate(10);
        return response()->json([
            'success' => true,
            'categorias' => $categorias->items(),
            'pagination' => [
                'current_page' => $categorias->currentPage(),
                'total' => $categorias->total(),
                'per_page' => $categorias->perPage(),
                'last_page' => $categorias->lastPage(),
            ],
        ]);
    }
    public function storeCategorias(Request $request)
    {
        $categoria = Categoria::create([
            'nombre' => $request->input('nombre'),
        ]);
        return response()->json([
            'categoria' => $categoria,
            'message' => 'Categoria creada exitosamente',
        ], 200);
    }
    public function showCategorias($id)
    {
        $categoria = Categoria::find($id);
        return response()->json([
            'categoria' => $categoria,
        ]);
    }
    public function updateCategorias(Request $request, Categoria $categoria)
    {
        $categoria->update([
            'nombre' => $request->input('nombre'),
        ]);
        return response()->json([
            'categoria' => $categoria,
        ]);
    }
    public function destroyCategorias($id)
    {
        Categoria::destroy($id);
        return response()->json([
            'message' => 'Categoria eliminado exitosamente',
        ], 200);
    }
}

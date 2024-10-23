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
    public function index(Request $request)
    {
        $externalUrl = env('APP_URL_IP');

        // Obtén el valor del encabezado Authorization
        $authHeader = $request->header('Authorization');

        // Verifica si el encabezado está presente
        if (!$authHeader) {
            return response()->json(['message' => 'Authorization header not found'], 401);
        }

        // Extrae el token Bearer del encabezado
        $token = str_replace('Bearer ', '', $authHeader); // Esto elimina la parte 'Bearer ' y deja solo el token

        // Ahora puedes usar este token para buscar en tu base de datos
        $tokenRecord = Token::where('token3', $token)->first();
        $token4 = $tokenRecord->token4;

        $response =  Http::withOptions(['verify' => false])
            ->withToken($token4)
            ->timeout(80)
            ->get('http://192.168.116.70:5400/verTodo');

        $datas = $response->json();

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
            'Tobias' => $datas

        ]);
    }
    // Almacenar un nuevo libro 
    public function store(Request $request)
    {
        $externalUrl = env('APP_URL_IP');
        $faker = Faker::create(); // Create a new Faker instance
        // Obtén el valor del encabezado Authorization
        $authHeader = $request->header('Authorization');

        // Verifica si el encabezado está presente
        if (!$authHeader) {
            return response()->json(['message' => 'Authorization header not found'], 401);
        }

        // Extrae el token Bearer del encabezado
        $token = str_replace('Bearer ', '', $authHeader); // Esto elimina la parte 'Bearer ' y deja solo el token

        // Ahora puedes usar este token para buscar en tu base de datos
        $tokenRecord = Token::where('token3', $token)->first();
        $token4 = $tokenRecord->token4;
        // -------- Node 2 (Tobias)------- //
        $response = Http::withOptions(['verify' => false])
            ->withToken($token4)
            ->timeout(80)
            // Node -> 2 
            ->post(
                'http://192.168.116.70:5400/crear_artista',
                [
                    'name' => $faker->name
                ]
            );
        $response = $response->json();
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
            'Tobias' => $response
        ]);
    }
    // Mostrar un libro específico
    public function show(Request $request, $id)
    {
        $externalUrl = env('APP_URL_IP');
        // login en la otra api 
        // Obtén el valor del encabezado Authorization
        $authHeader = $request->header('Authorization');

        // Verifica si el encabezado está presente
        if (!$authHeader) {
            return response()->json(['message' => 'Authorization header not found'], 401);
        }

        // Extrae el token Bearer del encabezado
        $token = str_replace('Bearer ', '', $authHeader); // Esto elimina la parte 'Bearer ' y deja solo el token

        // Ahora puedes usar este token para buscar en tu base de datos
        $tokenRecord = Token::where('token3', $token)->first();
        $token4 = $tokenRecord->token4;
        // -------- Node 2 (Tobias)------- //
        $response = Http::withOptions(['verify' => false])
            ->withToken($token4)
            ->timeout(80)
            // Node -> 2 
            ->get('http://192.168.116.70:5400/verUno/' . $id);
        $response = $response->json();

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
            'tobias' => $response
        ]);
    }
    // Actualizar un libro 
    public function update(Request $request, Libro $libro, Autor $autor)
    {
        $externalUrl = env('APP_URL_IP');
        $faker = Faker::create();
        // Obtén el valor del encabezado Authorization
        $authHeader = $request->header('Authorization');

        // Verifica si el encabezado está presente
        if (!$authHeader) {
            return response()->json(['message' => 'Authorization header not found'], 401);
        }

        // Extrae el token Bearer del encabezado
        $token = str_replace('Bearer ', '', $authHeader); // Esto elimina la parte 'Bearer ' y deja solo el token

        // Ahora puedes usar este token para buscar en tu base de datos
        $tokenRecord = Token::where('token3', $token)->first();
        $token4 = $tokenRecord->token4;

        // Realiza la solicitud de actualización al API 
        $response = Http::withOptions(['verify' => false])
            ->withToken($token4)
            ->timeout(80)
            ->put(
                'http://192.168.116.70:5400/actualizar/' . $libro->id,
                [
                    'unitPrice' => $faker->numberBetween(1, 5),
                    'quantity' => $faker->numberBetween(1, 5)
                ]
            );
        $data = $response->json();


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
            'Tobias' => $data
        ]);
    }
    // Eliminar un libro de la base de datos
    public function destroy(Request $request,   $id)
    {
        $externalUrl = env('APP_URL_IP');
        // Obtén el valor del encabezado Authorization
        $authHeader = $request->header('Authorization');

        // Verifica si el encabezado está presente
        if (!$authHeader) {
            return response()->json(['message' => 'Authorization header not found'], 401);
        }

        // Extrae el token Bearer del encabezado
        $token = str_replace('Bearer ', '', $authHeader); // Esto elimina la parte 'Bearer ' y deja solo el token

        // Ahora puedes usar este token para buscar en tu base de datos
        $tokenRecord = Token::where('token3', $token)->first();
        $token4 = $tokenRecord->token4;
        // -------- Node 2 (Tobias)------- //
        $response = Http::withOptions(['verify' => false])
            ->withToken($token4)
            ->timeout(80)
            // Node -> 2 
            ->delete('http://192.168.116.70:5400/destroy/' . $id);
        $response = $response->json();
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
            return response()->json(null, 204);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el libro',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    ///////////////////////////////////////////////////////////////////////////
    public function indexAutor(Request $request)
    {

        $externalUrl = env('APP_URL_IP');
        
        // Obtén el valor del encabezado Authorization
        $authHeader = $request->header('Authorization');

        // Verifica si el encabezado está presente
        if (!$authHeader) {
            return response()->json(['message' => 'Authorization header not found'], 401);
        }

        // Extrae el token Bearer del encabezado
        $token = str_replace('Bearer ', '', $authHeader); // Esto elimina la parte 'Bearer ' y deja solo el token

        // Ahora puedes usar este token para buscar en tu base de datos
        $tokenRecord = Token::where('token3', $token)->first();
        $token4 = $tokenRecord->token4;

        // Realiza la solicitud de actualización al API 
        $response = Http::withOptions(['verify' => false])
        ->withToken($token4)
        ->timeout(80)
            ->get('http://192.168.116.70:5400/Employee');

        $data = $response->json();

        $autor = Autor::all();
        return response()->json([
            'autor' => $autor,
            'Tobias' => $data,
        ]);
    }
    public function storeAutor(Request $request)
    {

        $faker = Faker::create();
        $externalUrl = env('APP_URL_IP');
        // Realiza la solicitud de login para obtener el token (método POST)
        $login = Http::post('http://192.168.116.70:5400/login', [
            'email' => $request->input('email'),
            'password' => $request->input('password')
        ]);
        $token = $login->json()['token'];
        // Realiza la solicitud de actualización al API 
        $response = Http::withToken($token)
            ->timeout(80)
            ->post('http://192.168.116.70:5400/employee', [
                'name' => $faker->name
            ]);
        $data = $response->json();

        // Crear el autor
        $autor = Autor::create([
            'nombre' => $request->input('nombre'),
            'nacionalidad' => $request->input('nacionalidad'),
        ]);

        return response()->json([
            'autor' => $autor,
            'Tobias' => $data
        ]);
    }
    public function showAutor(Request $request, $id)
    {
        $externalUrl = env('APP_URL_IP');
        // Realiza la solicitud de login para obtener el token (método POST)
        $login = Http::post('http://192.168.116.70:5400/login', [
            'email' => $request->input('email'),
            'password' => $request->input('password')
        ]);
        $token = $login->json()['token'];
        // Realiza la solicitud de actualización al API 
        $response = Http::withToken($token)
            ->timeout(80)
            ->get('http://192.168.116.70:5400/employee/' . $id);
        $data = $response->json();
        $autor = Autor::with(['libros'])->find($id);
        return response()->json([
            'autor' => $autor,
            'Tobias' => $data
        ]);
    }
    public function updateAutor(Request $request, $id, Autor $autor)
    {
        $externalUrl = env('APP_URL_IP');
        // Realiza la solicitud de login para obtener el token (método POST)
        $login = Http::post('http://192.168.116.70:5400/login', [
            'email' => $request->input('email'),
            'password' => $request->input('password')
        ]);
        $token = $login->json()['token'];
        // Realiza la solicitud de actualización al API 
        $response = Http::withToken($token)
            ->timeout(80)
            ->put('http://192.168.116.70:5400/employee/' . $id, [
                'name' => $request->input('name')
            ]);
        $data = $response->json();
        $autor->update([
            'nacionalidad' => $request->input('nacionalidad'),
            'nombre' => $request->input('nombre'),
        ]);
        return response()->json([
            'autor' => $autor,
            'Tobias' => $data
        ]);
    }
    public function destroyAutor(Request $request, $id)
    {
        $externalUrl = env('APP_URL_IP');
        // Realiza la solicitud de login para obtener el token (método POST)
        $login = Http::post('http://192.168.116.70:5400/login', [
            'email' => $request->input('email'),
            'password' => $request->input('password')
        ]);
        $token = $login->json()['token'];
        // Realiza la solicitud de actualización al API 
        $response = Http::withToken($token)
            ->timeout(80)
            ->delete('http://192.168.116.70:5400/employee/' . $id);
        $data = $response->json();
        $autor = Autor::destroy($id);
        return response()->json([
            'autor' => $autor,
            'Tobias' => $data
        ]);
    }
    ///////////////////////////////////////////////////////////////////////////////
    public function indexEditorials(Request $request)
    {

        $externalUrl = env('APP_URL_IP');
        // Realiza la solicitud de login para obtener el token (método POST)
        $login = Http::post('http://' + $externalUrl . '/login', [
            'email' => $request->input('email'),
            'password' => $request->input('password')
        ]);
        $token = $login->json()['token'];

        // Realiza la solicitud de actualización al API 
        $response = Http::withToken($token)
            ->timeout(80)
            ->get('http://192.168.116.70:5400/customers');

        $data = $response->json();

        $editorial = Editorial::all();
        return response()->json([
            'editorial' => $editorial,
            'Tobias' => $data,
        ]);
    }
    public function storeEditorials(Request $request)
    {

        $faker = Faker::create();
        $externalUrl = env('APP_URL_IP');
        // Realiza la solicitud de login para obtener el token (método POST)
        $login = Http::post('http://192.168.116.70:5400/login', [
            'email' => $request->input('email'),
            'password' => $request->input('password')
        ]);
        $token = $login->json()['token'];
        // Realiza la solicitud de actualización al API 
        $response = Http::withToken($token)
            ->timeout(80)
            ->post('http://192.168.116.70:5400/customer', [
                'name' => $faker->name
            ]);
        $data = $response->json();

        // Crear el editorial
        $editorial = Editorial::create([
            'nombre' => $request->input('nombre'),
            'pais' => $request->input('pais'),
        ]);

        return response()->json([
            'editorial' => $editorial,
            'Tobias' => $data
        ]);
    }
    public function showEditorials(Request $request, $id)
    {
        $externalUrl = env('APP_URL_IP');
        // Realiza la solicitud de login para obtener el token (método POST)
        $login = Http::post('http://192.168.116.70:5400/login', [
            'email' => $request->input('email'),
            'password' => $request->input('password')
        ]);
        $token = $login->json()['token'];
        // Realiza la solicitud de actualización al API 
        $response = Http::withToken($token)
            ->timeout(80)
            ->get('http://192.168.116.70:5400/customer/' . $id);
        $data = $response->json();
        $editorial = Editorial::with(['editoriales'])->find($id);
        return response()->json([
            'editorial' => $editorial,
            'Tobias' => $data
        ]);
    }
    public function updateEditorials(Request $request, $id, Editorial $editorial)
    {
        $externalUrl = env('APP_URL_IP');
        // Realiza la solicitud de login para obtener el token (método POST)
        $login = Http::post('http://192.168.116.70:5400/login', [
            'email' => $request->input('email'),
            'password' => $request->input('password')
        ]);
        $token = $login->json()['token'];
        // Realiza la solicitud de actualización al API 
        $response = Http::withToken($token)
            ->timeout(80)
            ->put('http://192.168.116.70:5400/customer/' . $id, [
                'name' => $request->input('name')
            ]);
        $data = $response->json();
        $editorial->update([
            'pais' => $request->input('pais'),
            'nombre' => $request->input('nombre'),
        ]);
        return response()->json([
            'editorial' => $editorial,
            'Tobias' => $data
        ]);
    }
    public function destroyEditorials(Request $request, $id)
    {
        $externalUrl = env('APP_URL_IP');
        // Realiza la solicitud de login para obtener el token (método POST)
        $login = Http::post('http://192.168.116.70:5400/login', [
            'email' => $request->input('email'),
            'password' => $request->input('password')
        ]);
        $token = $login->json()['token'];
        // Realiza la solicitud de actualización al API 
        $response = Http::withToken($token)
            ->timeout(80)
            ->delete('http://192.168.116.70:5400/customer/' . $id);
        $data = $response->json();
        $editorial = Editorial::destroy($id);
        return response()->json([
            'Editorial' => $editorial,
            'Tobias' => $data
        ]);
    }
    ///////////////////////////////////////////////////////////////////////////////
    public function indexEntos_literarios(Request $request)
    {

        $externalUrl = env('APP_URL_IP');
        // Realiza la solicitud de login para obtener el token (método POST)
        $login = Http::post('http://' + $externalUrl . '/login', [
            'email' => $request->input('email'),
            'password' => $request->input('password')
        ]);
        $token = $login->json()['token'];

        // Realiza la solicitud de actualización al API 
        $response = Http::withToken($token)
            ->timeout(80)
            ->get('http://192.168.116.70:5400/invoice_lines');

        $data = $response->json();

        $Evento_literario = Eventoliterario::all();
        return response()->json([
            'Evento_literario' => $Evento_literario,
            'Tobias' => $data,
        ]);
    }
    public function storeEntos_literarios(Request $request)
    {

        $faker = Faker::create();
        $externalUrl = env('APP_URL_IP');
        // Realiza la solicitud de login para obtener el token (método POST)
        $login = Http::post('http://192.168.116.70:5400/login', [
            'email' => $request->input('email'),
            'password' => $request->input('password')
        ]);
        $token = $login->json()['token'];
        // Realiza la solicitud de actualización al API 
        $response = Http::withToken($token)
            ->timeout(80)
            ->post('http://192.168.116.70:5400/invoice_lines', [
                'name' => $faker->name
            ]);
        $data = $response->json();

        // Crear el Evento_literario
        $Evento_literario = Eventoliterario::create([
            'nombre' => $request->input('nombre'),
            'fecha' => $request->input('fecha'),
            'ubicacion' => $request->input('ubicacion'),
        ]);

        return response()->json([
            'Evento_literario' => $Evento_literario,
            'Tobias' => $data
        ]);
    }
    public function showEntos_literarios(Request $request, $id)
    {
        $externalUrl = env('APP_URL_IP');
        // Realiza la solicitud de login para obtener el token (método POST)
        $login = Http::post('http://192.168.116.70:5400/login', [
            'email' => $request->input('email'),
            'password' => $request->input('password')
        ]);
        $token = $login->json()['token'];
        // Realiza la solicitud de actualización al API 
        $response = Http::withToken($token)
            ->timeout(80)
            ->get('http://192.168.116.70:5400/invoice_lines/' . $id);
        $data = $response->json();
        $Evento_literario = Eventoliterario::find($id);
        return response()->json([
            'E$Evento_literario' => $Evento_literario,
            'Tobias' => $data
        ]);
    }
    public function updateEntos_literarios(Request $request, $id, Eventoliterario $eventoliterario)
    {
        $externalUrl = env('APP_URL_IP');
        // Realiza la solicitud de login para obtener el token (método POST)
        $login = Http::post('http://192.168.116.70:5400/login', [
            'email' => $request->input('email'),
            'password' => $request->input('password')
        ]);
        $token = $login->json()['token'];
        // Realiza la solicitud de actualización al API 
        $response = Http::withToken($token)
            ->timeout(80)
            ->put('http://192.168.116.70:5400/invoice_lines/' . $id, [
                'name' => $request->input('name')
            ]);
        $data = $response->json();
        $eventoliterario->update([
            'nombre' => $request->input('nombre'),
            'fecha' => $request->input('fecha'),
            'ubicacion' => $request->input('ubicacion'),
        ]);
        return response()->json([
            'Entos_literarios' => $eventoliterario,
            'Tobias' => $data
        ]);
    }
    public function destroyEntos_literarios(Request $request, $id)
    {
        $externalUrl = env('APP_URL_IP');
        // Realiza la solicitud de login para obtener el token (método POST)
        $login = Http::post('http://192.168.116.70:5400/login', [
            'email' => $request->input('email'),
            'password' => $request->input('password')
        ]);
        $token = $login->json()['token'];
        // Realiza la solicitud de actualización al API 
        $response = Http::withToken($token)
            ->timeout(80)
            ->delete('http://192.168.116.70:5400/invoice_lines/' . $id);
        $data = $response->json();
        $Entos_literarios = Eventoliterario::destroy($id);
        return response()->json([
            'Entos_literarios' => $Entos_literarios,
            'Tobias' => $data
        ]);
    }
    ///////////////////////////////////////////////////////////////////////////////
    public function indexLectores(Request $request)
    {

        $externalUrl = env('APP_URL_IP');
        // Realiza la solicitud de login para obtener el token (método POST)
        $login = Http::post('http://' + $externalUrl . '/login', [
            'email' => $request->input('email'),
            'password' => $request->input('password')
        ]);
        $token = $login->json()['token'];

        // Realiza la solicitud de actualización al API 
        $response = Http::withToken($token)
            ->timeout(80)
            ->get('http://192.168.116.70:5400/tracks');

        $data = $response->json();

        $Lectores = Lector::all();
        return response()->json([
            'Lectores' => $Lectores,
            'Tobias' => $data,
        ]);
    }
    public function storeLectores(Request $request)
    {

        $faker = Faker::create();
        $externalUrl = env('APP_URL_IP');
        // Realiza la solicitud de login para obtener el token (método POST)
        $login = Http::post('http://192.168.116.70:5400/login', [
            'email' => $request->input('emails'),
            'password' => $request->input('password')
        ]);
        $token = $login->json()['token'];
        // Realiza la solicitud de actualización al API 
        $response = Http::withToken($token)
            ->timeout(80)
            ->post('http://192.168.116.70:5400/tracks', [
                'name' => $faker->name
            ]);
        $data = $response->json();

        // Crear el Lector
        $Lector = Lector::create([
            'nombre' => $request->input('nombre'),
            'email' => $request->input('email'),
        ]);

        return response()->json([
            'Lector' => $Lector,
            'Tobias' => $data
        ]);
    }
    public function showLectores(Request $request, $id)
    {
        $externalUrl = env('APP_URL_IP');
        // Realiza la solicitud de login para obtener el token (método POST)
        $login = Http::post('http://192.168.116.70:5400/login', [
            'email' => $request->input('email'),
            'password' => $request->input('password')
        ]);
        $token = $login->json()['token'];
        // Realiza la solicitud de actualización al API 
        $response = Http::withToken($token)
            ->timeout(80)
            ->get('http://192.168.116.70:5400/tracks/' . $id);
        $data = $response->json();
        $Lector = Lector::find($id);
        return response()->json([
            'Lector' => $Lector,
            'Tobias' => $data
        ]);
    }
    public function updateLectores(Request $request, $id, Lector $lector)
    {
        $externalUrl = env('APP_URL_IP');
        // Realiza la solicitud de login para obtener el token (método POST)
        $login = Http::post('http://192.168.116.70:5400/login', [
            'email' => $request->input('emails'),
            'password' => $request->input('password')
        ]);
        $token = $login->json()['token'];
        // Realiza la solicitud de actualización al API 
        $response = Http::withToken($token)
            ->timeout(80)
            ->put('http://192.168.116.70:5400/tracks/' . $id, [
                'name' => $request->input('name')
            ]);
        $data = $response->json();
        $lector->update([
            'email' => $request->input('email'),
            'nombre' => $request->input('nombre'),
        ]);
        return response()->json([
            'lector' => $lector,
            'Tobias' => $data
        ]);
    }
    public function destroyLectores(Request $request, $id)
    {
        $externalUrl = env('APP_URL_IP');
        // Realiza la solicitud de login para obtener el token (método POST)
        $login = Http::post('http://192.168.116.70:5400/login', [
            'email' => $request->input('email'),
            'password' => $request->input('password')
        ]);
        $token = $login->json()['token'];
        // Realiza la solicitud de actualización al API 
        $response = Http::withToken($token)
            ->timeout(80)
            ->delete('http://192.168.116.70:5400/tracks/' . $id);
        $data = $response->json();

        $lector = Lector::destroy($id);
        return response()->json([
            'lector' => $lector,
            'Tobias' => $data
        ]);
    }
    ///////////////////////////////////////////////////////////////////////////////
    public function indexLibrerías(Request $request)
    {

        $externalUrl = env('APP_URL_IP');
        // Realiza la solicitud de login para obtener el token (método POST)
        $login = Http::post('http://' + $externalUrl . '/login', [
            'email' => $request->input('email'),
            'password' => $request->input('password')
        ]);
        $token = $login->json()['token'];

        // Realiza la solicitud de actualización al API 
        $response = Http::withToken($token)
            ->timeout(80)
            ->get('http://192.168.116.70:5400/genres');

        $data = $response->json();

        $Librerias = Libreria::all();
        return response()->json([
            'Librerias' => $Librerias,
            'Tobias' => $data,
        ]);
    }
    public function storeLibrerías(Request $request)
    {

        $faker = Faker::create();
        $externalUrl = env('APP_URL_IP');
        // Realiza la solicitud de login para obtener el token (método POST)
        $login = Http::post('http://192.168.116.70:5400/login', [
            'email' => $request->input('email'),
            'password' => $request->input('password')
        ]);
        $token = $login->json()['token'];
        // Realiza la solicitud de actualización al API 
        $response = Http::withToken($token)
            ->timeout(80)
            ->post('http://192.168.116.70:5400/genres', [
                'name' => $faker->name
            ]);
        $data = $response->json();

        // Crear el Libreria
        $Libreria = Libreria::create([
            'nombre' => $request->input('nombre'),
            'ubucacion' => $request->input('ubucacion'),
        ]);
        return response()->json([
            'Libreria' => $Libreria,
            'Tobias' => $data
        ]);
    }
    public function showLibrerías(Request $request, $id)
    {
        $externalUrl = env('APP_URL_IP');
        // Realiza la solicitud de login para obtener el token (método POST)
        $login = Http::post('http://192.168.116.70:5400/login', [
            'email' => $request->input('email'),
            'password' => $request->input('password')
        ]);
        $token = $login->json()['token'];
        // Realiza la solicitud de actualización al API 
        $response = Http::withToken($token)
            ->timeout(80)
            ->get('http://192.168.116.70:5400/genres/' . $id);
        $data = $response->json();
        $Libreria = Libreria::find($id);
        return response()->json([
            'Libreria' => $Libreria,
            'Tobias' => $data
        ]);
    }
    public function updateLibrerías(Request $request, $id, Libreria $libreria)
    {
        $externalUrl = env('APP_URL_IP');
        // Realiza la solicitud de login para obtener el token (método POST)
        $login = Http::post('http://192.168.116.70:5400/login', [
            'email' => $request->input('email'),
            'password' => $request->input('password')
        ]);
        $token = $login->json()['token'];
        // Realiza la solicitud de actualización al API 
        $response = Http::withToken($token)
            ->timeout(80)
            ->put('http://192.168.116.70:5400/genres/' . $id, [
                'name' => $request->input('name')
            ]);
        $data = $response->json();

        $libreria->update([
            'nombre' => $request->input('nombre'),
            'ubucacion' => $request->input('ubucacion'),
        ]);
        return response()->json([
            'Libreria' => $libreria,
            'Tobias' => $data
        ]);
    }
    public function destroyLibrerías(Request $request, $id)
    {
        $externalUrl = env('APP_URL_IP');
        // Realiza la solicitud de login para obtener el token (método POST)
        $login = Http::post('http://192.168.116.70:5400/login', [
            'email' => $request->input('email'),
            'password' => $request->input('password')
        ]);
        $token = $login->json()['token'];
        // Realiza la solicitud de actualización al API 
        $response = Http::withToken($token)
            ->timeout(80)
            ->delete('http://192.168.116.70:5400/genres/' . $id);
        $data = $response->json();
        $Libreria = Libreria::destroy($id);
        return response()->json([
            'Libreria' => $Libreria,
            'Tobias' => $data
        ]);
    }
    ///////////////////////////////////////////////////////////////////////////////
    public function indexParticipacion_evento(Request $request)
    {

        $externalUrl = env('APP_URL_IP');
        // Realiza la solicitud de login para obtener el token (método POST)
        $login = Http::post('http://' + $externalUrl . '/login', [
            'email' => $request->input('email'),
            'password' => $request->input('password')
        ]);
        $token = $login->json()['token'];

        // Realiza la solicitud de actualización al API 
        $response = Http::withToken($token)
            ->timeout(80)
            ->get('http://192.168.116.70:5400/albums');

        $data = $response->json();

        $ParticipacionEvento = ParticipacionEvento::with(['autor', 'evento'])->get();
        return response()->json([
            'ParticipacionEvento' => $ParticipacionEvento,
            'Tobias' => $data,
        ]);
    }
    public function storeParticipacion_evento(Request $request)
    {

        $faker = Faker::create();
        $externalUrl = env('APP_URL_IP');
        // Realiza la solicitud de login para obtener el token (método POST)
        $login = Http::post('http://192.168.116.70:5400/login', [
            'email' => $request->input('email'),
            'password' => $request->input('password')
        ]);
        $token = $login->json()['token'];
        // Realiza la solicitud de actualización al API 
        $response = Http::withToken($token)
            ->timeout(80)
            ->post('http://192.168.116.70:5400/albums', [
                'name' => $faker->name
            ]);
        $data = $response->json();

        // Crear el un evento
        $Evento = EventoLiterario::create([
            'name' => $request->input('name'),
            'fecha' => $request->input('fecha'),
            'ubicacion' => $request->input('ubicacion'),
        ]);
        // Crear el autor
        $Autor = Autor::create([
            'name' => $faker->name,
            'nacionalidad' => $request->input('nacionalidad'),
        ]);
        // Crear el ParticipacionEvento
        $participacionEvento = ParticipacionEvento::create([
            'autor_id' => $Autor->id,
            'evento_id' => $Evento->id,
        ]);
        return response()->json([
            'ParticipacionEvento' => $participacionEvento,
            'Tobias' => $data
        ]);
    }
    public function showParticipacion_evento(Request $request, $id)
    {
        $externalUrl = env('APP_URL_IP');
        // Realiza la solicitud de login para obtener el token (método POST)
        $login = Http::post('http://192.168.116.70:5400/login', [
            'email' => $request->input('email'),
            'password' => $request->input('password')
        ]);
        $token = $login->json()['token'];
        // Realiza la solicitud de actualización al API 
        $response = Http::withToken($token)
            ->timeout(80)
            ->get('http://192.168.116.70:5400/albums/' . $id);
        $data = $response->json();
        $participacionEvento = ParticipacionEvento::with(['autor', 'evento'])->find($id);
        return response()->json([
            'ParticipacionEvento' => $participacionEvento,
            'Tobias' => $data
        ]);
    }
    public function updateParticipacion_evento(Request $request, $id, ParticipacionEvento $participacionEvento)
    {
        $externalUrl = env('APP_URL_IP');
        // Realiza la solicitud de login para obtener el token (método POST)
        $login = Http::post('http://192.168.116.70:5400/login', [
            'email' => $request->input('email'),
            'password' => $request->input('password')
        ]);
        $token = $login->json()['token'];
        // Realiza la solicitud de actualización al API 
        $response = Http::withToken($token)
            ->timeout(80)
            ->put('http://192.168.116.70:5400/albums/' . $id, [
                'name' => $request->input('name')
            ]);
        $data = $response->json();
        $participacionEvento->update([
            'autor_id' => $request->input('autor_id'),
            'evento_id' => $request->input('evento_id'),
        ]);

        return response()->json([
            'ParticipacionEvento' => $participacionEvento,
            'Tobias' => $data
        ]);
    }
    public function destroyParticipacion_evento(Request $request, $id)
    {
        $externalUrl = env('APP_URL_IP');
        // Realiza la solicitud de login para obtener el token (método POST)
        $login = Http::post('http://192.168.116.70:5400/login', [
            'email' => $request->input('email'),
            'password' => $request->input('password')
        ]);
        $token = $login->json()['token'];
        // Realiza la solicitud de actualización al API 
        $response = Http::withToken($token)
            ->timeout(80)
            ->delete('http://192.168.116.70:5400/albums/' . $id);
        $data = $response->json();
        $ParticipacionEvento = ParticipacionEvento::destroy($id);
        return response()->json([
            'ParticipacionEvento' => $ParticipacionEvento,
            'Tobias' => $data
        ]);
    }
    ///////////////////////////////////////////////////////////////////////////////
    public function indexPrestamos(Request $request)
    {

        $externalUrl = env('APP_URL_IP');
        // Realiza la solicitud de login para obtener el token (método POST)
        $login = Http::post('http://' + $externalUrl . '/login', [
            'email' => $request->input('email'),
            'password' => $request->input('password')
        ]);
        $token = $login->json()['token'];

        // Realiza la solicitud de actualización al API 
        $response = Http::withToken($token)
            ->timeout(80)
            ->get('http://192.168.116.70:5400/playlist_tracks');

        $data = $response->json();
        $Prestamos = Prestamo::all();
        return response()->json([
            'Prestamos' => $Prestamos,
            'Tobias' => $data,
        ]);
    }
    public function storePrestamos(Request $request)
    {

        $faker = Faker::create();
        $externalUrl = env('APP_URL_IP');
        // Realiza la solicitud de login para obtener el token (método POST)
        $login = Http::post('http://192.168.116.70:5400/login', [
            'email' => $request->input('email'),
            'password' => $request->input('password')
        ]);
        $token = $login->json()['token'];
        // Realiza la solicitud de actualización al API 
        $response = Http::withToken($token)
            ->timeout(80)
            ->post('http://192.168.116.70:5400/playlist_tracks', [
                'name' => $faker->name
            ]);
        $data = $response->json();
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
            'Tobias' => $data
        ]);
    }
    public function showPrestamos(Request $request, $id)
    {
        $externalUrl = env('APP_URL_IP');
        // Realiza la solicitud de login para obtener el token (método POST)
        $login = Http::post('http://192.168.116.70:5400/login', [
            'email' => $request->input('email'),
            'password' => $request->input('password')
        ]);
        $token = $login->json()['token'];
        // Realiza la solicitud de actualización al API 
        $response = Http::withToken($token)
            ->timeout(80)
            ->get('http://192.168.116.70:5400/playlist_tracks/' . $id);
        $data = $response->json();
        $prestamo = Prestamo::find($id);
        return response()->json([
            'Prestamos' => $prestamo,
            'Tobias' => $data
        ]);
    }
    public function updatePrestamos(Request $request, $id, Prestamo $prestamo)
    {
        $externalUrl = env('APP_URL_IP');
        // Realiza la solicitud de login para obtener el token (método POST)
        $login = Http::post('http://192.168.116.70:5400/login', [
            'email' => $request->input('email'),
            'password' => $request->input('password')
        ]);
        $token = $login->json()['token'];
        // Realiza la solicitud de actualización al API 
        $response = Http::withToken($token)
            ->timeout(80)
            ->put('http://192.168.116.70:5400/playlist_tracks/' . $id, [
                'name' => $request->input('name')
            ]);
        $data = $response->json();
        $prestamo = $prestamo->update([
            'fecha_prestamo' => $request->input('fecha_prestamo'),
            'fecha_devolucion' => $request->input('fecha_devolucion'),
        ]);
        return response()->json([
            'Prestamos' => $prestamo,
            'Tobias' => $data
        ]);
    }
    public function destroyPrestamos(Request $request, $id)
    {
        $externalUrl = env('APP_URL_IP');
        // Realiza la solicitud de login para obtener el token (método POST)
        $login = Http::post('http://192.168.116.70:5400/login', [
            'email' => $request->input('email'),
            'password' => $request->input('password')
        ]);
        $token = $login->json()['token'];
        // Realiza la solicitud de actualización al API 
        $response = Http::withToken($token)
            ->timeout(80)
            ->delete('http://192.168.116.70:5400/playlist_tracks/' . $id);
        $data = $response->json();
        $Prestamos = Prestamo::destroy($id);
        return response()->json([
            'Prestamos' => $Prestamos,
            'Tobias' => $data
        ]);
    }
    ///////////////////////////////////////////////////////////////////////////////
    public function indexPublicaciones(Request $request)
    {

        $externalUrl = env('APP_URL_IP');
        // Realiza la solicitud de login para obtener el token (método POST)
        $login = Http::post('http://' + $externalUrl . '/login', [
            'email' => $request->input('email'),
            'password' => $request->input('password')
        ]);
        $token = $login->json()['token'];

        // Realiza la solicitud de actualización al API 
        $response = Http::withToken($token)
            ->timeout(80)
            ->get('http://192.168.116.70:5400/playlist');

        $data = $response->json();
        $publicaciones = Publicacion::all();
        return response()->json([
            'publicaciones' => $publicaciones,
            'Tobias' => $data,
        ]);
    }

    public function storePublicaciones(Request $request)
    {

        $faker = Faker::create();
        $externalUrl = env('APP_URL_IP');
        // Realiza la solicitud de login para obtener el token (método POST)
        $login = Http::post('http://192.168.116.70:5400/login', [
            'email' => $request->input('email'),
            'password' => $request->input('password')
        ]);
        $token = $login->json()['token'];
        // Realiza la solicitud de actualización al API 
        $response = Http::withToken($token)
            ->timeout(80)
            ->post('http://192.168.116.70:5400/playlist', [
                'name' => $faker->name
            ]);
        $data = $response->json();
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
            'Tobias' => $data
        ]);
    }
    public function showPublicaciones(Request $request, $id)
    {
        $externalUrl = env('APP_URL_IP');
        // Realiza la solicitud de login para obtener el token (método POST)
        $login = Http::post('http://192.168.116.70:5400/login', [
            'email' => $request->input('email'),
            'password' => $request->input('password')
        ]);
        $token = $login->json()['token'];
        // Realiza la solicitud de actualización al API 
        $response = Http::withToken($token)
            ->timeout(80)
            ->get('http://192.168.116.70:5400/playlist/' . $id);
        $data = $response->json();
        $publicaciones = Publicacion::find($id);
        return response()->json([
            'publicaciones' => $publicaciones,
            'Tobias' => $data
        ]);
    }
    public function updatePublicaciones(Request $request, $id, Publicacion $publicacion)
    {
        $externalUrl = env('APP_URL_IP');
        // Realiza la solicitud de login para obtener el token (método POST)
        $login = Http::post('http://' + $externalUrl + '/login', [
            'email' => $request->input('email'),
            'password' => $request->input('password')
        ]);
        $token = $login->json()['token'];
        // Realiza la solicitud de actualización al API 
        $response = Http::withToken($token)
            ->timeout(80)
            ->put('http://' + $externalUrl + '/playlist/' . $id, [
                'name' => $request->input('name')
            ]);
        $data = $response->json();
        $publicacion->update([
            'fecha_publicacion' => $request->input('fecha_publicacion'),
        ]);
        return response()->json([
            'publicaciones' => $publicacion,
            'Tobias' => $data
        ]);
    }
    public function destroyPublicaciones(Request $request, $id)
    {
        $externalUrl = env('APP_URL_IP');
        // Realiza la solicitud de login para obtener el token (método POST)
        $login = Http::post('http://' + $externalUrl + '/login', [
            'email' => $request->input('email'),
            'password' => $request->input('password')
        ]);
        $token = $login->json()['token'];
        // Realiza la solicitud de actualización al API 
        $response = Http::withToken($token)
            ->timeout(80)
            ->delete('http://' + $externalUrl + '/playlist/' . $id);
        $data = $response->json();
        $publicaciones = Publicacion::destroy($id);
        return response()->json([
            'publicaciones' => $publicaciones,
            'Tobias' => $data
        ]);
    }
    ///////////////////////////////////////////////////////////////////////////////
    public function indexResena(Request $request)
    {

        $externalUrl = env('APP_URL_IP');
        // Realiza la solicitud de login para obtener el token (método POST)
        $login = Http::post('http://' + $externalUrl . '/login', [
            'email' => $request->input('email'),
            'password' => $request->input('password')
        ]);
        $token = $login->json()['token'];

        // Realiza la solicitud de actualización al API 
        $response = Http::withToken($token)
            ->timeout(80)
            ->get('http://' + $externalUrl + '/media_type');

        $data = $response->json();
        $resena = Resena::all();
        return response()->json([
            'resena' => $resena,
            'Tobias' => $data,
        ]);
    }
    public function storeResena(Request $request)
    {

        $faker = Faker::create();
        $externalUrl = env('APP_URL_IP');
        // Realiza la solicitud de login para obtener el token (método POST)
        $login = Http::post('http://' + $externalUrl + '/login', [
            'email' => $request->input('email'),
            'password' => $request->input('password')
        ]);
        $token = $login->json()['token'];
        // Realiza la solicitud de actualización al API 
        $response = Http::withToken($token)
            ->timeout(80)
            ->post('http://' + $externalUrl + '/media_type', [
                'name' => $faker->name
            ]);
        $data = $response->json();

        $resena = Resena::create([
            'calificacion' => $request->input('calificacion'),
            'comentario' => $request->input('comentarios'),
        ]);


        return response()->json([
            'resena' => $resena,
            'Tobias' => $data
        ]);
    }
    public function showResena(Request $request, $id)
    {
        $externalUrl = env('APP_URL_IP');
        // Realiza la solicitud de login para obtener el token (método POST)
        $login = Http::post('http://' + $externalUrl + '/login', [
            'email' => $request->input('email'),
            'password' => $request->input('password')
        ]);
        $token = $login->json()['token'];
        // Realiza la solicitud de actualización al API 
        $response = Http::withToken($token)
            ->timeout(80)
            ->get('http://' + $externalUrl + '/media_type/' . $id);
        $data = $response->json();
        $resena = Resena::find($id);
        return response()->json([
            'resena' => $resena,
            'Tobias' => $data
        ]);
    }
    public function updateResena(Request $request, $id, Resena $resena)
    {
        $externalUrl = env('APP_URL_IP');
        // Realiza la solicitud de login para obtener el token (método POST)
        $login = Http::post('http://' + $externalUrl + '/login', [
            'email' => $request->input('email'),
            'password' => $request->input('password')
        ]);
        $token = $login->json()['token'];
        // Realiza la solicitud de actualización al API 
        $response = Http::withToken($token)
            ->timeout(80)
            ->put('http://' + $externalUrl + '/media_type/' . $id, [
                'name' => $request->input('name')
            ]);
        $data = $response->json();
        $resena->update([
            'calificacion' => $request->input('calificacion'),
            'comentario' => $request->input('comentarios'),

        ]);
        return response()->json([
            'resena' => $resena,
            'Tobias' => $data
        ]);
    }
    public function destroyResena(Request $request, $id)
    {
        $externalUrl = env('APP_URL_IP');
        // Realiza la solicitud de login para obtener el token (método POST)
        $login = Http::post('http://' + $externalUrl + '/login', [
            'email' => $request->input('email'),
            'password' => $request->input('password')
        ]);
        $token = $login->json()['token'];
        // Realiza la solicitud de actualización al API 
        $response = Http::withToken($token)
            ->timeout(80)
            ->delete('http://' + $externalUrl + '/media_type/' . $id);
        $data = $response->json();
        $resena = Resena::destroy($id);
        return response()->json([
            'resena' => $resena,
            'Tobias' => $data
        ]);
    }
    ///////////////////////////////////////////////////////////////////////////////

}

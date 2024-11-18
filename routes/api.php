<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LibroController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TokenController;
use App\Http\Controllers\EmailController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\Juego;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// hello
Route::get('/hello', function () {
    return 'hello';
});

// rutas que solo puedes acceder si eres usuario 
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/user', [AuthController::class, 'me']);
});

// rutas que solo puedes acceder si eres admin }
Route::middleware(['admin', 'auth:sanctum'])->group(function () {
    Route::post('/activate', [AuthController::class, 'activateUser']);
    Route::get('/admin', [AdminController::class, 'index']);
    Route::put('/admin', [AdminController::class, 'update']);

});
Route::get('/activate/{user}', [AuthController::class, 'activateAccount'])->name('user.activate')->middleware('signed');
Route::post('/activation-link', [AuthController::class, 'resendActivationLink'])->name('activation-link');

Route::post('/register', [AuthController::class, 'register_sanctum'])->name('register');
Route::post('/login', [AuthController::class, 'login_sanctum'])->name('login');

Route::post('/picture', [ImageController::class, 'subirImagen'])->middleware('auth:sanctum');
Route::get('/picture', [ImageController::class, 'obtenerImagen'])->middleware('auth:sanctum');
//con s3
Route::post('/picture-s3', [ImageController::class, 'uploadProfilePicture'])->middleware('auth:sanctum');
Route::get('/picture-s3', [ImageController::class, 'getProfilePicture'])->middleware('auth:sanctum');


Route::post('/token-command', [TokenController::class, 'store']);
Route::get('/token-command', [TokenController::class, 'show']); // Ruta para buscar el token por correo

//tokmail
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/index', [EmailController::class, 'index']);
    Route::get('/email-command', [EmailController::class, 'sendEmail']);
    Route::post('/email', [EmailController::class, 'archivo']);
});

Route::middleware(['auth:sanctum','CheckUserRole','CheckActive','CheckInActive'])->group(function () {

Route::post('/game', [Juego::class, 'game']);
Route::post('/join/{id}', [Juego::class, 'join'])
    ->where('id', '[0-9]+');
Route::post('/barcos/{id}', [Juego::class, 'barcos'])
    ->where('id', '[0-9]+');
Route::post('/atacar/{id}', [Juego::class, 'atacar'])
    ->where('id', '[0-9]+');
Route::post('/abandonar/{id}', [Juego::class, 'abandonar'])
    ->where('id', '[0-9]+');
Route::post('/consultaratakes/{id}', [Juego::class, 'consultaratakes'])
    ->where('id', '[0-9]+');
Route::post('/consultar/{id}', [Juego::class, 'consultar'])
    ->where('id', '[0-9]+');
Route::post('/partidosjuego', [Juego::class, 'partidosjuego']);

});


Route::middleware(['auth:sanctum'])->group(function () {
    // Mostrar todos
    Route::get('/v1/libros', [LibroController::class, 'index'])->middleware('checkUserRole');
    // Crear
    Route::post('/v1/libros', [LibroController::class, 'store'])->middleware('admin');
    // Uno en especifico
    Route::get('/v1/libros/{libro}', [LibroController::class, 'show'])->middleware('checkUserRole')
        ->where('libro', '[0-9]+');
    // Actualizar
    Route::put('/v1/libros/{libro}', [LibroController::class, 'update'])->middleware('admin');
    // Eliminar
    Route::delete('/v1/libros/{libro}', [LibroController::class, 'destroy'])->middleware('admin');
    //////////////////////////////////////////////////////////////////////////////
    //Mostrar todos los autores
    Route::get('/v1/autores', [LibroController::class, 'indexAutor'])->middleware('checkUserRole');
    // Crear
    Route::post('/v1/autores', [LibroController::class, 'storeAutor'])->middleware('admin');
    // Uno en especifico
    Route::get('/v1/autores/{autor}', [LibroController::class, 'showAutor'])->middleware('checkUserRole');
    // Actualizar
    Route::put('/v1/autores/{autor}', [LibroController::class, 'updateAutor'])->middleware('admin');
    // Eliminar
    Route::delete('/v1/autores/{autor}', [LibroController::class, 'destroyAutor'])->middleware('admin');
    //////////////////////////////////////////////////////////////////////////////
    // mostrar todos los editorials
    Route::get('/v1/editorials', [LibroController::class, 'indexEditorials'])->middleware('checkUserRole');
    // Crear
    Route::post('/v1/editorials', [LibroController::class, 'storeEditorials'])->middleware('admin');
    // Uno en especifico
    Route::get('/v1/editorials/{editorial}', [LibroController::class, 'showEditorials']);
    // Actualizar
    Route::put('/v1/editorials/{editorial}', [LibroController::class, 'updateEditorials'])->middleware('admin');
    // Eliminar
    Route::delete('/v1/editorials/{editorial}', [LibroController::class, 'destroyEditorials'])->middleware('admin');
    ///////////////////////////////////////////////////////////////////////////////
    // Mostrar todos los eventos_literarios
    Route::get('/v1/eventos_literarios', [LibroController::class, 'indexEventos_literarios']);
    // Crear
    Route::post('/v1/eventos_literarios', [LibroController::class, 'storeEventos_literarios'])->middleware('admin');
    // Uno en especifico
    Route::get('/v1/eventos_literarios/{eventos_literarios}', [LibroController::class, 'showEventos_literarios']);
    // Actualizar
    Route::put('/v1/eventos_literarios/{eventos_literarios}', [LibroController::class, 'updateEventos_literarios'])->middleware('admin');
    // Eliminar
    Route::delete('/v1/eventos_literarios/{eventos_literarios}', [LibroController::class, 'destroyEventos_literarios'])->middleware('admin');
    //////////////////////////////////////////////////////////////////////////////
    // Mostrar todos los lectores
    Route::get('/v1/lectores', [LibroController::class, 'indexLectores']);
    // Crear
    Route::post('/v1/lectores', [LibroController::class, 'storeLectores'])->middleware('admin');
    // Uno en especifico
    Route::get('/v1/lectores/{lector}', [LibroController::class, 'showLectores']);
    // Actualizar
    Route::put('/v1/lectores/{lector}', [LibroController::class, 'updateLectores'])->middleware('admin');
    // Eliminar
    Route::delete('/v1/lectores/{lector}', [LibroController::class, 'destroyLectores'])->middleware('admin');
    //////////////////////////////////////////////////////////////////////////////
    // Mostrar todos lal librerías
    Route::get('/v1/librerías', [LibroController::class, 'indexLibrerías']);
    // Crear
    Route::post('/v1/librerías', [LibroController::class, 'storeLibrerías'])->middleware('admin');
    // Uno en especifico
    Route::get('/v1/librerías/{librería}', [LibroController::class, 'showLibrerías']);
    // Actualizar
    Route::put('/v1/librerías/{librería}', [LibroController::class, 'updateLibrerías'])->middleware('admin');
    // Eliminar
    Route::delete('/v1/librerías/{librería}', [LibroController::class, 'destroyLibrerías'])->middleware('admin');
    //////////////////////////////////////////////////////////////////////////////
    // Mostrar todos los participacion_evento
    Route::get('/v1/participacion_evento', [LibroController::class, 'indexParticipacion_evento']);
    // Crear
    Route::post('/v1/participacion_evento', [LibroController::class, 'storeParticipacion_evento'])->middleware('admin');
    // Uno en especifico
    Route::get('/v1/participacion_evento/{participacion_evento}', [LibroController::class, 'showParticipacion_evento']);
    // Actualizar
    Route::put('/v1/participacion_evento/{participacion_evento}', [LibroController::class, 'updateParticipacion_evento'])->middleware('admin');
    // Eliminar
    Route::delete('/v1/participacion_evento/{participacion_evento}', [LibroController::class, 'destroyParticipacion_evento'])->middleware('admin');
    ///////////////////////////////////////////////////////////////////////////////
    // Mostrar todos los prestamos
    Route::get('/v1/prestamos', [LibroController::class, 'indexPrestamos']);
    // Crear
    Route::post('/v1/prestamos', [LibroController::class, 'storePrestamos'])->middleware('admin');
    // Uno en especifico
    Route::get('/v1/prestamos/{prestamo}', [LibroController::class, 'showPrestamos']);
    // Actualizar
    Route::put('/v1/prestamos/{prestamo}', [LibroController::class, 'updatePrestamos'])->middleware('admin');
    // Eliminar
    Route::delete('/v1/prestamos/{prestamo}', [LibroController::class, 'destroyPrestamos'])->middleware('admin');
    //////////////////////////////////////////////////////////////////////////////
    // Mostrar todos los publicaciones
    Route::get('/v1/publicaciones', [LibroController::class, 'indexPublicaciones']);
    // Crear
    Route::post('/v1/publicaciones', [LibroController::class, 'storePublicaciones'])->middleware('admin');
    // Uno en especifico
    Route::get('/v1/publicaciones/{publicacion}', [LibroController::class, 'showPublicaciones']);
    // Actualizar
    Route::put('/v1/publicaciones/{publicacion}', [LibroController::class, 'updatePublicaciones'])->middleware('admin');
    // Eliminar
    Route::delete('/v1/publicaciones/{publicacion}', [LibroController::class, 'destroyPublicaciones'])->middleware('admin');
    /////////////////////////////////////////////////////////////////////////////
    // Mostrar todos los resenas
    Route::get('/v1/resenas', [LibroController::class, 'indexResenas']);
    // Crear
    Route::post('/v1/resenas', [LibroController::class, 'storeResenas'])->middleware('admin');
    // Uno en especifico
    Route::get('/v1/resenas/{resena}', [LibroController::class, 'showResenas']);
    // Actualizar
    Route::put('/v1/resenas/{resena}', [LibroController::class, 'updateResenas'])->middleware('admin');
    // Eliminar
    Route::delete('/v1/resenas/{resena}', [LibroController::class, 'destroyResenas'])->middleware('admin');
    ///////////////////////////////////////////////////////////////////////////


});

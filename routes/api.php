<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LibroController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TokenController;
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
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/register', [AuthController::class, 'register_sanctum'])->name('register');
Route::post('/login', [AuthController::class, 'login_sanctum'])->name('login');
Route::post('/user/tokens', [AuthController::class, 'getTokensByEmail']);
Route::middleware('auth.jwt')->get('/me', [AuthController::class, 'me']);
Route::post('/token-command', [TokenController::class, 'store']);
Route::get('/token-command', [TokenController::class, 'show']); // Ruta para buscar el token por correo



Route::middleware(['auth:sanctum'])->group(function () {
// Mostrar todos
Route::get('/libros', [LibroController::class, 'index']);
// Crear
Route::post('/libros/create', [LibroController::class, 'store']);
// Uno en especifico
Route::get('/libros/{libro}', [LibroController::class, 'show']);
// Actualizar
Route::put('/libros/{libro}/update', [LibroController::class, 'update']);
// Eliminar
Route::delete('/libros/{libro}', [LibroController::class, 'destroy']);
//////////////////////////////////////////////////////////////////////////////
//Mostrar todos los autores
Route::get('/autores', [LibroController::class, 'indexAutor']);
// Crear
Route::post('/autores', [LibroController::class, 'storeAutor']);
// Uno en especifico
Route::get('/autores/{autor}', [LibroController::class, 'showAutor']);
// Actualizar
Route::put('/autores/{autor}', [LibroController::class, 'updateAutor']);
// Eliminar
Route::delete('/autores/{autor}', [LibroController::class, 'destroyAutor']);
//////////////////////////////////////////////////////////////////////////////
// mostrar todos los editorials
Route::get('/editorials', [LibroController::class, 'indexEditorials']);
// Crear
Route::post('/editorials', [LibroController::class, 'storeEditorials']);
// Uno en especifico
Route::get('/editorials/{editorial}', [LibroController::class, 'showEditorials']);
// Actualizar
Route::put('/editorials/{editorial}', [LibroController::class, 'updateEditorials']);
// Eliminar
Route::delete('/editorials/{editorial}', [LibroController::class, 'destroyEditorials']);
///////////////////////////////////////////////////////////////////////////////
// Mostrar todos los eventos_literarios
Route::get('/eventos_literarios', [LibroController::class, 'indexEventos_literarios']);
// Crear
Route::post('/eventos_literarios', [LibroController::class, 'storeEventos_literarios']);
// Uno en especifico
Route::get('/eventos_literarios/{eventos_literarios}', [LibroController::class, 'showEventos_literarios']);
// Actualizar
Route::put('/eventos_literarios/{eventos_literarios}', [LibroController::class, 'updateEventos_literarios']);
// Eliminar
Route::delete('/eventos_literarios/{eventos_literarios}', [LibroController::class, 'destroyEventos_literarios']);
//////////////////////////////////////////////////////////////////////////////
// Mostrar todos los lectores
Route::get('/lectores', [LibroController::class, 'indexLectores']);
// Crear
Route::post('/lectores', [LibroController::class, 'storeLectores']);
// Uno en especifico
Route::get('/lectores/{lector}', [LibroController::class, 'showLectores']);
// Actualizar
Route::put('/lectores/{lector}', [LibroController::class, 'updateLectores']);
// Eliminar
Route::delete('/lectores/{lector}', [LibroController::class, 'destroyLectores']);
//////////////////////////////////////////////////////////////////////////////
// Mostrar todos lal librerías
Route::get('/librerías', [LibroController::class, 'indexLibrerías']);
// Crear
Route::post('/librerías', [LibroController::class, 'storeLibrerías']);
// Uno en especifico
Route::get('/librerías/{librería}', [LibroController::class, 'showLibrerías']);
// Actualizar
Route::put('/librerías/{librería}', [LibroController::class, 'updateLibrerías']);
// Eliminar
Route::delete('/librerías/{librería}', [LibroController::class, 'destroyLibrerías']);
//////////////////////////////////////////////////////////////////////////////
// Mostrar todos los participacion_evento
Route::get('/participacion_evento', [LibroController::class, 'indexParticipacion_evento']);
// Crear
Route::post('/participacion_evento', [LibroController::class, 'storeParticipacion_evento']);
// Uno en especifico
Route::get('/participacion_evento/{participacion_evento}', [LibroController::class, 'showParticipacion_evento']);
// Actualizar
Route::put('/participacion_evento/{participacion_evento}', [LibroController::class, 'updateParticipacion_evento']);
// Eliminar
Route::delete('/participacion_evento/{participacion_evento}', [LibroController::class, 'destroyParticipacion_evento']);
///////////////////////////////////////////////////////////////////////////////
// Mostrar todos los prestamos
Route::get('/prestamos', [LibroController::class, 'indexPrestamos']);
// Crear
Route::post('/prestamos', [LibroController::class, 'storePrestamos']);
// Uno en especifico
Route::get('/prestamos/{prestamo}', [LibroController::class, 'showPrestamos']);
// Actualizar
Route::put('/prestamos/{prestamo}', [LibroController::class, 'updatePrestamos']);
// Eliminar
Route::delete('/prestamos/{prestamo}', [LibroController::class, 'destroyPrestamos']);
//////////////////////////////////////////////////////////////////////////////
// Mostrar todos los publicaciones
Route::get('/publicaciones', [LibroController::class, 'indexPublicaciones']);
// Crear
Route::post('/publicaciones', [LibroController::class, 'storePublicaciones']);
// Uno en especifico
Route::get('/publicaciones/{publicacion}', [LibroController::class, 'showPublicaciones']);
// Actualizar
Route::put('/publicaciones/{publicacion}', [LibroController::class, 'updatePublicaciones']);
// Eliminar
Route::delete('/publicaciones/{publicacion}', [LibroController::class, 'destroyPublicaciones']);
/////////////////////////////////////////////////////////////////////////////
// Mostrar todos los resenas
Route::get('/resenas', [LibroController::class, 'indexResenas']);
// Crear
Route::post('/resenas', [LibroController::class, 'storeResenas']);
// Uno en especifico
Route::get('/resenas/{resena}', [LibroController::class, 'showResenas']);
// Actualizar
Route::put('/resenas/{resena}', [LibroController::class, 'updateResenas']);
// Eliminar
Route::delete('/resenas/{resena}', [LibroController::class, 'destroyResenas']);
///////////////////////////////////////////////////////////////////////////


});
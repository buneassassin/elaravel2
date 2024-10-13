<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LibroController;
use App\Http\Controllers\AuthController;
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


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::post('/register', [AuthController::class, 'register_sanctum'])->name('register');
Route::post('/login', [AuthController::class, 'login_sanctum'])->name('login');
Route::middleware('auth.jwt')->get('/me', [AuthController::class, 'me']);

Route::middleware(['auth:sanctum'])->group(function () {

Route::get('/libros', [LibroController::class, 'index']);
Route::post('/libros', [LibroController::class, 'store']);
Route::get('/libros/{libro}', [LibroController::class, 'show']);
Route::put('/libros/{libro}', [LibroController::class, 'update']);
Route::delete('/libros/{libro}', [LibroController::class, 'destroy']);

});
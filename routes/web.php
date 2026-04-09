<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\GrupoController;
use App\Http\Controllers\AsignacionController;
use App\Http\Controllers\TestController; // Mueve esta línea aquí para mantener el orden
use App\Http\Controllers\AnalisisController;

// Ruta pública para la landing page
Route::get('/', function () {
    return view('landing');  // Puedes cambiar 'welcome' a tu vista real de landing
})->name('landing');

// Ruta separada para la landing (opcional)
Route::get('/pruebas', function () {
    return view('pruebas');  // Vista de la landing page
});

// --------------------RUTAS GRUPO-------------------------------------------------------
Route::middleware(['auth'])->group(function () {
    // Rutas para la gestión de grupos
    Route::get('/grupos', [GrupoController::class, 'index'])->name('grupos.index');
    Route::get('/grupos/create', [GrupoController::class, 'create'])->name('grupos.create');
    Route::post('/grupos', [GrupoController::class, 'store'])->name('grupos.store');
    
    // Ruta para el dashboard, protegida por autenticación y verificación de email
    Route::view('dashboard', 'dashboard')->middleware(['verified'])->name('dashboard');

    Route::get('/analisis', [AnalisisController::class, 'index'])->name('analisis')->middleware(['verified']);


    // Rutas para el perfil del usuario (edit, update y delete)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Ruta para el análisis del test (sociograma)
    Route::get('/prueba-sociograma', function () {
        return view('prueba-sociograma');
    });
    
});

// --------------------FIN RUTAS GRUPO-------------------------------------------------------

// --------------------RUTAS DE AUTENTICACIÓN--------------------------------------------------
// Incluye las rutas de autenticación generadas por Breeze (login, register, etc.)
require __DIR__.'/auth.php';

// --------------------RUTAS PARA TESTS-------------------------------------------------------
Route::get('/test/ingresar', [TestController::class, 'mostrarTestForm'])->name('test.ingresar');
Route::post('/test/verificar', [TestController::class, 'verificarClave'])
    ->middleware('throttle:10,1')
    ->name('test.verificar');
Route::get('/test/realizar/{asignacion}', [TestController::class, 'mostrarTest'])->name('test.realizar');
Route::post('/test/submit/{asignacion}', [TestController::class, 'submitTest'])->name('test.submit');

Route::get('/test/success', function () {
    return view('test.success'); // Asegúrate de que esta vista exista
})->name('test.success');

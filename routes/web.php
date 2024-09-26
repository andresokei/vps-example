<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\GrupoController;
use App\Models\Grupo;

// Ruta pública para la landing page
Route::get('/', function () {
    return view('landing');  // Puedes cambiar 'welcome' a tu vista real de landing
})->name('landing');

// Ruta separada para la landing (opcional)
Route::get('/pruebas', function () {
    return view('pruebas');  // Vista de la landing page
});

// --------------------RUTAS GRUPO-------------------------------------------------------
// Estas rutas estarán protegidas por el middleware de autenticación (solo accesibles si el usuario está autenticado).
Route::middleware(['auth'])->group(function () {
    // Rutas para la gestión de grupos
    Route::get('/grupos', [GrupoController::class, 'index'])->name('grupos.index');
    Route::get('/grupos/create', [GrupoController::class, 'create'])->name('grupos.create');
    Route::post('/grupos', [GrupoController::class, 'store'])->name('grupos.store');
    
    // Ruta para el dashboard, protegida por autenticación y verificación de email
    Route::view('dashboard', 'dashboard')->middleware(['verified'])->name('dashboard');

    // Rutas para el perfil del usuario (edit, update y delete)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// --------------------FIN RUTAS GRUPO-------------------------------------------------------

// Ruta de prueba para ver los grupos (esto es opcional)
Route::get('/test-grupos', function() {
    $groups = Grupo::all();
    return view('test-grupos', ['groups' => $groups]);
});

// --------------------RUTAS DE AUTENTICACIÓN--------------------------------------------------
// Incluye las rutas de autenticación generadas por Breeze (login, register, etc.)
require __DIR__.'/auth.php';


// RUTAS PARA TESTS
use App\Http\Controllers\TestController;

Route::get('/test/ingresar', [TestController::class, 'mostrarTestForm'])->name('test.ingresar');
Route::post('/test/verificar', [TestController::class, 'verificarClave'])->name('test.verificar');
Route::get('/test/realizar/{id}', [TestController::class, 'mostrarTest'])->name('test.realizar');

Route::get('/test/realizar/{id}', [TestController::class, 'mostrarTest'])->name('test.realizar');
Route::post('/test/submit/{id}', [TestController::class, 'submitTest'])->name('test.submit');
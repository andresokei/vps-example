<?php

use App\Http\Controllers\AnalisisController;
use App\Http\Controllers\GrupoController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TestController;
use Illuminate\Support\Facades\Route;

Route::post('/locale', [LocaleController::class, 'switch'])->name('locale.switch');

Route::get('/', function () {
    return view('landing');
})->name('landing');

Route::get('/pruebas', function () {
    return view('pruebas');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/grupos', [GrupoController::class, 'index'])->name('grupos.index');
    Route::get('/grupos/create', [GrupoController::class, 'create'])->name('grupos.create');
    Route::post('/grupos', [GrupoController::class, 'store'])->name('grupos.store');

    Route::view('dashboard', 'dashboard')
        ->middleware(['verified'])
        ->name('dashboard');

    Route::get('/analisis', [AnalisisController::class, 'index'])
        ->middleware(['verified', 'role:profesor|admin'])
        ->name('analisis');

    Route::get('/analisis/{asignacion}/pdf', [AnalisisController::class, 'exportarPdf'])
        ->middleware(['verified', 'role:profesor|admin'])
        ->name('analisis.pdf');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/prueba-sociograma', function () {
        return view('prueba-sociograma');
    });
});

require __DIR__.'/auth.php';

Route::get('/test/ingresar', [TestController::class, 'mostrarTestForm'])->name('test.ingresar');
Route::post('/test/verificar', [TestController::class, 'verificarClave'])
    ->middleware('throttle:10,1')
    ->name('test.verificar');
Route::get('/test/realizar/{asignacion}', [TestController::class, 'mostrarTest'])->name('test.realizar');
Route::post('/test/submit/{asignacion}', [TestController::class, 'submitTest'])->name('test.submit');

Route::get('/test/success', function () {
    return view('test.success');
})->name('test.success');

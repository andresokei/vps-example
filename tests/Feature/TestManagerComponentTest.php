<?php

use App\Livewire\TestManager;
use App\Models\Pregunta;
use App\Models\Test;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(Tests\TestCase::class, RefreshDatabase::class);

it('permite configurar preguntas que aceptan respuestas en blanco', function () {
    $profesor = User::factory()->create();
    $test = Test::create([
        'nombre_test' => 'Test configurable',
        'descripcion' => 'Configura preguntas opcionales',
        'id_profesor' => $profesor->id,
    ]);

    $this->actingAs($profesor);

    Livewire::test(TestManager::class)
        ->call('seleccionarTest', $test->id)
        ->set('preguntaTexto', 'Con quien quieres trabajar?')
        ->set('preguntaTipo', 'preferencia')
        ->set('preguntaPermiteRespuestaVacia', true)
        ->call('añadirPregunta');

    $pregunta = Pregunta::where('test_id', $test->id)->firstOrFail();
    expect($pregunta->permite_respuesta_vacia)->toBeTrue();

    Livewire::test(TestManager::class)
        ->call('seleccionarTest', $test->id)
        ->call('alternarPreguntaRespuestaVacia', $pregunta->id);

    expect($pregunta->fresh()->permite_respuesta_vacia)->toBeFalse();
});

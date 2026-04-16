<?php

use App\Models\User;
use App\Services\AnalisisGrupalService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(Tests\TestCase::class, RefreshDatabase::class);

function seedAnalisisFixture(): array
{
    $now = now();
    $user = User::factory()->create();

    $testId = DB::table('tests')->insertGetId([
        'nombre_test' => 'Sociometria Base',
        'descripcion' => 'Test para pruebas',
        'created_at' => $now,
        'updated_at' => $now,
    ]);

    $grupoId = DB::table('grupos')->insertGetId([
        'nombre_grupo' => 'Grupo Prueba',
        'id_profesor' => $user->id,
        'created_at' => $now,
        'updated_at' => $now,
    ]);

    $nombres = ['Ana', 'Beto', 'Carla', 'Diego', 'Eva'];
    $estudiantes = [];
    foreach ($nombres as $nombre) {
        $id = DB::table('estudiantes')->insertGetId([
            'nombre' => $nombre,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $estudiantes[$nombre] = $id;

        DB::table('estudiantes_grupos')->insert([
            'id_grupo' => $grupoId,
            'id_estudiante' => $id,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    $asignacionId = DB::table('asignaciones_test')->insertGetId([
        'test_id' => $testId,
        'profesor_id' => $user->id,
        'grupo_id' => $grupoId,
        'clave_acceso' => 'clave-prueba-123',
        'estado' => 'aplicado',
        'created_at' => $now,
        'updated_at' => $now,
    ]);

    $preguntaPrefId = DB::table('preguntas')->insertGetId([
        'test_id' => $testId,
        'tipo_pregunta' => 'preferencia',
        'texto_pregunta' => 'Con quien te gustaria trabajar',
        'created_at' => $now,
        'updated_at' => $now,
    ]);

    $preguntaRechazoId = DB::table('preguntas')->insertGetId([
        'test_id' => $testId,
        'tipo_pregunta' => 'rechazo',
        'texto_pregunta' => 'Con quien prefieres no trabajar',
        'created_at' => $now,
        'updated_at' => $now,
    ]);

    $insertRelacion = function (int $a, int $b, string $tipo, int $preguntaId) use ($asignacionId, $now) {
        DB::table('relaciones')->insert([
            'asignacion_test_id' => $asignacionId,
            'pregunta_id' => $preguntaId,
            'alumno_a_id' => $a,
            'alumno_b_id' => $b,
            'tipo_relacion' => $tipo,
            'intensidad' => 1,
            'estado_relacion' => 'activa',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    };

    // Preferencias: Ana <-> Beto, Ana -> Diego
    $insertRelacion($estudiantes['Ana'], $estudiantes['Beto'], 'preferido', $preguntaPrefId);
    $insertRelacion($estudiantes['Beto'], $estudiantes['Ana'], 'preferido', $preguntaPrefId);
    $insertRelacion($estudiantes['Ana'], $estudiantes['Diego'], 'preferido', $preguntaPrefId);
    // Rechazo: Carla -> Ana
    $insertRelacion($estudiantes['Carla'], $estudiantes['Ana'], 'rechazado', $preguntaRechazoId);

    return [
        'grupo_id' => $grupoId,
        'asignacion_id' => $asignacionId,
        'ids' => $estudiantes,
    ];
}

it('genera metricas, comunidades y roles consistentes', function () {
    $fixture = seedAnalisisFixture();

    $result = app(AnalisisGrupalService::class)->generar(
        $fixture['grupo_id'],
        $fixture['asignacion_id']
    );

    expect($result['totales']['alumnos'])->toBe(5)
        ->and($result['totales']['relaciones'])->toBe(4)
        ->and($result['totales']['preferencias'])->toBe(3)
        ->and($result['totales']['rechazos'])->toBe(1)
        ->and($result['totales']['respondieron'])->toBe(3)
        ->and($result['participation_rate'])->toBe(0.6)
        ->and($result['density'])->toBe(0.2)
        ->and($result['polarization'])->toBe(0.5)
        ->and($result['reciprocity'])->toBe(0.6667);

    expect($result['communities'])->toHaveCount(1)
        ->and($result['communities'][0])->toBe(['Ana', 'Beto', 'Diego']);

    expect($result['roles']['leaders'])->toContain('Ana')
        ->and($result['roles']['puentes'])->toContain('Ana')
        ->and($result['roles']['aislados'])->toContain('Eva')
        ->and($result['roles']['cohesivos'])->toBe(['Ana', 'Beto', 'Diego']);

    expect($result['sociograma']['nodes'])->toHaveCount(5)
        ->and($result['sociograma']['links'])->toHaveCount(4)
        ->and($result['sociograma']['preguntas'])->toHaveCount(2);
});

it('genera matriz de reciprocidad con dimensiones del grupo completo', function () {
    $fixture = seedAnalisisFixture();

    $result = app(AnalisisGrupalService::class)->generar(
        $fixture['grupo_id'],
        $fixture['asignacion_id']
    );

    expect($result['reciprocidad']['labels'])->toHaveCount(5)
        ->and($result['reciprocidad']['data'])->toHaveCount(25);

    $anaIndex = array_search('Ana', $result['reciprocidad']['labels'], true);
    $betoIndex = array_search('Beto', $result['reciprocidad']['labels'], true);
    $evaIndex = array_search('Eva', $result['reciprocidad']['labels'], true);

    expect($anaIndex)->not->toBeFalse()
        ->and($betoIndex)->not->toBeFalse()
        ->and($evaIndex)->not->toBeFalse();

    $cellAB = collect($result['reciprocidad']['data'])->first(fn ($c) => $c['x'] === $betoIndex && $c['y'] === $anaIndex);
    $cellAA = collect($result['reciprocidad']['data'])->first(fn ($c) => $c['x'] === $anaIndex && $c['y'] === $anaIndex);
    $cellEE = collect($result['reciprocidad']['data'])->first(fn ($c) => $c['x'] === $evaIndex && $c['y'] === $evaIndex);

    expect($cellAB['v'] ?? null)->toBe(4)
        ->and($cellAA['v'] ?? null)->toBe(0)
        ->and($cellEE['v'] ?? null)->toBe(0);
});

it('elige leaders por preferencias recibidas y no por rechazos', function () {
    $fixture = seedAnalisisFixture();

    DB::table('relaciones')->delete();

    DB::table('relaciones')->insert([
        [
            'asignacion_test_id' => $fixture['asignacion_id'],
            'pregunta_id' => DB::table('preguntas')->where('tipo_pregunta', 'preferencia')->value('id'),
            'alumno_a_id' => $fixture['ids']['Ana'],
            'alumno_b_id' => $fixture['ids']['Diego'],
            'tipo_relacion' => 'preferido',
            'intensidad' => 1,
            'estado_relacion' => 'activa',
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'asignacion_test_id' => $fixture['asignacion_id'],
            'pregunta_id' => DB::table('preguntas')->where('tipo_pregunta', 'rechazo')->value('id'),
            'alumno_a_id' => $fixture['ids']['Ana'],
            'alumno_b_id' => $fixture['ids']['Beto'],
            'tipo_relacion' => 'rechazado',
            'intensidad' => 1,
            'estado_relacion' => 'activa',
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'asignacion_test_id' => $fixture['asignacion_id'],
            'pregunta_id' => DB::table('preguntas')->where('tipo_pregunta', 'rechazo')->value('id'),
            'alumno_a_id' => $fixture['ids']['Carla'],
            'alumno_b_id' => $fixture['ids']['Beto'],
            'tipo_relacion' => 'rechazado',
            'intensidad' => 1,
            'estado_relacion' => 'activa',
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);

    $result = app(AnalisisGrupalService::class)->generar(
        $fixture['grupo_id'],
        $fixture['asignacion_id'],
        true
    );

    expect($result['roles']['leaders'])->toContain('Diego')
        ->and($result['roles']['leaders'])->not->toContain('Beto');
});

<?php

use App\Livewire\GroupManager;
use App\Models\AsignacionTest;
use App\Models\Estudiante;
use App\Models\Grupo;
use App\Models\Pregunta;
use App\Models\Test;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(Tests\TestCase::class, RefreshDatabase::class);

function createAssignedTestWithStudents(int $studentCount = 4): array
{
    static $sequence = 1;

    $teacher = User::factory()->create();
    $group = Grupo::create([
        'nombre_grupo' => 'Grupo Seguro',
        'id_profesor' => $teacher->id,
    ]);
    $test = Test::create([
        'nombre_test' => 'Test protegido',
        'descripcion' => 'Descripcion',
    ]);
    $question = Pregunta::create([
        'test_id' => $test->id,
        'tipo_pregunta' => 'preferencia',
        'texto_pregunta' => 'A quien eliges?',
    ]);
    $assignment = AsignacionTest::create([
        'test_id' => $test->id,
        'grupo_id' => $group->id,
        'profesor_id' => $teacher->id,
        'clave_acceso' => 'CLAVE'.str_pad((string) $sequence++, 4, '0', STR_PAD_LEFT),
        'estado' => 'pendiente',
    ]);

    $students = collect();
    for ($i = 1; $i <= $studentCount; $i++) {
        $student = Estudiante::create(['nombre' => 'Alumno '.$i]);
        $group->estudiantes()->attach($student->id);
        $students->push($student);
    }

    return [$teacher, $group, $test, $question, $assignment, $students];
}

it('forbids opening a test without a validated assignment in session', function () {
    [, , $test, , $assignment] = createAssignedTestWithStudents();

    $this->get(route('test.realizar', $assignment))
        ->assertForbidden();
});

it('binds public test access to the assignment validated by key', function () {
    [, , $testA, , $assignmentA] = createAssignedTestWithStudents();
    [, , , , $assignmentB] = createAssignedTestWithStudents();

    $this->post(route('test.verificar'), ['clave_acceso' => $assignmentA->clave_acceso])
        ->assertRedirect(route('test.realizar', $assignmentA));

    $this->get(route('test.realizar', $assignmentA))
        ->assertOk()
        ->assertSee($testA->nombre_test);

    $this->get(route('test.realizar', $assignmentB))
        ->assertForbidden();
});

it('rejects manipulated answers that target students outside the assignment group', function () {
    [, , , $question, $assignment, $students] = createAssignedTestWithStudents();
    $outsider = Estudiante::create(['nombre' => 'Intruso']);

    $this->withSession(['test_access.assignment_id' => $assignment->id])
        ->post(route('test.submit', $assignment), [
            'estudiante_id' => $students[0]->id,
            'respuesta_'.$question->id.'_1' => $outsider->id,
            'respuesta_'.$question->id.'_2' => $students[1]->id,
            'respuesta_'.$question->id.'_3' => $students[2]->id,
        ])
        ->assertSessionHasErrors(['respuesta_'.$question->id.'_1']);

    $this->assertDatabaseCount('respuestas', 0);
    $this->assertDatabaseCount('relaciones', 0);
});

it('creates a fresh student record instead of reusing one from another teacher group', function () {
    $teacherA = User::factory()->create();
    $teacherB = User::factory()->create();

    $groupA = Grupo::create([
        'nombre_grupo' => 'Grupo A',
        'id_profesor' => $teacherA->id,
    ]);
    $groupB = Grupo::create([
        'nombre_grupo' => 'Grupo B',
        'id_profesor' => $teacherB->id,
    ]);

    $existingStudent = Estudiante::create(['nombre' => 'Alumno Compartido']);
    $groupB->estudiantes()->attach($existingStudent->id);

    $this->actingAs($teacherA);

    Livewire::test(GroupManager::class)
        ->set('selectedGroup', $groupA->id)
        ->set('studentNames', 'Alumno Compartido')
        ->call('addStudentFromList');

    expect(Estudiante::where('nombre', 'Alumno Compartido')->count())->toBe(2);

    $groupAStudentIds = $groupA->fresh()->estudiantes->pluck('id');
    expect($groupAStudentIds)->not->toContain($existingStudent->id);
});

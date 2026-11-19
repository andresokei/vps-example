<?php

use App\Livewire\GroupRoster;
use App\Models\Estudiante;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;

uses(Tests\TestCase::class, RefreshDatabase::class);

it('opens a new class from Groups and links to its own analysis', function () {
    $teacher = User::factory()->create();
    $teacher->grantProfesorAccess();
    $this->actingAs($teacher);
    $this->get(route('grupos.index'))->assertOk()->assertSee(route('grupos.create'));
    $this->get(route('grupos.create'))->assertOk()->assertSee(route('grupos.store'));
    $response = $this->post(route('grupos.store'), ['nombre_grupo' => 'Segundo B']);
    $group = $teacher->grupos()->sole();
    $response->assertRedirect(route('grupos.show', $group));
    $this->get(route('grupos.index'))->assertSee(route('grupos.show', $group));
    $this->get(route('grupos.show', $group))->assertOk()->assertSeeText('Segundo B')
        ->assertSeeText(__('No students in this group.'))->assertSee(route('analisis', ['grupo' => $group->id]));
    $this->post(route('grupos.store'), ['nombre_grupo' => 'Segundo B'])->assertSessionHasErrors('nombre_grupo');
});

it('keeps the roster and its count in sync for pasted names, CSV and removal', function () {
    $teacher = User::factory()->create();
    $group = $teacher->grupos()->create(['nombre_grupo' => 'Primero']);
    $this->actingAs($teacher);
    $component = Livewire::test(GroupRoster::class, ['grupo' => $group])
        ->set('studentNames', "Ana-María\nO’Connor, Ana-María")
        ->call('addStudentFromList')->assertHasNoErrors()
        ->assertSeeText(trans_choice('classroom.students', 2));
    $csv = UploadedFile::fake()->createWithContent('clase.csv', "\xEF\xBB\xBFAna-María\nLeo\n");
    $component->set('csvFile', $csv)->call('uploadCSV')->assertHasNoErrors()
        ->assertSeeText(trans_choice('classroom.students', 3));
    expect($component->get('groupStudents')->pluck('nombre')->all())->toBe(['Ana-María', 'Leo', 'O’Connor']);
    $student = $group->estudiantes()->where('nombre', 'Leo')->firstOrFail();
    $component->call('removeStudentFromGroup', $student->id)->assertSeeText(trans_choice('classroom.students', 2));
    expect($group->estudiantes()->whereKey($student->id)->exists())->toBeFalse();
    expect($student->fresh())->not->toBeNull();
    $component->call('uploadCSV')->assertHasErrors('csvFile');
});

it('preserves optional group memberships on creation and scopes them to the teacher', function () {
    $a = User::factory()->create();
    $group = $a->grupos()->create(['nombre_grupo' => 'Primero']);
    $student = Estudiante::create(['nombre' => 'Demo']);
    $group->estudiantes()->attach($student);
    $this->actingAs($a)->post(route('grupos.store'), ['nombre_grupo' => 'Otro', 'estudiantes' => [$student->id]])
        ->assertSessionHasNoErrors();
    expect($a->grupos()->where('nombre_grupo', 'Otro')->firstOrFail()->estudiantes()->sole()->id)->toBe($student->id);
    $b = User::factory()->create();
    $this->actingAs($b)->get(route('grupos.show', $group))->assertNotFound();
    $this->post(route('grupos.store'), ['nombre_grupo' => 'Destino', 'estudiantes' => [$student->id]])
        ->assertSessionHasErrors('estudiantes.0');
    expect($b->grupos()->count())->toBe(0);
});

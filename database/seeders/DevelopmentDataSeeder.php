<?php

namespace Database\Seeders;

use App\Models\AsignacionTest;
use App\Models\Estudiante;
use App\Models\Grupo;
use App\Models\Pregunta;
use App\Models\Relacion;
use App\Models\Respuesta;
use App\Models\Test;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DevelopmentDataSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        foreach ([
            'relaciones',
            'respuestas',
            'estudiantes_grupos',
            'asignaciones_test',
            'preguntas',
            'tests',
            'grupos',
            'estudiantes',
            'model_has_roles',
            'model_has_permissions',
            'users',
            'sessions',
            'cache',
            'cache_locks',
        ] as $table) {
            DB::table($table)->truncate();
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        DB::transaction(function () {
            $teacher = User::create([
                'name' => 'Profesor Demo',
                'email' => 'demo@vps-example.test',
                'password' => Hash::make('password'),
            ]);

            $teacher->forceFill([
                'rol' => 'profesor',
                'email_verified_at' => now(),
            ])->save();
            $teacher->assignRole('profesor');

            $secondaryTeacher = User::create([
                'name' => 'Profesor Analisis',
                'email' => 'analisis@vps-example.test',
                'password' => Hash::make('password'),
            ]);

            $secondaryTeacher->forceFill([
                'rol' => 'profesor',
                'email_verified_at' => now(),
            ])->save();
            $secondaryTeacher->assignRole('profesor');

            $groupA = Grupo::create([
                'nombre_grupo' => 'grupo1',
                'id_profesor' => $teacher->id,
            ]);

            $groupB = Grupo::create([
                'nombre_grupo' => 'grupo2',
                'id_profesor' => $secondaryTeacher->id,
            ]);

            $testA = Test::create([
                'nombre_test' => 'Analisis de Interacciones en el Aula',
                'descripcion' => 'Dataset demo para practicar la visualizacion sociometrica.',
            ]);

            $testB = Test::create([
                'nombre_test' => 'Evaluacion de Preferencias Sociales',
                'descripcion' => 'Segundo test de ejemplo para asignaciones pendientes.',
            ]);

            $questions = collect([
                ['tipo_pregunta' => 'preferencia', 'texto_pregunta' => 'Con quien te gustaria trabajar en grupo?'],
                ['tipo_pregunta' => 'rechazo', 'texto_pregunta' => 'Con quien preferirias no trabajar en grupo?'],
                ['tipo_pregunta' => 'preferencia', 'texto_pregunta' => 'A quien elegirias para un proyecto de clase?'],
                ['tipo_pregunta' => 'rechazo', 'texto_pregunta' => 'A quien preferirias no tener en tu equipo para un proyecto?'],
            ])->map(fn (array $data) => Pregunta::create([
                'test_id' => $testA->id,
                'tipo_pregunta' => $data['tipo_pregunta'],
                'texto_pregunta' => $data['texto_pregunta'],
            ]));

            foreach ([
                ['tipo_pregunta' => 'preferencia', 'texto_pregunta' => 'Con quien te sentarias en clase?'],
                ['tipo_pregunta' => 'rechazo', 'texto_pregunta' => 'Con quien preferirias no sentarte?'],
            ] as $data) {
                Pregunta::create([
                    'test_id' => $testB->id,
                    'tipo_pregunta' => $data['tipo_pregunta'],
                    'texto_pregunta' => $data['texto_pregunta'],
                ]);
            }

            $studentsA = collect([
                'Ana',
                'Bruno',
                'Carla',
                'Diego',
                'Elena',
                'Fabio',
                'Gabriela',
                'Hector',
            ])->map(function (string $name) use ($groupA) {
                $student = Estudiante::create(['nombre' => $name]);
                $groupA->estudiantes()->attach($student->id);

                return $student;
            });

            collect(['Irene', 'Javier', 'Karen', 'Leo'])->each(function (string $name) use ($groupB) {
                $student = Estudiante::create(['nombre' => $name]);
                $groupB->estudiantes()->attach($student->id);
            });

            $analysisAssignment = AsignacionTest::create([
                'test_id' => $testA->id,
                'grupo_id' => $groupA->id,
                'profesor_id' => $teacher->id,
                'clave_acceso' => 'DEMO2026',
                'estado' => 'aplicado',
            ]);

            $pendingAssignment = AsignacionTest::create([
                'test_id' => $testB->id,
                'grupo_id' => $groupA->id,
                'profesor_id' => $teacher->id,
                'clave_acceso' => 'CLASE123',
                'estado' => 'pendiente',
            ]);

            $preferenceTargets = [
                0 => [1, 2, 4],
                1 => [0, 2, 3],
                2 => [0, 1, 4],
                3 => [0, 2, 5],
                4 => [0, 2, 6],
                5 => [1, 3, 6],
                6 => [0, 4, 7],
                7 => [0, 6, 4],
            ];

            $rejectionTargets = [
                0 => [5, 7, 3],
                1 => [7, 5, 6],
                2 => [5, 7, 3],
                3 => [7, 1, 4],
                4 => [3, 5, 7],
                5 => [0, 2, 4],
                6 => [1, 5, 3],
                7 => [1, 3, 5],
            ];

            foreach ($studentsA as $index => $student) {
                foreach ($questions as $question) {
                    $targets = $question->tipo_pregunta === 'preferencia'
                        ? $preferenceTargets[$index]
                        : $rejectionTargets[$index];

                    foreach ($targets as $order => $targetIndex) {
                        $target = $studentsA[$targetIndex];

                        Respuesta::create([
                            'alumno_id' => $student->id,
                            'asignacion_test_id' => $analysisAssignment->id,
                            'pregunta_id' => $question->id,
                            'respuesta' => (string) $target->id,
                            'orden_preferencia' => $order + 1,
                            'tipo_relacion' => $question->tipo_pregunta,
                        ]);
                    }
                }
            }

            Relacion::generarDesdeRespuestas($analysisAssignment->id);
            $analysisAssignment->recalcularEstado();
            $pendingAssignment->recalcularEstado();
        });

        $this->command?->info('Datos de desarrollo reconstruidos.');
        $this->command?->info('Login profesor demo: demo@vps-example.test / password');
        $this->command?->info('Clave de test pendiente: CLASE123');
        $this->command?->info('Clave de test con analisis cargado: DEMO2026');
    }
}

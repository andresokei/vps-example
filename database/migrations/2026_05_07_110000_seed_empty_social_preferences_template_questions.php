<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const TEST_NAMES = [
        'Evaluacion de Preferencias Sociales',
        'Evaluación de Preferencias Sociales',
        'EvaluaciÃ³n de Preferencias Sociales',
    ];

    private const QUESTIONS = [
        [
            'tipo_pregunta' => 'preferencia',
            'texto_pregunta' => 'Con quien te gustaria sentarte en clase?',
            'orden' => 1,
        ],
        [
            'tipo_pregunta' => 'rechazo',
            'texto_pregunta' => 'Con quien preferirias no sentarte en clase?',
            'orden' => 2,
        ],
        [
            'tipo_pregunta' => 'preferencia',
            'texto_pregunta' => 'A quien elegirias para compartir una actividad del recreo?',
            'orden' => 3,
        ],
        [
            'tipo_pregunta' => 'rechazo',
            'texto_pregunta' => 'Con quien preferirias no compartir una actividad del recreo?',
            'orden' => 4,
        ],
    ];

    public function up(): void
    {
        $tests = DB::table('tests')
            ->whereIn('nombre_test', self::TEST_NAMES)
            ->get(['id']);

        foreach ($tests as $test) {
            $hasQuestions = DB::table('preguntas')
                ->where('test_id', $test->id)
                ->exists();

            if ($hasQuestions) {
                continue;
            }

            $timestamp = now();

            DB::table('preguntas')->insert(array_map(
                fn (array $question) => [
                    'test_id' => $test->id,
                    'tipo_pregunta' => $question['tipo_pregunta'],
                    'texto_pregunta' => $question['texto_pregunta'],
                    'orden' => $question['orden'],
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ],
                self::QUESTIONS
            ));
        }
    }

    public function down(): void
    {
        $testIds = DB::table('tests')
            ->whereIn('nombre_test', self::TEST_NAMES)
            ->pluck('id');

        DB::table('preguntas')
            ->whereIn('test_id', $testIds)
            ->whereIn('texto_pregunta', array_column(self::QUESTIONS, 'texto_pregunta'))
            ->delete();
    }
};

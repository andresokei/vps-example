<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const TEST_TRANSLATIONS = [
        'Analisis de Interacciones en el Aula' => [
            'nombre_test_en' => 'Classroom Interaction Analysis',
            'descripcion_en' => 'This test evaluates interactions between students in a school setting.',
        ],
        'Análisis de Interacciones en el Aula' => [
            'nombre_test_en' => 'Classroom Interaction Analysis',
            'descripcion_en' => 'This test evaluates interactions between students in a school setting.',
        ],
        'AnÃ¡lisis de Interacciones en el Aula' => [
            'nombre_test_en' => 'Classroom Interaction Analysis',
            'descripcion_en' => 'This test evaluates interactions between students in a school setting.',
        ],
        'Evaluacion de Preferencias Sociales' => [
            'nombre_test_en' => 'Social Preferences Assessment',
            'descripcion_en' => 'This test analyzes students preferences when choosing work partners and friends.',
        ],
        'Evaluación de Preferencias Sociales' => [
            'nombre_test_en' => 'Social Preferences Assessment',
            'descripcion_en' => 'This test analyzes students preferences when choosing work partners and friends.',
        ],
        'EvaluaciÃ³n de Preferencias Sociales' => [
            'nombre_test_en' => 'Social Preferences Assessment',
            'descripcion_en' => 'This test analyzes students preferences when choosing work partners and friends.',
        ],
        'EvaluaciÃƒÂ³n de Preferencias Sociales' => [
            'nombre_test_en' => 'Social Preferences Assessment',
            'descripcion_en' => 'This test analyzes students preferences when choosing work partners and friends.',
        ],
    ];

    private const QUESTION_TRANSLATIONS = [
        'Con quien te gustaria trabajar en grupo?' => 'Who would you like to work with in a group?',
        'Con quien preferirias no trabajar en grupo?' => 'Who would you prefer not to work with in a group?',
        'A quien elegirias para un proyecto de clase?' => 'Who would you choose for a class project?',
        'A quien preferirias no tener en tu equipo para un proyecto?' => 'Who would you prefer not to have on your team for a project?',
        'Con quien te gustaria sentarte en clase?' => 'Who would you like to sit with in class?',
        'Con quien te sentarias en clase?' => 'Who would you sit with in class?',
        'Con quien preferirias no sentarte en clase?' => 'Who would you prefer not to sit with in class?',
        'Con quien preferirias no sentarte?' => 'Who would you prefer not to sit with?',
        'A quien elegirias para compartir una actividad del recreo?' => 'Who would you choose to share a recess activity with?',
        'Con quien preferirias no compartir una actividad del recreo?' => 'Who would you prefer not to share a recess activity with?',
    ];

    public function up(): void
    {
        Schema::table('tests', function (Blueprint $table) {
            $table->string('nombre_test_en')->nullable()->after('nombre_test');
            $table->text('descripcion_en')->nullable()->after('descripcion');
        });

        Schema::table('preguntas', function (Blueprint $table) {
            $table->text('texto_pregunta_en')->nullable()->after('texto_pregunta');
        });

        foreach (self::TEST_TRANSLATIONS as $spanishName => $translation) {
            DB::table('tests')
                ->where('nombre_test', $spanishName)
                ->update($translation);
        }

        foreach (self::QUESTION_TRANSLATIONS as $spanishText => $englishText) {
            DB::table('preguntas')
                ->where('texto_pregunta', $spanishText)
                ->update(['texto_pregunta_en' => $englishText]);
        }
    }

    public function down(): void
    {
        Schema::table('preguntas', function (Blueprint $table) {
            $table->dropColumn('texto_pregunta_en');
        });

        Schema::table('tests', function (Blueprint $table) {
            $table->dropColumn(['nombre_test_en', 'descripcion_en']);
        });
    }
};

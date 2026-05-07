<?php

namespace Database\Seeders;

use App\Models\Test;
use Illuminate\Database\Seeder;

class TestsTableSeeder extends Seeder
{
    public function run()
    {
        Test::create([
            'nombre_test' => 'Analisis de Interacciones en el Aula',
            'nombre_test_en' => 'Classroom Interaction Analysis',
            'descripcion' => 'Este test evalua las interacciones entre los estudiantes en un entorno escolar.',
            'descripcion_en' => 'This test evaluates interactions between students in a school setting.',
        ]);

        Test::create([
            'nombre_test' => 'Evaluacion de Preferencias Sociales',
            'nombre_test_en' => 'Social Preferences Assessment',
            'descripcion' => 'Este test analiza las preferencias de los estudiantes en la eleccion de companeros de trabajo y amigos.',
            'descripcion_en' => 'This test analyzes students preferences when choosing work partners and friends.',
        ]);
    }
}

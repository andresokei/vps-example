<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Test;

class TestsTableSeeder extends Seeder
{
    public function run()
    {
        Test::create([
            'nombre_test' => 'Análisis de Interacciones en el Aula',
            'descripcion' => 'Este test evalúa las interacciones entre los estudiantes en un entorno escolar.'
        ]);

        Test::create([
            'nombre_test' => 'Evaluación de Preferencias Sociales',
            'descripcion' => 'Este test analiza las preferencias de los estudiantes en la elección de compañeros de trabajo y amigos.'
        ]);
    }
}


<?php



namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PreguntasTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('preguntas')->insert([
            [
                'test_id' => 7, // Supongamos que este es el primer test en la tabla 'tests'
                'tipo_pregunta' => 'preferencia',
                'texto_pregunta' => '¿Con quién te gustaría trabajar en grupo?',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'test_id' => 7, 
                'tipo_pregunta' => 'rechazo',
                'texto_pregunta' => '¿Con quién preferirías no trabajar en grupo?',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'test_id' => 7, 
                'tipo_pregunta' => 'preferencia',
                'texto_pregunta' => '¿A quién elegirías para un proyecto de clase?',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'test_id' => 7, 
                'tipo_pregunta' => 'rechazo',
                'texto_pregunta' => '¿A quién preferirías no tener en tu equipo para un proyecto?',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}

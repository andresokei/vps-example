<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Llamar a los seeders que hayas creado
        $this->call([
            TestsTableSeeder::class,
            PreguntasTableSeeder::class,
            $this->call(GenerarRespuestasSeeder::class)
        ]);

        // Crear 10 usuarios de prueba
        User::factory(10)->create();

        // Crear un usuario específico
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
    }
}

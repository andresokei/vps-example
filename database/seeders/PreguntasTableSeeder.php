<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PreguntasTableSeeder extends Seeder
{
    public function run(): void
    {
        $timestamp = now();

        DB::table('preguntas')->insert([
            [
                'test_id' => 1,
                'tipo_pregunta' => 'preferencia',
                'texto_pregunta' => 'Con quien te gustaria trabajar en grupo?',
                'orden' => 1,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
            [
                'test_id' => 1,
                'tipo_pregunta' => 'rechazo',
                'texto_pregunta' => 'Con quien preferirias no trabajar en grupo?',
                'orden' => 2,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
            [
                'test_id' => 1,
                'tipo_pregunta' => 'preferencia',
                'texto_pregunta' => 'A quien elegirias para un proyecto de clase?',
                'orden' => 3,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
            [
                'test_id' => 1,
                'tipo_pregunta' => 'rechazo',
                'texto_pregunta' => 'A quien preferirias no tener en tu equipo para un proyecto?',
                'orden' => 4,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
            [
                'test_id' => 2,
                'tipo_pregunta' => 'preferencia',
                'texto_pregunta' => 'Con quien te gustaria sentarte en clase?',
                'orden' => 1,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
            [
                'test_id' => 2,
                'tipo_pregunta' => 'rechazo',
                'texto_pregunta' => 'Con quien preferirias no sentarte en clase?',
                'orden' => 2,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
            [
                'test_id' => 2,
                'tipo_pregunta' => 'preferencia',
                'texto_pregunta' => 'A quien elegirias para compartir una actividad del recreo?',
                'orden' => 3,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
            [
                'test_id' => 2,
                'tipo_pregunta' => 'rechazo',
                'texto_pregunta' => 'Con quien preferirias no compartir una actividad del recreo?',
                'orden' => 4,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
        ]);
    }
}

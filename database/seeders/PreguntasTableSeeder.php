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
                'texto_pregunta_en' => 'Who would you like to work with in a group?',
                'orden' => 1,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
            [
                'test_id' => 1,
                'tipo_pregunta' => 'rechazo',
                'texto_pregunta' => 'Con quien preferirias no trabajar en grupo?',
                'texto_pregunta_en' => 'Who would you prefer not to work with in a group?',
                'orden' => 2,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
            [
                'test_id' => 1,
                'tipo_pregunta' => 'preferencia',
                'texto_pregunta' => 'A quien elegirias para un proyecto de clase?',
                'texto_pregunta_en' => 'Who would you choose for a class project?',
                'orden' => 3,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
            [
                'test_id' => 1,
                'tipo_pregunta' => 'rechazo',
                'texto_pregunta' => 'A quien preferirias no tener en tu equipo para un proyecto?',
                'texto_pregunta_en' => 'Who would you prefer not to have on your team for a project?',
                'orden' => 4,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
            [
                'test_id' => 2,
                'tipo_pregunta' => 'preferencia',
                'texto_pregunta' => 'Con quien te gustaria sentarte en clase?',
                'texto_pregunta_en' => 'Who would you like to sit with in class?',
                'orden' => 1,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
            [
                'test_id' => 2,
                'tipo_pregunta' => 'rechazo',
                'texto_pregunta' => 'Con quien preferirias no sentarte en clase?',
                'texto_pregunta_en' => 'Who would you prefer not to sit with in class?',
                'orden' => 2,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
            [
                'test_id' => 2,
                'tipo_pregunta' => 'preferencia',
                'texto_pregunta' => 'A quien elegirias para compartir una actividad del recreo?',
                'texto_pregunta_en' => 'Who would you choose to share a recess activity with?',
                'orden' => 3,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
            [
                'test_id' => 2,
                'tipo_pregunta' => 'rechazo',
                'texto_pregunta' => 'Con quien preferirias no compartir una actividad del recreo?',
                'texto_pregunta_en' => 'Who would you prefer not to share a recess activity with?',
                'orden' => 4,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
        ]);
    }
}

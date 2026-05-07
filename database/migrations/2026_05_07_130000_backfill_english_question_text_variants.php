<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $this->translateQuestion('%trabajar en grupo%', 'preferencia', 'Who would you like to work with in a group?');
        $this->translateQuestion('%trabajar en grupo%', 'rechazo', 'Who would you prefer not to work with in a group?');
        $this->translateQuestion('%proyecto de clase%', 'preferencia', 'Who would you choose for a class project?');
        $this->translateQuestion('%equipo para un proyecto%', 'rechazo', 'Who would you prefer not to have on your team for a project?');
        $this->translateQuestion('%sentar%clase%', 'preferencia', 'Who would you like to sit with in class?');
        $this->translateQuestion('%sentar%clase%', 'rechazo', 'Who would you prefer not to sit with in class?');
        $this->translateQuestion('%sentarias%clase%', 'preferencia', 'Who would you sit with in class?');
        $this->translateQuestion('%sentarte%', 'rechazo', 'Who would you prefer not to sit with?');
        $this->translateQuestion('%actividad del recreo%', 'preferencia', 'Who would you choose to share a recess activity with?');
        $this->translateQuestion('%actividad del recreo%', 'rechazo', 'Who would you prefer not to share a recess activity with?');
    }

    public function down(): void
    {
        DB::table('preguntas')
            ->whereIn('texto_pregunta_en', [
                'Who would you like to work with in a group?',
                'Who would you prefer not to work with in a group?',
                'Who would you choose for a class project?',
                'Who would you prefer not to have on your team for a project?',
                'Who would you like to sit with in class?',
                'Who would you prefer not to sit with in class?',
                'Who would you sit with in class?',
                'Who would you prefer not to sit with?',
                'Who would you choose to share a recess activity with?',
                'Who would you prefer not to share a recess activity with?',
            ])
            ->update(['texto_pregunta_en' => null]);
    }

    private function translateQuestion(string $pattern, string $type, string $englishText): void
    {
        DB::table('preguntas')
            ->where('tipo_pregunta', $type)
            ->where('texto_pregunta', 'like', $pattern)
            ->where(function ($query) {
                $query->whereNull('texto_pregunta_en')
                    ->orWhere('texto_pregunta_en', '');
            })
            ->update(['texto_pregunta_en' => $englishText]);
    }
};

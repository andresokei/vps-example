<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

/** Baja de los avisos automáticos por correo. La URL va firmada: solo quien tiene el enlace del correo puede usarla. */
class DesactivarAvisosController extends Controller
{
    public function __invoke(Request $request, User $user)
    {
        if ($user->recordatorios_desactivados_at === null) {
            $user->forceFill(['recordatorios_desactivados_at' => now()])->save();
        }

        return response()->view('avisos.baja');
    }
}

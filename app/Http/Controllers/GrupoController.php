<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Grupo;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class GrupoController extends Controller
{
    // Listar los grupos del profesor
    public function index()
    {
        // $grupos = Grupo::where('id_profesor', Auth::user()->id)->get();
        return view('grupos.index');
    }

    // Mostrar el formulario para crear un grupo
    public function create()
    {
        $estudiantes = User::where('rol', 'estudiante')->get();  // Lista de estudiantes disponibles
        return view('grupos.create', compact('estudiantes'));
    }

    // Guardar un nuevo grupo
    public function store(Request $request)
    {
        $grupo = Grupo::create([
            'nombre_grupo' => $request->nombre_grupo,
            'id_profesor' => Auth::user()->id,
        ]);

        // Asignar estudiantes al grupo
        $grupo->estudiantes()->attach($request->estudiantes);

        return redirect()->route('grupos.index');
    }
}


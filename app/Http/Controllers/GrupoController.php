<?php

namespace App\Http\Controllers;

use App\Models\Grupo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class GrupoController extends Controller
{
    public function index(Request $request)
    {
        $grupos = $request->user()->grupos()->withCount('estudiantes')->orderBy('nombre_grupo')->get();

        return view('grupos.index', compact('grupos'));
    }

    public function create()
    {
        return view('grupos.create');
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $data = $request->validate([
            'nombre_grupo' => ['required', 'string', 'max:255', Rule::unique('grupos')->where('id_profesor', $user->id)],
            'estudiantes' => ['nullable', 'array'],
            'estudiantes.*' => ['integer', 'distinct', Rule::exists('estudiantes_grupos', 'id_estudiante')
                ->where(fn ($query) => $query->whereIn('id_grupo', $user->grupos()->select('id')))],
        ]);

        $grupo = DB::transaction(function () use ($user, $data) {
            $grupo = $user->grupos()->create(['nombre_grupo' => $data['nombre_grupo']]);
            $grupo->estudiantes()->attach($data['estudiantes'] ?? []);
            return $grupo;
        });

        return redirect()->route('grupos.show', $grupo)->with('message', __('Group created successfully.'));
    }

    public function show(Request $request, int $grupo)
    {
        $grupo = $request->user()->grupos()->findOrFail($grupo);

        return view('grupos.show', compact('grupo'));
    }
}

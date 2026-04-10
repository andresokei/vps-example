<?php

namespace App\Http\Controllers;

use App\Models\AsignacionTest;
use App\Models\Respuesta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class TestController extends Controller
{
    private const ACCESS_SESSION_KEY = 'test_access.assignment_id';

    public function mostrarTestForm()
    {
        return view('alumnos.ingresar-clave');
    }

    public function verificarClave(Request $request)
    {
        $validated = $request->validate([
            'clave_acceso' => ['required', 'string', 'max:50'],
        ]);

        $asignacion = AsignacionTest::with(['grupo.estudiantes', 'test.preguntas'])
            ->where('clave_acceso', $validated['clave_acceso'])
            ->whereIn('estado', ['pendiente', 'en progreso'])
            ->first();

        if (
            ! $asignacion ||
            ! $asignacion->test ||
            $asignacion->grupo?->estudiantes->isEmpty()
        ) {
            return back()->withErrors([
                'clave_acceso' => 'Clave de acceso invalida o test no disponible.',
            ]);
        }

        $request->session()->put(self::ACCESS_SESSION_KEY, $asignacion->id);

        return redirect()->route('test.realizar', $asignacion);
    }

    public function mostrarTest(Request $request, AsignacionTest $asignacion)
    {
        $this->ensureAssignmentAccess($request, $asignacion);

        if ($asignacion->estado === 'aplicado') {
            $this->forgetAssignmentAccess($request);

            return redirect()->route('test.ingresar')->withErrors([
                'clave_acceso' => 'Clave de acceso invalida o test no disponible.',
            ]);
        }

        $asignacion->loadMissing(['test.preguntas', 'grupo.estudiantes']);

        if (! $asignacion->test || $asignacion->test->preguntas->isEmpty()) {
            $this->forgetAssignmentAccess($request);

            return redirect()->route('test.ingresar')->withErrors([
                'clave_acceso' => 'Este test no tiene preguntas disponibles.',
            ]);
        }

        $estudiantes = $asignacion->grupo->estudiantes()
            ->orderBy('nombre')
            ->get(['estudiantes.id', 'nombre']);

        if ($estudiantes->isEmpty()) {
            $this->forgetAssignmentAccess($request);

            return redirect()->route('test.ingresar')->withErrors([
                'clave_acceso' => 'Este grupo no tiene estudiantes asignados.',
            ]);
        }

        $selectionCount = min(3, max($estudiantes->count() - 1, 0));

        if ($selectionCount < 1) {
            $this->forgetAssignmentAccess($request);

            return redirect()->route('test.ingresar')->withErrors([
                'clave_acceso' => 'Este grupo necesita al menos 2 estudiantes para responder el test.',
            ]);
        }

        return view('alumnos.realizar-test', [
            'test' => $asignacion->test,
            'estudiantes' => $estudiantes,
            'asignacion' => $asignacion,
            'selectionCount' => $selectionCount,
        ]);
    }

    public function submitTest(Request $request, AsignacionTest $asignacion)
    {
        $this->ensureAssignmentAccess($request, $asignacion);

        $asignacion->loadMissing(['test.preguntas', 'grupo.estudiantes']);

        if ($asignacion->estado === 'aplicado') {
            throw ValidationException::withMessages([
                'clave_acceso' => 'Clave de acceso invalida o test no disponible.',
            ]);
        }

        $studentIds = $asignacion->grupo->estudiantes
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();

        if (empty($studentIds)) {
            throw ValidationException::withMessages([
                'estudiante_id' => 'No hay estudiantes disponibles para este test.',
            ]);
        }

        $selectionCount = min(3, max(count($studentIds) - 1, 0));

        if ($selectionCount < 1) {
            throw ValidationException::withMessages([
                'estudiante_id' => 'Este grupo necesita al menos 2 estudiantes para responder el test.',
            ]);
        }

        $rules = [
            'estudiante_id' => ['required', 'integer', Rule::in($studentIds)],
        ];

        foreach ($asignacion->test->preguntas as $pregunta) {
            for ($i = 1; $i <= $selectionCount; $i++) {
                $rules['respuesta_'.$pregunta->id.'_'.$i] = [
                    'required',
                    'integer',
                    Rule::in($studentIds),
                ];
            }
        }

        $validated = $request->validate($rules);
        $studentId = (int) $validated['estudiante_id'];

        if (
            Respuesta::where('asignacion_test_id', $asignacion->id)
                ->where('alumno_id', $studentId)
                ->exists()
        ) {
            throw ValidationException::withMessages([
                'estudiante_id' => 'Este estudiante ya ha respondido este test.',
            ]);
        }

        $errors = [];
        foreach ($asignacion->test->preguntas as $pregunta) {
            $choices = [];

            for ($i = 1; $i <= $selectionCount; $i++) {
                $choices[] = (int) $validated['respuesta_'.$pregunta->id.'_'.$i];
            }

            if (in_array($studentId, $choices, true)) {
                $errors['respuesta_'.$pregunta->id.'_1'] = 'No puedes seleccionarte a ti mismo.';
            }

            if (count(array_unique($choices)) !== count($choices)) {
                $errors['respuesta_'.$pregunta->id.'_1'] = 'Cada respuesta debe apuntar a un estudiante distinto.';
            }
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }

        DB::transaction(function () use ($asignacion, $studentId, $validated) {
            $selectionCount = min(3, max($asignacion->grupo->estudiantes->count() - 1, 0));

            foreach ($asignacion->test->preguntas as $pregunta) {
                $tipoPregunta = $pregunta->tipo_pregunta;
                $tipoRelacion = $tipoPregunta === 'rechazo' ? 'rechazado' : 'preferido';

                for ($i = 1; $i <= $selectionCount; $i++) {
                    $respuesta = (int) $validated['respuesta_'.$pregunta->id.'_'.$i];

                    DB::table('respuestas')->insert([
                        'alumno_id' => $studentId,
                        'asignacion_test_id' => $asignacion->id,
                        'pregunta_id' => $pregunta->id,
                        'respuesta' => (string) $respuesta,
                        'orden_preferencia' => $i,
                        'tipo_relacion' => $tipoPregunta,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    DB::table('relaciones')->insert([
                        'asignacion_test_id' => $asignacion->id,
                        'pregunta_id' => $pregunta->id,
                        'alumno_a_id' => $studentId,
                        'alumno_b_id' => $respuesta,
                        'tipo_relacion' => $tipoRelacion,
                        'intensidad' => $i,
                        'estado_relacion' => 'activa',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        });

        $asignacion->refresh();
        $asignacion->recalcularEstado();
        $this->forgetAssignmentAccess($request);

        return redirect()
            ->route('test.success')
            ->with('status', 'Respuestas y relaciones guardadas exitosamente');
    }

    private function ensureAssignmentAccess(Request $request, AsignacionTest $asignacion): void
    {
        if ((int) $request->session()->get(self::ACCESS_SESSION_KEY) !== (int) $asignacion->id) {
            abort(403);
        }
    }

    private function forgetAssignmentAccess(Request $request): void
    {
        $request->session()->forget(self::ACCESS_SESSION_KEY);
    }
}

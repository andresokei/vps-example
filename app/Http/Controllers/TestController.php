<?php

namespace App\Http\Controllers;

use App\Models\AsignacionTest;
use App\Models\Relacion;
use App\Models\Respuesta;
use App\Services\AnalisisGrupalService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
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
                'clave_acceso' => __('Invalid access key or test unavailable.'),
            ]);
        }

        $request->session()->put(self::ACCESS_SESSION_KEY, $asignacion->id);

        return redirect()->route('test.realizar', $asignacion);
    }

    public function mostrarTest(Request $request, AsignacionTest $asignacion)
    {
        if ($redirect = $this->redirectIfAssignmentAccessMissing($request, $asignacion)) {
            return $redirect;
        }

        if ($asignacion->estado === 'aplicado') {
            $this->forgetAssignmentAccess($request);

            return redirect()->route('test.ingresar')->withErrors([
                'clave_acceso' => __('Invalid access key or test unavailable.'),
            ]);
        }

        $asignacion->loadMissing(['test.preguntas', 'grupo.estudiantes']);

        if (! $asignacion->test || $asignacion->test->preguntas->isEmpty()) {
            $this->forgetAssignmentAccess($request);

            return redirect()->route('test.ingresar')->withErrors([
                'clave_acceso' => __('This test has no available questions.'),
            ]);
        }

        $estudiantes = $asignacion->grupo->estudiantes()
            ->orderBy('nombre')
            ->get(['estudiantes.id', 'nombre']);

        if ($estudiantes->isEmpty()) {
            $this->forgetAssignmentAccess($request);

            return redirect()->route('test.ingresar')->withErrors([
                'clave_acceso' => __('This group has no assigned students.'),
            ]);
        }

        $selectionCount = min(3, max($estudiantes->count() - 1, 0));

        if ($selectionCount < 1) {
            $this->forgetAssignmentAccess($request);

            return redirect()->route('test.ingresar')->withErrors([
                'clave_acceso' => __('This group needs at least 2 students to answer the test.'),
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
        if ($redirect = $this->redirectIfAssignmentAccessMissing($request, $asignacion)) {
            return $redirect;
        }

        $asignacion->loadMissing(['test.preguntas', 'grupo.estudiantes']);

        if ($asignacion->estado === 'aplicado') {
            throw ValidationException::withMessages([
                'clave_acceso' => __('Invalid access key or test unavailable.'),
            ]);
        }

        $studentIds = $asignacion->grupo->estudiantes
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();

        if (empty($studentIds)) {
            throw ValidationException::withMessages([
                'estudiante_id' => __('No students available for this test.'),
            ]);
        }

        $selectionCount = min(3, max(count($studentIds) - 1, 0));

        if ($selectionCount < 1) {
            throw ValidationException::withMessages([
                'estudiante_id' => __('This group needs at least 2 students to answer the test.'),
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
                'estudiante_id' => __('This student has already answered this test.'),
            ]);
        }

        $errors = [];
        foreach ($asignacion->test->preguntas as $pregunta) {
            $choices = [];

            for ($i = 1; $i <= $selectionCount; $i++) {
                $choices[] = (int) $validated['respuesta_'.$pregunta->id.'_'.$i];
            }

            if (in_array($studentId, $choices, true)) {
                $errors['respuesta_'.$pregunta->id.'_1'] = __('You cannot select yourself.');
            }

            if (count(array_unique($choices)) !== count($choices)) {
                $errors['respuesta_'.$pregunta->id.'_1'] = __('Each answer must target a different student.');
            }
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }

        try {
            DB::transaction(function () use ($asignacion, $studentId, $validated) {
                $selectionCount = min(3, max($asignacion->grupo->estudiantes->count() - 1, 0));
                $timestamp = now();
                $rows = [];

                foreach ($asignacion->test->preguntas as $pregunta) {
                    foreach (range(1, $selectionCount) as $i) {
                        $respuesta = (int) $validated['respuesta_'.$pregunta->id.'_'.$i];

                        $rows[] = [
                            'alumno_id' => $studentId,
                            'asignacion_test_id' => $asignacion->id,
                            'pregunta_id' => $pregunta->id,
                            'respuesta' => (string) $respuesta,
                            'orden_preferencia' => $i,
                            'tipo_relacion' => $pregunta->tipo_pregunta,
                            'created_at' => $timestamp,
                            'updated_at' => $timestamp,
                        ];
                    }
                }

                DB::table('respuestas')->insert($rows);
                Relacion::generarDesdeRespuestas((int) $asignacion->id);
            });
        } catch (QueryException $e) {
            if ($this->isDuplicateSubmissionException($e)) {
                throw ValidationException::withMessages([
                    'estudiante_id' => __('This student has already answered this test.'),
                ]);
            }

            throw $e;
        }

        $asignacion->refresh();
        $asignacion->recalcularEstado();
        app(AnalisisGrupalService::class)->invalidateForAssignment((int) $asignacion->grupo_id, (int) $asignacion->id);
        $this->forgetAssignmentAccess($request);

        return redirect()
            ->route('test.success')
            ->with('status', __('Answers saved successfully'));
    }

    private function redirectIfAssignmentAccessMissing(Request $request, AsignacionTest $asignacion): ?RedirectResponse
    {
        if ((int) $request->session()->get(self::ACCESS_SESSION_KEY) !== (int) $asignacion->id) {
            return redirect()
                ->route('test.ingresar')
                ->withErrors([
                    'clave_acceso' => __('Your test session has expired. Please enter the access key again.'),
                ]);
        }

        return null;
    }

    private function forgetAssignmentAccess(Request $request): void
    {
        $request->session()->forget(self::ACCESS_SESSION_KEY);
    }

    private function isDuplicateSubmissionException(QueryException $e): bool
    {
        $message = strtolower($e->getMessage());

        if (! in_array((string) $e->getCode(), ['23000', '23505'], true)) {
            return false;
        }

        return str_contains($message, 'respuestas')
            && str_contains($message, 'asignacion_test_id')
            && str_contains($message, 'alumno_id')
            && str_contains($message, 'pregunta_id')
            && str_contains($message, 'orden_preferencia');
    }
}

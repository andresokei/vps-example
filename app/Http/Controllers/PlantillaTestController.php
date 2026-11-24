<?php

namespace App\Http\Controllers;

use App\Models\Test;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\JsonResponse;

class PlantillaTestController extends Controller
{
    private const CAMPOS_TEST = ['nombre_test', 'nombre_test_en', 'descripcion', 'descripcion_en', 'max_respuestas'];
    private const CAMPOS_PREGUNTA = ['texto_pregunta', 'texto_pregunta_en', 'tipo_pregunta', 'permite_respuesta_vacia'];

    public function exportar(Request $request, int $test): JsonResponse
    {
        $propio = Test::where('id_profesor', $request->user()->id)->findOrFail($test);
        $nombre = Str::slug(Str::limit($propio->nombre_test, 80, '')) ?: 'test';

        return response()->json([
            'formato' => 'sociogram.test',
            'version' => 1,
            'test' => $propio->only(self::CAMPOS_TEST),
            'preguntas' => $propio->preguntas()->orderBy('id')->get()
                ->map(fn ($pregunta) => $pregunta->only(self::CAMPOS_PREGUNTA)),
        ], 200, [
            'Content-Disposition' => 'attachment; filename="'.$nombre.'.sociogram.json"',
            'Cache-Control' => 'private, no-store',
        ], JSON_UNESCAPED_UNICODE);
    }

    public function importar(Request $request): RedirectResponse
    {
        $request->validate(['plantilla' => ['required', 'file', 'max:2048']], [
            'plantilla.required' => __('plantillas.archivo'),
            'plantilla.file' => __('plantillas.archivo'),
            'plantilla.uploaded' => __('plantillas.tamano'),
            'plantilla.max' => __('plantillas.tamano'),
        ]);
        $datos = json_decode($request->file('plantilla')->get(), true, 16);
        if (($datos['formato'] ?? null) !== 'sociogram.test' || ($datos['version'] ?? null) !== 1) {
            throw ValidationException::withMessages(['plantilla' => __('plantillas.formato')]);
        }

        $reglas = [
            'test' => ['required', 'array:'.implode(',', self::CAMPOS_TEST)],
            'test.nombre_test' => ['required', 'string', 'max:255'],
            'test.nombre_test_en' => ['nullable', 'string', 'max:255'],
            'test.descripcion' => ['nullable', 'string', 'max:1000'],
            'test.descripcion_en' => ['nullable', 'string', 'max:1000'],
            'test.max_respuestas' => ['required', 'integer', 'between:1,10'],
            'preguntas' => ['present', 'array', 'list'],
        ];
        if (Validator::make($datos, $reglas)->fails()) {
            throw ValidationException::withMessages(['plantilla' => __('plantillas.invalida')]);
        }

        $reglasPregunta = [
            'texto_pregunta' => ['required', 'string', 'max:500'],
            'texto_pregunta_en' => ['nullable', 'string', 'max:500'],
            'tipo_pregunta' => ['required', 'string', 'in:preferencia,rechazo'],
            'permite_respuesta_vacia' => ['required', 'boolean'],
        ];
        foreach ($datos['preguntas'] as $i => $pregunta) {
            if (!is_array($pregunta) || array_diff(array_keys($pregunta), self::CAMPOS_PREGUNTA)
                || Validator::make($pregunta, $reglasPregunta)->fails()) {
                throw ValidationException::withMessages(['plantilla' => __('plantillas.pregunta', ['numero' => $i + 1])]);
            }
        }

        $test = DB::transaction(function () use ($request, $datos) {
            $profesor = User::lockForUpdate()->findOrFail($request->user()->id);
            $campos = $datos['test'];
            $original = $campos['nombre_test'];
            $numero = 1;
            while (Test::where('id_profesor', $profesor->id)->where('nombre_test', $campos['nombre_test'])->exists()) {
                $sufijo = ' ('.$numero++.')';
                $campos['nombre_test'] = mb_substr($original, 0, 255 - mb_strlen($sufijo)).$sufijo;
                if (!empty($datos['test']['nombre_test_en'])) {
                    $campos['nombre_test_en'] = mb_substr($datos['test']['nombre_test_en'], 0, 255 - mb_strlen($sufijo)).$sufijo;
                }
            }
            $test = Test::create($campos + ['id_profesor' => $profesor->id]);
            foreach ($datos['preguntas'] as $i => $pregunta) {
                $test->preguntas()->create($pregunta + ['orden' => $i + 1]);
            }
            return $test;
        }, 3);

        return to_route('tests.index')->with('test_importado', $test->id)
            ->with('message', __('plantillas.importada', ['nombre' => $test->localized_nombre_test]));
    }
}

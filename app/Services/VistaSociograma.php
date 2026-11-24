<?php

namespace App\Services;

class VistaSociograma
{
    public static function filtrar(array $graph, array $filters): array
    {
        $students = array_map('intval', $filters['alumnos'] ?? []);
        $questions = array_map('intval', $filters['preguntas'] ?? []);
        $mutualOnly = (bool) ($filters['mutuas'] ?? false);
        $mark = (bool) ($filters['marcar'] ?? false);
        $links = collect($graph['links'])->filter(fn ($link) => !$questions ||
            array_intersect($questions, $link['preguntas_ids'] ?? [$link['pregunta_id']]));
        $positive = $links->where('tipo', 'preferido')->mapWithKeys(fn ($link) => [$link['source'].'-'.$link['target'] => true]);
        $visible = $links->filter(fn ($link) => (!$mutualOnly ||
                ($link['tipo'] === 'preferido' && $positive->has($link['target'].'-'.$link['source'])))
            && (!$students || in_array($link['source'], $students, true) || in_array($link['target'], $students, true)));
        $visibleIds = array_merge($students, $visible->pluck('source')->all(), $visible->pluck('target')->all());
        $wholeGroup = !$mutualOnly && !$students && (!$questions || !array_diff(array_column($graph['preguntas'], 'id'), $questions));
        $nodes = collect($graph['nodes']);
        return [
            'graph' => ['nodes' => ($wholeGroup ? $nodes : $nodes->whereIn('id', $visibleIds))->values()->all(),
                        'links' => $visible->values()->all()],
            'students' => $nodes->whereIn('id', $students)->pluck('label')->all(),
            'questions' => collect($graph['preguntas'])->whereIn('id', $questions)->pluck('texto')->all(),
            'mutualOnly' => $mutualOnly, 'mark' => $mark,
        ];
    }
}

@php
    $labels = $matrix['labels'];
    $size = count($labels);
    $categories = ['none', 'neg', 'pos', 'nn', 'pp', 'conflict', 'mixed'];
    $colors = ['#f2f3f5', '#f6d2d7', '#cae9d0', '#ee707e', '#9799ed', '#ffc06c', '#c493d0'];
    $symbols = ['·', '+', '−', '±'];
@endphp
@foreach(array_chunk($labels, 16, true) as $columns)
<div style="page-break-before:always"></div>
<h1>{{ __('Reciprocity Matrix') }}</h1>
<p style="color:#555">{{ $grupo }} · {{ $test }}</p>
<p>{{ app()->getLocale() === 'en' ? 'Read from the respondent’s row to the classmate’s numbered column.' : 'Lee desde la fila de quien responde hasta la columna numerada del compañero.' }}</p>
<p>{{ __('reciprocity.signs') }}</p>
<p style="line-height:1.9">
@foreach($categories as $i => $key)
<span style="color:{{ $colors[$i] }}">&#9632;</span> {{ __('reciprocity.'.$key) }} &nbsp;
@endforeach
</p>
@if(!collect($matrix['data'])->contains(fn ($cell) => $cell['v'] !== 0))
<p>{{ __('reciprocity.empty') }}</p>
@endif
<table style="width:100%;border-collapse:collapse;table-layout:fixed">
<thead><tr><th style="text-align:left;width:29%;background:#e1e8ec;padding:4px">{{ __('reciprocity.origin') }}</th>
@foreach($columns as $col => $label)<th style="background:#e1e8ec;font-size:9px">{{ $col+1 }}</th>@endforeach</tr></thead>
<tbody>
@foreach($labels as $row => $from)
<tr><td style="font-size:10px;border-bottom:1px solid #ddd;padding:4px">{{ $row+1 }}. {{ $from }}</td>
@foreach($columns as $col => $to)
@php($cell = $matrix['data'][$row*$size + $col])
<td style="text-align:center;border:1px solid white;background-color:{{ $row === $col ? '#dadee3' : $colors[$cell['v']] }}">{{ $row === $col ? '—' : $symbols[$cell['out']] }}</td>
@endforeach</tr>
@endforeach
</tbody></table>
@endforeach

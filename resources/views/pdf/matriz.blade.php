<div style="page-break-before:always"></div>
<h1>{{ __('Reciprocity Matrix') }}</h1>
<p style="color:#555">{{ $grupo }} · {{ $test }}</p>
@php
    $labels = $matrix['labels'];
    $size = count($labels);
    $colors = ['#f2f3f5', '#f6d2d7', '#cae9d0', '#ee707e', '#9799ed', '#ffc06c', '#c493d0'];
    $categories = ['none', 'neg', 'pos', 'nn', 'pp', 'conflict', 'mixed'];
@endphp
@if($size)
<p>{{ app()->getLocale() === 'en' ? 'Read from the respondent’s row to the classmate’s numbered column.' : 'Lee desde la fila de quien responde hasta la columna numerada del compañero.' }} {{ __('reciprocity.signs') }}</p>
<p>
@foreach($categories as $i => $key)
<span style="white-space:nowrap"><span style="color:{{ $colors[$i] }}">&#9632;</span> {{ __('reciprocity.'.$key) }}</span> &nbsp;
@endforeach
</p>
@if(!collect($matrix['data'])->contains(fn ($cell) => $cell['v'] !== 0))
<p>{{ __('reciprocity.empty') }}</p>
@endif
@php
    $svg = view('pdf.matriz-svg', compact('labels', 'size', 'matrix', 'colors'))->render();
@endphp
<img style="width:100%" alt="{{ __('Reciprocity Matrix') }}" src="data:image/svg+xml;base64,{{ base64_encode($svg) }}">
@else
<p>{{ __('No data.') }}</p>
@endif

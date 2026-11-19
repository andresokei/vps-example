<div style="page-break-before:always"></div>
<h1>{{ __('Sociogram') }}</h1>
<p style="color:#555">{{ $grupo }} · {{ $test }}</p>
@php
    $positions = [];
    $nodes = $graph['nodes'];
    $count = count($nodes);
    $radius = min(16, 430 / max(1, $count));
    foreach ($nodes as $index => $node) {
        $angle = 2 * M_PI * $index / $count - M_PI / 2;
        $positions[$node['id']] = [280 + 240 * cos($angle), 225 + 195 * sin($angle)];
    }
@endphp
@if($count)
@php
    $svg = view('pdf.grafo-svg', compact('graph', 'nodes', 'positions', 'radius'))->render();
@endphp
<img style="width:100%" alt="{{ __('Sociogram') }}" src="data:image/svg+xml;base64,{{ base64_encode($svg) }}">
<p><strong style="color:#117448">{{ __('Preference') }}</strong> &nbsp; / &nbsp;
<strong style="color:#bd3547">{{ __('Rejection') }}</strong> &nbsp; · &nbsp;
<span style="color:#bf3841">&#9679;</span> {{ __('Not chosen') }}</p>
<table class="data">
<thead><tr><th>#</th><th>{{ __('Student') }}</th><th>#</th><th>{{ __('Student') }}</th></tr></thead>
<tbody>
@foreach(array_chunk($nodes, 2, true) as $pair)
<tr>@foreach($pair as $i => $node)<td>{{ $i+1 }}</td><td>{{ $node['label'] }}</td>@endforeach
@if(count($pair) === 1)<td></td><td></td>@endif</tr>
@endforeach
</tbody></table>
@else
<p>{{ __('No data.') }}</p>
@endif

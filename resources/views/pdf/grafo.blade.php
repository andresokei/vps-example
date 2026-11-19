<div style="page-break-before:always"></div>
<h1>{{ __('Sociogram') }}</h1>
<p style="color:#555">{{ $grupo }} · {{ $test }}</p>
@php
    $positions = [];
    $nodes = $graph['nodes'];
    $count = count($nodes);
    $perSide = max(1, (int) ceil($count / 2));
    $rowHeight = 380 / $perSide;
    $radius = min(8, $rowHeight / 4);
    foreach ($nodes as $index => $node) {
        $left = $index < $perSide;
        $sideCount = $left ? $perSide : $count - $perSide;
        $fraction = (($index % $perSide) + 0.5) / $sideCount;
        $offset = 80 + 145 * sin(M_PI * $fraction);
        $positions[$node['id']] = [500 + ($left ? -$offset : $offset), 12 + 380 * $fraction];
    }
@endphp
@if($count)
@php
    $svg = view('pdf.grafo-svg', compact('graph', 'nodes', 'positions', 'radius', 'rowHeight'))->render();
@endphp
<img style="width:100%" alt="{{ __('Sociogram') }}" src="data:image/svg+xml;base64,{{ base64_encode($svg) }}">
<p><strong style="color:#117448">{{ __('Preference') }}</strong> &nbsp; / &nbsp;
<strong style="color:#bd3547">{{ __('Rejection') }}</strong> &nbsp; · &nbsp;
<span style="color:#bf3841">&#9679;</span> {{ __('Not chosen') }}</p>
@else
<p>{{ __('No data.') }}</p>
@endif

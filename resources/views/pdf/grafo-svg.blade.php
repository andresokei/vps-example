<svg xmlns="http://www.w3.org/2000/svg" width="560" height="450" viewBox="0 0 560 450">
@foreach($graph['links'] as $edge)
@php
    [$ax, $ay] = $positions[$edge['source']];
    [$bx, $by] = $positions[$edge['target']];
    $length = max(1, hypot($bx - $ax, $by - $ay));
    $preference = $edge['tipo'] === 'preferido';
    $bend = min($preference ? 24 : 48, $length * ($preference ? 0.15 : 0.30));
    $cx = ($ax + $bx) / 2 - ($by - $ay) / $length * $bend;
    $cy = ($ay + $by) / 2 + ($bx - $ax) / $length * $bend;
    $endLength = max(1, hypot($bx - $cx, $by - $cy));
    $ux = ($bx - $cx) / $endLength;
    $uy = ($by - $cy) / $endLength;
    $tipX = $bx - $ux * ($radius + 1);
    $tipY = $by - $uy * ($radius + 1);
    $baseX = $tipX - 8 * $ux;
    $baseY = $tipY - 8 * $uy;
    $color = $preference ? '#117448' : '#bd3547';
@endphp
<path d="M {{ $ax }} {{ $ay }} Q {{ $cx }} {{ $cy }} {{ $baseX }} {{ $baseY }}" fill="none" stroke="{{ $color }}" stroke-width="1.3" @unless($preference) stroke-dasharray="4 3" @endunless />
<polygon points="{{ $tipX }},{{ $tipY }} {{ $baseX - 3.4*$uy }},{{ $baseY + 3.4*$ux }} {{ $baseX + 3.4*$uy }},{{ $baseY - 3.4*$ux }}" fill="{{ $color }}" />
@endforeach
@foreach($nodes as $i => $node)
@php([$x,$y] = $positions[$node['id']])
<circle cx="{{ $x }}" cy="{{ $y }}" r="{{ $radius }}" fill="{{ $node['isIsolate'] ? '#bf3841' : '#484fa6' }}" />
<text x="{{ $x }}" y="{{ $y + $radius*0.30 }}" text-anchor="middle" font-family="DejaVu Sans" font-size="{{ $radius*0.88 }}" fill="#ffffff">{{ $i+1 }}</text>
@endforeach
</svg>

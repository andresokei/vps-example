<svg xmlns="http://www.w3.org/2000/svg" width="1000" height="404" viewBox="0 0 1000 404">
@foreach($graph['links'] as $edge)
@php
    [$ax, $ay] = $positions[$edge['source']];
    [$bx, $by] = $positions[$edge['target']];
    $length = max(1, hypot($bx - $ax, $by - $ay));
    $preference = $edge['tipo'] === 'preferido';
    $bend = min($preference ? 24 : 48, $length * ($preference ? 0.15 : 0.30));
    $cx = ($ax + $bx) / 2 - ($by - $ay) / $length * $bend;
    $cy = ($ay + $by) / 2 + ($bx - $ax) / $length * $bend;
    if (($ax < 500) === ($bx < 500)) {
        $inset = $bend + ($ay < $by ? 12 : 32);
        $cx = $ax < 500 ? max($ax, $bx) + $inset : min($ax, $bx) - $inset;
    }
    $endLength = max(1, hypot($bx - $cx, $by - $cy));
    $ux = ($bx - $cx) / $endLength;
    $uy = ($by - $cy) / $endLength;
    $tipX = $bx - $ux * ($radius + 1);
    $tipY = $by - $uy * ($radius + 1);
    $baseX = $tipX - 11 * $ux;
    $baseY = $tipY - 11 * $uy;
    $color = $preference ? '#117448' : '#bd3547';
@endphp
<path d="M {{ $ax }} {{ $ay }} Q {{ $cx }} {{ $cy }} {{ $baseX }} {{ $baseY }}" fill="none" stroke="{{ $color }}" stroke-width="1.3" @unless($preference) stroke-dasharray="4 3" @endunless />
<polygon points="{{ $tipX }},{{ $tipY }} {{ $baseX - 4.8*$uy }},{{ $baseY + 4.8*$ux }} {{ $baseX + 4.8*$uy }},{{ $baseY - 4.8*$ux }}" fill="{{ $color }}" stroke="white" stroke-width="0.6" />
@endforeach
@foreach($nodes as $node)
@php
    [$x,$y] = $positions[$node['id']];
    $left = $x < 500;
    $space = min($x, 1000 - $x) - $radius - 14;
    $font = min(17, $rowHeight * 0.66, $space / max(1, mb_strwidth($node['label'])));
@endphp
<circle cx="{{ $x }}" cy="{{ $y }}" r="{{ $radius }}" fill="{{ $mark && $node['isIsolate'] ? '#bf3841' : '#484fa6' }}" />
<text x="{{ $x + ($left ? -1 : 1) * ($radius + 6) }}" y="{{ $y + $font*0.32 }}" text-anchor="{{ $left ? 'end' : 'start' }}" font-family="DejaVu Sans" font-size="{{ $font }}" fill="#24364a">{{ $node['label'] }}</text>
@endforeach
</svg>

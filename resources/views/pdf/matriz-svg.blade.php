@php
    $cellWidth = min(68, 775 / $size);
    $cellHeight = min(32, 380 / ($size + 1));
    $font = min(14, $cellHeight * 0.72, $cellWidth / max(1, strlen((string) $size)));
    $height = ceil(($size + 1) * $cellHeight + 2);
    $symbols = ['·', '+', '−', '±'];
@endphp
<svg xmlns="http://www.w3.org/2000/svg" width="1000" height="{{ $height }}" viewBox="0 0 1000 {{ $height }}" font-family="DejaVu Sans">
<text x="2" y="{{ 0.70 * $cellHeight }}" font-size="{{ $font }}">{{ __('reciprocity.origin') }}</text>
@foreach($labels as $column => $label)
<text x="{{ 220 + ($column + 0.5) * $cellWidth }}" y="{{ 0.70 * $cellHeight }}" text-anchor="middle" font-size="{{ $font }}">{{ $column + 1 }}</text>
@endforeach
@foreach($labels as $row => $label)
@php
    $y = ($row + 1) * $cellHeight;
    $name = ($row + 1).'. '.$label;
@endphp
<text x="2" y="{{ $y + 0.70 * $cellHeight }}" font-size="{{ min($font, 210 / max(1, mb_strwidth($name))) }}">{{ $name }}</text>
@foreach($labels as $column => $unused)
@php
    $cell = $matrix['data'][$row * $size + $column];
    $x = 220 + $column * $cellWidth;
@endphp
<rect x="{{ $x }}" y="{{ $y }}" width="{{ $cellWidth }}" height="{{ $cellHeight }}" fill="{{ $row === $column ? '#dadee3' : $colors[$cell['v']] }}" stroke="white" stroke-width="{{ min(1, $cellHeight / 12) }}" />
<text x="{{ $x + 0.5 * $cellWidth }}" y="{{ $y + 0.70 * $cellHeight }}" text-anchor="middle" font-size="{{ $font }}">{{ $row === $column ? '—' : $symbols[$cell['out']] }}</text>
@endforeach
@endforeach
</svg>

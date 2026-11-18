@php
    $matrix = $datos['reciprocidad'];
    $labels = $matrix['labels'];
    $counts = array_count_values($labels);
    foreach ($labels as $index => $name) {
        if ($counts[$name] > 1) {
            $labels[$index] = $name.' ('.($index + 1).')';
        }
    }
    $categories = ['none', 'neg', 'pos', 'nn', 'pp', 'conflict', 'mixed'];
    $symbols = ['·', '+', '−', '±'];
@endphp
<div class="card shadow-sm mb-4 reciprocity">
    <div class="card-header">{{ __('Reciprocity Matrix') }}</div>
    <div class="card-body">
        <p>{{ __('reciprocity.axis') }}</p>
        <p class="small">{{ __('reciprocity.signs') }}</p>
        <ul class="list-inline small">
            @foreach ($categories as $category)
                <li class="list-inline-item"><span class="matrix-key matrix-{{ $category }}"></span> {{ __('reciprocity.'.$category) }}</li>
            @endforeach
        </ul>
        <div class="matrix-scroll">
            <table aria-label="{{ __('Reciprocity Matrix') }}">
                <thead><tr><th scope="col">{{ __('reciprocity.origin') }}</th>
                    @foreach ($labels as $label)<th scope="col">{{ $label }}</th>@endforeach
                </tr></thead>
                <tbody>
                    @foreach ($labels as $row => $from)
                        <tr><th scope="row">{{ $from }}</th>
                        @foreach ($labels as $col => $to)
                            @if ($col === $row)
                                <td class="matrix-self" title="{{ __('reciprocity.skip') }}">—</td>
                            @else
                                @php
                                    $cell = $matrix['data'][$row * count($labels) + $col];
                                    $description = $from.' → '.$to.': '.__('reciprocity.out'.$cell['out']).'. '.$to.' → '.$from.': '.__('reciprocity.out'.$cell['back']).'.';
                                @endphp
                                <td class="matrix-{{ $categories[$cell['v']] }}"><button type="button" data-matrix-cell title="{{ $description }}" aria-label="{{ $description }}">{{ $symbols[$cell['out']] }}</button></td>
                            @endif
                        @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <output class="matrix-detail d-block mt-2" aria-live="polite"></output>
    </div>
</div>
<style>
.reciprocity .matrix-scroll {overflow:auto;max-height:65vh}
.reciprocity table {border-collapse:separate;border-spacing:1px;width:max-content;min-width:100%;text-align:center}
.reciprocity th {background:#f6f8fa;padding:.35rem .6rem;max-width:12rem;overflow-wrap:anywhere}
.reciprocity thead th {position:sticky;top:0;z-index:1}
.reciprocity tr th:first-child {position:sticky;left:0;text-align:left;z-index:1}
.reciprocity thead th:first-child {z-index:2}
.reciprocity td {padding:0;color:#18202a}
.reciprocity button {border:0;background:transparent;color:inherit;width:100%;min-width:2.6rem;padding:.25rem;font-weight:bold}
.matrix-key {display:inline-block;width:.9rem;height:.9rem;vertical-align:middle;border:1px solid #777}
.matrix-self {background:#dedede}.matrix-none {background:#f3f4f6}.matrix-neg {background:#ffd9da}.matrix-pos {background:#d3f2df}
.matrix-nn {background:#ec939b}.matrix-pp {background:#93d2af}.matrix-conflict {background:#ffd095}.matrix-mixed {background:#ddc9f4}
</style>

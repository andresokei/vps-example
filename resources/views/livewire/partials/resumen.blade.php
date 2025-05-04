{{-- resources/views/livewire/partials/resumen.blade.php --}}
@php
$cards = [
    [
        'label' => 'Participación',
        'value' => count($datos['sociograma']['nodes'] ?? []) ? '100%' : '0%',
        'icon'  => 'participation',
        'color' => 'primary',
    ],
    [
        'label' => 'Densidad',
        'value' => number_format($datos['density'] ?? 0, 2),
        'icon'  => 'density',
        'color' => 'success',
    ],
    [
        'label' => 'Polarización',
        'value' => number_format($datos['polarization'] ?? 0, 2),
        'icon'  => 'polarization',
        'color' => 'danger',
    ],
    [
        'label' => 'Reciprocidad',
        'value' => number_format(($datos['reciprocity'] ?? 0) * 100, 0) . '%',
        'icon'  => 'reciprocity',
        'color' => 'info',
    ],
];
@endphp

<div class="stats-cards-container">
    <div class="row g-4">
        @foreach($cards as $c)
        <div class="col-lg-3 col-md-6 col-sm-6">
            <div class="stats-card stats-card-{{ $c['color'] }}">
                <div class="stats-card-content">
                    <div class="stats-info">
                        <div class="stats-label">{{ $c['label'] }}</div>
                        <div class="stats-value">{{ $c['value'] }}</div>
                    </div>
                    <div class="stats-icon stats-icon-{{ $c['color'] }} {{ $c['icon'] }}"></div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
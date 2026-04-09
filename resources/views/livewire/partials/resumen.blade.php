@php
$cards = [
    [
        'label' => 'Participacion',
        'value' => number_format(($datos['participation_rate'] ?? 0) * 100, 0) . '%',
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
        'label' => 'Polarizacion',
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

    @if(!empty($datos['totales']))
        <div class="card shadow-sm border-0 mt-3">
            <div class="card-body py-3 d-flex flex-wrap gap-3">
                <span><strong>Alumnos:</strong> {{ $datos['totales']['alumnos'] ?? 0 }}</span>
                <span><strong>Respondieron:</strong> {{ $datos['totales']['respondieron'] ?? 0 }}</span>
                <span><strong>Relaciones:</strong> {{ $datos['totales']['relaciones'] ?? 0 }}</span>
                <span><strong>Preferencias:</strong> {{ $datos['totales']['preferencias'] ?? 0 }}</span>
                <span><strong>Rechazos:</strong> {{ $datos['totales']['rechazos'] ?? 0 }}</span>
            </div>
        </div>
    @endif
</div>

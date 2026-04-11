@php
$metrics = [
  [
    'label' => __('Participation'),
    'value' => number_format(($datos['participation_rate'] ?? 0) * 100, 0) . '%',
    'icon'  => 'bi-person-check',
    'color' => '#6366f1',
    'bg'    => '#eef2ff',
  ],
  [
    'label' => __('Density'),
    'value' => number_format($datos['density'] ?? 0, 2),
    'icon'  => 'bi-bezier2',
    'color' => '#22c55e',
    'bg'    => '#f0fdf4',
  ],
  [
    'label' => __('Polarization'),
    'value' => number_format($datos['polarization'] ?? 0, 2),
    'icon'  => 'bi-arrow-left-right',
    'color' => '#ef4444',
    'bg'    => '#fff1f2',
  ],
  [
    'label' => __('Reciprocity rate'),
    'value' => number_format(($datos['reciprocity'] ?? 0) * 100, 0) . '%',
    'icon'  => 'bi-arrow-repeat',
    'color' => '#0ea5e9',
    'bg'    => '#f0f9ff',
  ],
];
@endphp

<div class="row g-3 mb-4">
  @foreach($metrics as $m)
    <div class="col-6 col-lg-3">
      <div class="card h-100" style="border-color:#e2e8f0;">
        <div class="card-body d-flex align-items-center gap-3 py-3">
          <div style="width:44px;height:44px;border-radius:10px;background:{{ $m['bg'] }};
                      display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <i class="bi {{ $m['icon'] }}" style="font-size:1.25rem;color:{{ $m['color'] }};"></i>
          </div>
          <div>
            <div style="font-size:1.4rem;font-weight:700;color:#0f172a;line-height:1.1;">
              {{ $m['value'] }}
            </div>
            <div style="font-size:0.72rem;color:#64748b;text-transform:uppercase;letter-spacing:0.04em;font-weight:500;">
              {{ $m['label'] }}
            </div>
          </div>
        </div>
      </div>
    </div>
  @endforeach
</div>

@if(!empty($datos['totales']))
  <div class="card mb-4">
    <div class="card-body py-3">
      <div class="d-flex flex-wrap gap-4" style="font-size:0.875rem;">
        <span><span class="text-muted">{{ __('Students') }}:</span> <strong>{{ $datos['totales']['alumnos'] ?? 0 }}</strong></span>
        <span><span class="text-muted">{{ __('Answered') }}:</span> <strong>{{ $datos['totales']['respondieron'] ?? 0 }}</strong></span>
        <span><span class="text-muted">{{ __('Relations') }}:</span> <strong>{{ $datos['totales']['relaciones'] ?? 0 }}</strong></span>
        <span><span class="text-muted">{{ __('Preferences') }}:</span> <strong>{{ $datos['totales']['preferencias'] ?? 0 }}</strong></span>
        <span><span class="text-muted">{{ __('Rejections') }}:</span> <strong>{{ $datos['totales']['rechazos'] ?? 0 }}</strong></span>
      </div>
    </div>
  </div>
@endif

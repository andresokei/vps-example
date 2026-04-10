{{-- resources/views/livewire/partials/roles.blade.php --}}
@php
$rolesMeta = [
  'leaders'   => [
    'label'   => 'Líderes',
    'icon'    => 'bi-star-fill',
    'color'   => 'warning',
    'tooltip' => 'Estudiantes con mayor número de elecciones recibidas (in-degree)',
  ],
  'puentes'   => [
    'label'   => 'Puentes',
    'icon'    => 'bi-intersect',
    'color'   => 'info',
    'tooltip' => 'Estudiantes con mayor betweenness: conectan diferentes subgrupos',
  ],
  'aislados'  => [
    'label'   => 'Aislados',
    'icon'    => 'bi-person-slash',
    'color'   => 'danger',
    'tooltip' => 'Estudiantes sin ninguna relación (ni elegidos ni eligen)',
  ],
  'cohesivos' => [
    'label'   => 'Cohesivos',
    'icon'    => 'bi-people-fill',
    'color'   => 'success',
    'tooltip' => 'Miembros del subgrupo más grande detectado',
  ],
];
@endphp

<div class="row g-3 mb-4">
  @foreach($rolesMeta as $key => $meta)
    <div class="col-6 col-md-3">
      <div class="card shadow-sm h-100">
        <div class="card-header d-flex align-items-center gap-2">
          <i class="bi {{ $meta['icon'] }} text-{{ $meta['color'] }}"></i>
          <span class="fw-semibold" style="font-size:0.875rem;">{{ $meta['label'] }}</span>
          <i class="bi bi-info-circle text-muted ms-auto small"
             data-bs-toggle="tooltip"
             data-bs-placement="top"
             title="{{ $meta['tooltip'] }}"></i>
        </div>
        <div class="card-body">
          @forelse($datos['roles'][$key] ?? [] as $name)
            <span class="badge text-bg-{{ $meta['color'] }} mb-1 me-1 fw-normal">{{ $name }}</span>
          @empty
            <span class="text-muted" style="font-size:0.8rem;">Ninguno identificado</span>
          @endforelse
        </div>
      </div>
    </div>
  @endforeach
</div>

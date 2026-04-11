{{-- resources/views/livewire/partials/roles.blade.php --}}
@php
$rolesMeta = [
  'leaders'   => [
    'label'   => __('Leaders'),
    'icon'    => 'bi-star-fill',
    'color'   => 'warning',
    'tooltip' => __('Students with the highest number of received choices (in-degree)'),
  ],
  'puentes'   => [
    'label'   => __('Bridges'),
    'icon'    => 'bi-intersect',
    'color'   => 'info',
    'tooltip' => __('Students with highest betweenness: connect different subgroups'),
  ],
  'aislados'  => [
    'label'   => __('Isolated'),
    'icon'    => 'bi-person-slash',
    'color'   => 'danger',
    'tooltip' => __('Students with no relationships (neither chosen nor choose)'),
  ],
  'cohesivos' => [
    'label'   => __('Cohesive group'),
    'icon'    => 'bi-people-fill',
    'color'   => 'success',
    'tooltip' => __('Members of the largest detected subgroup'),
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
            <span class="text-muted" style="font-size:0.8rem;">{{ __('None identified') }}</span>
          @endforelse
        </div>
      </div>
    </div>
  @endforeach
</div>

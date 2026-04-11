{{-- resources/views/livewire/partials/comunidades.blade.php --}}
@php
$clusterColors = ['primary','success','warning','danger','info','secondary','dark','primary'];
$communities   = collect($datos['communities'] ?? [])
    ->sortByDesc(fn ($c) => count($c))
    ->values()
    ->all();
@endphp

<div class="card shadow-sm mb-4">
  <div class="card-header">
    <i class="bi bi-diagram-2 me-1 text-primary"></i>{{ __('Detected Communities') }}
  </div>
  <div class="card-body">
    @forelse($communities as $i => $grupo)
      @php $color = $clusterColors[$i % count($clusterColors)]; @endphp
      <div class="mb-3">
        <h6 class="fw-semibold mb-2">
          <span class="badge bg-{{ $color }} me-1">&nbsp;</span>
          Cluster {{ $i + 1 }}
          <span class="text-muted fw-normal small">({{ count($grupo) }} {{ count($grupo) === 1 ? __('member') : __('members') }})</span>
        </h6>
        <div class="d-flex flex-wrap gap-1">
          @foreach($grupo as $nombre)
            <span class="badge rounded-pill text-bg-{{ $color }} px-2 py-1 fw-normal">{{ $nombre }}</span>
          @endforeach
        </div>
      </div>
    @empty
      <p class="text-muted mb-0" style="font-size:0.875rem;">{{ __('No subgroups detected.') }}</p>
    @endforelse
  </div>
</div>

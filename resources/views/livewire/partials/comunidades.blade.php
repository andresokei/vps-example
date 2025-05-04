{{-- resources/views/livewire/partials/comunidades.blade.php --}}
<div class="card shadow-sm mb-4">
  <div class="card-header">Comunidades Detectadas</div>
  <div class="card-body">
    <ul class="list-unstyled mb-0">
      @foreach($datos['communities'] ?? [] as $i => $grupo)
        <li><strong>Cluster {{ $i+1 }}:</strong> {{ implode(', ', $grupo) }}</li>
      @endforeach
      @if(empty($datos['communities']))
        <li class="text-muted">No se detectaron subgrupos.</li>
      @endif
    </ul>
  </div>
</div>

{{-- resources/views/livewire/partials/reciprocidad.blade.php --}}
<div class="card shadow-sm mb-4">
  <div class="card-header">
    <i class="bi bi-grid-3x3 me-1 text-primary"></i>{{ __('Reciprocity Matrix') }}
  </div>
  <div class="card-body">
    @php
      $matrix = $datos['reciprocidad'];
      $names = array_count_values($matrix['labels']);
      foreach ($matrix['labels'] as $i => $name) {
          if ($names[$name] > 1) $matrix['labels'][$i] = $name.' ('.($i + 1).')';
      }
      $matrix['types'] = array_map(fn ($key) => __('reciprocity.'.$key), ['none', 'neg', 'pos', 'nn', 'pp', 'conflict', 'mixed']);
      $matrix['directions'] = array_map(fn ($i) => __('reciprocity.out'.$i), range(0, 3));
      $matrix['diagonal'] = __('reciprocity.skip');
    @endphp
    @if (!collect($matrix['data'])->contains(fn ($cell) => $cell['v'] !== 0))
      <p class="alert alert-info" role="status">{{ __('reciprocity.empty') }}</p>
    @endif
    <script type="application/json" id="reciprocity-data">@json($matrix)</script>
    <div style="overflow-x:auto">
    <div class="heatmap-wrapper" style="min-width:{{ max(350, count($matrix['labels']) * 21 + 110) }}px;height:{{ max(400, count($matrix['labels']) * 21 + 90) }}px">
      <canvas id="heatmapReciprocidad" role="img" aria-label="{{ __('Reciprocity Matrix') }}"></canvas>
    </div>
    </div>
    <div class="heatmap-legend" id="reciprocity-legend" wire:ignore></div>
  </div>
</div>

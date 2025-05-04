<div class="container my-5">
  <!-- TÍTULO -->
  <h2 class="mb-4 pb-2 border-bottom text-primary">
    <i class="fas fa-chart-bar me-2"></i> Análisis de Grupos
  </h2>

  <!-- SELECTORES -->
  @include('livewire.partials.selectores')

  <!-- MENSAJES -->
  @if ($resultadoAnalisis)
    <div class="alert alert-info mb-4">
      {{ $resultadoAnalisis }}
    </div>
  @endif

  <!-- RESULTADOS -->
  @if (!empty($analisis))
    @include('livewire.partials.resumen',       ['datos' => $analisis])
    @include('livewire.partials.barras',        ['datos' => $analisis])
    @include('livewire.partials.sociograma',    ['datos' => $analisis])
    @include('livewire.partials.reciprocidad',  ['datos' => $analisis])
    @include('livewire.partials.comunidades',   ['datos' => $analisis])
    @include('livewire.partials.centralidades', ['datos' => $analisis])
    @include('livewire.partials.roles',         ['datos' => $analisis])

    @isset($tests)
      @include('livewire.partials.historial',  ['tests' => $tests, 'indiceTest' => $indiceTest])
    @endisset
  @endif
</div>

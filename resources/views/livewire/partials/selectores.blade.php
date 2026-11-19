<div class="card mb-4">
  <div class="card-body">
    <label for="grupoSeleccionado" class="form-label fw-semibold">{{ __('Group') }}</label>
    <select id="grupoSeleccionado" class="form-select" style="max-width:32rem" wire:model.live="grupoSeleccionado">
      <option value="">{{ __('— Select a group —') }}</option>
      @foreach ($grupos as $grupo)
        <option value="{{ $grupo->id }}" @selected((string) $grupo->id === $grupoSeleccionado)>{{ $grupo->nombre_grupo }}</option>
      @endforeach
    </select>
    <span wire:loading wire:target="grupoSeleccionado" role="status" class="small text-muted mt-2">
      {{ __('analysis_screen.loading') }}
    </span>
  </div>
</div>

@if ($actual)
  <section class="mb-3" aria-labelledby="assignment-title" wire:key="assignment-header-{{ $actual->id }}">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
      <div>
        <h2 id="assignment-title" class="h4 mb-1">{{ $actual->test->localized_nombre_test }}</h2>
        <p class="mb-0 text-muted">
          {{ $actual->grupo->nombre_grupo }} ·
          <time datetime="{{ $actual->created_at->toIso8601String() }}">{{ $actual->created_at->locale(app()->getLocale())->isoFormat('LL · HH:mm') }}</time>
        </p>
      </div>
      <div class="d-flex align-items-center flex-wrap gap-3">
        @if ($asignaciones->count() > 1)
          <a href="{{ route('analisis', ['grupo' => $actual->grupo_id]) }}">{{ __('analysis_screen.change') }}</a>
        @endif
        @if ($analisis)
          <a href="{{ route('analisis.pdf', $actual->id) }}" target="_blank" rel="noopener" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-file-earmark-pdf me-1" aria-hidden="true"></i>{{ __('Export PDF') }}
          </a>
        @endif
      </div>
    </div>
    @if (! $actual->respuestas_count)
      <p class="mt-3 mb-0 alert alert-light border">{{ __('analysis_screen.no_answers') }}</p>
    @endif
  </section>
@elseif ($asignaciones->isNotEmpty())
  <section class="card mb-4" aria-labelledby="choose-assignment">
    <div class="card-body pb-2">
      <h2 id="choose-assignment" class="h5">{{ __('analysis_screen.choose') }}</h2>
    </div>
    <ul class="list-group list-group-flush">
      @foreach ($asignaciones as $item)
        <li class="list-group-item px-3 py-3 d-flex flex-wrap align-items-center justify-content-between gap-3" wire:key="assignment-option-{{ $item->id }}">
          <div>
            <h3 class="h6 mb-1">{{ $item->test->localized_nombre_test }}</h3>
            <div class="text-muted small">
              <time datetime="{{ $item->created_at->toIso8601String() }}">{{ $item->created_at->locale(app()->getLocale())->isoFormat('LL · HH:mm') }}</time>
              · {{ __('analysis_screen.respondents', ['people' => $item->participantes_count, 'total' => count($item->alumnado_ids ?? $item->grupo->estudiantes)]) }}
            </div>
          </div>
          <a class="btn btn-outline-primary btn-sm" href="{{ route('analisis', ['grupo' => $item->grupo_id, 'asignacion' => $item->id]) }}">
            {{ __('analysis_screen.view') }}<span class="visually-hidden"> · {{ $item->test->localized_nombre_test }} · {{ $item->created_at->format('d/m/Y H:i') }}</span>
          </a>
        </li>
      @endforeach
    </ul>
  </section>
@elseif ($grupoSeleccionado && ! $resultadoAnalisis)
  <p class="alert alert-light border">{{ __('No test assignments found for this group.') }}</p>
@endif

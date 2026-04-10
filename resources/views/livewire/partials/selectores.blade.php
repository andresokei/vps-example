<div class="card mb-4">
  <div class="card-body">
    <div class="row g-3">
      <div class="col-md-6">
        <label for="grupoSeleccionado" class="form-label">
          <i class="bi bi-people me-1 text-primary"></i> Grupo
        </label>
        <select id="grupoSeleccionado"
                class="form-select"
                wire:model.live="grupoSeleccionado">
          <option value="">— Seleccione un grupo —</option>
          @foreach ($grupos as $grupo)
            <option value="{{ $grupo->id }}">{{ $grupo->nombre_grupo }}</option>
          @endforeach
        </select>
      </div>

      <div class="col-md-6">
        <label for="asignacionSeleccionada" class="form-label">
          <i class="bi bi-file-earmark-text me-1 text-primary"></i> Asignación de test
        </label>
        <select id="asignacionSeleccionada"
                class="form-select"
                wire:model.live="asignacionTestId"
                @disabled(!$asignaciones || (is_object($asignaciones) && $asignaciones->isEmpty()))>
          <option value="">— Seleccione una asignación —</option>
          @foreach ($asignaciones as $asignacion)
            <option value="{{ $asignacion->id }}">
              {{ optional($asignacion->test)->nombre_test ?: 'Test' }}
              · {{ $asignacion->created_at->format('d/m/Y H:i') }}
              · {{ $asignacion->respuestas_count ?? 0 }} resp.
            </option>
          @endforeach
        </select>
      </div>
    </div>

    <div class="mt-4 d-flex justify-content-center">
      <button wire:click="procesarAnalisis"
              wire:loading.attr="disabled"
              wire:target="procesarAnalisis"
              @disabled(!$grupoSeleccionado || !$asignacionTestId)
              class="btn btn-primary px-5">
        <span wire:loading.remove wire:target="procesarAnalisis">
          <i class="bi bi-play-fill me-1"></i> Procesar análisis
        </span>
        <span wire:loading wire:target="procesarAnalisis">
          <span class="spinner-border spinner-border-sm me-1" role="status"></span>
          Procesando…
        </span>
      </button>
    </div>
  </div>
</div>

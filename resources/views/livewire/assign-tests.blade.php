<div>
  <form wire:submit.prevent="assign">
    <div class="mb-3">
      <label class="form-label">Grupo</label>
      <select wire:model="grupo_id" class="form-select">
        <option value="" disabled selected>Selecciona un grupo</option>
        @foreach($grupos as $g)
          <option value="{{ $g->id }}">{{ $g->nombre_grupo }}</option>
        @endforeach
      </select>
    </div>

    <div class="mb-3">
      <label class="form-label">Test</label>
      <select wire:model="test_id" class="form-select">
        <option value="" disabled selected>Selecciona un test</option>
        @foreach($tests as $t)
          <option value="{{ $t->id }}">{{ $t->nombre_test }}</option>
        @endforeach
      </select>
    </div>

    <div class="d-grid">
      <button type="submit" class="btn btn-primary">
        <i class="bi bi-clipboard-check me-1"></i> Asignar test
      </button>
    </div>
  </form>

  @if($successMessage)
    <div class="alert alert-success mt-3 d-flex align-items-center gap-2">
      <i class="bi bi-check-circle-fill flex-shrink-0"></i>
      {{ $successMessage }}
    </div>
  @endif

  @if($errorMessage)
    <div class="alert alert-danger mt-3 d-flex align-items-center gap-2">
      <i class="bi bi-x-circle-fill flex-shrink-0"></i>
      {{ $errorMessage }}
    </div>
  @endif
</div>

<div>
<form wire:submit.prevent="assign">
  <div class="form-group mb-4">
    <label class="font-weight-bold">Selecciona un Grupo</label>
    <div class="dropdown-select">
      <select wire:model="grupo_id" class="form-control rounded">
        <option value="" disabled selected>Selecciona un grupo</option>
        @foreach($grupos as $g)
          <option value="{{ $g->id }}">{{ $g->nombre_grupo }}</option>
        @endforeach
      </select>
      <i class="fas fa-chevron-down select-arrow"></i>
    </div>
  </div>

  <div class="form-group mb-4">
    <label class="font-weight-bold">Selecciona un Test</label>
    <div class="dropdown-select">
      <select wire:model="test_id" class="form-control rounded">
        <option value="" disabled selected>Selecciona un test</option>
        @foreach($tests as $t)
          <option value="{{ $t->id }}">{{ $t->nombre_test }}</option>
        @endforeach
      </select>
      <i class="fas fa-chevron-down select-arrow"></i>
    </div>
  </div>

  <button type="submit" class="btn btn-primary btn-block py-2">Asignar Test</button>
</form>
@if($successMessage)
    <div class="alert alert-success mt-3">{{ $successMessage }}</div>
@endif

@if($errorMessage)
    <div class="alert alert-danger mt-3">{{ $errorMessage }}</div>
@endif



</div>
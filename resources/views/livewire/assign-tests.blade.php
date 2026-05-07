<div>
  @if ($grupos->isEmpty())
    <div class="text-center py-4">
      <i class="bi bi-diagram-3 text-muted d-block mb-2" style="font-size:2rem;"></i>
      <p class="text-muted mb-1" style="font-size:0.9rem;">{{ __('No groups yet.') }}</p>
      <p class="text-muted" style="font-size:0.8rem;">{{ __('Create a group first to be able to assign tests.') }}</p>
    </div>
  @else
  <form wire:submit.prevent="assign">
    <div class="mb-3">
      <label class="form-label">{{ __('Group') }}</label>
      <select wire:model="grupo_id" class="form-select">
        <option value="" disabled selected>{{ __('Select a group') }}</option>
        @foreach($grupos as $g)
          <option value="{{ $g->id }}">{{ $g->nombre_grupo }}</option>
        @endforeach
      </select>
    </div>

    <div class="mb-3">
      <label class="form-label">{{ __('Test') }}</label>
      <select wire:model="test_id" class="form-select">
        <option value="" disabled selected>{{ __('Select a test') }}</option>
        @foreach($tests as $t)
          <option value="{{ $t->id }}">
            {{ $t->localized_nombre_test }}
            @if(!$t->id_profesor) · {{ __('template') }}@endif
            ({{ $t->preguntas_count }} {{ $t->preguntas_count === 1 ? __('question') : __('questions') }})
          </option>
        @endforeach
      </select>
      @if($tests->isEmpty())
        <div class="form-text text-muted">
          <a href="{{ route('tests.index') }}">{{ __('Create a test first.') }}</a>
        </div>
      @endif
    </div>

    <div class="d-grid">
      <button type="submit" class="btn btn-primary">
        <i class="bi bi-clipboard-check me-1"></i> {{ __('Assign test') }}
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
  @endif
</div>

<div>
  {{-- Flash messages --}}
  @if (session()->has('message'))
    <div class="alert alert-success d-flex align-items-center gap-2 mb-3" role="alert">
      <i class="bi bi-check-circle-fill flex-shrink-0"></i>
      {{ session('message') }}
    </div>
  @endif
  @if (session()->has('error'))
    <div class="alert alert-danger d-flex align-items-center gap-2 mb-3" role="alert">
      <i class="bi bi-exclamation-circle-fill flex-shrink-0"></i>
      {{ session('error') }}
    </div>
  @endif

  <div class="row g-4">

    {{-- Columna izquierda: lista de tests --}}
    <div class="col-md-5">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h6 class="fw-semibold mb-0">{{ __('My Tests') }}</h6>
        <button class="btn btn-primary btn-sm" wire:click="abrirModalCrear">
          <i class="bi bi-plus-lg me-1"></i>{{ __('New Test') }}
        </button>
      </div>

      @forelse ($tests as $test)
        <div class="card mb-2 test-card {{ $selectedTestId === $test->id ? 'border-primary' : '' }}"
             style="cursor:pointer"
             wire:click="seleccionarTest({{ $test->id }})">
          <div class="card-body py-2 px-3">
            <div class="d-flex justify-content-between align-items-start gap-2">
              <div class="flex-grow-1 min-width-0">
                <div class="fw-medium text-truncate">{{ $test->nombre_test }}</div>
                <small class="text-muted">
                  {{ $test->preguntas_count }}
                  {{ $test->preguntas_count === 1 ? __('question') : __('questions') }}
                </small>
              </div>
              <div class="btn-group btn-group-sm flex-shrink-0" @click.stop>
                <button class="btn btn-outline-secondary"
                        wire:click.stop="abrirModalEditar({{ $test->id }})"
                        title="{{ __('Edit test') }}">
                  <i class="bi bi-pencil"></i>
                </button>
                <button class="btn btn-outline-danger"
                        wire:click.stop="eliminarTest({{ $test->id }})"
                        wire:confirm="{{ __('Are you sure you want to delete this test?') }}"
                        title="{{ __('Delete test') }}">
                  <i class="bi bi-trash"></i>
                </button>
              </div>
            </div>
          </div>
        </div>
      @empty
        <div class="text-center text-muted py-4">
          <i class="bi bi-file-earmark-x fs-3 d-block mb-2"></i>
          {{ __('No tests created yet.') }}
        </div>
      @endforelse
    </div>

    {{-- Columna derecha: panel de preguntas --}}
    <div class="col-md-7">
      @if ($selectedTestId)
        @php
          $testSeleccionado = $tests->firstWhere('id', $selectedTestId);
        @endphp

        <div class="d-flex justify-content-between align-items-center mb-3">
          <h6 class="fw-semibold mb-0 text-truncate">
            <i class="bi bi-list-ol me-1 text-primary"></i>
            {{ $testSeleccionado?->nombre_test }}
          </h6>
          <span class="badge bg-primary-subtle text-primary">
            {{ count($preguntas) }} {{ count($preguntas) === 1 ? __('question') : __('questions') }}
          </span>
        </div>

        {{-- Lista de preguntas --}}
        @if (count($preguntas) > 0)
          <div class="list-group mb-3">
            @foreach ($preguntas as $i => $pregunta)
              <div class="list-group-item list-group-item-action d-flex align-items-start gap-2 py-2">
                <span class="text-muted small mt-1 fw-medium" style="min-width:1.5rem">{{ $i + 1 }}.</span>
                <div class="flex-grow-1">
                  <div class="mb-1">{{ $pregunta['texto_pregunta'] }}</div>
                  <span class="badge {{ $pregunta['tipo_pregunta'] === 'preferencia' ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }} rounded-pill">
                    <i class="bi {{ $pregunta['tipo_pregunta'] === 'preferencia' ? 'bi-heart' : 'bi-x-circle' }} me-1"></i>
                    {{ $pregunta['tipo_pregunta'] === 'preferencia' ? __('Preference') : __('Rejection') }}
                  </span>
                </div>
                <div class="btn-group btn-group-sm flex-shrink-0">
                  <button class="btn btn-outline-secondary"
                          wire:click="moverPregunta({{ $pregunta['id'] }}, 'up')"
                          {{ $i === 0 ? 'disabled' : '' }}
                          title="{{ __('Move up') }}">
                    <i class="bi bi-arrow-up"></i>
                  </button>
                  <button class="btn btn-outline-secondary"
                          wire:click="moverPregunta({{ $pregunta['id'] }}, 'down')"
                          {{ $i === count($preguntas) - 1 ? 'disabled' : '' }}
                          title="{{ __('Move down') }}">
                    <i class="bi bi-arrow-down"></i>
                  </button>
                  <button class="btn btn-outline-danger"
                          wire:click="eliminarPregunta({{ $pregunta['id'] }})"
                          wire:confirm="{{ __('Are you sure?') }}"
                          title="{{ __('Delete') }}">
                    <i class="bi bi-trash"></i>
                  </button>
                </div>
              </div>
            @endforeach
          </div>
        @else
          <div class="text-center text-muted py-3 mb-3">
            <i class="bi bi-question-circle fs-4 d-block mb-1"></i>
            {{ __('No questions yet. Add the first one below.') }}
          </div>
        @endif

        {{-- Formulario añadir pregunta --}}
        <form wire:submit.prevent="añadirPregunta" class="card card-body bg-light border-dashed">
          <div class="mb-2">
            <textarea wire:model="preguntaTexto"
                      class="form-control form-control-sm @error('preguntaTexto') is-invalid @enderror"
                      rows="2"
                      placeholder="{{ __('Question text...') }}"></textarea>
            @error('preguntaTexto')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
          <div class="d-flex gap-2">
            <select wire:model="preguntaTipo"
                    class="form-select form-select-sm @error('preguntaTipo') is-invalid @enderror"
                    style="max-width:180px">
              <option value="preferencia">{{ __('Preference') }}</option>
              <option value="rechazo">{{ __('Rejection') }}</option>
            </select>
            <button type="submit" class="btn btn-sm btn-primary">
              <i class="bi bi-plus-lg me-1"></i>{{ __('Add question') }}
            </button>
          </div>
        </form>

      @else
        <div class="d-flex flex-column align-items-center justify-content-center text-muted h-100 py-5">
          <i class="bi bi-arrow-left-circle fs-2 mb-2"></i>
          <p class="mb-0">{{ __('Select a test to view its questions.') }}</p>
        </div>
      @endif
    </div>

  </div>

  {{-- Modal: Crear test --}}
  <div class="modal fade" id="modalCrearTest"
       tabindex="-1"
       aria-labelledby="modalCrearTestLabel"
       aria-hidden="true"
       wire:ignore.self>
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <form wire:submit.prevent="crearTest">
          <div class="modal-header">
            <h5 class="modal-title" id="modalCrearTestLabel">
              <i class="bi bi-file-earmark-plus me-2 text-primary"></i>{{ __('New Test') }}
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label fw-medium">{{ __('Test name') }} <span class="text-danger">*</span></label>
              <input type="text"
                     wire:model="nombre"
                     class="form-control @error('nombre') is-invalid @enderror"
                     placeholder="{{ __('E.g. Class sociometry') }}"
                     autofocus>
              @error('nombre')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
            <div class="mb-1">
              <label class="form-label fw-medium">{{ __('Description') }}</label>
              <textarea wire:model="descripcion"
                        class="form-control @error('descripcion') is-invalid @enderror"
                        rows="3"
                        placeholder="{{ __('Optional description for the test...') }}"></textarea>
              @error('descripcion')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
            <button type="submit" class="btn btn-primary">
              <i class="bi bi-check-lg me-1"></i>{{ __('Create') }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  {{-- Modal: Editar test --}}
  <div class="modal fade" id="modalEditarTest"
       tabindex="-1"
       aria-labelledby="modalEditarTestLabel"
       aria-hidden="true"
       wire:ignore.self>
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <form wire:submit.prevent="actualizarTest">
          <div class="modal-header">
            <h5 class="modal-title" id="modalEditarTestLabel">
              <i class="bi bi-pencil-square me-2 text-primary"></i>{{ __('Edit test') }}
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label fw-medium">{{ __('Test name') }} <span class="text-danger">*</span></label>
              <input type="text"
                     wire:model="nombre"
                     class="form-control @error('nombre') is-invalid @enderror">
              @error('nombre')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
            <div class="mb-1">
              <label class="form-label fw-medium">{{ __('Description') }}</label>
              <textarea wire:model="descripcion"
                        class="form-control @error('descripcion') is-invalid @enderror"
                        rows="3"></textarea>
              @error('descripcion')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
            <button type="submit" class="btn btn-primary">
              <i class="bi bi-check-lg me-1"></i>{{ __('Save') }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  @push('scripts')
  <script>
    (function () {
      const elCrear  = document.getElementById('modalCrearTest');
      const elEditar = document.getElementById('modalEditarTest');
      if (!elCrear || !elEditar) return;

      const modalCrear  = new bootstrap.Modal(elCrear);
      const modalEditar = new bootstrap.Modal(elEditar);

      Livewire.on('openModalCrearTest',  () => modalCrear.show());
      Livewire.on('closeModalCrearTest', () => modalCrear.hide());
      Livewire.on('openModalEditarTest',  () => modalEditar.show());
      Livewire.on('closeModalEditarTest', () => modalEditar.hide());
    })();
  </script>
  @endpush
</div>

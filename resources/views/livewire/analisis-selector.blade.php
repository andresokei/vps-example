<div>
  <div class="page-header">
    <h1 class="page-title">
      <i class="bi bi-graph-up me-2 text-primary"></i>{{ __('Group Analysis') }}
    </h1>
    <p class="page-subtitle">{{ __('Select a group and an assignment to generate the sociometric analysis.') }}</p>
  </div>

  @include('livewire.partials.selectores')

  @if ($resultadoAnalisis)
    <div class="alert alert-{{ $resultadoTipo ?? 'info' }} d-flex align-items-center gap-2 mb-4">
      @if(($resultadoTipo ?? 'info') === 'success')
        <i class="bi bi-check-circle-fill flex-shrink-0"></i>
      @elseif(($resultadoTipo ?? 'info') === 'warning')
        <i class="bi bi-exclamation-triangle-fill flex-shrink-0"></i>
      @elseif($resultadoTipo === 'danger')
        <i class="bi bi-x-circle-fill flex-shrink-0"></i>
      @else
        <i class="bi bi-info-circle-fill flex-shrink-0"></i>
      @endif
      {{ $resultadoAnalisis }}
    </div>
  @endif

  @if (!empty($analisis))
    <div class="d-flex justify-content-end mb-3">
      <a href="{{ route('analisis.pdf', $asignacionTestId) }}"
         target="_blank"
         class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-file-earmark-pdf me-1"></i> {{ __('Export PDF') }}
      </a>
    </div>

    <ul class="nav nav-tabs-analisis mb-0" id="analisisTabs" role="tablist">
      <li class="nav-item" role="presentation">
        <button class="nav-link active" id="tab-resumen-btn"
                data-bs-toggle="tab" data-bs-target="#tab-resumen"
                type="button" role="tab" aria-controls="tab-resumen" aria-selected="true">
          <i class="bi bi-speedometer2 me-1"></i>{{ __('Summary') }}
        </button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link" id="tab-sociograma-btn"
                data-bs-toggle="tab" data-bs-target="#tab-sociograma"
                type="button" role="tab" aria-controls="tab-sociograma" aria-selected="false">
          <i class="bi bi-diagram-3 me-1"></i>{{ __('Sociogram') }}
        </button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link" id="tab-detalles-btn"
                data-bs-toggle="tab" data-bs-target="#tab-detalles"
                type="button" role="tab" aria-controls="tab-detalles" aria-selected="false">
          <i class="bi bi-table me-1"></i>{{ __('Details') }}
        </button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link" id="tab-reciprocidad-btn"
                data-bs-toggle="tab" data-bs-target="#tab-reciprocidad"
                type="button" role="tab" aria-controls="tab-reciprocidad" aria-selected="false">
          <i class="bi bi-grid me-1"></i>{{ __('Reciprocity') }}
        </button>
      </li>
    </ul>

    <div class="tab-content tab-content-analisis" id="analisisTabContent">
      <div class="tab-pane fade show active" id="tab-resumen" role="tabpanel" aria-labelledby="tab-resumen-btn">
        @include('livewire.partials.resumen', ['datos' => $analisis])
        @include('livewire.partials.barras', ['datos' => $analisis])
      </div>

      <div class="tab-pane fade" id="tab-sociograma" role="tabpanel" aria-labelledby="tab-sociograma-btn">
        @include('livewire.partials.sociograma', ['datos' => $analisis])
      </div>

      <div class="tab-pane fade" id="tab-detalles" role="tabpanel" aria-labelledby="tab-detalles-btn">
        @include('livewire.partials.centralidades', ['datos' => $analisis])
        @include('livewire.partials.roles', ['datos' => $analisis])
        @include('livewire.partials.comunidades', ['datos' => $analisis])
      </div>

      <div class="tab-pane fade" id="tab-reciprocidad" role="tabpanel" aria-labelledby="tab-reciprocidad-btn">
        @include('livewire.partials.reciprocidad', ['datos' => $analisis])
      </div>
    </div>

    <div class="modal fade" id="studentDetailModal"
         tabindex="-1"
         aria-labelledby="studentDetailModalLabel"
         aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
          <div class="modal-header border-0 pb-0">
            <div class="d-flex align-items-center gap-3">
              <div class="student-avatar" id="sdm-avatar"></div>
              <div>
                <h5 class="modal-title mb-0 fw-bold" id="studentDetailModalLabel"></h5>
                <span class="badge mt-1" id="sdm-role-badge"></span>
              </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
          </div>
          <div class="modal-body pt-3">
            <p class="text-muted mb-2" style="font-size:0.75rem;text-transform:uppercase;letter-spacing:0.05em;font-weight:600;">
              {{ __('Relations') }}
            </p>
            <div class="row g-2 mb-3" id="sdm-metrics-row">
            </div>

            <p class="text-muted mb-2" style="font-size:0.75rem;text-transform:uppercase;letter-spacing:0.05em;font-weight:600;">
              {{ __('Centrality') }}
            </p>
            <div class="row g-2 mb-3" id="sdm-centrality-row">
            </div>

            <div class="row g-3" id="sdm-relations-row">
            </div>
          </div>
        </div>
      </div>
    </div>
  @endif
</div>

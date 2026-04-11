<div wire:poll.30000ms="loadAsignaciones">
  @if ($resultado)
    <div class="alert alert-{{ $resultadoTipo }} alert-dismissible fade show d-flex align-items-center gap-2 mb-3" role="alert">
      <i class="bi {{ $resultadoTipo === 'danger' ? 'bi-x-circle-fill' : ($resultadoTipo === 'warning' ? 'bi-exclamation-triangle-fill' : ($resultadoTipo === 'info' ? 'bi-info-circle-fill' : 'bi-check-circle-fill')) }}"></i>
      <span>{{ $resultado }}</span>
      <button type="button" class="btn-close" wire:click="clearResult" aria-label="{{ __('Close') }}"></button>
    </div>
  @endif

  <div class="table-responsive">
    <table class="table table-hover mb-0">
      <thead>
        <tr>
          <th>{{ __('Test name') }}</th>
          <th>{{ __('Group') }}</th>
          <th>{{ __('Status') }}</th>
          <th>{{ __('Progress') }}</th>
          <th>{{ __('Pending') }}</th>
          <th>{{ __('Date') }}</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        @forelse ($asignaciones as $item)
          <tr>
            <td>{{ $item->test->nombre_test }}</td>
            <td>{{ $item->grupo->nombre_grupo }}</td>

            <td>
              @php
                $estado = strtolower($item->estado);
                $badgeMap = [
                  'pendiente' => 'badge-warning',
                  'en progreso' => 'badge-info',
                  'aplicado' => 'badge-success',
                ];
                $badgeClass = $badgeMap[$estado] ?? 'badge-secondary';
                $estadoLabels = [
                  'pendiente'   => __('Pending status'),
                  'en progreso' => __('In progress'),
                  'aplicado'    => __('Applied'),
                ];
              @endphp
              <span class="badge rounded-pill {{ $badgeClass }} px-3 py-2">
                {{ $estadoLabels[$estado] ?? ucfirst($estado) }}
              </span>
            </td>

            <td style="min-width:130px">
              <div class="d-flex align-items-center gap-2">
                <div class="progress flex-grow-1" style="height:6px">
                  <div class="progress-bar
                    @if($item->progreso_pct >= 100) bg-success
                    @elseif($item->progreso_pct > 0) bg-primary
                    @else bg-secondary @endif"
                    role="progressbar"
                    style="width: {{ $item->progreso_pct }}%"
                    aria-valuenow="{{ $item->progreso_pct }}"
                    aria-valuemin="0"
                    aria-valuemax="100">
                  </div>
                </div>
                <small class="text-muted text-nowrap">
                  {{ $item->progreso_respondieron }}/{{ $item->progreso_total }}
                </small>
              </div>
            </td>

            <td>
              <span class="fw-semibold">{{ $item->alumnos_pendientes_count }}</span>
            </td>

            <td>{{ $item->created_at->format('d/m/Y') }}</td>

            <td class="text-end">
              <button wire:click.prevent="seleccionarAsignacion({{ $item->id }})"
                      class="btn btn-sm btn-outline-primary"
                      data-bs-toggle="modal"
                      data-bs-target="#detalleTestModal"
                      title="{{ __('Test details') }}">
                <i class="bi bi-eye"></i>
              </button>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="7" class="text-center py-4 text-muted">
              {{ __('No tests assigned.') }}
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div class="modal fade" id="detalleTestModal"
       tabindex="-1"
       aria-labelledby="detalleTestModalLabel"
       aria-hidden="true"
       wire:ignore.self>
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <div>
            <h5 class="modal-title" id="detalleTestModalLabel">
              {{ $asignacionSeleccionada?->test->nombre_test ?? __('Test details') }}
            </h5>
            @if ($asignacionSeleccionada)
              <p class="text-muted small mb-0 mt-1">
                {{ $asignacionSeleccionada->grupo->nombre_grupo }}
              </p>
            @endif
          </div>
          <button type="button"
                  class="btn-close"
                  data-bs-dismiss="modal"
                  aria-label="{{ __('Close') }}">
          </button>
        </div>

        <div class="modal-body">
          @if ($asignacionSeleccionada)
            <div class="row g-3">
              <div class="col-sm-4">
                <div class="metric-tile">
                  <div class="metric-value">{{ $asignacionSeleccionada->progreso_pct ?? 0 }}%</div>
                  <div class="metric-label">{{ __('Completed') }}</div>
                </div>
              </div>
              <div class="col-sm-4">
                <div class="metric-tile">
                  <div class="metric-value">
                    {{ $asignacionSeleccionada->progreso_respondieron ?? 0 }}/{{ $asignacionSeleccionada->progreso_total ?? 0 }}
                  </div>
                  <div class="metric-label">{{ __('Answered') }}</div>
                </div>
              </div>
              <div class="col-sm-4">
                <div class="metric-tile">
                  <div class="metric-value">{{ $asignacionSeleccionada->alumnos_pendientes_count ?? 0 }}</div>
                  <div class="metric-label">{{ __('Pending') }}</div>
                </div>
              </div>
            </div>

            <hr class="my-3">

            <dl class="row mb-4" style="font-size:0.875rem;">
              <dt class="col-sm-4 text-muted fw-normal">{{ __('Status') }}</dt>
              <dd class="col-sm-8 fw-medium">
                @php
                  $estadoLabels = [
                    'pendiente'   => __('Pending status'),
                    'en progreso' => __('In progress'),
                    'aplicado'    => __('Applied'),
                  ];
                  $e = strtolower($asignacionSeleccionada->estado);
                @endphp
                {{ $estadoLabels[$e] ?? ucfirst($e) }}
              </dd>

              <dt class="col-sm-4 text-muted fw-normal">{{ __('Access key') }}</dt>
              <dd class="col-sm-8 d-flex flex-wrap align-items-center gap-2">
                <code class="bg-light px-2 py-1 rounded">{{ $asignacionSeleccionada->clave_acceso }}</code>
                <button type="button"
                        class="btn btn-sm btn-outline-secondary"
                        data-copy-text="{{ $asignacionSeleccionada->clave_acceso }}"
                        data-copy-label="{{ __('Access key') }}">
                  <i class="bi bi-copy me-1"></i>{{ __('Copy') }}
                </button>
              </dd>

              <dt class="col-sm-4 text-muted fw-normal">{{ __('Student link') }}</dt>
              <dd class="col-sm-8 d-flex flex-wrap align-items-center gap-2">
                <a href="{{ $asignacionSeleccionada->entry_url }}" target="_blank" class="text-primary">
                  {{ $asignacionSeleccionada->entry_url }}
                </a>
                <button type="button"
                        class="btn btn-sm btn-outline-secondary"
                        data-copy-text="{{ $asignacionSeleccionada->share_text }}"
                        data-copy-label="{{ __('Student link') }}">
                  <i class="bi bi-link-45deg me-1"></i>{{ __('Copy access') }}
                </button>
              </dd>

              <dt class="col-sm-4 text-muted fw-normal">{{ __('Assignment date') }}</dt>
              <dd class="col-sm-8">{{ $asignacionSeleccionada->created_at->format('d/m/Y H:i') }}</dd>
            </dl>

            <div class="card border-0 bg-light-subtle">
              <div class="card-body">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                  <div>
                    <h6 class="mb-1">{{ __('Pending students') }}</h6>
                    <p class="text-muted small mb-0">{{ __('It helps you quickly see who has not yet answered.') }}</p>
                  </div>

                  <div class="d-flex flex-wrap gap-2">
                    @if ($asignacionSeleccionada->can_export_pdf)
                      <a href="{{ route('analisis', ['grupo' => $asignacionSeleccionada->grupo_id, 'asignacion' => $asignacionSeleccionada->id]) }}"
                         class="btn btn-sm btn-primary">
                        <i class="bi bi-graph-up-arrow me-1"></i>{{ __('View analysis') }}
                      </a>

                      <a href="{{ route('analisis.pdf', $asignacionSeleccionada->id) }}"
                         target="_blank"
                         class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-file-earmark-pdf me-1"></i>{{ __('Export PDF') }}
                      </a>
                    @endif
                  </div>
                </div>

                @if (($asignacionSeleccionada->alumnos_pendientes_count ?? 0) > 0)
                  <div class="d-flex flex-wrap gap-2">
                    @foreach ($asignacionSeleccionada->alumnos_pendientes as $alumno)
                      <span class="badge text-bg-light border fw-normal px-3 py-2">
                        {{ $alumno->nombre }}
                      </span>
                    @endforeach
                  </div>
                @else
                  <div class="alert alert-success mb-0">
                    {{ __('All students in the group have completed this test.') }}
                  </div>
                @endif
              </div>
            </div>
          @else
            <div class="alert alert-warning mb-0">
              {{ __('No test details found.') }}
            </div>
          @endif
        </div>

        <div class="modal-footer justify-content-between">
          @if ($asignacionSeleccionada)
            <div class="d-flex flex-wrap gap-2">
              <button type="button"
                      class="btn btn-outline-secondary btn-sm"
                      wire:click="regenerarClave({{ $asignacionSeleccionada->id }})">
                <i class="bi bi-arrow-repeat me-1"></i>{{ __('Regenerate key') }}
              </button>

              @if ($asignacionSeleccionada->can_close)
                <button type="button"
                        class="btn btn-outline-danger btn-sm"
                        wire:click="cerrarAsignacion({{ $asignacionSeleccionada->id }})">
                  <i class="bi bi-pause-circle me-1"></i>{{ __('Close assignment') }}
                </button>
              @elseif ($asignacionSeleccionada->can_reopen)
                <button type="button"
                        class="btn btn-outline-success btn-sm"
                        wire:click="reabrirAsignacion({{ $asignacionSeleccionada->id }})">
                  <i class="bi bi-play-circle me-1"></i>{{ __('Reopen assignment') }}
                </button>
              @endif
            </div>
          @else
            <span></span>
          @endif

          <button type="button"
                  class="btn btn-secondary"
                  data-bs-dismiss="modal"
                  wire:click="resetModal">
            {{ __('Close') }}
          </button>
        </div>
      </div>
    </div>
  </div>

  @push('scripts')
    <script>
      (function () {
        if (window.__assignedTestsCopyBound) {
          return;
        }

        window.__assignedTestsCopyBound = true;

        async function copyText(text) {
          if (navigator.clipboard?.writeText) {
            await navigator.clipboard.writeText(text);
            return;
          }

          const area = document.createElement('textarea');
          area.value = text;
          area.setAttribute('readonly', '');
          area.style.position = 'absolute';
          area.style.left = '-9999px';
          document.body.appendChild(area);
          area.select();
          document.execCommand('copy');
          area.remove();
        }

        document.addEventListener('click', async function (event) {
          const button = event.target.closest('[data-copy-text]');
          if (!button) {
            return;
          }

          const originalHtml = button.innerHTML;
          const label = button.getAttribute('data-copy-label') || 'Text';

          try {
            await copyText(button.getAttribute('data-copy-text') || '');
            button.innerHTML = '<i class="bi bi-check2 me-1"></i>{{ __("Copied") }}';
            button.classList.remove('btn-outline-secondary');
            button.classList.add('btn-success');
          } catch (error) {
            button.innerHTML = `<i class="bi bi-x-circle me-1"></i>${label}`;
            button.classList.remove('btn-outline-secondary');
            button.classList.add('btn-danger');
          }

          setTimeout(() => {
            button.innerHTML = originalHtml;
            button.classList.remove('btn-success', 'btn-danger');
            button.classList.add('btn-outline-secondary');
          }, 1600);
        });
      })();
    </script>
  @endpush
</div>

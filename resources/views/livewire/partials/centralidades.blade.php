{{-- resources/views/livewire/partials/centralidades.blade.php --}}
@php $centralities = $datos['centralities'] ?? []; @endphp

<div class="card shadow-sm mb-4">
  <div class="card-header d-flex align-items-center justify-content-between">
    <span>
      <i class="bi bi-bar-chart-steps me-1 text-primary"></i>Tabla de Centralidades
    </span>
    <small class="text-muted d-none d-md-block">
      Clic en encabezado para ordenar &middot; Clic en fila para ver detalles
    </small>
  </div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover mb-0 align-middle" id="centralidades-table">
        <thead class="table-light">
          <tr>
            <th data-sort="name"        class="ps-3">Alumno <i class="bi bi-arrow-down-up text-muted small ms-1"></i></th>
            <th data-sort="inDegree"    class="text-center">In-Degree <i class="bi bi-arrow-down-up text-muted small ms-1"></i></th>
            <th data-sort="outDegree"   class="text-center">Out-Degree <i class="bi bi-arrow-down-up text-muted small ms-1"></i></th>
            <th data-sort="betweenness" class="text-center">Betweenness <i class="bi bi-arrow-down-up text-muted small ms-1"></i></th>
            <th data-sort="closeness"   class="text-center">Closeness <i class="bi bi-arrow-down-up text-muted small ms-1"></i></th>
          </tr>
        </thead>
        <tbody>
          @forelse($centralities as $c)
            <tr
              data-student-id="{{ $c['id'] }}"
              data-name="{{ $c['name'] }}"
              data-in-degree="{{ $c['inDegree'] }}"
              data-out-degree="{{ $c['outDegree'] }}"
              data-betweenness="{{ $c['betweenness'] }}"
              data-closeness="{{ $c['closeness'] }}"
            >
              <td class="ps-3 fw-medium">{{ $c['name'] }}</td>
              <td class="text-center">
                <div class="d-flex align-items-center gap-2">
                  <span class="fw-bold">{{ $c['inDegree'] }}</span>
                  <div class="inline-bar-track flex-grow-1">
                    <div class="indegree-bar-fill inline-bar-fill bg-primary" style="width:0%"></div>
                  </div>
                </div>
              </td>
              <td class="text-center">
                <div class="d-flex align-items-center gap-2">
                  <span class="fw-bold">{{ $c['outDegree'] }}</span>
                  <div class="inline-bar-track flex-grow-1">
                    <div class="outdegree-bar-fill inline-bar-fill bg-secondary" style="width:0%"></div>
                  </div>
                </div>
              </td>
              <td class="text-center">{{ number_format($c['betweenness'], 2) }}</td>
              <td class="text-center">{{ number_format($c['closeness'],   2) }}</td>
            </tr>
          @empty
            <tr>
              <td colspan="5" class="text-center text-muted py-3">Sin datos de centralidad.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>

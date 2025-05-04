{{-- resources/views/livewire/partials/centralidades.blade.php --}}
<div class="card shadow-sm mb-4">
  <div class="card-header">Tabla de Centralidades</div>
  <div class="card-body p-0">
    <table class="table mb-0">
      <thead class="table-light">
        <tr>
          <th>Alumno</th><th>In-Degree</th><th>Out-Degree</th>
          <th>Betweenness</th><th>Closeness</th>
        </tr>
      </thead>
      <tbody>
        @forelse($datos['centralities'] ?? [] as $c)
          <tr>
            <td>{{ $c['name'] }}</td>
            <td>{{ $c['inDegree'] }}</td>
            <td>{{ $c['outDegree'] }}</td>
            <td>{{ number_format($c['betweenness'],2) }}</td>
            <td>{{ number_format($c['closeness'],2) }}</td>
          </tr>
        @empty
          <tr>
            <td colspan="5" class="text-center text-muted py-3">
              Sin datos de centralidad.
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

{{-- resources/views/livewire/partials/metricas-individuales.blade.php --}}
{{-- Este partial se muestra cuando se hace clic en un nodo y recibe un array $estudiante --}}
{{-- $estudiante contiene 'info' (datos básicos) y 'metricas' (métricas detalladas) --}}

<div class="card mt-4" id="panelMetricasIndividuales">
    <div class="card-header">
        Métricas Detalladas de {{ $estudiante['info']['nombre'] ?? 'Estudiante' }}
    </div>
    <div class="card-body">
        @if (!empty($estudiante['metricas']))
            <p><strong>Estatus Sociométrico:</strong> {{ $estudiante['metricas']['estatus_sociometrico'] ?? 'N/A' }}</p>
            <p><strong>Popularidad:</strong> {{ $estudiante['metricas']['popularidad_porcentaje'] ?? 'N/A' }} %</p>
            <hr>
            <p><strong>Preferencias Recibidas:</strong> {{ $estudiante['metricas']['preferencias_recibidas'] ?? 0 }}</p>
            <p><strong>Rechazos Recibidos:</strong> {{ $estudiante['metricas']['rechazos_recibidos'] ?? 0 }}</p>
            <p><strong>Preferencias Emitidas:</strong> {{ $estudiante['metricas']['preferencias_emitidas'] ?? 0 }}</p>
            <p><strong>Rechazos Emitidos:</strong> {{ $estudiante['metricas']['rechazos_emitidos'] ?? 0 }}</p>
            <p><strong>Mutuos Preferencia:</strong> {{ $estudiante['metricas']['mutuos_preferencia'] ?? 0 }}</p>
            <p><strong>Mutuos Rechazo:</strong> {{ $estudiante['metricas']['mutuos_rechazo'] ?? 0 }}</p>

            {{-- Puedes añadir aquí la visualización de quién le eligió/rechazó si pasas esa lista en el backend --}}

        @else
            <p>No hay datos de métricas detalladas para este estudiante.</p>
        @endif
    </div>
</div>
{{-- resources/views/livewire/partials/metricas-grupales.blade.php --}}
{{-- Este partial recibe un array $datos que contiene las métricas grupales (top_estrellas, top_rechazados, aislados_lista, etc.) --}}

<div class="card mt-4">
    <div class="card-header">
        Métricas Grupales
    </div>
    <div class="card-body">
        <h5>Resumen del Grupo:</h5>
        {{-- Asegúrate de que la densidad se pasa y formate correctamente, ej. en el backend --}}
        <p>Densidad de preferencias: {{ $datos['densidad_preferencias'] ?? 'N/A' }} %</p>

        <h6>Top Estrellas:</h6>
        {{-- Verificar que 'top_estrellas' existe y no está vacío --}}
        @if (!empty($datos['top_estrellas']))
            <ul>
                {{-- Usar sintaxis de OBJETO ->nombre y ->total --}}
                @foreach ($datos['top_estrellas'] as $estrella)
                    <li>{{ $estrella->nombre ?? 'Nombre desconocido' }} ({{ $estrella->total ?? 0 }} elecciones)</li>
                @endforeach
            </ul>
        @else
            <p>No hay estrellas identificadas.</p>
        @endif

        <h6>Top Rechazados:</h6>
        {{-- Verificar que 'top_rechazados' existe y no está vacío --}}
        @if (!empty($datos['top_rechazados']))
             <ul>
                 {{-- Usar sintaxis de OBJETO ->nombre y ->total --}}
                 @foreach ($datos['top_rechazados'] as $rechazado)
                     <li>{{ $rechazado->nombre ?? 'Nombre desconocido' }} ({{ $rechazado->total ?? 0 }} rechazos)</li>
                 @endforeach
             </ul>
         @else
             <p>No hay estudiantes con rechazos significativos.</p>
         @endif

        <h6>Aislados (sin preferencias recibidas):</h6>
         {{-- Verificar que 'aislados_lista' existe y no está vacío --}}
         @if (!empty($datos['aislados_lista']))
             <ul>
                 {{-- $aislado es solo el nombre, que es un string simple --}}
                 @foreach ($datos['aislados_lista'] as $aislado)
                     <li>{{ $aislado }}</li>
                 @endforeach
             </ul>
         @else
             <p>No hay estudiantes aislados identificados.</p>
         @endif

         {{-- Añadir aquí la visualización de otras métricas grupales si las implementas --}}

    </div>
</div>
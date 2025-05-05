<div>
    <div class="card shadow-sm rounded-lg overflow-hidden">
        <!-- <div class="card-header bg-white d-flex justify-content-between align-items-center py-3 px-4 border-bottom border-light">
            <h5 class="font-weight-bold m-0">Tests Asignados</h5>

            <div>
                <button class="btn btn-link text-muted p-1" title="Buscar">
                    <i class="fas fa-search"></i>
                </button>
                <button class="btn btn-link text-muted p-1" title="Configuración">
                    <i class="fas fa-cog"></i>
                </button>
            </div>
        </div> -->

        <div class="table-responsive">
            <table class="table mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="py-3 font-weight-medium text-muted">Nombre del Test</th>
                        <th class="py-3 font-weight-medium text-muted">Grupo</th>
                        <th class="py-3 font-weight-medium text-muted">Estado</th>
                        <th class="py-3 font-weight-medium text-muted">Fecha</th>
                        <th class="py-3 font-weight-medium text-muted">Acción</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($asignaciones as $item)
                        <tr class="border-top border-light">
                            <td class="py-3">{{ $item->test->nombre_test }}</td>
                            <td class="py-3">{{ $item->grupo->nombre_grupo }}</td>

                            {{-- ===== Badge de estado ===== --}}
                            <td class="py-3">
                                @php
                                    // estado → clase Bootstrap
                                    $badgeColors = [
                                        'pendiente'    => 'badge-warning',  // amarillo
                                        'en progreso'  => 'badge-info',     // azul claro
                                        'aplicado'     => 'badge-success',  // verde
                                    ];

                                    $estado      = strtolower($item->estado);        // asegura minúsculas
                                    $badgeClass  = $badgeColors[$estado] ?? 'badge-secondary';
                                    $badgeText   = ucfirst($estado);
                                @endphp

                                <span class="badge badge-pill {{ $badgeClass }} px-3 py-2">
                                    {{ $badgeText }}
                                </span>
                            </td>
                            {{-- =========================== --}}

                            <td class="py-3">{{ $item->created_at->format('d/m/Y') }}</td>

                            <td class="py-3">
                                <button wire:click.prevent="seleccionarAsignacion({{ $item->id }})"
                                        class="btn btn-link p-0 text-primary"
                                        data-toggle="modal"
                                        data-target="#detalleTestModal">
                                    <i class="fas fa-chevron-right"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">
                                No hay tests asignados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ========= Modal Detalle Test ========= --}}
    <div class="modal fade" id="detalleTestModal"
         tabindex="-1"
         role="dialog"
         aria-labelledby="detalleTestModalLabel"
         aria-hidden="true"
         wire:ignore.self>
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content rounded-lg">
                <div class="modal-header border-0">
                    <h5 class="modal-title" id="detalleTestModalLabel">
                        {{ $asignacionSeleccionada?->test->nombre_test ?? 'Detalles del Test' }}
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    @if ($asignacionSeleccionada)
                        <div class="bg-light p-4 rounded">
                            <p><strong>Grupo:</strong> {{ $asignacionSeleccionada->grupo->nombre_grupo }}</p>
                            <p><strong>Estado:</strong> {{ ucfirst($asignacionSeleccionada->estado) }}</p>
                            <p><strong>Clave de acceso:</strong> {{ $asignacionSeleccionada->clave_acceso }}</p>
                            <p><strong>Fecha de asignación:</strong> {{ $asignacionSeleccionada->created_at->format('d/m/Y H:i') }}</p>
                            <p><strong>Enlace:</strong>
                                <a href="{{ url('/test/ingresar') }}" target="_blank">
                                    {{ url('/test/ingresar') }}
                                </a>
                            </p>
                        </div>
                    @else
                        <div class="alert alert-warning m-0">
                            No se han encontrado detalles del test seleccionado.
                        </div>
                    @endif
                </div>

                <div class="modal-footer border-0">
                    <button type="button"
                            class="btn btn-secondary"
                            data-dismiss="modal"
                            wire:click="resetModal">
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>
    {{-- ========= Fin modal ========= --}}
</div>

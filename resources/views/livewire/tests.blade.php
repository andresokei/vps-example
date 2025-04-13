<div>
    <div class="row">
        <!-- Card 1: Asignar Tests a Grupos -->
        <div class="col-md-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-primary text-white">
                    <h6 class="m-0 font-weight-bold">Asignar Tests a Grupos</h6>
                </div>
                <div class="card-body">
                    @if (session()->has('success'))
                        <div class="alert alert-success d-flex align-items-center" role="alert">
                            <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
                        </div>
                    @endif

                    <form wire:submit.prevent="asignar">
                        <div class="form-group">
                            <label for="grupo_id" class="font-weight-bold">Selecciona un Grupo:</label>
                            <select wire:model="grupo_id" id="grupo_id" class="form-control" required>
                                <option value="">Seleccione un grupo</option>
                                @foreach ($grupos as $grupo)
                                    <option value="{{ $grupo->id }}">{{ $grupo->nombre_grupo }}</option>
                                @endforeach
                            </select>
                            @error('grupo_id') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="test_id" class="font-weight-bold">Selecciona un Test:</label>
                            <select wire:model="test_id" id="test_id" class="form-control" required>
                                <option value="">Seleccione un test</option>
                                @foreach ($tests as $test)
                                    <option value="{{ $test->id }}">{{ $test->nombre_test }}</option>
                                @endforeach
                            </select>
                            @error('test_id') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <button type="submit" class="btn btn-primary btn-block mt-4">Asignar Test</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Card 2: Tests Asignados -->
        <div class="col-md-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-secondary text-white">
                    <h6 class="m-0 font-weight-bold">Tests Asignados</h6>
                </div>
                <div class="card-body">
                    <ul class="list-group">
                    @forelse ($asignaciones as $asignacion)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span>
                                <strong>{{ $asignacion->test->nombre_test }}</strong> - Grupo: <strong>{{ $asignacion->grupo->nombre_grupo }}</strong>
                            </span>
                            <span class="badge {{ $asignacion->estado == 'realizado' ? 'badge-success' : 'badge-warning' }}">
                                {{ ucfirst($asignacion->estado) }}
                            </span>
                            <a href="#" wire:click.prevent="seleccionarAsignacion({{ $asignacion->id }})"
                            data-toggle="modal" data-target="#detalleTestModal" 
                            class="btn btn-sm btn-outline-primary border-0 text-primary">
                                <i class="fas fa-eye mr-1"></i> Ver detalles
                            </a>
                        </li>
                    @empty
                        <li class="list-group-item text-muted">No hay tests asignados aún.</li>
                    @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Detalles del Test -->
    <div class="modal fade" id="detalleTestModal" tabindex="-1" role="dialog" aria-labelledby="detalleTestModalLabel" aria-hidden="true" wire:ignore.self>
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="detalleTestModalLabel">
                        @if($asignacionSeleccionada)
                            {{ $asignacionSeleccionada->test->nombre_test }}
                        @else
                            Detalles del Test
                        @endif
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    @if($asignacionSeleccionada)
                        <div class="card">
                            <div class="card-body">
                                <p><strong>Grupo:</strong> {{ $asignacionSeleccionada->grupo->nombre_grupo }}</p>
                                <p><strong>Estado:</strong> {{ ucfirst($asignacionSeleccionada->estado) }}</p>
                                <p><strong>Clave de acceso:</strong> {{ $asignacionSeleccionada->clave_acceso }}</p>
                                <p><strong>Fecha de asignación:</strong> {{ $asignacionSeleccionada->created_at->format('d/m/Y H:i') }}</p>
                                <p><strong>Enlace de acceso:</strong> 
                                    <a href="{{ url('/test/ingresar') }}" target="_blank">
                                        {{ url('/test/ingresar') }}
                                    </a>
                                </p>
                            </div>
                        </div>
                    @else
                        <div class="alert alert-warning">
                            No se han encontrado detalles del test seleccionado.
                        </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal" wire:click="resetModal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Cuando el modal se cierre, resetear la asignación seleccionada
        document.addEventListener('DOMContentLoaded', function() {
            $('#detalleTestModal').on('hidden.bs.modal', function (e) {
                Livewire.dispatch('resetAsignacion');
            });
        });
    </script>
</div>
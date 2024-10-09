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
                    </div>
                    
                    <div class="form-group">
                        <label for="test_id" class="font-weight-bold">Selecciona un Test:</label>
                        <select wire:model="test_id" id="test_id" class="form-control" required>
                            <option value="">Seleccione un test</option>
                            @foreach ($tests as $test)
                                <option value="{{ $test->id }}">{{ $test->nombre_test }}</option>
                            @endforeach
                        </select>
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
                            <a href="#" wire:click.prevent="verDetalles({{ $asignacion->id }})" class="btn btn-link btn-sm">Ver detalles</a>
                        </li>
                    @empty
                        <li class="list-group-item text-muted">No hay tests asignados aún.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Modal para detalles -->
<div class="modal fade" id="detalleTestModal" tabindex="-1" role="dialog" aria-labelledby="detalleTestModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="detalleTestModalLabel">{{ $testSeleccionado ? $testSeleccionado->test->nombre_test : 'Detalles del Test' }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Información del test seleccionado -->
                @if ($testSeleccionado)
                    <p><strong>Grupo:</strong> {{ $testSeleccionado->grupo->nombre_grupo }}</p>
                    <p><strong>Estado:</strong> {{ ucfirst($testSeleccionado->estado) }}</p>
                    <p><strong>Clave de acceso:</strong> {{ $testSeleccionado->clave_acceso }}</p>
                    <p><strong>Enlace de acceso:</strong> <a href="http://127.0.0.1:8000/test/ingresar">http://127.0.0.1:8000/test/ingresar</a> </p>                    <!-- Añadir más detalles si es necesario -->
                @else
                    <p>No se ha seleccionado ningún test.</p>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
</div>

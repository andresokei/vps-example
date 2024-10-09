<div>
    <div class="card shadow mb-4 rounded-lg">
        <div class="card-header py-3 bg-primary text-white">
            <h6 class="m-0 font-weight-bold">Gestiona tus Grupos</h6>
        </div>

        <div class="card-body">
            <!-- Mensaje de éxito o error -->
            @if (session()->has('message'))
                <div class="alert alert-success d-flex align-items-center" role="alert">
                    <i class="fas fa-check-circle mr-2"></i> {{ session('message') }}
                </div>
            @endif

            <!-- Formulario para crear un nuevo grupo -->
            <form wire:submit.prevent="createGroup" class="form-inline d-flex justify-content-center mb-4">
                <div class="input-group" style="max-width: 400px; width: 100%;">
                    <input type="text" wire:model="groupName" placeholder="Nombre del grupo" class="form-control" aria-label="Nombre del grupo">
                    <div class="input-group-append">
                        <button class="btn btn-primary" type="submit">Crear Grupo</button>
                    </div>
                </div>
            </form>

            <!-- Lista de grupos -->
            <ul class="list-group">
                @foreach ($groups as $group)
                    <li class="list-group-item d-flex justify-content-between align-items-center shadow-sm rounded mb-2">
                        <span class="font-weight-bold">{{ $group->nombre_grupo }}</span>
                        <div>
                            <button class="btn btn-outline-primary btn-sm" wire:click.prevent="openModal({{ $group->id }})">
                                Añadir alumnos
                            </button>
                            <button class="btn btn-danger btn-sm ml-2" wire:click.prevent="deleteGroup({{ $group->id }})">
                                Eliminar
                            </button>
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>

    <!-- Modal para añadir alumnos -->
    <div class="modal fade" id="addStudentsModal" tabindex="-1" aria-labelledby="addStudentsModalLabel" aria-hidden="true" wire:ignore.self>
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addStudentsModalLabel">Añadir Alumnos al Grupo</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <h6>Subir alumnos mediante archivo CSV:</h6>
                    <div class="input-group mb-3">
                        <input type="file" wire:model="csvFile" class="form-control" accept=".csv">
                        <button class="btn btn-primary ml-2" wire:click="uploadCSV">Subir CSV</button>
                    </div>
                    @error('csvFile') <span class="text-danger">{{ $message }}</span> @enderror

                    <h6 class="mt-4">Añadir alumnos manualmente (separados por comas):</h6>
                    <form wire:submit.prevent="addStudentFromList">
                        <div class="input-group mb-3">
                            <textarea wire:model="studentNames" class="form-control" placeholder="Escribe los nombres de los alumnos, separados por comas"></textarea>
                            <button class="btn btn-primary ml-2" type="submit">Añadir</button>
                        </div>
                        @error('studentNames') <span class="text-danger">{{ $message }}</span> @enderror
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    window.addEventListener('openModal', () => {
        $('#addStudentsModal').modal('show');
    });

    window.addEventListener('closeModal', () => {
        $('#addStudentsModal').modal('hide');
    });
</script>

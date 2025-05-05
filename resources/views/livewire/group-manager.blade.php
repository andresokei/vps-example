<div>
    <!-- Mensaje de éxito o error -->
    @if (session()->has('message'))
        <div class="alert alert-success d-flex align-items-center" role="alert">
            <i class="fas fa-check-circle mr-2"></i> {{ session('message') }}
        </div>
    @endif

    <!-- Formulario para crear un nuevo grupo -->
    <form wire:submit.prevent="createGroup" class="mb-4">
        <div class="input-group" style="max-width: 400px; width: 100%;">
            <input type="text" wire:model="groupName" placeholder="Nuevo grupo" class="form-control rounded-left" aria-label="Nombre del grupo">
            <div class="input-group-append">
                <button class="btn btn-primary rounded-right" type="submit">
                    <i class="fas fa-plus"></i>
                </button>
            </div>
        </div>
    </form>

    <!-- Lista de grupos -->
    <ul class="list-unstyled">
        @foreach ($groups as $group)
            <li class="d-flex justify-content-between align-items-center py-2 border-bottom">
                <span class="font-weight-bold">{{ $group->nombre_grupo }}</span>
                <div class="btn-group btn-group-sm">
                    <button class="btn btn-outline-secondary" wire:click.prevent="openModal({{ $group->id }})">
                        <i class="fas fa-user-plus"></i>
                    </button>
                    <button class="btn btn-outline-danger" wire:click.prevent="deleteGroup({{ $group->id }})">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </li>
        @endforeach
    </ul>

    <!-- Modal para añadir alumnos -->
    <!-- Modal para añadir alumnos -->
<div class="modal fade" id="addStudentsModal" tabindex="-1" aria-labelledby="addStudentsModalLabel" aria-hidden="true" wire:ignore.self>
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow rounded-lg">
            <div class="modal-header bg-light">
                <h5 class="modal-title font-weight-bold" id="addStudentsModalLabel">Añadir Alumnos</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4">
                <!-- Pestañas para alternar entre métodos -->
                <ul class="nav nav-tabs mb-4">
                    <li class="nav-item">
                        <a class="nav-link" id="csv-tab" data-toggle="tab" href="#csv" role="tab">Importar CSV</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" id="manual-tab" data-toggle="tab" href="#manual" role="tab">Añadir Manualmente</a>
                    </li>
                </ul>
                
                <div class="tab-content">
                    <!-- Pestaña CSV -->
                    <div class="tab-pane fade" id="csv" role="tabpanel">
                        <div class="custom-file mb-3">
                            <input type="file" class="custom-file-input" id="csvFileInput" wire:model="csvFile">
                            <label class="custom-file-label" for="csvFileInput">Seleccionar archivo</label>
                        </div>
                        @error('csvFile') <div class="text-danger mb-3 small"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</div> @enderror
                        
                        <button class="btn btn-primary btn-block" wire:click="uploadCSV" wire:loading.attr="disabled">
                            <i class="fas fa-upload mr-1"></i> Importar Alumnos
                            <span wire:loading wire:target="uploadCSV" class="spinner-border spinner-border-sm ml-1" role="status"></span>
                        </button>
                        
                        <div class="text-center mt-2">
                            <small class="text-muted">Formato: un nombre por línea</small>
                        </div>
                    </div>
                    
                    <!-- Pestaña Manual -->
                    <div class="tab-pane fade show active" id="manual" role="tabpanel">
                        <form wire:submit.prevent="addStudentFromList">
                            <div class="form-group">
                                <label for="studentNames">Nombres separados por comas:</label>
                                <textarea wire:model="studentNames" class="form-control" id="studentNames" rows="3" placeholder="Ej: Juan Pérez, María García"></textarea>
                                <small class="form-text text-muted">Utiliza comas para separar cada nombre</small>
                                @error('studentNames') <div class="text-danger small"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</div> @enderror
                            </div>
                            
                            <button type="submit" class="btn btn-primary btn-block" wire:loading.attr="disabled">
                                <i class="fas fa-plus mr-1"></i> Añadir Alumnos
                                <span wire:loading wire:target="addStudentFromList" class="spinner-border spinner-border-sm ml-1" role="status"></span>
                            </button>
                        </form>
                    </div>
                </div>
                
                <!-- Lista de alumnos en el grupo -->
                <div class="mt-4 pt-3 border-top">
                    <h6 class="d-flex justify-content-between align-items-center mb-3">
                        <span>Alumnos en este grupo</span>
                        <span class="badge badge-primary badge-pill" wire:loading.class="d-none" wire:target="loadGroupStudents">
                            {{ count($groupStudents ?? []) }}
                        </span>
                        <span wire:loading wire:target="loadGroupStudents" class="spinner-border spinner-border-sm" role="status"></span>
                    </h6>
                    
                    @if(!empty($groupStudents) && count($groupStudents) > 0)
                        <div class="list-group list-group-flush" style="max-height: 250px; overflow-y: auto;">
                            @foreach($groupStudents as $student)
                                <div class="list-group-item list-group-item-action d-flex justify-content-between align-items-center py-2 px-3">
                                    <div>
                                        <div class="font-weight-medium">{{ $student->nombre }}</div>
                                    </div>
                                    <button class="btn btn-sm btn-outline-danger" wire:click="removeStudentFromGroup({{ $student->id }})" wire:loading.attr="disabled">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-3 text-muted">
                            <i class="fas fa-users fa-2x mb-2 d-block"></i>
                            <p class="mb-0">No hay alumnos en este grupo</p>
                        </div>
                    @endif
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" wire:click="closeModal">Cerrar</button>
            </div>
        </div>
    </div>
</div>


<!-- Modal para confirmar eliminación de grupo -->
<div class="modal fade" id="deleteGroupModal" tabindex="-1" aria-labelledby="deleteGroupModalLabel" aria-hidden="true" wire:ignore.self>
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow rounded-lg">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title font-weight-bold" id="deleteGroupModalLabel">Confirmar eliminación</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4 text-center">
                <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
                <p class="mb-1">¿Estás seguro de que quieres eliminar este grupo?</p>
                <p class="text-danger mb-0"><strong>Esta acción no se puede deshacer.</strong></p>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i> Cancelar
                </button>
                <button type="button" class="btn btn-danger" wire:click="confirmDeleteGroup" wire:loading.attr="disabled">
                    <i class="fas fa-trash mr-1"></i> Eliminar
                    <span wire:loading wire:target="confirmDeleteGroup" class="spinner-border spinner-border-sm ml-1" role="status"></span>
                </button>
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
<script>
    document.addEventListener('livewire:load', function () {
        // Eventos para el modal existente de alumnos
        window.addEventListener('openModal', event => {
            $('#addStudentsModal').modal('show');
        });

        window.addEventListener('closeModal', event => {
            $('#addStudentsModal').modal('hide');
        });
        
        // Nuevos eventos para el modal de confirmación de eliminación
        window.addEventListener('openDeleteModal', event => {
            $('#deleteGroupModal').modal('show');
        });

        window.addEventListener('closeDeleteModal', event => {
            $('#deleteGroupModal').modal('hide');
        });
        
        // Para mostrar el nombre del archivo seleccionado
        $(document).on('change', '.custom-file-input', function () {
            let fileName = $(this).val().split('\\').pop();
            $(this).next('.custom-file-label').html(fileName || 'Seleccionar archivo');
        });
        
        // Mantener la pestaña activa después de una actualización
        let activeTab = sessionStorage.getItem('activeTab');
        if (activeTab) {
            $('#' + activeTab).tab('show');
        }
        
        $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
            sessionStorage.setItem('activeTab', $(e.target).attr('id'));
        });
    });
</script>
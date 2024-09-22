<div>
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Gestiona tus Grupos</h6>
            <p class="text-muted">Crea y organiza tus grupos de estudiantes aquí.</p>
        </div>

        <div class="card-body">
            <!-- Mensaje de éxito o error -->
            @if (session()->has('message'))
                <div class="alert alert-success">
                    {{ session('message') }}
                </div>
            @elseif (session()->has('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif

            <!-- Formulario para crear un nuevo grupo -->
            <div class="text-center mb-4">
                <form wire:submit.prevent="createGroup" class="form-inline d-flex justify-content-center">
                    <div class="input-group" style="max-width: 400px; width: 100%;">
                        <input type="text" wire:model="groupName" placeholder="Nombre del grupo"
                            class="form-control" aria-label="Nombre del grupo" aria-describedby="button-addon"
                            style="border: 1px solid #ccc; padding: 10px; font-size: 14px; border-radius: 5px 0 0 5px; height: 42px;">
                        <div class="input-group-append">
                            <button class="btn btn-primary" type="submit" id="button-addon"
                                style="padding: 10px 20px; font-size: 14px; border-radius: 0 5px 5px 0; height: 42px;">
                                Crear Grupo
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Separador visual -->
            <hr>

            <!-- Lista de grupos -->
            <ul class="list-group">
                @foreach($groups as $group)
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span class="font-weight-bold">{{ $group->nombre_grupo }}</span>
                        <button class="btn btn-link text-primary" wire:click.prevent="selectGroup({{ $group->id }})">
                            <i class="fas fa-user-plus"></i> Añadir alumnos
                        </button>
                    </li>

                    <!-- Mostrar el formulario si se ha seleccionado el grupo -->
                    @if($selectedGroup === $group->id)
                        <div class="expandable mt-3 p-3 border rounded bg-light active">
                            <h5>Añadir alumnos al grupo "{{ $group->nombre_grupo }}"</h5>
                            <form wire:submit.prevent="addStudentToGroup" class="mt-3">
                                <div class="input-group mb-3">
                                    <select wire:model="studentId" class="form-control">
                                        <option value="">Selecciona un estudiante</option>
                                        @foreach($students as $student)
                                            <option value="{{ $student->id }}">{{ $student->name }}</option>
                                        @endforeach
                                    </select>
                                    <button class="btn btn-primary ml-2" type="submit">
                                        Añadir
                                    </button>
                                </div>
                            </form>
                        </div>
                    @endif
                @endforeach
            </ul>
        </div>
    </div>
</div>


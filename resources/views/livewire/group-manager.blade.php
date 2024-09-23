<div>
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Gestiona tus Grupos</h6>
        </div>

        <div class="card-body">
            <!-- Mensaje de éxito o error -->
            @if (session()->has('message'))
                <div class="alert alert-success">
                    {{ session('message') }}
                </div>
            @endif

            <!-- Formulario para crear un nuevo grupo -->
            <form wire:submit.prevent="createGroup" class="form-inline d-flex justify-content-center mb-4">
                <div class="input-group" style="max-width: 400px; width: 100%;">
                    <input type="text" wire:model="groupName" placeholder="Nombre del grupo" class="form-control">
                    <div class="input-group-append">
                        <button class="btn btn-primary" type="submit">Crear Grupo</button>
                    </div>
                </div>
            </form>

            <!-- Lista de grupos -->
            <ul class="list-group">
                @foreach ($groups as $group)
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span>{{ $group->nombre_grupo }}</span>
                        <div>
                            <button class="btn btn-link" wire:click.prevent="selectGroup({{ $group->id }})">
                                {{ $selectedGroup === $group->id ? 'Ocultar' : 'Añadir alumnos' }}
                            </button>
                            <button class="btn btn-danger btn-sm" wire:click.prevent="deleteGroup({{ $group->id }})">
                                Eliminar
                            </button>
                        </div>
                    </li>

                    @if ($selectedGroup === $group->id)
                        <div class="expandable mt-3 p-3 border rounded bg-light">
                            <h5>Añadir alumnos al grupo "{{ $group->nombre_grupo }}"</h5>

                            <!-- Lista de alumnos ya añadidos al grupo -->
                            <h6 class="mt-4">Alumnos en el grupo:</h6>
                            @if(count($group->students) > 0)
                                <ul class="list-group mb-3">
                                    @foreach ($group->students as $student)
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            {{ $student->nombre }}
                                            <button class="btn btn-danger btn-sm" wire:click.prevent="deleteStudentFromGroup({{ $student->id }}, {{ $group->id }})">
                                                Eliminar
                                            </button>
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <p>No hay alumnos en este grupo aún.</p>
                            @endif

                            <!-- Formulario para añadir nuevos alumnos -->
                            <form wire:submit.prevent="addStudentToGroup" class="mt-3">
                                <div class="input-group mb-3">
                                    <input type="text" wire:model="studentName" placeholder="Escribe el nombre del alumno" class="form-control">
                                    <button class="btn btn-primary ml-2" type="submit">Añadir</button>
                                </div>
                            </form>
                        </div>
                    @endif
                @endforeach
            </ul>
        </div>
    </div>
</div>

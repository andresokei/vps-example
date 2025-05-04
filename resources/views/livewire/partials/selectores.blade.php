{{-- ───────────── SELECTORES ───────────── --}}
    <div class="modern-card">
        <div class="card-body">
            <div class="row">
                {{-- Grupo --}}
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="grupoSeleccionado" class="form-label-modern">
                            <i class="fas fa-users form-label-icon mr-1"></i> Seleccione un grupo:
                        </label>
                        <select id="grupoSeleccionado"
                                class="form-control-modern"
                                wire:model.live="grupoSeleccionado">
                            <option value="">-- Seleccione un grupo --</option>
                            @foreach ($grupos as $grupo)
                                <option value="{{ $grupo->id }}">{{ $grupo->nombre_grupo }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Asignación de test --}}
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="asignacionSeleccionada" class="form-label-modern">
                            <i class="fas fa-file-alt form-label-icon mr-1"></i> Seleccione la asignación de test:
                        </label>
                        <select id="asignacionSeleccionada"
                                class="form-control-modern"
                                wire:model.live="asignacionTestId"
                                @disabled(!$asignaciones || $asignaciones->isEmpty())>
                            <option value="">-- Asignación --</option>
                            @foreach ($asignaciones as $asignacion)
                                <option value="{{ $asignacion->id }}">
                                    {{ $asignacion->created_at->format('d/m/Y · H:i') }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            {{-- ───────────── BOTÓN ───────────── --}}
            <div class="row mt-4">
                <div class="col-12 text-center">
                    <button wire:click="procesarAnalisis"
                            class="btn-modern-primary">
                        <i class="fas fa-search mr-1"></i> Procesar Análisis
                    </button>
                </div>
            </div>
        </div>
    </div>
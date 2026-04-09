<div class="modern-card">
    <div class="card-body">
        <div class="row">
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

            <div class="col-md-6">
                <div class="form-group">
                    <label for="asignacionSeleccionada" class="form-label-modern">
                        <i class="fas fa-file-alt form-label-icon mr-1"></i> Seleccione la asignacion de test:
                    </label>
                    <select id="asignacionSeleccionada"
                            class="form-control-modern"
                            wire:model.live="asignacionTestId"
                            @disabled(!$asignaciones || (is_object($asignaciones) && $asignaciones->isEmpty()))>
                        <option value="">-- Asignacion --</option>
                        @foreach ($asignaciones as $asignacion)
                            <option value="{{ $asignacion->id }}">
                                {{ optional($asignacion->test)->nombre_test ?: 'Test' }}
                                · {{ $asignacion->created_at->format('d/m/Y H:i') }}
                                · {{ $asignacion->respuestas_count ?? 0 }} respuestas
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-12 text-center">
                <button wire:click="procesarAnalisis"
                        wire:loading.attr="disabled"
                        wire:target="procesarAnalisis"
                        @disabled(!$grupoSeleccionado || !$asignacionTestId)
                        class="btn-modern-primary">
                    <span wire:loading.remove wire:target="procesarAnalisis">
                        <i class="fas fa-search mr-1"></i> Procesar Analisis
                    </span>
                    <span wire:loading wire:target="procesarAnalisis">
                        <i class="fas fa-spinner fa-spin mr-1"></i> Procesando...
                    </span>
                </button>
            </div>
        </div>
    </div>
</div>

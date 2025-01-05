<div>
    <!-- Formulario de selección -->
    <div class="form-group mb-3">
        <label for="grupoSeleccionado"><i class="fas fa-users"></i> Seleccione un grupo:</label>
        <select id="grupoSeleccionado" class="form-control" wire:model="grupoSeleccionado">
            <option value="">-- Seleccione un grupo --</option>
            @foreach($grupos as $grupo)
                <option value="{{ $grupo->id }}">{{ $grupo->nombre_grupo }}</option>
            @endforeach
        </select>
    </div>

    <div class="form-group mb-3">
        <label for="tipoAnalisis"><i class="fas fa-chart-line"></i> Seleccione el tipo de análisis:</label>
        <select id="tipoAnalisis" class="form-control" wire:model="tipoAnalisis">
            <option value="">-- Seleccione un análisis --</option>
            <option value="sociograma">Sociograma</option>
            <!-- Añadir más opciones si es necesario -->
        </select>
    </div>

    <button class="btn btn-primary" wire:click="procesarAnalisis">Procesar Análisis</button>

    <!-- Mostrar resultado -->
    @if($resultadoAnalisis)
        <div class="alert alert-info mt-3">
            {{ $resultadoAnalisis }}
        </div>

        <!-- Mostrar el componente Sociograma si se selecciona -->
    @if($tipoAnalisis === 'sociograma' && $grupoSeleccionado)
    <livewire:sociograma :asignacionTestId="1" />

    @endif
    
    @endif
</div>

@extends('layouts.custom')

@section('content')
<div>
    <!-- Título -->
    <h2>Seleccione un grupo y tipo de análisis</h2>

    <!-- Selección de grupo -->
    <div class="form-group">
        <label for="grupoSeleccionado">Seleccione un grupo:</label>
        <select id="grupoSeleccionado" class="form-control" wire:model="grupoSeleccionado">
            <option value="">-- Seleccione un grupo --</option>
            @foreach($grupos as $grupo)
                <option value="{{ $grupo->id }}">{{ $grupo->nombre_grupo }}</option>
            @endforeach
        </select>
    </div>

    <!-- Selección de tipo de análisis -->
    <div class="form-group">
        <label for="tipoAnalisis">Seleccione el tipo de análisis:</label>
        <select id="tipoAnalisis" class="form-control" wire:model="tipoAnalisis">
            <option value="">-- Seleccione un análisis --</option>
            <option value="sociograma">Sociograma</option>
            <!-- Puedes añadir más opciones aquí -->
        </select>
    </div>

    <!-- Div para el análisis -->
    <div id="analisis">
        @if($tipoAnalisis == 'sociograma' && $jsonData)
            <h3>Sociograma</h3>
            <div id="sociograma" style="width: 100%; height: 600px;"></div>
        @else
            <p>Seleccione un grupo y un tipo de análisis para ver los resultados.</p>
        @endif
    </div>
</div>

@push('scripts')
<script type="text/javascript">
    document.addEventListener('livewire:load', function() {
        Livewire.on('actualizarSociograma', (data) => {
            var container = document.getElementById('sociograma');
            var graphData = data;

            var nodes = new vis.DataSet(graphData.nodes);
            var edges = new vis.DataSet(graphData.links.map(function(link) {
                return {
                    from: link.source,
                    to: link.target,
                    arrows: 'to',
                    color: {
                        color: link.tipo_relacion === 'preferido' ? 'green' : 'red'
                    },
                    width: link.intensidad
                };
            }));

            var options = {
                nodes: {
                    shape: 'circle',
                    size: 20,
                    font: {
                        size: 14
                    }
                },
                edges: {
                    smooth: {
                        type: 'continuous'
                    }
                },
                physics: {
                    enabled: true
                }
            };

            var network = new vis.Network(container, { nodes: nodes, edges: edges }, options);
        });
    });
</script>
@endpush
@endsection

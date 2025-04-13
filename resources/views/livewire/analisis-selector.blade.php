<div>
    <!-- Título -->
    <h2 class="h3 mb-4 text-gray-800">
        <i class="fas fa-chart-bar mr-2"></i>Análisis de Grupos
    </h2>

    <div class="row mb-4">
        <!-- Selección de grupo -->
        <div class="col-md-6">
            <div class="form-group">
                <label for="grupoSeleccionado">
                    <i class="fas fa-users mr-1"></i> Seleccione un grupo:
                </label>
                <select id="grupoSeleccionado" class="form-control" wire:model.live="grupoSeleccionado">
                    <option value="">-- Seleccione un grupo --</option>
                    @foreach($grupos as $grupo)
                        <option value="{{ $grupo->id }}">{{ $grupo->nombre_grupo }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- Selección de tipo de análisis -->
        <div class="col-md-6">
            <div class="form-group">
                <label for="tipoAnalisis">
                    <i class="fas fa-chart-line mr-1"></i> Seleccione el tipo de análisis:
                </label>
                <select id="tipoAnalisis" class="form-control" wire:model="tipoAnalisis">
                    <option value="">-- Seleccione un análisis --</option>
                    <option value="sociograma">Sociograma</option>
                    <option value="recuento_preferencias">Recuento de Preferencias</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Botón para procesar análisis -->
    <div class="row mb-4">
        <div class="col-12 text-center">
            <button wire:click="procesarAnalisis" class="btn btn-primary px-4 py-2">
                <i class="fas fa-search mr-1"></i> Procesar Análisis
            </button>
        </div>
    </div>

    <!-- Mensaje de resultados -->
@if(strpos($resultadoAnalisis, 'No hay respuestas') !== false)
    <div class="alert alert-warning mb-4">
        <i class="fas fa-exclamation-triangle mr-2"></i>
        {{ $resultadoAnalisis }}
        <hr>
        <small>Para solucionar este problema, asegúrese de que los estudiantes hayan completado el test asociado a este grupo.</small>
    </div>
@elseif($resultadoAnalisis)
    <div class="alert alert-info mb-4">
        {{ $resultadoAnalisis }}
    </div>
@endif

    <!-- Contenedor para mostrar el análisis -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                @if($tipoAnalisis == 'sociograma')
                    Sociograma
                @elseif($tipoAnalisis == 'recuento_preferencias')
                    Recuento de Preferencias
                @else
                    Resultados del Análisis
                @endif
            </h6>
        </div>
        <div class="card-body">
            @if($tipoAnalisis == 'sociograma' && $jsonData)
                <!-- Visualización del sociograma -->
                <div wire:ignore>
                    <div id="sociograma" style="width: 100%; height: 600px;"></div>
                </div>
            @elseif($tipoAnalisis == 'recuento_preferencias')
                <!-- Visualización del recuento de preferencias -->
                <div wire:ignore>
                    <canvas id="graficoPreferencias" style="width: 100%; height: 400px;"></canvas>
                </div>
                <p class="mt-3 text-muted small">Este gráfico muestra la cantidad de veces que cada estudiante fue elegido como preferido.</p>
            @else
                <p class="text-center py-5 text-muted">
                    <i class="fas fa-chart-bar fa-3x mb-3 d-block"></i>
                    Seleccione un grupo y un tipo de análisis, luego haga clic en "Procesar Análisis".
                </p>
            @endif
        </div>
    </div>

    @push('scripts')
    <script>
        // Variables globales para los gráficos
        let preferenciasChart = null;
        let sociogramaNetwork = null;
        
        // Función para actualizar el gráfico con datos proporcionados
        function actualizarGraficoConDatos(labels, data) {
            console.log("Actualizando gráfico con datos proporcionados");
            console.log("Labels proporcionados:", labels);
            console.log("Data proporcionados:", data);
            
            // Agregar un retardo mayor para asegurar que el DOM esté listo
            setTimeout(function() {
                // Obtener el canvas
                const canvas = document.getElementById('graficoPreferencias');
                
                if (!canvas) {
                    console.error("Canvas 'graficoPreferencias' no encontrado! Reintentando...");
                    // Reintentar después de un tiempo adicional
                    setTimeout(function() {
                        const canvasRetry = document.getElementById('graficoPreferencias');
                        if (!canvasRetry) {
                            console.error("Canvas 'graficoPreferencias' no encontrado después de reintento!");
                            return;
                        }
                        renderizarGrafico(canvasRetry, labels, data);
                    }, 500);
                    return;
                }
                
                renderizarGrafico(canvas, labels, data);
            }, 300);
        }

        // Función para renderizar el gráfico una vez que tengamos el canvas
        function renderizarGrafico(canvas, labels, data) {
    // Obtener el contexto 2D
    const ctx = canvas.getContext('2d');
    
    // Destruir el gráfico anterior si existe
    if (preferenciasChart !== null) {
        preferenciasChart.destroy();
    }
    
    // Configuración del gráfico
    const config = {
        type: 'bar',
        data: {
            labels: labels,  // Usar directamente los labels proporcionados
            datasets: [{
                label: 'Veces que fue elegido',
                data: data,  // Usar directamente los data proporcionados
                backgroundColor: 'rgba(54, 162, 235, 0.5)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    max: 10  // Establecer un máximo explícito para la escala Y
                }
            }
        }
    };
    
    try {
        // Crear el nuevo gráfico
        preferenciasChart = new Chart(ctx, config);
        console.log("Gráfico de preferencias creado exitosamente!");
    } catch (error) {
        console.error("Error al crear el gráfico:", error);
    }
}
        
        // Inicializar cuando Livewire esté listo
        document.addEventListener('livewire:init', function() {
            console.log("Livewire inicializado");
            
            // Escuchar evento para actualizar el gráfico de preferencias
            Livewire.on('actualizarGrafico', function(eventData) {
                console.log("EVENTO RECIBIDO:", eventData);
                console.log("TIPO DE DATOS:", typeof eventData);
                console.log("¿ES ARRAY?", Array.isArray(eventData));
                console.log("¿ES OBJETO?", typeof eventData === 'object' && eventData !== null);
                
                if (eventData) {
                    console.log("PROPIEDADES DEL OBJETO:", Object.keys(eventData));
                }
                
                // Extraer los datos con verificación más robusta
                let labels = [];
                let data = [];
                

                // Si eventData es un array, tomar el primer elemento
if (Array.isArray(eventData) && eventData.length > 0) {
    console.log("Datos recibidos como array, tomando primer elemento");
    eventData = eventData[0];
}
                // Comprobar si eventData tiene la estructura esperada
                if (eventData && typeof eventData === 'object') {
                    if (eventData.labels && Array.isArray(eventData.labels)) {
                        labels = eventData.labels;
                        console.log("LABELS ENCONTRADOS:", labels);
                    }
                    
                    if (eventData.data && Array.isArray(eventData.data)) {
                        data = eventData.data;
                        console.log("DATA ENCONTRADOS:", data);
                    }
                }
                
                // Si no hay datos en el evento, usar los del componente
                if (labels.length === 0 || data.length === 0) {
                    console.log("Usando datos del componente");
                    const componentLabels = @json($labels);
                    const componentData = @json($data);
                    
                    if (componentLabels && componentLabels.length > 0) {
                        labels = componentLabels;
                    }
                    
                    if (componentData && componentData.length > 0) {
                        data = componentData;
                    }
                }
                
                // Actualizar el gráfico con los datos que hayamos podido obtener
                actualizarGraficoConDatos(labels, data);
            });
            
            // Escuchar evento para actualizar el sociograma
            Livewire.on('actualizarSociograma', function(data) {
                console.log("Evento actualizarSociograma recibido");
                
                var container = document.getElementById('sociograma');
                
                if (!container) {
                    console.error("Contenedor 'sociograma' no encontrado!");
                    return;
                }
                
                try {
                    // Preparar datos para vis.js
                    var nodes = new vis.DataSet(data.nodes);
                    var edges = new vis.DataSet(data.links.map(function(link) {
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
                    
                    // Configuración de la red
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
                            enabled: true,
                            stabilization: {
                                iterations: 100
                            },
                            barnesHut: {
                                gravitationalConstant: -2000,
                                centralGravity: 0.3,
                                springLength: 95,
                                springConstant: 0.04
                            }
                        }
                    };
                    
                    // Crear la red
                    sociogramaNetwork = new vis.Network(container, { nodes: nodes, edges: edges }, options);
                    console.log("Sociograma creado exitosamente!");
                } catch (error) {
                    console.error("Error al crear el sociograma:", error);
                }
            });
        });
    </script>
    @endpush
</div>
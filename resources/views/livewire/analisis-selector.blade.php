<div>
    {{-- ─────────────  TÍTULO  ───────────── --}}
    <h2 class="h3 mb-4 text-gray-800">
        <i class="fas fa-chart-bar mr-2"></i>
        Análisis de Grupos
    </h2>

    {{-- ─────────────  SELECTORES  ───────────── --}}
    <div class="row mb-4">
        {{-- Grupo --}}
        <div class="col-md-6">
            <div class="form-group">
                <label for="grupoSeleccionado">
                    <i class="fas fa-users mr-1"></i> Seleccione un grupo:
                </label>
                <select id="grupoSeleccionado"
                        class="form-control"
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
                <label for="asignacionSeleccionada">
                    <i class="fas fa-file-alt mr-1"></i> Seleccione la asignación de test:
                </label>
                <select id="asignacionSeleccionada"
                        class="form-control"
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

    {{-- ─────────────  BOTÓN  ───────────── --}}
    <div class="row mb-4">
        <div class="col-12 text-center">
            <button wire:click="procesarAnalisis"
                    class="btn btn-primary px-4 py-2">
                <i class="fas fa-search mr-1"></i> Procesar Análisis
            </button>
        </div>
    </div>

    {{-- ─────────────  MENSAJES  ───────────── --}}
    @if (strpos($resultadoAnalisis, 'No hay respuestas') !== false)
        <div class="alert alert-warning mb-4">
            <i class="fas fa-exclamation-triangle mr-2"></i>
            {{ $resultadoAnalisis }}
            <hr>
            <small>Para solucionar este problema, asegúrese de que los estudiantes hayan completado el test.</small>
        </div>
    @elseif ($resultadoAnalisis)
        <div class="alert alert-info mb-4">{{ $resultadoAnalisis }}</div>
    @endif

    {{-- ─────────────  RESULTADOS  ───────────── --}}
    @if (!empty($analisis))
        @foreach ($analisis as $clave => $datos)
            {{-- Asegúrate de que estos partials existen y contienen el HTML para cada sección --}}
            @include("livewire.partials.$clave", ['datos' => $datos])
        @endforeach
    @endif

    {{-- Contenedor para el Sociograma --}}
    {{-- ¡Asegúrate de definir la altura de este div en tu CSS! Por ejemplo, en un archivo CSS aparte: #sociograma { height: 600px; } --}}
    <div id="sociograma" style="width: 100%; height: 600px;"></div>



    <style>
        .sociograma-tooltip {
            /* Estilos de apariencia (fondo, borde, padding, fuente, etc.) */
            background-color: white;
            border: 1px solid #ccc;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
            padding: 10px;
            font-size: 14px;
            font-family: Arial, sans-serif; /* Ajusta a tu fuente */
            max-width: 250px; /* Controla el ancho */
            white-space: normal; /* Permite saltos de línea */
            word-wrap: break-word; /* Para palabras largas */

            /* Estilos de posicionamiento y comportamiento ESENCIALES */
            position: fixed; /* Posicionamiento fijo en la pantalla */
            z-index: 10000; /* Asegura que esté por encima de otros elementos */
            pointer-events: none; /* Impide que el tooltip interfiera con el ratón en el grafo */
            /* La posición exacta (top, left) se establece dinámicamente con JavaScript */

            /* Asegurarse de que sea visible si alguna regla externa lo oculta */
            visibility: visible !important;
            display: block !important;
        }
    </style>

    {{-- ─────────────  SCRIPTS  ───────────── --}}
    @push('scripts')
    <script>
        // ──────────────────────────────────────────────────────────────────────
        //  VARS GLOBALES
        // ──────────────────────────────────────────────────────────────────────
        let chartPref = null,
            chartRech = null,
            sociogramaNetwork = null;

        // ──────────────────────────────────────────────────────────────────────
        //  CARGAR vis‑network DINÁMICAMENTE
        // ──────────────────────────────────────────────────────────────────────
        function cargarVisJS() {
            return new Promise((ok, fail) => {
                if (window.vis) return ok();
                const css = document.createElement('link');
                css.rel  = 'stylesheet';
                css.href = 'https://unpkg.com/vis-network/dist/vis-network.min.css';
                document.head.appendChild(css);
                const js  = document.createElement('script');
                js.src    = 'https://unpkg.com/vis-network/dist/vis-network.min.js';
                js.onload = ok;
                js.onerror= fail;
                document.head.appendChild(js);
            });
        }

        // ──────────────────────────────────────────────────────────────────────
        //  GRÁFICAS (Chart.js)
        // ──────────────────────────────────────────────────────────────────────
        function renderBar(canvasId, labels, data, esRechazos, intento = 0) {
            const canvas = document.getElementById(canvasId);
            if (!canvas) {
                if (intento < 10) {
                    setTimeout(() => renderBar(canvasId, labels, data, esRechazos, intento+1), 100);
                }
                return console.warn(`⚠️  No se encontró ${canvasId}`);
            }
            const prev = Chart.getChart(canvas);
            if (prev) prev.destroy();
            new Chart(canvas, {
                type: 'bar',
                data: {
                    labels,
                    datasets: [{
                        label: esRechazos ? 'Veces rechazado' : 'Veces elegido',
                        data,
                        backgroundColor: esRechazos ? 'rgba(255,99,132,.5)' : 'rgba(54,162,235,.5)',
                        borderColor:      esRechazos ? 'rgba(255,99,132,1)' : 'rgba(54,162,235,1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: { y: { beginAtZero: true } }
                }
            });
        }

        // ──────────────────────────────────────────────────────────────────────
        //  SOCIOGRAMA (con Tooltip Custom)
        // ──────────────────────────────────────────────────────────────────────
        function renderizarSociograma(payload) {
            const data = Array.isArray(payload) ? payload[0] : payload;
            if (!data || !data.nodes || !data.links) {
                // Asegurarse de destruir red previa y tooltip custom si no hay datos válidos
                if (window.sociogramaNetwork) {
                    window.sociogramaNetwork.destroy();
                    window.sociogramaNetwork = null;
                    document.getElementById('sociogram-legend')?.remove();
                     hideTooltip(); // Ocultar tooltip custom si estaba visible
                }
                const cont = document.getElementById('sociograma');
                if(cont) cont.innerHTML = '<div class="alert alert-info">No hay datos suficientes para mostrar el sociograma.</div>';
                return console.error('❌ Datos sociograma mal formados o vacíos:', payload);
            }

            cargarVisJS().then(() => {
                const cont = document.getElementById('sociograma');
                 if (!cont) {
                     console.warn('⚠️ Contenedor del sociograma no encontrado.');
                     return;
                 }

                // destruir red previa y tooltip custom
                if (window.sociogramaNetwork) {
                    window.sociogramaNetwork.destroy();
                    window.sociogramaNetwork = null;
                    document.getElementById('sociogram-legend')?.remove();
                    hideTooltip(); // Ocultar tooltip custom
                }

                // Map nodes and edges data
                const nodes = new vis.DataSet(data.nodes.map(n => ({
                    id:      n.id,
                    label:   n.label,
                    group:   (n.metricas?.popularidad >= 0.7) ? 'popular' :
                             (n.metricas?.popularidad >= 0.4) ? 'neutral' :
                             (n.metricas?.popularidad >= 0.2) ? 'unpopular' : 'isolated_rejected',
                    value:   1 + (n.metricas?.preferencias_recibidas || 0) + (n.metricas?.rechazos_recibidos || 0),
                    // === IMPORTANTE: ELIMINADA la propiedad 'title' para usar tooltip custom ===
                    metricas: n.metricas // Mantener métricas para el tooltip custom
                })));

                const edges = new vis.DataSet(data.links.map(l => ({
                    from: l.source,
                    to:   l.target,
                    color: {
                        color: l.tipo_relacion === 'preferido' ? '#28a745' : '#dc3545',
                        opacity: 0.6
                    },
                    width: Math.min(Math.max(l.intensidad || 1, 1), 4),
                    arrows: { to: { enabled: true, scaleFactor: 0.7 } },
                    // === IMPORTANTE: ELIMINADA la propiedad 'title' para usar tooltip custom ===
                })));

                // opciones de vis-network
                const options = {
                    nodes: {
                        shape:'dot',
                        size: 15,
                        font:{size:12, face:'Arial', multi: 'html'},
                        scaling:{
                            min:10,
                            max:20,
                            label: {min: 10, max: 12}
                        },
                        borderWidth: 2,
                        shadow: true
                    },
                    edges: {
                        smooth: {
                            enabled: true,
                            type: 'continuous'
                        },
                        shadow: true,
                        color: { inherit: 'from' }, // CORREGIDO
                        dashes: false
                    },
                    physics: {
                        enabled: true,
                        barnesHut: {
                            gravitationalConstant: -4000,
                            centralGravity: 0.4,
                            springLength: 150,
                            springConstant: 0.05,
                            damping: 0.09,
                            avoidOverlap: 1
                        },
                        maxVelocity: 50,
                        minVelocity: 0.1,
                        solver: 'barnesHut',
                        stabilization: {enabled: true, iterations: 2000, updateInterval: 50, fit: true}
                    },
                     layout: {
                        // randomSeed: undefined,
                     },
                    interaction: {
                        hover: true, // ESENCIAL para que funcionen los eventos hoverNode/hoverEdge
                        dragNodes: true,
                        zoomView: true,
                        navigationButtons: true,
                        tooltipDelay: 300 // Este delay afecta a los eventos hover, no al tooltip nativo
                    },
                    groups: {
                        popular: {
                            color: { background: '#4CAF50', border: '#388E3C' }
                        },
                        neutral: {
                            color: { background: '#2196F3', border: '#1976D2' }
                        },
                        unpopular: {
                             color: { background: '#FFC107', border: '#FFA000' }
                        },
                         isolated_rejected: {
                             color: { background: '#F44336', border: '#D32F2F' }
                        }
                    }
                };

                // crear red
                window.sociogramaNetwork = new vis.Network(cont, { nodes, edges }, options);

                // Stop physics after initial stabilization
                window.sociogramaNetwork.once('stabilizationIterationsDone', function() {
                     window.sociogramaNetwork.setOptions( { physics: false, layout: { improvedLayout: false } } );
                     console.log('Physics and Improved Layout stopped after stabilization.');
                });

                // ──────────────────────────────────────────────────────────────────────
                //  TOOLTIP CUSTOM IMPLEMENTATION
                // ──────────────────────────────────────────────────────────────────────
                let customTooltip = null;
                let tooltipTimeout = null;

                function showTooltip(x, y, content) {
                    if (customTooltip) customTooltip.remove();

                    customTooltip = document.createElement('div');
                    customTooltip.className = 'sociograma-tooltip'; // Clase CSS para tus estilos
                    customTooltip.innerHTML = content;

                    customTooltip.style.cssText = `
                        position: fixed;
                        background: white;
                        border-radius: 8px;
                        box-shadow: 0 4px 8px rgba(0,0,0,0.2);
                        padding: 10px;
                        font-size: 14px;
                        pointer-events: none;
                        z-index: 10000;
                        top: ${y + 15}px;
                        left: ${x + 15}px;
                        max-width: 250px;
                        white-space: normal;
                        /* Incluye aquí estilos de apariencia si no están en la clase CSS */
                    `;
                    document.body.appendChild(customTooltip);
                }

                function hideTooltip() {
                    clearTimeout(tooltipTimeout);
                    if (customTooltip) customTooltip.remove();
                    customTooltip = null;
                }

                // Listener para cuando el ratón pasa sobre un NODO
                window.sociogramaNetwork.on('hoverNode', params => {
                    clearTimeout(tooltipTimeout);
                    tooltipTimeout = setTimeout(() => {
                        const nodeId = params.node;
                        const node = nodes.get(nodeId);
                        if (node && node.metricas) {
                             const content = `
                                <strong>${node.label}</strong><br>
                                Pref. recibidas: ${node.metricas?.preferencias_recibidas || 0}<br>
                                Rech. recibidos: ${node.metricas?.rechazos_recibidos || 0}<br>
                                Popularidad: ${Math.round((node.metricas?.popularidad || 0) * 100)}%
                             `;
                             showTooltip(params.event.clientX, params.event.clientY, content);
                        }
                    }, options.interaction.tooltipDelay || 300);
                });

                // Listener para cuando el ratón sale de un NODO
                window.sociogramaNetwork.on('blurNode', hideTooltip);

                // Listener para cuando el ratón pasa sobre una ARISTA
                window.sociogramaNetwork.on('hoverEdge', params => {
                     clearTimeout(tooltipTimeout);
                     tooltipTimeout = setTimeout(() => {
                        const edgeId = params.edge;
                        const edge = edges.get(edgeId);
                        if (edge) {
                            const fromNode = nodes.get(edge.from);
                            const toNode = nodes.get(edge.to);
                            // Verifica el color para determinar si es preferencia o rechazo
                            const relationshipText = edge.color.color === '#28a745' ? 'prefiere' : 'rechaza';
                            const content = `${fromNode.label} ${relationshipText} a ${toNode.label}`;
                            showTooltip(params.event.clientX, params.event.clientY, content);
                        }
                     }, options.interaction.tooltipDelay || 300);
                });

                // Listener para cuando el ratón sale de una ARISTA
                window.sociogramaNetwork.on('blurEdge', hideTooltip);

                // Ocultar tooltip si se destruye la red
                // (Esto es importante si cambias de grupo y se redibuja el sociograma)
                 window.sociogramaNetwork.on('beforeDestroy', hideTooltip);


                // ──────────────────────────────────────────────────────────────────────
                //  LEYENDA
                // ──────────────────────────────────────────────────────────────────────
                const legendId = 'sociogram-legend';
                document.getElementById(legendId)?.remove();
                const legend = document.createElement('div');
                legend.id = legendId;
                legend.className = 'p-2 border rounded bg-light small';
                legend.style.cssText = 'position:absolute; bottom:15px; left:15px; z-index:10;';

                legend.innerHTML = `
                    <div><b>Leyenda</b></div>
                    <div class="mb-1"><span style="background:#28a745; width:12px; height:12px; display:inline-block; border-radius:50%; margin-right:6px;"></span> Relación de Preferencia</div>
                    <div class="mb-2"><span style="background:#dc3545; width:12px; height:12px; display:inline-block; border-radius:50%; margin-right:6px;"></span> Relación de Rechazo</div>

                    <div><b>Popularidad del Nodo:</b></div>
                    <div class="mb-1"><span style="background:#4CAF50; border: 2px solid #388E3C; width:12px; height:12px; display:inline-block; border-radius:50%; margin-right:6px;"></span> Alta (> 70%)</div>
                    <div class="mb-1"><span style="background:#2196F3; border: 2px solid #1976D2; width:12px; height:12px; display:inline-block; border-radius:50%; margin-right:6px;"></span> Media (40% - 70%)</div>
                    <div class="mb-1"><span style="background:#FFC107; border: 2px solid #FFA000; width:12px; height:12px; display:inline-block; border-radius:50%; margin-right:6px;"></span> Baja (20% - 40%)</div>
                    <div><span style="background:#F44336; border: 2px solid #D32F2F; width:12px; height:12px; display:inline-block; border-radius:50%; margin-right:6px;"></span> Muy Baja (< 20%) / Aislado</div>
                `;
                cont.appendChild(legend);
            }).catch(error => {
                console.error('Error loading VisNetwork:', error);
                const cont = document.getElementById('sociograma');
                if(cont) cont.innerHTML = '<div class="alert alert-danger">Error al cargar la librería de visualización.</div>';
            });
        }

        // ──────────────────────────────────────────────────────────────────────
        //  LISTENERS LIVEWIRE
        // ──────────────────────────────────────────────────────────────────────
        document.addEventListener('livewire:init', () => {
            Livewire.on('actualizarGraficoPreferencias', (payload) => {
                console.log('Payload recibido para Preferencias:', payload);
                const chartData = (Array.isArray(payload) && payload.length > 0) ? payload[0] : payload;
                if (chartData && chartData.labels && chartData.data) {
                    renderBar('graficoPreferencias', chartData.labels, chartData.data, false);
                } else {
                    console.warn('⚠️ Payload inválido o vacío para gráfico de preferencias (después de extraer):', chartData);
                    const canvas = document.getElementById('graficoPreferencias');
                    const prev = canvas ? Chart.getChart(canvas) : null;
                    if (prev) prev.destroy();
                }
            });

            Livewire.on('actualizarGraficoRechazos', (payload) => {
                console.log('Payload recibido para Rechazos:', payload);
                const chartData = (Array.isArray(payload) && payload.length > 0) ? payload[0] : payload;
                if (chartData && chartData.labels && chartData.data) {
                    renderBar('graficoRechazos', chartData.labels, chartData.data, true);
                } else {
                    console.warn('⚠️ Payload inválido o vacío para gráfico de rechazos (después de extraer):', chartData);
                    const canvas = document.getElementById('graficoRechazos');
                    const prev = canvas ? Chart.getChart(canvas) : null;
                    if (prev) prev.destroy();
                }
            });

            Livewire.on('actualizarSociograma', payload => {
                console.log('Payload recibido para Sociograma:', payload);
                renderizarSociograma(payload);
            });
        });
    </script>
    @endpush
</div>
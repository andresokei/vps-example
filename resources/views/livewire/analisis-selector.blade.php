<div>
    {{-- ───────────── TÍTULO ───────────── --}}
    <h2 class="modern-title">
        <i class="fas fa-chart-bar modern-title-icon"></i>
        Análisis de Grupos
    </h2>

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


    {{-- ───────────── MENSAJES ───────────── --}}
    @if (strpos($resultadoAnalisis, 'No hay respuestas') !== false)
        <div class="alert alert-warning modern-alert mt-4">
            <i class="fas fa-exclamation-triangle mr-2"></i>
            {{ $resultadoAnalisis }}
            <hr>
            <small>Para solucionar este problema, asegúrese de que los estudiantes hayan completado el test.</small>
        </div>
    @elseif ($resultadoAnalisis)
        <div class="alert alert-info modern-alert mt-4">{{ $resultadoAnalisis }}</div>
    @endif

    {{-- ───────────── RESULTADOS ───────────── --}}
    @if (!empty($analisis))
        <div class="modern-results mt-4">
            @foreach ($analisis as $clave => $datos)
                {{-- Asegúrate de que estos partials existen y contienen el HTML para cada sección --}}
                @include("livewire.partials.$clave", ['datos' => $datos])
            @endforeach
        </div>
    @endif

    <style>
    /* Fuentes (ejemplo con Google Fonts) */
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap');

    body {
        font-family: 'Poppins', sans-serif;
        background-color: #f8f9fa; /* Color de fondo suave */
    }

    /* Título principal */
    .modern-title {
        font-size: 2rem; /* Tamaño de fuente más grande */
        font-weight: 700; /* Negrita */
        color: #343a40; /* Color de texto oscuro */
        margin-bottom: 1.5rem; /* Espaciado inferior */
        border-bottom: 2px solid #007bff; /* Línea decorativa */
        padding-bottom: 0.5rem; /* Espaciado interno inferior */
    }

    .modern-title-icon {
        color: #007bff; /* Color del icono */
    }

    /* Card para agrupar selectores */
    .modern-card {
        background-color: #fff;
        border-radius: 8px; /* Bordes redondeados */
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05); /* Sombra suave */
        padding: 1.5rem; /* Espaciado interno */
        margin-bottom: 1.5rem; /* Espaciado inferior */
    }

    .modern-subtitle {
         font-size: 1.5rem;
         font-weight: 600;
         color: #343a40;
         margin-bottom: 1rem;
    }

    /* Estilo de las etiquetas de formulario */
    .form-label-modern {
        font-weight: 500; /* Seminegrita */
        color: #555; /* Color de texto */
        margin-bottom: 0.5rem;
        display: block; /* Asegura que ocupe su propia línea */
    }

     .form-label-icon {
         color: #007bff; /* Color del icono */
     }


    /* Estilo de los selectores (basado en form-control) */
    .form-control-modern {
        display: block;
        width: 100%;
        padding: 0.75rem 1rem; /* Espaciado interno */
        font-size: 1rem;
        font-weight: 400;
        line-height: 1.5;
        color: #495057;
        background-color: #fff;
        background-clip: padding-box;
        border: 1px solid #ced4da; /* Borde suave */
        border-radius: 0.25rem; /* Bordes ligeramente redondeados */
        transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out; /* Transición suave */
    }

    .form-control-modern:focus {
        border-color: #80bdff; /* Borde al enfocar */
        outline: 0;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25); /* Sombra al enfocar */
    }

    .form-control-modern:disabled {
        background-color: #e9ecef;
        opacity: 1;
    }


    /* Estilo del botón */
    .btn-modern-primary {
        display: inline-block;
        font-weight: 600; /* Seminegrita */
        color: #fff; /* Color de texto */
        background-color: #007bff; /* Color de fondo */
        border: 1px solid #007bff; /* Borde */
        padding: 0.75rem 1.5rem; /* Espaciado interno */
        font-size: 1.1rem; /* Tamaño de fuente */
        border-radius: 0.5rem; /* Bordes más redondeados */
        transition: background-color 0.15s ease-in-out, border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out; /* Transición suave */
        cursor: pointer;
    }

    .btn-modern-primary:hover {
        background-color: #0056b3; /* Color de fondo al pasar el ratón */
        border-color: #004085; /* Color del borde al pasar el ratón */
    }

    .btn-modern-primary:focus {
        outline: 0;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.5); /* Sombra al enfocar */
    }

     .btn-modern-primary:active {
         background-color: #004085;
         border-color: #002752;
         box-shadow: inset 0 3px 5px rgba(0, 0, 0, 0.125);
     }

    /* Estilo de las alertas */
    .modern-alert {
        border-radius: 8px; /* Bordes redondeados */
        padding: 1rem 1.5rem; /* Espaciado interno */
        margin-bottom: 1.5rem;
    }

    /* Contenedor de resultados (si tienes secciones incluidas) */
    .modern-results {
        margin-top: 1.5rem;
    }


    /* Contenedor del sociograma */
    .sociograma-container {
        width: 100%;
        height: 600px; /* Altura definida, asegúrate de que sea adecuada */
        border: 1px solid #ced4da; /* Borde suave */
        border-radius: 8px; /* Bordes redondeados */
        background-color: #fff; /* Fondo blanco para el área del sociograma */
        position: relative; /* ADDED: Make the container a positioned element for absolute children */
    }


    /* Estilo mejorado para el Tooltip Custom */
    .sociograma-tooltip {
        background-color: rgba(0, 0, 0, 0.85); /* Fondo oscuro semi-transparente */
        color: #fff; /* Texto blanco */
        border: none; /* Sin borde */
        border-radius: 4px; /* Bordes ligeramente redondeados */
        box-shadow: 0 2px 5px rgba(0,0,0,0.3); /* Sombra más sutil */
        padding: 8px 12px; /* Espaciado interno */
        font-size: 0.9rem; /* Tamaño de fuente ligeramente más pequeño */
        font-family: 'Poppins', sans-serif; /* Usar la fuente del cuerpo */
        max-width: 200px; /* Ancho máximo */
        white-space: normal;
        word-wrap: break-word;
        position: fixed; /* Keep fixed so it follows the cursor on the screen */
        z-index: 10000;
        pointer-events: none;
        visibility: visible !important;
        display: block !important;
    }

     .sociograma-tooltip strong {
         font-weight: 600; /* Seminegrita para el nombre */
     }

    /* Estilo mejorado para la Leyenda */
    #sociogram-legend {
        position: absolute; /* Keep absolute to be relative to the positioned container */
        bottom: 25px; /* Ajusta la posición */
        left: 25px; /* Ajusta la posición */
        z-index: 10;
        background-color: rgba(255, 255, 255, 0.95); /* Fondo blanco semi-transparente */
        border: 1px solid #ced4da;
        border-radius: 8px;
        padding: 12px; /* Espaciado interno */
        font-size: 0.9rem;
        font-family: 'Poppins', sans-serif;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }

    #sociogram-legend b {
        font-weight: 600;
        display: block; /* Asegura que el título de la sección esté en su propia línea */
        margin-bottom: 5px;
    }

    #sociogram-legend > div {
        display: flex; /* Usar flexbox para alinear el color y el texto */
        align-items: center; /* Centrar verticalmente */
        margin-bottom: 6px; /* Espaciado entre elementos de la leyenda */
    }

     #sociogram-legend span {
         display: inline-block;
         width: 12px;
         height: 12px;
         border-radius: 50%;
         margin-right: 8px; /* Espacio entre el círculo de color y el texto */
         flex-shrink: 0; /* Evita que el círculo se encoja */
     }

</style>


    <style>
        /* Fuentes (ejemplo con Google Fonts) */
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap');

        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fa; /* Color de fondo suave */
        }

        /* Título principal */
        .modern-title {
            font-size: 2rem; /* Tamaño de fuente más grande */
            font-weight: 700; /* Negrita */
            color: #343a40; /* Color de texto oscuro */
            margin-bottom: 1.5rem; /* Espaciado inferior */
            border-bottom: 2px solid #007bff; /* Línea decorativa */
            padding-bottom: 0.5rem; /* Espaciado interno inferior */
        }

        .modern-title-icon {
            color: #007bff; /* Color del icono */
        }

        /* Card para agrupar selectores */
        .modern-card {
            background-color: #fff;
            border-radius: 8px; /* Bordes redondeados */
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05); /* Sombra suave */
            padding: 1.5rem; /* Espaciado interno */
            margin-bottom: 1.5rem; /* Espaciado inferior */
        }

        .modern-subtitle {
             font-size: 1.5rem;
             font-weight: 600;
             color: #343a40;
             margin-bottom: 1rem;
        }

        /* Estilo de las etiquetas de formulario */
        .form-label-modern {
            font-weight: 500; /* Seminegrita */
            color: #555; /* Color de texto */
            margin-bottom: 0.5rem;
            display: block; /* Asegura que ocupe su propia línea */
        }

         .form-label-icon {
             color: #007bff; /* Color del icono */
         }


        /* Estilo de los selectores (basado en form-control) */
        .form-control-modern {
            display: block;
            width: 100%;
            padding: 0.75rem 1rem; /* Espaciado interno */
            font-size: 1rem;
            font-weight: 400;
            line-height: 1.5;
            color: #495057;
            background-color: #fff;
            background-clip: padding-box;
            border: 1px solid #ced4da; /* Borde suave */
            border-radius: 0.25rem; /* Bordes ligeramente redondeados */
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out; /* Transición suave */
        }

        .form-control-modern:focus {
            border-color: #80bdff; /* Borde al enfocar */
            outline: 0;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25); /* Sombra al enfocar */
        }

        .form-control-modern:disabled {
            background-color: #e9ecef;
            opacity: 1;
        }


        /* Estilo del botón */
        .btn-modern-primary {
            display: inline-block;
            font-weight: 600; /* Seminegrita */
            color: #fff; /* Color de texto */
            background-color: #007bff; /* Color de fondo */
            border: 1px solid #007bff; /* Borde */
            padding: 0.75rem 1.5rem; /* Espaciado interno */
            font-size: 1.1rem; /* Tamaño de fuente */
            border-radius: 0.5rem; /* Bordes más redondeados */
            transition: background-color 0.15s ease-in-out, border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out; /* Transición suave */
            cursor: pointer;
        }

        .btn-modern-primary:hover {
            background-color: #0056b3; /* Color de fondo al pasar el ratón */
            border-color: #004085; /* Color del borde al pasar el ratón */
        }

        .btn-modern-primary:focus {
            outline: 0;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.5); /* Sombra al enfocar */
        }

         .btn-modern-primary:active {
             background-color: #004085;
             border-color: #002752;
             box-shadow: inset 0 3px 5px rgba(0, 0, 0, 0.125);
         }

        /* Estilo de las alertas */
        .modern-alert {
            border-radius: 8px; /* Bordes redondeados */
            padding: 1rem 1.5rem; /* Espaciado interno */
            margin-bottom: 1.5rem;
        }

        /* Contenedor de resultados (si tienes secciones incluidas) */
        .modern-results {
            margin-top: 1.5rem;
        }


        /* Contenedor del sociograma */
        .sociograma-container {
            width: 100%;
            height: 600px; /* Altura definida, asegúrate de que sea adecuada */
            border: 1px solid #ced4da; /* Borde suave */
            border-radius: 8px; /* Bordes redondeados */
            background-color: #fff; /* Fondo blanco para el área del sociograma */
        }


        /* Estilo mejorado para el Tooltip Custom */
        .sociograma-tooltip {
            background-color: rgba(0, 0, 0, 0.85); /* Fondo oscuro semi-transparente */
            color: #fff; /* Texto blanco */
            border: none; /* Sin borde */
            border-radius: 4px; /* Bordes ligeramente redondeados */
            box-shadow: 0 2px 5px rgba(0,0,0,0.3); /* Sombra más sutil */
            padding: 8px 12px; /* Espaciado interno */
            font-size: 0.9rem; /* Tamaño de fuente ligeramente más pequeño */
            font-family: 'Poppins', sans-serif; /* Usar la fuente del cuerpo */
            max-width: 200px; /* Ancho máximo */
            white-space: normal;
            word-wrap: break-word;
            position: fixed;
            z-index: 10000;
            pointer-events: none;
            visibility: visible !important;
            display: block !important;
        }

         .sociograma-tooltip strong {
             font-weight: 600; /* Seminegrita para el nombre */
         }

        /* Estilo mejorado para la Leyenda */
        #sociogram-legend {
            position: absolute;
            bottom: 25px; /* Ajusta la posición */
            left: 25px; /* Ajusta la posición */
            z-index: 10;
            background-color: rgba(255, 255, 255, 0.95); /* Fondo blanco semi-transparente */
            border: 1px solid #ced4da;
            border-radius: 8px;
            padding: 12px; /* Espaciado interno */
            font-size: 0.9rem;
            font-family: 'Poppins', sans-serif;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        #sociogram-legend b {
            font-weight: 600;
            display: block; /* Asegura que el título de la sección esté en su propia línea */
            margin-bottom: 5px;
        }

        #sociogram-legend > div {
            display: flex; /* Usar flexbox para alinear el color y el texto */
            align-items: center; /* Centrar verticalmente */
            margin-bottom: 6px; /* Espaciado entre elementos de la leyenda */
        }

         #sociogram-legend span {
             display: inline-block;
             width: 12px;
             height: 12px;
             border-radius: 50%;
             margin-right: 8px; /* Espacio entre el círculo de color y el texto */
             flex-shrink: 0; /* Evita que el círculo se encoja */
         }


        /* Ajustes a los estilos de los nodos en vis-network (esto se hace en el JS, pero los colores deben coincidir con la leyenda) */
        /* Aquí solo como referencia para que coincidan con la leyenda
        .vis-network .vis-node.vis-dot {
             border-width: 2px;
        }
        .vis-network .vis-node.vis-dot.popular {
            border-color: #388E3C; // Darker green border
        }
         .vis-network .vis-node.vis-dot.neutral {
             border-color: #1976D2; // Darker blue border
        }
         .vis-network .vis-node.vis-dot.unpopular {
             border-color: #FFA000; // Darker orange border
        }
         .vis-network .vis-node.vis-dot.isolated_rejected {
             border-color: #D32F2F; // Darker red border
        }
        */

    </style>

    {{-- ───────────── SCRIPTS ───────────── --}}
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
                return console.warn(`⚠️ No se encontró ${canvasId}`);
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
                        // Usar colores de la paleta moderna
                        backgroundColor: esRechazos ? 'rgba(220, 53, 69, 0.7)' : 'rgba(0, 123, 255, 0.7)', /* modern-danger vs modern-primary */
                        borderColor:      esRechazos ? 'rgba(220, 53, 69, 1)' : 'rgba(0, 123, 255, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0 // Asegura que los ticks del eje Y sean enteros
                            }
                        }
                    },
                     plugins: {
                        legend: {
                            display: true,
                            labels: {
                                font: {
                                    family: 'Poppins', // Usar la fuente moderna
                                }
                            }
                        }
                    }
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
                if(cont) cont.innerHTML = '<div class="alert alert-info modern-alert">No hay datos suficientes para mostrar el sociograma.</div>';
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
                        color: l.tipo_relacion === 'preferido' ? '#28a745' : '#dc3545', // Usar colores consistentes con la leyenda
                        opacity: 0.7 // Ligeramente más opaco
                    },
                    width: Math.min(Math.max(l.intensidad || 1, 1), 4),
                    arrows: { to: { enabled: true, scaleFactor: 0.8 } }, // Flechas ligeramente más grandes
                    // === IMPORTANTE: ELIMINADA la propiedad 'title' para usar tooltip custom ===
                })));

               // opciones de vis-network
const options = {
    nodes: {
        shape:'circle', // Keep shape as 'circle'
        size: 18, // Keep node size as is
        font:{
            size:16,
            face:'Poppins',
            color:'#000', // Keep text color black
            multi: 'html',
            // REMOVED: strokeWidth: 0.5,
            // REMOVED: strokeColor: '#fff',
            position: 'middle' // Keep position: 'middle'
        },
        scaling:{
            min:10,
            max:30,
            label: {min: 14, max: 20}
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
        color: { inherit: 'from' },
        dashes: false,
         width: 2
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
        // hierarchical: { enabled:true, direction: 'UD', sortMethod: 'hubsize'}
    },
    interaction: {
        hover: true,
        dragNodes: true,
        zoomView: true,
        navigationButtons: true,
        tooltipDelay: 100
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
                //  TOOLTIP CUSTOM IMPLEMENTATION (Asegúrate de que esta lógica coincida con el CSS del tooltip)
                // ──────────────────────────────────────────────────────────────────────
                let customTooltip = null;
                let tooltipTimeout = null;

                function showTooltip(x, y, content) {
                    if (customTooltip) customTooltip.remove();
                    customTooltip = document.createElement('div');
                    customTooltip.className = 'sociograma-tooltip'; // Usar la clase CSS mejorada
                    customTooltip.innerHTML = content;

                    // Posicionar el tooltip cerca del cursor
                    customTooltip.style.top = `${y + 15}px`;
                    customTooltip.style.left = `${x + 15}px`;


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
                    }, options.interaction.tooltipDelay || 100);
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
                            const relationshipText = edge.color.color === '#28a745' ? 'prefiere a' : 'rechaza a';
                            const content = `${fromNode.label} ${relationshipText} ${toNode.label}`;
                            showTooltip(params.event.clientX, params.event.clientY, content);
                        }
                    }, options.interaction.tooltipDelay || 100);
                });

                // Listener para cuando el ratón sale de una ARISTA
                window.sociogramaNetwork.on('blurEdge', hideTooltip);

                // Ocultar tooltip si se destruye la red
                 window.sociogramaNetwork.on('beforeDestroy', hideTooltip);

                // ──────────────────────────────────────────────────────────────────────
                //  LEYENDA (Asegúrate de que esta lógica coincida con el CSS de la leyenda)
                // ──────────────────────────────────────────────────────────────────────
                const legendId = 'sociogram-legend';
                document.getElementById(legendId)?.remove(); // Eliminar leyenda previa si existe
                const legend = document.createElement('div');
                legend.id = legendId;
                legend.className = 'sociogram-legend'; // Usar la clase CSS mejorada para la leyenda


                legend.innerHTML = `
                    <div><b>Leyenda</b></div>
                    <div><span style="background:#28a745;"></span> Relación de Preferencia</div>
                    <div class="mb-2"><span style="background:#dc3545;"></span> Relación de Rechazo</div>

                    <div><b>Popularidad del Nodo:</b></div>
                    <div><span style="background:#4CAF50; border: 2px solid #388E3C;"></span> Alta (> 70%)</div>
                    <div><span style="background:#2196F3; border: 2px solid #1976D2;"></span> Media (40% - 70%)</div>
                    <div><span style="background:#FFC107; border: 2px solid #FFA000;"></span> Baja (20% - 40%)</div>
                    <div><span style="background:#F44336; border: 2px solid #D32F2F;"></span> Muy Baja (< 20%) / Aislado</div>
                `;
                cont.appendChild(legend);

            }).catch(error => {
                console.error('Error loading VisNetwork:', error);
                const cont = document.getElementById('sociograma');
                if(cont) cont.innerHTML = '<div class="alert alert-danger modern-alert">Error al cargar la librería de visualización.</div>';
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
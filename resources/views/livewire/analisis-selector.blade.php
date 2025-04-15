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
                    <option value="recuento_rechazos">Recuento de Rechazos</option>
                    <option value="aislamiento">Aislamiento</option>
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
            @elseif($tipoAnalisis == 'recuento_rechazos')
                Recuento de Rechazos
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

        @elseif($tipoAnalisis == 'recuento_preferencias' || $tipoAnalisis == 'recuento_rechazos')
            <!-- Visualización del gráfico de preferencias o rechazos -->
            <div wire:ignore>
                <canvas id="graficoPreferencias" style="width: 100%; height: 400px;"></canvas>
            </div>
            <p class="mt-3 text-muted small">
                @if($tipoAnalisis == 'recuento_preferencias')
                    Este gráfico muestra la cantidad de veces que cada estudiante fue elegido como preferido.
                @elseif($tipoAnalisis == 'recuento_rechazos')
                    Este gráfico muestra cuántas veces cada estudiante fue señalado como alguien con quien no se desea trabajar.
                @endif
            </p>

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
const tipoAnalisis = @json($tipoAnalisis);

// Variables globales para los gráficos
let preferenciasChart = null;
let sociogramaNetwork = null;

// Función para cargar vis.js dinámicamente
function cargarVisJS() {
    return new Promise((resolve, reject) => {
        // Si vis ya está cargado, resolvemos inmediatamente
        if (typeof vis !== 'undefined') {
            console.log('vis.js ya está cargado');
            resolve();
            return;
        }

        console.log('Cargando vis.js dinámicamente...');

        // Cargar el CSS
        const linkElement = document.createElement('link');
        linkElement.rel = 'stylesheet';
        linkElement.href = 'https://unpkg.com/vis-network/dist/dist/vis-network.min.css';
        document.head.appendChild(linkElement);

        // Cargar el JavaScript
        const scriptElement = document.createElement('script');
        scriptElement.src = 'https://unpkg.com/vis-network/dist/vis-network.min.js';
        scriptElement.onload = () => {
            console.log('vis.js cargado correctamente');
            resolve();
        };
        scriptElement.onerror = (error) => {
            console.error('Error al cargar vis.js:', error);
            reject(error);
        };
        document.head.appendChild(scriptElement);
    });
}

// Función para actualizar el gráfico con datos proporcionados
function actualizarGraficoConDatos(labels, data) {
    console.log("Actualizando gráfico con datos proporcionados");
    console.log("Labels proporcionados:", labels);
    console.log("Data proporcionados:", data);
    
    // Verificar que tengamos datos válidos
    if (!labels || !labels.length || !data || !data.length) {
        console.error("Datos insuficientes para crear el gráfico");
        return;
    }
    
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
    
    const tipoAnalisis = document.getElementById('tipoAnalisis')?.value || '';
    
    // Configuración del gráfico - CORREGIDA la estructura
    const config = {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: tipoAnalisis === 'recuento_rechazos' ? 'Veces que fue rechazado' : 'Veces que fue elegido',
                data: data,
                backgroundColor: tipoAnalisis === 'recuento_rechazos' ? 'rgba(255, 99, 132, 0.5)' : 'rgba(54, 162, 235, 0.5)',
                borderColor: tipoAnalisis === 'recuento_rechazos' ? 'rgba(255, 99, 132, 1)' : 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    max: Math.max(...data) + 2  // Establecer un máximo dinámico
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

// Función específica para el manejo del sociograma
function inicializarSociograma() {
    console.log("Inicializando módulo de sociograma");
    
    // Primero cargar vis.js, luego continuar
    cargarVisJS().then(() => {
        console.log("vis.js cargado, listo para crear sociogramas");
    }).catch(error => {
        console.error("No se pudo cargar vis.js:", error);
        // Mostrar mensaje de error en el contenedor del sociograma
        const container = document.getElementById('sociograma');
        if (container) {
            container.innerHTML = '<div class="alert alert-danger">Error: No se pudo cargar la biblioteca necesaria para el sociograma. Por favor, contacte al administrador.</div>';
        }
    });
}

// Función para buscar el contenedor del sociograma con reintentos
function buscarContenedorSociograma(callback, intentos = 0, maxIntentos = 5) {
    const container = document.getElementById('sociograma');
    
    if (container) {
        // Si encontramos el contenedor, ejecutar el callback con él
        callback(container);
        return;
    }
    
    if (intentos >= maxIntentos) {
        // Si superamos el número máximo de intentos, mostrar error
        console.error(`Error: Contenedor 'sociograma' no encontrado después de ${maxIntentos} intentos`);
        
        // Intentar crear el contenedor si no existe
        const cardBody = document.querySelector('.card-body');
        if (cardBody) {
            console.log("Intentando crear el contenedor del sociograma");
            const wireIgnoreDiv = document.createElement('div');
            wireIgnoreDiv.setAttribute('wire:ignore', '');
            
            const sociogramaDiv = document.createElement('div');
            sociogramaDiv.id = 'sociograma';
            sociogramaDiv.style.width = '100%';
            sociogramaDiv.style.height = '600px';
            
            wireIgnoreDiv.appendChild(sociogramaDiv);
            cardBody.innerHTML = '';
            cardBody.appendChild(wireIgnoreDiv);
            
            // Intentar una vez más con el contenedor recién creado
            setTimeout(() => {
                const nuevoContainer = document.getElementById('sociograma');
                if (nuevoContainer) {
                    callback(nuevoContainer);
                } else {
                    mostrarErrorSociograma("No se pudo crear el contenedor para el sociograma");
                }
            }, 100);
        } else {
            mostrarErrorSociograma("No se encuentra el contenedor para el sociograma");
        }
        return;
    }
    
    // Si no encontramos el contenedor, intentar de nuevo después de un tiempo
    console.log(`Contenedor 'sociograma' no encontrado. Reintentando (${intentos + 1}/${maxIntentos})...`);
    setTimeout(() => {
        buscarContenedorSociograma(callback, intentos + 1, maxIntentos);
    }, 300);
}

// Función para mostrar un error en el contenedor del sociograma
function mostrarErrorSociograma(mensaje) {
    // Buscar el contenedor o un elemento alternativo donde mostrar el error
    const container = document.getElementById('sociograma') || 
                      document.querySelector('.card-body') ||
                      document.querySelector('.card');
    
    if (container) {
        if (container.id !== 'sociograma') {
            // Si estamos usando un contenedor alternativo
            container.innerHTML = `<div class="alert alert-warning text-center p-4">
                <i class="fas fa-exclamation-triangle fa-2x mb-3"></i>
                <p>${mensaje}</p>
                <p class="small mt-2">Nota: No se encontró el contenedor original del sociograma.</p>
            </div>`;
        } else {
            // Si encontramos el contenedor correcto
            container.innerHTML = `<div class="alert alert-warning text-center p-4">
                <i class="fas fa-exclamation-triangle fa-2x mb-3"></i>
                <p>${mensaje}</p>
            </div>`;
        }
    } else {
        console.error("No se pudo mostrar el mensaje de error: No se encontró un contenedor adecuado");
        alert("Error en el sociograma: " + mensaje);
    }
}

// Función para preparar los nodos con posiciones estáticas
function prepararNodosConPosicionesEstaticas(nodes, links) {
    // Calcular el radio según la cantidad de nodos
    const radio = Math.max(300, nodes.length * 50);
    const centro = { x: 0, y: 0 };
    
    return nodes.map((node, index) => {
        // Calcular posición en círculo
        const angulo = (index / nodes.length) * 2 * Math.PI;
        const x = centro.x + radio * Math.cos(angulo);
        const y = centro.y + radio * Math.sin(angulo);
        
        // Calcular el número de conexiones para este nodo
        const connections = links.filter(link => 
            link.source === node.id || link.target === node.id
        ).length;
        
        // Obtener color según métricas
        let group = 'neutral';
        
        if (node.metricas && typeof node.metricas.popularidad === 'number') {
            const popularidad = node.metricas.popularidad;
            if (popularidad > 0.7) {
                group = 'popular';
            } else if (popularidad > 0.4) {
                group = 'neutral';
            } else if (popularidad > 0.2) {
                group = 'unpopular';
            } else {
                group = 'rejected';
            }
        }
        
        // Asegurar que cada nodo tenga las propiedades necesarias
        return {
            id: node.id,
            label: node.label || `Estudiante ${node.id}`,
            x: x,
            y: y,
            connections: connections,
            group: group,
            size: 25 + (connections * 2),
            metricas: node.metricas // Guardar las métricas para el tooltip
        };
    });
}

// Función para renderizar el sociograma con un enfoque totalmente nuevo
function renderizarSociograma(datosRecibidos) {
    // Primero, aseguramos que vis.js esté cargado
    cargarVisJS().then(() => {
        console.log("Datos del sociograma recibidos:", datosRecibidos);
        
        // Extraer los datos correctamente dependiendo del formato
        let datos;
        
        // Si es un array, tomar el primer elemento
        if (Array.isArray(datosRecibidos) && datosRecibidos.length > 0) {
            console.log("Datos recibidos como array, tomando primer elemento");
            datos = datosRecibidos[0];
        } else {
            datos = datosRecibidos;
        }
        
        // Validar los datos recibidos
        if (!datos) {
            console.error("Error: No se recibieron datos para el sociograma");
            mostrarErrorSociograma("No se recibieron datos para el sociograma");
            return;
        }
        
        // Validar la estructura de datos
        if (!datos.nodes || !Array.isArray(datos.nodes) || !datos.links || !Array.isArray(datos.links)) {
            console.error("Error: Formato de datos incorrecto para el sociograma", datos);
            mostrarErrorSociograma("Los datos recibidos no tienen el formato correcto");
            return;
        }
        
        // Verificar que hay nodos para mostrar
        if (datos.nodes.length === 0) {
            console.warn("Advertencia: No hay nodos para mostrar en el sociograma");
            mostrarErrorSociograma("No hay estudiantes para mostrar en el sociograma");
            return;
        }
        
        // Buscar el contenedor con reintentos
        buscarContenedorSociograma(function(container) {
            try {
                console.log("Preparando datos para el sociograma...");
                
                // Limpiar cualquier contenido previo
                container.innerHTML = '';
                
                // Primero, colocar los nodos en un círculo grande de forma estática
                const nodosPreparados = prepararNodosConPosicionesEstaticas(datos.nodes, datos.links);
                
                // Configuración simple sin física
                const opciones = {
                    nodes: {
                        shape: 'circle',
                        borderWidth: 2,
                        shadow: true,
                        font: {
                            size: 15,
                            face: 'Arial'
                        },
                        scaling: {
                            min: 30,
                            max: 60
                        }
                    },
                    edges: {
                        smooth: {
                            enabled: true,
                            type: 'curvedCW',
                            roundness: 0.3
                        },
                        arrows: 'to',
                        shadow: true
                    },
                    physics: {
                        enabled: false // Desactivada siempre
                    },
                    interaction: {
                        dragNodes: true,
                        zoomView: true,
                        hover: true,
                        navigationButtons: true,
                        keyboard: true
                    },
                    manipulation: {
                        enabled: false
                    },
                    groups: {
                        popular: {
                            color: { background: '#28a745', border: '#1e7e34' },
                            borderWidth: 2,
                            shadow: true
                        },
                        neutral: {
                            color: { background: '#17a2b8', border: '#138496' },
                            borderWidth: 2,
                            shadow: true
                        },
                        unpopular: {
                            color: { background: '#ffc107', border: '#d39e00' },
                            borderWidth: 2,
                            shadow: true
                        },
                        rejected: {
                            color: { background: '#dc3545', border: '#bd2130' },
                            borderWidth: 2,
                            shadow: true
                        }
                    }
                };
                
                // Crear conjuntos de datos para vis.js
                const nodes = new vis.DataSet(nodosPreparados);
                
                // Crear conjunto de enlaces, pero sin física
                const edges = new vis.DataSet(datos.links.map(function(link) {
                    const esPreferencia = link.tipo_relacion === 'preferido';
                    return {
                        from: link.source,
                        to: link.target,
                        color: esPreferencia ? '#28a745' : '#dc3545',
                        width: Math.min(Math.max(link.intensidad || 1, 1), 10) * 1.5
                    };
                }));
                
                // Crear la red sin física
                const network = new vis.Network(container, { nodes, edges }, opciones);
                
                // No permitir que la red arranque simulación física
                network.on("startStabilization", function() {
                    network.stopSimulation();
                });
                
                // Manejar los eventos de tooltip manualmente para mayor control
                let tooltipTimeout;
                let tooltipDiv = null;
                
                network.on("hoverNode", function(params) {
                    const nodeId = params.node;
                    const nodeData = nodes.get(nodeId);
                    
                    // Obtener la posición del nodo en el canvas
                    const position = network.getPositions([nodeId])[nodeId];
                    // Convertir la posición del canvas a coordenadas del DOM
                    const canvasPosition = network.canvasToDOM(position);
                    
                    // Limpiar cualquier tooltip anterior
                    if (tooltipDiv) {
                        document.body.removeChild(tooltipDiv);
                        tooltipDiv = null;
                    }
                    
                    clearTimeout(tooltipTimeout);
                    
                    // Crear un nuevo tooltip después de un breve retraso
                    tooltipTimeout = setTimeout(() => {
                        mostrarTooltipPersonalizado(nodeData, {
                            x: canvasPosition.x + container.getBoundingClientRect().left,
                            y: canvasPosition.y + container.getBoundingClientRect().top
                        }, container);
                    }, 200);
                });
                
                network.on("blurNode", function() {
                    // Limpiar el tooltip al dejar de pasar el mouse sobre el nodo
                    clearTimeout(tooltipTimeout);
                    if (tooltipDiv) {
                        document.body.removeChild(tooltipDiv);
                        tooltipDiv = null;
                    }
                });
                
                // También manejar el evento cuando el usuario hace zoom o pan
                network.on("afterDrawing", function() {
                    // Si hay un nodo seleccionado, actualizar la posición del tooltip
                    if (tooltipDiv && network.getSelectedNodes().length > 0) {
                        const nodeId = network.getSelectedNodes()[0];
                        const nodeData = nodes.get(nodeId);
                        const position = network.getPositions([nodeId])[nodeId];
                        const canvasPosition = network.canvasToDOM(position);
                        
                        document.body.removeChild(tooltipDiv);
                        tooltipDiv = null;
                        
                        mostrarTooltipPersonalizado(nodeData, {
                            x: canvasPosition.x + container.getBoundingClientRect().left,
                            y: canvasPosition.y + container.getBoundingClientRect().top
                        }, container);
                    }
                });
                
                // Función para mostrar un tooltip personalizado
                // Función mejorada para mostrar un tooltip personalizado con posición precisa
function mostrarTooltipPersonalizado(nodeData, position, container) {
    // Crear el div del tooltip si no existe
    tooltipDiv = document.createElement('div');
    tooltipDiv.className = 'sociograma-tooltip';
    tooltipDiv.style.cssText = `
        position: fixed; /* Cambiado de absolute a fixed para posicionamiento global */
        background-color: white;
        border-radius: 8px;
        box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        padding: 12px 15px;
        font-family: Arial, sans-serif;
        color: #333;
        z-index: 10000; /* Aumentado para asegurar que esté por encima de todo */
        max-width: 250px;
        font-size: 14px;
        pointer-events: none; /* Para que no interfiera con los clics */
    `;
    
    // Contenido del tooltip
    let contenido = '';
    
    // Título
    contenido += `<div style="font-weight: bold; font-size: 16px; margin-bottom: 8px; border-bottom: 1px solid #eee; padding-bottom: 5px;">${nodeData.label}</div>`;
    
    // Información del estudiante
    if (nodeData.metricas) {
        // Determinar color del nivel de popularidad
        let colorPopularidad = '#777';
        let nivelPopularidad = 'No disponible';
        
        const popularidad = nodeData.metricas.popularidad;
        if (popularidad > 0.7) {
            nivelPopularidad = "Alta";
            colorPopularidad = "#28a745";
        } else if (popularidad > 0.4) {
            nivelPopularidad = "Media";
            colorPopularidad = "#17a2b8";
        } else if (popularidad > 0.2) {
            nivelPopularidad = "Baja";
            colorPopularidad = "#ffc107";
        } else {
            nivelPopularidad = "Muy baja";
            colorPopularidad = "#dc3545";
        }
        
        // Tabla para mejor formato
        contenido += '<table style="width: 100%; border-collapse: collapse;">';
        contenido += `<tr>
                      <td style="padding: 3px 0; font-weight: bold; color: #555; width: 70%;">Preferencias recibidas:</td>
                      <td style="padding: 3px 0; text-align: right; font-weight: bold;">${nodeData.metricas.preferencias || 0}</td>
                    </tr>`;
        contenido += `<tr>
                      <td style="padding: 3px 0; font-weight: bold; color: #555; width: 70%;">Rechazos recibidos:</td>
                      <td style="padding: 3px 0; text-align: right; font-weight: bold;">${nodeData.metricas.rechazos || 0}</td>
                    </tr>`;
        contenido += `<tr>
                      <td style="padding: 3px 0; font-weight: bold; color: #555; width: 70%;">Índice de popularidad:</td>
                      <td style="padding: 3px 0; text-align: right; font-weight: bold;">${Math.round((nodeData.metricas.popularidad || 0) * 100)}%</td>
                    </tr>`;
        contenido += `<tr>
                      <td style="padding: 3px 0; font-weight: bold; color: #555; width: 70%;">Nivel de popularidad:</td>
                      <td style="padding: 3px 0; text-align: right; font-weight: bold; color: ${colorPopularidad};">${nivelPopularidad}</td>
                    </tr>`;
        contenido += '</table>';
    }
    
    // Información de conexiones
    contenido += `<div style="margin-top: 8px; padding-top: 5px; border-top: 1px solid #eee;">
                  <span style="font-weight: bold; color: #555;">Conexiones totales:</span>
                  <span style="float: right; font-weight: bold;">${nodeData.connections || 0}</span>
                  <div style="clear: both;"></div>
                </div>`;
    
    tooltipDiv.innerHTML = contenido;
    
    // Añadir al DOM para poder calcular dimensiones
    document.body.appendChild(tooltipDiv);
    
    // Obtener las dimensiones reales del tooltip
    const tooltipRect = tooltipDiv.getBoundingClientRect();
    
    // Usar las coordenadas exactas del mouse (posición) para posicionar el tooltip
    // Primero intentamos colocar el tooltip a la derecha del puntero
    let tooltipX = position.x + 15; // 15px a la derecha del cursor
    let tooltipY = position.y - (tooltipRect.height / 2); // Centrado verticalmente con el cursor
    
    // Ajustar si se sale por la derecha
    if (tooltipX + tooltipRect.width > window.innerWidth - 10) {
        tooltipX = position.x - tooltipRect.width - 15; // 15px a la izquierda del cursor
    }
    
    // Ajustar si se sale por arriba o por abajo
    if (tooltipY < 10) {
        tooltipY = 10; // 10px desde el borde superior
    } else if (tooltipY + tooltipRect.height > window.innerHeight - 10) {
        tooltipY = window.innerHeight - tooltipRect.height - 10; // 10px desde el borde inferior
    }
    
    // Aplicar la posición final
    tooltipDiv.style.left = `${tooltipX}px`;
    tooltipDiv.style.top = `${tooltipY}px`;
    
    return tooltipDiv;
}
                
                // Añadir leyenda al contenedor
                const leyendaDiv = document.createElement('div');
                leyendaDiv.className = 'sociograma-leyenda';
                leyendaDiv.style.cssText = 'position: absolute; bottom: 20px; left: 20px; background: white; padding: 12px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.2); font-size: 12px; z-index: 10;';
                
                leyendaDiv.innerHTML = `
                    <div style="font-weight: bold; margin-bottom: 8px; font-size: 14px;">Leyenda</div>
                    <div style="display: flex; align-items: center; margin-bottom: 5px;">
                        <span style="display: inline-block; width: 12px; height: 12px; background-color: #28a745; border-radius: 50%; margin-right: 8px;"></span>
                        <span>Alta popularidad</span>
                    </div>
                    <div style="display: flex; align-items: center; margin-bottom: 5px;">
                        <span style="display: inline-block; width: 12px; height: 12px; background-color: #17a2b8; border-radius: 50%; margin-right: 8px;"></span>
                        <span>Popularidad media</span>
                    </div>
                    <div style="display: flex; align-items: center; margin-bottom: 5px;">
                        <span style="display: inline-block; width: 12px; height: 12px; background-color: #ffc107; border-radius: 50%; margin-right: 8px;"></span>
                        <span>Popularidad baja</span>
                    </div>
                    <div style="display: flex; align-items: center; margin-bottom: 8px;">
                        <span style="display: inline-block; width: 12px; height: 12px; background-color: #dc3545; border-radius: 50%; margin-right: 8px;"></span>
                        <span>Rechazado</span>
                    </div>
                    <div style="display: flex; align-items: center; margin-bottom: 5px;">
                        <span style="display: inline-block; width: 20px; height: 3px; background-color: #28a745; margin-right: 8px;"></span>
                        <span>Preferencia</span>
                    </div>
                    <div style="display: flex; align-items: center; margin-bottom: 5px;">
                        <span style="display: inline-block; width: 20px; height: 3px; background-color: #dc3545; margin-right: 8px;"></span>
                        <span>Rechazo</span>
                    </div>
                `;
                
                container.appendChild(leyendaDiv);
                
                // Guardar la red en la variable global
                window.sociogramaNetwork = network;
                
                console.log("Sociograma creado con éxito - Sin física");
                
                // Hacer zoom para asegurar que todo se vea bien
                network.fit({
                    animation: {
                        duration: 1000,
                        easingFunction: 'easeInOutQuad'
                    }
                });
                
            } catch (error) {
                console.error("Error al crear el sociograma:", error);
                mostrarErrorSociograma("Se produjo un error al crear el sociograma: " + error.message);
            }
        });
    }).catch(error => {
        console.error("No se pudo cargar vis.js:", error);
        mostrarErrorSociograma("No se pudo cargar la biblioteca necesaria para el sociograma");
    });
}

// Inicializar cuando Livewire esté listo
document.addEventListener('livewire:init', function() {
    console.log("Livewire inicializado");
    
    // Inicializar el módulo de sociograma
    inicializarSociograma();
    
    // Escuchar evento para actualizar el gráfico de preferencias
    Livewire.on('actualizarGrafico', function(eventData) {
        console.log("EVENTO RECIBIDO:", eventData);
        
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
        
        // Actualizar el gráfico solo si tenemos datos válidos
        if (labels.length > 0 && data.length > 0) {
            actualizarGraficoConDatos(labels, data);
        } else {
            console.warn("No hay datos suficientes para actualizar el gráfico");
        }
    });
    
    // Escuchar evento para actualizar el sociograma
    Livewire.on('actualizarSociograma', function(data) {
        console.log("Evento actualizarSociograma recibido");
        renderizarSociograma(data);
    });
});
        </script>
    @endpush
</div>
/* ======================================================================
   VARIABLES GLOBALES
   =====================================================================*/
let chartPref   = null,
    chartRech   = null,
    sociogramaNetwork = null;

let _origNodes = [],
    _origLinks = [],
    _origPreguntas = []; // <--- AÑADIDO

/* ======================================================================
   CARGAR vis‑network DINÁMICAMENTE
   =====================================================================*/
function cargarVisJS () {
    return new Promise((ok, fail) => {
        if (window.vis) return ok();

        const css   = document.createElement('link');
        css.rel     = 'stylesheet';
        css.href    = 'https://unpkg.com/vis-network/dist/vis-network.min.css';
        document.head.appendChild(css);

        const js    = document.createElement('script');
        js.src      = 'https://unpkg.com/vis-network/dist/vis-network.min.js';
        js.onload   = ok;
        js.onerror  = fail;
        document.head.appendChild(js);
    });
}

/* ======================================================================
   GRÁFICAS DE BARRAS (Chart.js)
   =====================================================================*/
function renderBar (canvasId, labels, data, esRechazos, intento = 0) {
    const canvas = document.getElementById(canvasId);
    if (!canvas) {
        if (intento < 10)
            return setTimeout(() => renderBar(canvasId, labels, data, esRechazos, intento + 1), 100);
        return console.warn(`⚠️ No se encontro ${canvasId}`);
    }

    Chart.getChart(canvas)?.destroy();

    new Chart(canvas, {
        type : 'bar',
        data : {
            labels,
            datasets: [{
                label: esRechazos ? 'Veces rechazado' : 'Veces elegido',
                data,
                backgroundColor: esRechazos ? 'rgba(220,53,69,.7)' : 'rgba(0,123,255,.7)',
                borderColor    : esRechazos ? 'rgba(220,53,69,1)'  : 'rgba(0,123,255,1)',
                borderWidth    : 1
            }]
        },
        options: {
            responsive         : true,
            maintainAspectRatio: false,
            scales : { y: { beginAtZero: true, ticks: { precision: 0 } } },
            plugins: { legend: { labels: { font: { family: 'Poppins' } } } }
        }
    });
}

/* ======================================================================
   HEATMAP  (Chart.js Matrix)
   =====================================================================*/
function renderMatrixWithRetry (labels, data, intento = 0) {
    const canvas = document.getElementById('heatmapReciprocidad');
    if (!canvas) {
        if (intento < 10)
            return setTimeout(() => renderMatrixWithRetry(labels, data, intento + 1), 100);
        return console.error('❌ #heatmapReciprocidad no encontrado');
    }

    if (!labels?.length || !data?.length) {
        const ctx = canvas.getContext('2d');
        Chart.getChart(canvas)?.destroy();
        ctx.clearRect(0,0,canvas.width,canvas.height);
        ctx.font='1rem Poppins';
        ctx.fillStyle='#6c757d';
        ctx.textAlign='center';
        ctx.textBaseline='middle';
        ctx.fillText('No hay datos suficientes.', canvas.width/2, canvas.height/2);
        return;
    }

    if (!Chart.controllers.matrix) {
        Chart.register(
            ChartMatrix.Controller, ChartMatrix.Element,
            ChartMatrix.Scales.CategoryScale, ChartMatrix.Scales.LinearScale,
            Chart.Tooltip, Chart.Legend
        );
    }

    Chart.getChart(canvas)?.destroy();

    new Chart(canvas, {
        type : 'matrix',
        data : {
            labels,
            datasets:[{
                label:'Reciprocidad',
                data,
                width : ({chart})=>chart.width / labels.length - 2,
                height: ({chart})=>chart.height / labels.length - 2,
                borderWidth  : 1,
                borderColor  : '#fff',
                backgroundColor: c => c.raw.v ? 'rgba(0,123,255,.6)' : 'rgba(238,238,238,.3)'
            }]
        },
        options:{
            maintainAspectRatio:false,
            scales:{
                x:{ type:'category', labels, offset:true, grid:{display:false},
                    position:'top', ticks:{ font:{family:'Poppins'}, autoSkip:false, maxRotation:90 }},
                y:{ type:'category', labels, offset:true, grid:{display:false},
                    ticks:{ font:{family:'Poppins'}, autoSkip:false }}
            },
            plugins:{ legend:{ display:false } }
        }
    });
}

/* ======================================================================
   SOCIOGRAMA  +  FILTROS
   =====================================================================*/
/* =========================================================
   SOCIOGRAMA – barra de filtros
   =========================================================*/
function buildToolbar () {
    const wrapper = document.getElementById('soc-wrapper');
    if (!wrapper) { console.warn('#soc-wrapper no encontrado'); return; }

    // Buscamos los elementos de control por sus IDs
    const selAlumnos = wrapper.querySelector('#soc-select'); // Cambiado nombre a selAlumnos para claridad
    const preguntasOptionsContainer = wrapper.querySelector('#soc-preguntas-filter-options'); // <-- Contenedor de checkboxes de pregunta
    const toggleMatch = wrapper.querySelector('#soc-onlyMatch');
    const toggleIsol  = wrapper.querySelector('#soc-markIsol');
    const resetButton = wrapper.querySelector('#soc-reset');
    const selectAllPreguntasButton = wrapper.querySelector('#select-all-preguntas'); // Boton "Seleccionar todas"


    // Verificamos que todos los elementos necesarios existan
    // (Adaptamos la verificación para la nueva estructura)
    if (!selAlumnos || !preguntasOptionsContainer || !toggleMatch || !toggleIsol || !resetButton) {
        console.error('Error: No se encontraron todos los elementos de filtro en el archivo Blade livewire.partials.sociograma.blade.php. Asegurate de que los IDs sean correctos.');
        return;
    }

    /* rellenar selector de alumnos (Este se mantiene igual) */
    selAlumnos.innerHTML = '';
    [..._origNodes].sort((a,b)=>a.label.localeCompare(b.label))
                   .forEach(n => selAlumnos.insertAdjacentHTML(
                       'beforeend', `<option value="${n.id}">${n.label}</option>`));
    selAlumnos.selectedIndex = -1;


    /* Rellenar opciones de preguntas (¡YA NO NECESITAMOS ESTO AQUÍ! Livewire Blade ya lo hace) */
    // selPregunta.innerHTML = ''; // Eliminar esta línea
    // [..._origPreguntas].forEach(...) // Eliminar este bucle


    /* listeners solo una vez */
    // Verificamos si ya hemos configurado los listeners
    if (!wrapper.dataset.listenersReady) {

        selAlumnos.addEventListener('change', applyFilters); // Listener selector alumnos

                // Anadir listeners a cada checkbox de pregunta
        preguntasOptionsContainer.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
            checkbox.addEventListener('change', applyFilters);
        });

	        // Listener para el boton "Seleccionar todas"
        if (selectAllPreguntasButton) {
            selectAllPreguntasButton.addEventListener('click', function(event) {
                event.preventDefault();
                const questionCheckboxes = [...preguntasOptionsContainer.querySelectorAll('input[type="checkbox"]')];
                const allChecked = questionCheckboxes.length > 0 && questionCheckboxes.every(cb => cb.checked);

                questionCheckboxes.forEach((checkbox) => {
                    checkbox.checked = !allChecked;
                });

                applyFilters(); // Aplicar filtros despues de alternar
            });
        }


        toggleMatch.addEventListener('change', applyFilters);
        toggleIsol .addEventListener('change', applyFilters);

        // Listener para el boton de limpiar
        resetButton.addEventListener('click', () => {
            // Deseleccionar todas las opciones del selector de alumnos
            [...selAlumnos.options].forEach(option => option.selected = false);

            // Desmarcar todos los checkboxes de pregunta
            preguntasOptionsContainer.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
                 checkbox.checked = false;
            });

            // Restablecer otros filtros a su estado por defecto
            toggleMatch.checked = false;
            toggleIsol .checked = true; // Asumiendo que por defecto está marcado

            applyFilters(); // Aplicar filtros después del reset
        });

        wrapper.dataset.listenersReady = '1'; // Marcamos que los listeners ya fueron configurados
    }
}

/* Construir barra en retry (por si Livewire aún no pintó el Blade) */
// Esta función ahora solo espera a que el wrapper del sociograma exista
function ensureToolbar (intento = 0) {
    const wrapper = document.getElementById('soc-wrapper');
    if (!wrapper) {
        // Si el wrapper no se encuentra después de varios intentos, registramos una advertencia y salimos
        if (intento < 10) return setTimeout(() => ensureToolbar(intento+1), 80);
        console.warn('#soc-wrapper no encontrado tras varios intentos. No se pudo construir la barra de herramientas o dibujar el sociograma.');
        return;
    }
    // Una vez que el wrapper existe, llamamos a buildToolbar para encontrar y rellenar los controles dentro de él
    // buildToolbar también maneja la adición de listeners la primera vez.
    buildToolbar();
    // Y finalmente, aplicamos los filtros iniciales (sin selección) para dibujar el sociograma por primera vez con los datos cargados
    applyFilters();
}

/* ======================================================================
   FILTRAR
   =====================================================================*/
function applyFilters () {
    /* — guard clause: si aún no hay links originales cargados — */
    if (!_origLinks.length) {
        drawSociograma(_origNodes, []);
        return;
    }

    // Obtenemos los elementos de filtro. Aseguramos que existan.
    const selAlumnos = document.getElementById('soc-select'); // Cambiado nombre a selAlumnos
    const preguntasOptionsContainer = document.getElementById('soc-preguntas-filter-options'); // <-- Contenedor de checkboxes de pregunta
    const toggleMatch = document.getElementById('soc-onlyMatch'); // Necesitamos leer su estado aquí también
    const toggleIsol = document.getElementById('soc-markIsol'); // Necesitamos leer su estado aquí también

    if (!selAlumnos || !preguntasOptionsContainer || !toggleMatch || !toggleIsol) {
        console.error('applyFilters: No se encontraron todos los elementos de filtro.');
        return;
    }


    // Obtenemos el estado actual de los checkboxes de opciones
    const onlyM   = toggleMatch.checked;
    // const markIsolados = toggleIsol.checked; // No necesitamos esto aquí, se lee en drawSociograma


    // Obtenemos los IDs de los alumnos seleccionados del selector
    const chosen  = [...selAlumnos.selectedOptions].map(o => +o.value);

    // Obtenemos los IDs de las preguntas seleccionadas leyendo los checkboxes marcados (nuevo)
    const chosenQuestions = [];
    preguntasOptionsContainer.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
        // Solo incluimos checkboxes de preguntas, no el de "Seleccionar todas"
        if (checkbox.id !== 'select-all-preguntas' && checkbox.checked) {
            chosenQuestions.push(+checkbox.value); // Añadimos el valor (ID de pregunta) si está marcado
        }
    });
    // console.log('Chosen Questions IDs:', chosenQuestions); // Opcional para depurar


    // Filtramos los enlaces originales (_origLinks) basándonos en las condiciones actuales de los filtros
    const links = _origLinks.filter(e =>
        (!onlyM || e.isMatch) && // Condición 1: Solo matches
        (chosen.length === 0 || chosen.includes(e.source) || chosen.includes(e.target)) && // Condición 2: Alumnos seleccionados
        (chosenQuestions.length === 0 || chosenQuestions.includes(e.pregunta_id)) // Condición 3: Preguntas seleccionadas
    );


    // Determinamos el conjunto de IDs de nodos que deben ser visibles (igual que antes)
    const visible = new Set();
    links.forEach(e => { visible.add(e.source); visible.add(e.target); });
    chosen.forEach(id => visible.add(id));

    // Filtramos los nodos originales para obtener solo los visibles (igual que antes)
    const nodesToDraw = _origNodes
        .filter(n => visible.has(n.id));

    // Dibujamos el sociograma con los nodos visibles y los enlaces filtrados
    drawSociograma(nodesToDraw, links);
}

/* ======================================================================
   DIBUJAR SOCIOGRAMA
   =====================================================================*/
// Esta función recibe el array de nodos YA FILTRADOS (nodesData) y el array de enlaces YA FILTRADOS (linksData)
function drawSociograma (nodesData, linksData) {
    const cont = document.getElementById('sociograma');
    if (!cont) {
        console.warn('#sociograma no encontrado. No se puede dibujar el grafo.');
        return;
    }

    // Destruimos la instancia anterior de la red si existe para evitar duplicados o problemas de memoria
    sociogramaNetwork?.destroy();
    sociogramaNetwork = null; // Aseguramos que la variable global sea null después de destruir

    // Si no hay nodos o enlaces para dibujar, mostramos un mensaje
    if (!nodesData.length || !linksData.length) {
        cont.innerHTML = '<div class="alert alert-info modern-alert">No hay datos suficientes para el filtro aplicado.</div>';
        console.log("No hay nodos o enlaces para dibujar con el filtro actual.");
        return;
    } else {
         // Limpiamos el contenido si había un mensaje anterior
         cont.innerHTML = '';
    }


    // Obtenemos el estado actual del checkbox "Marcar no-elegidos" para decidir el color del nodo
    // Lo obtenemos aquí porque afecta la apariencia (el grupo del nodo en vis.js)
    const markIsoCheckbox = document.getElementById('soc-markIsol');
    const markIsolados = markIsoCheckbox ? markIsoCheckbox.checked : true; // Por defecto true si no se encuentra el checkbox


    // Creamos los nodos para vis-network a partir de los nodos filtrados (nodesData)
    // Aquí definimos cómo se verá cada nodo en la visualizacion
    const nodes = new vis.DataSet(nodesData.map(n => ({
        id   : n.id,
        label: n.label,
        // Determinamos el GRUPO del nodo (que controla el color en la configuración de vis.js)
        // Si la opción "Marcar no-elegidos" está activada (markIsolados es true)
        // Y el nodo ORIGINALMENTE no recibió ninguna conexión (sus métricas recibidas son 0)
        // Entonces el nodo pertenece al grupo 'isolated' (rojo).
        // De lo contrario, pertenece al grupo 'default' (azul).
        group: (markIsolados && (n.metricas?.preferencias_recibidas ?? 0) + (n.metricas?.rechazos_recibidos ?? 0) === 0) ? 'isolated' : 'default',
        value: 10 + (n.metricas?.preferencias_recibidas ?? 0) + (n.metricas?.rechazos_recibidos ?? 0),
        title: n.label, // El nombre completo del alumno para mostrar en el tooltip al pasar el ratón
        metricas: n.metricas // Pasamos las métricas originales para mostrarlas en el tooltip
    })));

    // Creamos los enlaces (edges) para vis-network a partir de los enlaces filtrados (linksData)
    // Aquí definimos cómo se verá cada enlace en la visualizacion
    const edges = new vis.DataSet(linksData.map(l => ({
        from : l.source, // El nodo de origen del enlace
        to   : l.target, // El nodo de destino del enlace
        color: {
            color  : l.isMatch ? '#00b050' // Si es un "match" (reciprocidad mutua), color verde oscuro
                               : l.tipo === 'preferido' ? '#28a745' : '#dc3545', // Si es solo preferencia, verde; si es rechazo, rojo
            opacity: l.isMatch ? 1 : .6 // Los matches son completamente opacos, otros enlaces un poco transparentes
        },
        width : l.isMatch ? 4 : 2,
        dashes: l.isMatch ? false : [6,4],
        arrows: { to:{ enabled:true, scaleFactor:.55 } },
        tipo: l.tipo,
        isMatch: !!l.isMatch
    })));

    // Opciones de configuración de la red de vis.js
    const options = {
        nodes : {
            shape:'dot',
            size:20,
            borderWidth:2,
            borderWidthSelected:3,
            font:{
                size: 13,
                color: '#1f2937',
                face: 'Poppins',
                strokeWidth: 4,
                strokeColor: '#ffffff'
            },
            scaling: {
                min: 14,
                max: 34,
                label: { enabled: true, min: 10, max: 16 }
            },
            shadow:{ enabled:true, color:'rgba(0,0,0,.12)', size:7 }
        },
        edges : {
            smooth:{ enabled:true, type:'dynamic', roundness:.3 },
            selectionWidth: 3,
            shadow:{ enabled:true, color:'rgba(0,0,0,.08)', size:2 }
        },
        groups:{
            default :{ color:{ background:'#2196F3', border:'#1565c0', highlight:{ background:'#1976d2', border:'#0d47a1' } }},
            isolated:{ color:{ background:'#F44336', border:'#c62828', highlight:{ background:'#e53935', border:'#b71c1c' } }}
        },
        physics:{
            enabled:true, // Física activada inicialmente para posicionar los nodos
            barnesHut:{
                gravitationalConstant:-2500,
                centralGravity:.26,
                springLength:170,
                springConstant:.028,
                damping:.1
            },
            stabilization:{ iterations:1200, updateInterval:50, fit: true }
        },
        interaction:{ hover:true, dragNodes:true, zoomView:true, tooltipDelay:80, hoverConnectedEdges: true, multiselect: true },
        layout:{ improvedLayout:true } // Usar un layout mejorado
    };

    // Creamos la nueva instancia de la red vis.js
    sociogramaNetwork = new vis.Network(cont, { nodes, edges }, options);

    // Desactivar física una vez que la red ha terminado de estabilizarse para que no se muevan los nodos
    sociogramaNetwork.once('stabilizationIterationsDone',
        () => sociogramaNetwork.setOptions({ physics:false }));

    /* tooltips */
    // Lógica para mostrar tooltips personalizados al pasar el ratón sobre nodos o enlaces
    let tip=null, timer=null;
    const show = (x,y,h) => {
        tip?.remove(); // Elimina el tooltip anterior si existe
        tip = document.createElement('div'); // Crea un nuevo div para el tooltip
        tip.className = 'sociograma-tooltip'; // Asigna una clase CSS
        tip.innerHTML = h; // Establece el contenido HTML del tooltip
        tip.style.top  = `${y+10}px`; // Posiciona el tooltip (con un pequeño desplazamiento)
        tip.style.left = `${x+10}px`;
        document.body.appendChild(tip); // Añade el tooltip al cuerpo del documento
    };
    // Función para ocultar el tooltip
    const hide = () => { clearTimeout(timer); tip?.remove(); tip=null; };

    // Evento al pasar el ratón sobre un nodo
    sociogramaNetwork.on('hoverNode', ({ node,event }) => {
        clearTimeout(timer); // Limpia el temporizador anterior
        // Establece un nuevo temporizador para mostrar el tooltip después de un breve retardo
        timer = setTimeout(() => {
            const n = nodes.get(node); // Obtiene los datos del nodo de vis.js DataSet
            show(event.clientX, event.clientY, `
                <strong>${n.title}</strong><br> // Muestra el título (nombre del alumno)
                Pref. recibidas: ${n.metricas?.preferencias_recibidas ?? 0}<br> // Muestra métricas
                Rech. recibidos : ${n.metricas?.rechazos_recibidos ?? 0}`);
        }, 80); // Retardo de 80ms
    });
    // Evento al dejar de pasar el ratón sobre un nodo
    sociogramaNetwork.on('blurNode', hide);

    // Evento al pasar el ratón sobre un enlace
    sociogramaNetwork.on('hoverEdge', ({ edge,event }) => {
        clearTimeout(timer); // Limpia el temporizador anterior
        // Establece un nuevo temporizador para mostrar el tooltip después de un breve retardo
        timer = setTimeout(() => {
            const e = edges.get(edge), // Obtiene los datos del enlace de vis.js DataSet
                  a = nodes.get(e.from).title, // Obtiene el título del nodo de origen
                  b = nodes.get(e.to).title, // Obtiene el título del nodo de destino
                  txt = e.isMatch ? 'tiene match mutuo con' : (e.tipo === 'preferido' ? 'prefiere a' : 'rechaza a');
            show(event.clientX,event.clientY, `${a} ${txt} ${b}`);
        }, 80); // Retardo de 80ms
    });
    // Evento al dejar de pasar el ratón sobre un enlace
    sociogramaNetwork.on('blurEdge', hide);
    // Evento antes de destruir la red (para ocultar el tooltip si está visible)
    sociogramaNetwork.on('beforeDestroy', hide);

    sociogramaNetwork.on('selectNode', ({ nodes: selected }) => {
        const selectedId = selected?.[0];
        if (!selectedId) return;

        const connectedNodeIds = sociogramaNetwork.getConnectedNodes(selectedId);
        const related = new Set([selectedId, ...connectedNodeIds]);

        nodes.forEach((n) => {
            const isRelated = related.has(n.id);
            nodes.update({
                id: n.id,
                opacity: isRelated ? 1 : 0.28,
                font: {
                    ...n.font,
                    color: isRelated ? '#111827' : '#9ca3af'
                }
            });
        });

        edges.forEach((e) => {
            const isRelated = related.has(e.from) && related.has(e.to);
            edges.update({
                id: e.id,
                hidden: false,
                color: {
                    ...e.color,
                    opacity: isRelated ? 1 : 0.08
                }
            });
        });
    });

    sociogramaNetwork.on('deselectNode', () => {
        nodes.forEach((n) => {
            nodes.update({
                id: n.id,
                opacity: 1,
                font: {
                    ...n.font,
                    color: '#1f2937'
                }
            });
        });

        edges.forEach((e) => {
            edges.update({
                id: e.id,
                color: {
                    ...e.color,
                    opacity: e.isMatch ? 1 : 0.6
                }
            });
        });
    });

}

/* ======================================================================
   FUNCIÓN PRINCIPAL (Livewire → JS)
   =====================================================================*/
// Esta función se llama desde Livewire con los datos del análisis
function renderizarSociograma (payload) {
    // Manejamos payloads que son un array de un elemento o el objeto directamente
    const data = Array.isArray(payload) ? payload[0] : payload;

    console.log('Datos recibidos en renderizarSociograma:', data);

    // Verificamos si los datos esenciales (nodos, enlaces, preguntas) están presentes
    if (!data?.nodes || !data?.links || !data?.preguntas) {
        // Si faltan datos, destruimos la red existente y mostramos un mensaje
        sociogramaNetwork?.destroy();
        sociogramaNetwork = null;
        const sociogramaContainer = document.getElementById('sociograma');
        if(sociogramaContainer){ // Aseguramos que el contenedor exista antes de poner el mensaje
            sociogramaContainer.innerHTML =
                '<div class="alert alert-info modern-alert">No hay datos suficientes.</div>';
        } else {
            console.warn('#sociograma no encontrado para mostrar mensaje de "no hay datos".');
        }
        return; // Salimos de la función
    }

    // Cargamos la librería vis-network si no está ya cargada
    cargarVisJS().then(() => {
        // Guardamos los datos originales recibidos en las variables globales
        _origNodes = data.nodes ?? [];
        _origLinks = data.links ?? [];
        _origPreguntas = data.preguntas ?? []; // <-- AÑADIDO: Guardamos las preguntas originales

        console.log('Estructura de _origLinks:', _origLinks);
        console.log('Estructura de _origPreguntas:', _origPreguntas);

        // Aseguramos que la barra de herramientas esté construida y llenada,
        // y luego aplicamos los filtros iniciales para dibujar el sociograma.
        ensureToolbar();

    // Capturamos cualquier error durante la carga de vis-network
    }).catch(error => {
        console.error("Error al cargar vis-network:", error);
        // Mostramos un mensaje de error en el contenedor del sociograma
        const sociogramaContainer = document.getElementById('sociograma');
        if(sociogramaContainer){
            sociogramaContainer.innerHTML = '<div class="alert alert-danger">Error al cargar la visualizacion.</div>';
        }
    });
}

/* ======================================================================
   LIVEWIRE LISTENERS
   =====================================================================*/
// Escuchamos el evento 'livewire:init' para asegurarnos de que Livewire está listo
document.addEventListener('livewire:init', () => {

    // Listener para actualizar el gráfico de preferencias
    Livewire.on('actualizarGraficoPreferencias', d => {
        d = Array.isArray(d) && d.length ? d[0] : d; // Manejamos posible array en payload
        if (d?.labels && d?.data) renderBar('graficoPreferencias', d.labels, d.data, false); // Llamamos a la función de renderizado
    });

    // Listener para actualizar el gráfico de rechazos
    Livewire.on('actualizarGraficoRechazos', d => {
        d = Array.isArray(d) && d.length ? d[0] : d; // Manejamos posible array en payload
        if (d?.labels && d?.data) renderBar('graficoRechazos', d.labels, d.data, true); // Llamamos a la función de renderizado
    });

    // Listener principal para actualizar el sociograma con nuevos datos
    Livewire.on('actualizarSociograma', renderizarSociograma);

    // Listener para actualizar la matriz de reciprocidad
    Livewire.on('actualizarMatrizReciprocidad', d => {
        d = Array.isArray(d) && d.length ? d[0] : d; // Manejamos posible array en payload
        if (d?.labels && d?.data) renderMatrixWithRetry(d.labels, d.data); // Llamamos a la función de renderizado
    });
});

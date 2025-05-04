// ──────────────────────────────────────────────────────────────────────
//  VARS GLOBALES
// ──────────────────────────────────────────────────────────────────────
let chartPref = null,
    chartRech = null,
    sociogramaNetwork = null;

// ──────────────────────────────────────────────────────────────────────
//  CARGAR vis-network DINÁMICAMENTE
// ──────────────────────────────────────────────────────────────────────
function cargarVisJS () {
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
//  GRÁFICAS DE BARRAS (Chart.js)
// ──────────────────────────────────────────────────────────────────────
function renderBar (canvasId, labels, data, esRechazos, intento = 0) {
    const canvas = document.getElementById(canvasId);
    if (!canvas) {
        if (intento < 10)
            return setTimeout(() => renderBar(canvasId, labels, data, esRechazos, intento + 1), 100);
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
                backgroundColor: esRechazos ? 'rgba(220, 53, 69, 0.7)' : 'rgba(0, 123, 255, 0.7)',
                borderColor:      esRechazos ? 'rgba(220, 53, 69, 1)'  : 'rgba(0, 123, 255, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: { y: { beginAtZero: true, ticks: { precision: 0 } } },
            plugins: { legend: { display: true, labels: { font: { family: 'Poppins' } } } }
        }
    });
}
// ──────────────────────────────────────────────────────────────────────
//  HEATMAP – MATRIZ DE RECIPROCIDAD
// ──────────────────────────────────────────────────────────────────────
function renderMatrixWithRetry (labels, data, intento = 0) {
    const canvas = document.getElementById('heatmapReciprocidad');

    if (!canvas) {
        if (intento < 10) {
            console.warn(`⚠️ Intento ${intento + 1}: No se encontró #heatmapReciprocidad. Reintentando…`);
            return setTimeout(() => renderMatrixWithRetry(labels, data, intento + 1), 100);
        }
        console.error('❌ #heatmapReciprocidad no se encontró tras 10 intentos');
        return;
    }

    // Validar entrada
    if (!Array.isArray(labels) || !labels.length || !Array.isArray(data) || !data.length) {
        const ctx = canvas.getContext('2d');
        const prev = Chart.getChart(canvas); if (prev) prev.destroy();
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        ctx.font = '1rem Poppins';
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        ctx.fillStyle = '#6c757d';
        ctx.fillText('No hay datos suficientes para la matriz.', canvas.width / 2, canvas.height / 2);
        return;
    }

    // Registrar plugin matrix (solo 1 vez)
    if (!Chart.controllers.matrix) {
        Chart.register(
            ChartMatrix.Controller, ChartMatrix.Element,
            ChartMatrix.Scales.CategoryScale, ChartMatrix.Scales.LinearScale,
            Chart.Tooltip, Chart.Legend
        );
    }

    const ctx   = canvas.getContext('2d');
    const prev  = Chart.getChart(canvas); if (prev) prev.destroy();

    ctx._chart = new Chart(ctx, {
        type : 'matrix',
        data : {
            labels,
            datasets: [{
                label: 'Reciprocidad',
                data,
                width : ({ chart }) => chart.width  / labels.length - 2,
                height: ({ chart }) => chart.height / labels.length - 2,
                borderWidth : 1,
                borderColor : '#fff',
                // backgroundColor: ({ raw }) => {
                //     const v = raw?.v ?? 0;
                //     return ({
                //         4: 'rgba(76, 175, 80, 0.7)',   // Preferencia mutua
                //         3: 'rgba(220, 53, 69, 0.7)',   // Rechazo mutuo
                //         2: 'rgba(33, 150, 243, 0.7)',  // Preferencia uni
                //         1: 'rgba(255, 152, 0, 0.7)',   // Rechazo uni
                //         5: 'rgba(156, 39, 176, 0.7)'   // Conflicto
                //     })[v] || 'rgba(238,238,238,0.7)';  // Sin relación / diagonal
                // }
                backgroundColor: ctx => ctx.raw.v ? 'rgba(0,123,255,.6)' : 'rgba(238,238,238,.3)'

            }]
        },
        options: {
            maintainAspectRatio: false,
            scales: {
                x: { type:'category', labels, offset:true, grid:{display:false},
                     position:'top', ticks:{ font:{family:'Poppins'}, autoSkip:false, maxRotation:90 } },
                     y: {
                        type:'category',
                        labels,           // mismo array en el mismo orden
                        offset:true,
                        grid:{display:false},
                        ticks:{ font:{family:'Poppins'}, autoSkip:false }
                    }
                    
            },
            plugins: {
                tooltip: {
                    mode:'nearest', intersect:false, displayColors:false,
                    callbacks:{
                        title: ()=>' ',
                        label: ({ parsed, raw }) => {
                            const i = parsed?.y ?? 0, j = parsed?.x ?? 0, v = raw?.v ?? 0;
                            const estado = {
                                4:'Preferencia Mutua', 3:'Rechazo Mutuo',
                                2:'Preferencia Unidireccional', 1:'Rechazo Unidireccional',
                                5:'Conflicto (Pref vs Rech)'
                            }[v] || (i===j ? 'Mismo estudiante' : 'No hay relación');
                            return `${labels[i]} ↔ ${labels[j]} : ${estado}`;
                        }
                    }
                },
                legend:{ display:false }
            }
        }
    });
}
// ──────────────────────────────────────────────────────────────────────
//  SOCIOGRAMA (vis-network + tooltip custom)
// ──────────────────────────────────────────────────────────────────────
function renderizarSociograma (payload) {
    const data = Array.isArray(payload) ? payload[0] : payload;

    if (!data?.nodes || !data?.links) {
        if (window.sociogramaNetwork) { sociogramaNetwork.destroy(); sociogramaNetwork = null; }
        document.getElementById('sociograma').innerHTML =
            '<div class=\"alert alert-info modern-alert\">No hay datos suficientes para mostrar el sociograma.</div>';
        return console.error('❌ Datos sociograma mal formados o vacíos:', payload);
    }

    cargarVisJS().then(() => {
        const cont = document.getElementById('sociograma');
        if (!cont) return console.warn('⚠️ Contenedor sociograma no encontrado');

        if (window.sociogramaNetwork) sociogramaNetwork.destroy();

        const nodes = new vis.DataSet(
            data.nodes.map(n => ({
                id: n.id,
                label: '',
                group: (n.metricas?.popularidad >= 0.7) ? 'popular'
                     : (n.metricas?.popularidad >= 0.4) ? 'neutral'
                     : (n.metricas?.popularidad >= 0.2) ? 'unpopular'
                     : 'isolated_rejected',
                value: 1 + (n.metricas?.preferencias_recibidas ?? 0) + (n.metricas?.rechazos_recibidos ?? 0),
                title: n.label,
                metricas: n.metricas
            }))
        );

        const edges = new vis.DataSet(
            data.links.map(l => ({
                from: l.source, to: l.target,
                color : { color: l.tipo_relacion === 'preferido' ? '#28a745' : '#dc3545', opacity: 0.7 },
                width : 1.5,
                arrows: { to: { enabled: true, scaleFactor: 0.4 } }
            }))
        );

        const options = {
            nodes: {
                shape:'circle', size:20, borderWidth:0, font:{size:0},
                shadow:{ enabled:true, color:'rgba(0,0,0,0.1)', size:3 }
            },
            edges: {
                smooth:{ enabled:true, type:'continuous', roundness:0.5 },
                shadow:{ enabled:true, color:'rgba(0,0,0,0.1)', size:2 },
                color:{ inherit:'from' }, width:1.5
            },
            groups:{
                popular:{  color:{ background:'#4CAF50' } },
                neutral:{  color:{ background:'#2196F3' } },
                unpopular:{color:{ background:'#FFC107' } },
                isolated_rejected:{ color:{ background:'#F44336' } }
            },
            physics:{
                enabled:true,
                barnesHut:{ gravitationalConstant:-3000, centralGravity:0.4,
                            springLength:120, springConstant:0.04, damping:0.09, avoidOverlap:1 },
                stabilization:{ iterations:1000, updateInterval:50, fit:true }
            },
            interaction:{ hover:true, dragNodes:true, zoomView:true, tooltipDelay:100 },
            layout:{ improvedLayout:true }
        };

        sociogramaNetwork = new vis.Network(cont, { nodes, edges }, options);

        // parar físicas tras estabilizar
        sociogramaNetwork.once('stabilizationIterationsDone', () =>
            sociogramaNetwork.setOptions({ physics:false })
        );

        // Tool-tips personalizados
        let tooltipDiv=null,timer=null;
        const showTip = (x,y,html)=>{
            if(tooltipDiv) tooltipDiv.remove();
            tooltipDiv = document.createElement('div');
            tooltipDiv.className='sociograma-tooltip';
            tooltipDiv.innerHTML=html;
            tooltipDiv.style.top=`${y+10}px`;
            tooltipDiv.style.left=`${x+10}px`;
            document.body.appendChild(tooltipDiv);
        };
        const hideTip = ()=>{ clearTimeout(timer); if(tooltipDiv) tooltipDiv.remove(); tooltipDiv=null; };

        sociogramaNetwork.on('hoverNode', ({node,event})=>{
            clearTimeout(timer);
            timer=setTimeout(()=>{
                const n = nodes.get(node);
                const h = `<strong>${n.title}</strong><br>
                           Pref. recibidas: ${n.metricas?.preferencias_recibidas ?? 0}<br>
                           Rech. recibidos : ${n.metricas?.rechazos_recibidos ?? 0}<br>
                           Popularidad     : ${Math.round((n.metricas?.popularidad ?? 0)*100)}%`;
                showTip(event.clientX,event.clientY,h);
            },100);
        });
        sociogramaNetwork.on('blurNode', hideTip);

        sociogramaNetwork.on('hoverEdge', ({edge,event})=>{
            clearTimeout(timer);
            timer=setTimeout(()=>{
                const e = edges.get(edge),
                      a = nodes.get(e.from).title,
                      b = nodes.get(e.to).title,
                      txt = e.color.color==='#28a745'? 'prefiere a':'rechaza a';
                showTip(event.clientX,event.clientY,`${a} ${txt} ${b}`);
            },100);
        });
        sociogramaNetwork.on('blurEdge', hideTip);
        sociogramaNetwork.on('beforeDestroy', hideTip);
    });
}
// ──────────────────────────────────────────────────────────────────────
//  LISTENERS LIVEWIRE
// ──────────────────────────────────────────────────────────────────────
document.addEventListener('livewire:init', () => {

    Livewire.on('actualizarGraficoPreferencias', payload => {
        const p = (Array.isArray(payload) && payload.length) ? payload[0] : payload;
        if (p?.labels && p?.data) renderBar('graficoPreferencias', p.labels, p.data, false);
    });

    Livewire.on('actualizarGraficoRechazos', payload => {
        const p = (Array.isArray(payload) && payload.length) ? payload[0] : payload;
        if (p?.labels && p?.data) renderBar('graficoRechazos', p.labels, p.data, true);
    });

    Livewire.on('actualizarSociograma', payload => {
        renderizarSociograma(payload);
    });

    Livewire.on('actualizarMatrizReciprocidad', payload => {
        const p = (Array.isArray(payload) && payload.length) ? payload[0] : payload;
    
        /* DEBUG */
        window._matrizDebug = p;          //  ←  ahora lo podremos inspeccionar siempre
        console.groupCollapsed('[DEBUG] Payload Matriz de reciprocidad');
        console.log('labels →', p.labels);
        console.log('data   →', p.data);
        console.table(p.data.filter(c => c.v));
        console.groupEnd();
    
        if (p?.labels && p?.data) renderMatrixWithRetry(p.labels, p.data);
    });
    
});

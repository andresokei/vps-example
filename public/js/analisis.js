/* ======================================================================
   VARIABLES GLOBALES
   =====================================================================*/
let chartPref = null,
    chartRech = null,
    sociogramaNetwork = null;

let _origNodes = [],
    _origLinks = [];

/* ======================================================================
   CARGAR vis‑network DINÁMICAMENTE
   =====================================================================*/
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

/* ======================================================================
   GRÁFICAS DE BARRAS (Chart.js)
   =====================================================================*/
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
                backgroundColor: esRechazos ? 'rgba(220,53,69,.7)' : 'rgba(0,123,255,.7)',
                borderColor    : esRechazos ? 'rgba(220,53,69,1)'  : 'rgba(0,123,255,1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: { y: { beginAtZero: true, ticks: { precision: 0 } } },
            plugins: { legend: { labels: { font: { family: 'Poppins' } } } }
        }
    });
}

/* ======================================================================
   HEATMAP  (Chart.js Matrix)
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
        ctx.font='1rem Poppins'; ctx.fillStyle='#6c757d';
        ctx.textAlign='center';   ctx.textBaseline='middle';
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
                width : ({chart})=>chart.width/labels.length-2,
                height: ({chart})=>chart.height/labels.length-2,
                borderWidth:1,
                borderColor:'#fff',
                backgroundColor:c=>c.raw.v ? 'rgba(0,123,255,.6)' : 'rgba(238,238,238,.3)'
            }]
        },
        options:{
            maintainAspectRatio:false,
            scales:{
                x:{type:'category',labels,offset:true,grid:{display:false},
                   position:'top',ticks:{font:{family:'Poppins'},autoSkip:false,maxRotation:90}},
                y:{type:'category',labels,offset:true,grid:{display:false},
                   ticks:{font:{family:'Poppins'},autoSkip:false}}
            },
            plugins:{legend:{display:false}}
        }
    });
}

/* ======================================================================
   SOCIOGRAMA  +  FILTROS
   =====================================================================*/
/* =========================================================
   SOCIOGRAMA – barra de filtros
   =========================================================*/
function buildToolbar () {

    /* 1) contenedor fijo dentro de la tarjeta ------------ */
    const wrapper = document.getElementById('soc-wrapper');
    if (!wrapper) { console.warn('#soc-wrapper no encontrado'); return; }

    /* 2) inyectar la barra solo la primera vez ----------- */
    if (!wrapper.querySelector('#barra-socio')) {
        wrapper.insertAdjacentHTML('afterbegin', `
          <div id="barra-socio" class="socio-wrapper d-flex">
              <div class="socio-controls flex-shrink-0 pe-4">

                  <label class="socio-label fw-semibold small mb-1">
                      Filtrar alumno(s)
                  </label>
                  <select id="soc-select"
                          class="form-select form-select-sm mb-3" multiple></select>

                  <div class="form-check form-switch mb-2 small">
                      <input id="soc-onlyMatch" class="form-check-input" type="checkbox">
                      <label class="form-check-label ms-2" for="soc-onlyMatch">
                          Solo matches
                      </label>
                  </div>

                  <div class="form-check form-switch mb-3 small">
                      <input id="soc-markIsol" class="form-check-input" type="checkbox" checked>
                      <label class="form-check-label ms-2" for="soc-markIsol">
                          Marcar no‑elegidos
                      </label>
                  </div>

                  <button id="soc-reset"
                          class="btn btn-outline-primary btn-sm w-100">
                      Limpiar
                  </button>
              </div>
          </div>`);
    }

    /* 3) punteros a los controles ------------------------ */
    const sel         = wrapper.querySelector('#soc-select');
    const toggleMatch = wrapper.querySelector('#soc-onlyMatch');
    const toggleIsol  = wrapper.querySelector('#soc-markIsol');

    /* 4) rellenar selector ------------------------------ */
    sel.innerHTML = '';
    [..._origNodes].sort((a, b) => a.label.localeCompare(b.label))
                   .forEach(n => sel.insertAdjacentHTML(
                       'beforeend', `<option value="${n.id}">${n.label}</option>`));
    sel.selectedIndex = -1;

    /* 5) listeners (solo la primera vez) ---------------- */
    if (!sel.dataset.ready) {
        sel.addEventListener('change', applyFilters);
        toggleMatch.addEventListener('change', applyFilters);
        toggleIsol.addEventListener('change', applyFilters);
        wrapper.querySelector('#soc-reset').addEventListener('click', () => {
            sel.selectedIndex = -1;
            toggleMatch.checked = false;
            toggleIsol.checked  = true;
            applyFilters();
        });
        sel.dataset.ready = '1';
    }
}


function applyFilters () {
    const sel      = document.getElementById('soc-select');
    const onlyM    = document.getElementById('soc-onlyMatch').checked;
    const markIso  = document.getElementById('soc-markIsol').checked;
    const chosen   = [...sel.selectedOptions].map(o=>+o.value);

    const links = _origLinks.filter(e =>
        (!onlyM || e.isMatch) &&
        (chosen.length===0 || chosen.includes(e.source) || chosen.includes(e.target))
    );

    const visible = new Set();
    links.forEach(e=>{visible.add(e.source);visible.add(e.target);});
    chosen.forEach(id=>visible.add(id));

    const nodes = _origNodes
        .filter(n=>chosen.length===0 || visible.has(n.id))
        .map(n=>({...n, isIsolate: markIso ? n.isIsolate : false}));

    drawSociograma(nodes, links);
}

function drawSociograma (nodesData, linksData) {
    const cont = document.getElementById('sociograma');
    if (!cont) return;

    sociogramaNetwork?.destroy();   // limpia

    /* DataSets */
    const nodes = new vis.DataSet(nodesData.map(n=>({
        id:n.id,
        label:'',
        group:n.isIsolate?'isolated':'default',
        value:1+(n.metricas?.preferencias_recibidas??0)+(n.metricas?.rechazos_recibidos??0),
        title:n.label,
        metricas:n.metricas
    })));

    const edges = new vis.DataSet(linksData.map(l=>({
        from:l.source, to:l.target,
        color :{
            color : l.isMatch ? '#00b050'
                  : l.tipo==='preferido' ? '#28a745' : '#dc3545',
            opacity: l.isMatch ? 1 : .6
        },
        width : l.isMatch?3:1.5,
        dashes: l.isMatch?false:[6,4],
        arrows:{to:{enabled:true,scaleFactor:.4}}
    })));

    sociogramaNetwork = new vis.Network(cont, {nodes,edges},{
        nodes :{shape:'circle',size:20,borderWidth:0,font:{size:0},
                shadow:{enabled:true,color:'rgba(0,0,0,.1)',size:3}},
        edges :{smooth:{enabled:true,type:'continuous',roundness:.45},
                shadow:{enabled:true,color:'rgba(0,0,0,.1)',size:2}},
        groups:{default:{color:{background:'#2196F3'}},
                isolated:{color:{background:'#F44336'}}},
        physics:{enabled:true,barnesHut:{gravitationalConstant:-2800,
                centralGravity:.3,springLength:140,springConstant:.03,damping:.09},
                stabilization:{iterations:1000,updateInterval:50}},
        interaction:{hover:true,dragNodes:true,zoomView:true,tooltipDelay:80},
        layout:{improvedLayout:true}
    });

    sociogramaNetwork.once('stabilizationIterationsDone',
        ()=>sociogramaNetwork.setOptions({physics:false}));

    /* tooltips */
    let tip=null,timer=null;
    const show=(x,y,h)=>{
        tip?.remove();
        tip=document.createElement('div');
        tip.className='sociograma-tooltip';
        tip.innerHTML=h; tip.style.top=`${y+10}px`; tip.style.left=`${x+10}px`;
        document.body.appendChild(tip);
    };
    const hide=()=>{clearTimeout(timer); tip?.remove(); tip=null;};

    sociogramaNetwork.on('hoverNode',({node,event})=>{
        clearTimeout(timer);
        timer=setTimeout(()=>{
            const n=nodes.get(node);
            show(event.clientX,event.clientY,
                 `<strong>${n.title}</strong><br>
                  Pref. recibidas: ${n.metricas?.preferencias_recibidas??0}<br>
                  Rech. recibidos : ${n.metricas?.rechazos_recibidos??0}`);
        },80);
    });
    sociogramaNetwork.on('blurNode',hide);

    sociogramaNetwork.on('hoverEdge',({edge,event})=>{
        clearTimeout(timer);
        timer=setTimeout(()=>{
            const e=edges.get(edge),
                  a=nodes.get(e.from).title,
                  b=nodes.get(e.to).title,
                  txt=e.color.color==='#28a745'?'prefiere a':'rechaza a';
            show(event.clientX,event.clientY,`${a} ${txt} ${b}`);
        },80);
    });
    sociogramaNetwork.on('blurEdge',hide);
    sociogramaNetwork.on('beforeDestroy',hide);
}

/* ======================================================================
   FUNCIÓN PRINCIPAL (Livewire → JS)
   =====================================================================*/
function renderizarSociograma (payload) {
    const data = Array.isArray(payload)?payload[0]:payload;
    if (!data?.nodes || !data?.links) {
        sociogramaNetwork?.destroy(); sociogramaNetwork=null;
        document.getElementById('sociograma').innerHTML =
            '<div class="alert alert-info modern-alert">No hay datos suficientes.</div>';
        return;
    }
    cargarVisJS().then(()=>{
        _origNodes = data.nodes;
        _origLinks = data.links;
        buildToolbar();
        applyFilters();
    });
}

/* ======================================================================
   LIVEWIRE LISTENERS
   =====================================================================*/
document.addEventListener('livewire:init',()=>{
    Livewire.on('actualizarGraficoPreferencias',d=>{
        d=Array.isArray(d)&&d.length?d[0]:d;
        if (d?.labels&&d?.data) renderBar('graficoPreferencias',d.labels,d.data,false);
    });
    Livewire.on('actualizarGraficoRechazos',d=>{
        d=Array.isArray(d)&&d.length?d[0]:d;
        if (d?.labels&&d?.data) renderBar('graficoRechazos',d.labels,d.data,true);
    });
    Livewire.on('actualizarSociograma',renderizarSociograma);
    Livewire.on('actualizarMatrizReciprocidad',d=>{
        d=Array.isArray(d)&&d.length?d[0]:d;
        if (d?.labels&&d?.data) renderMatrixWithRetry(d.labels,d.data);
    });
});

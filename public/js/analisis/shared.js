(function () {
    const analysis = window.Analisis = window.Analisis || {};

    analysis.state = analysis.state || {
        chartPref: null,
        chartRech: null,
        sociogramaNetwork: null,
        origNodes: [],
        origLinks: [],
        origPreguntas: [],
        centralityMap: {},
        linkIndex: {},
        nodeMap: {},
        incomingRelationsByTarget: {},
        roleById: {},
        heatmapPending: null,
        studentModal: null,
    };

    const state = analysis.state;

    const HEAT_COLORS = {
        0: 'rgba(240,240,240,0.4)',
        1: 'rgba(220,53,69,0.30)',
        2: 'rgba(40,167,69,0.35)',
        3: 'rgba(220,53,69,0.85)',
        4: 'rgba(99,102,241,0.85)',
        5: 'rgba(255,153,0,0.75)',
    };

    const HEAT_LABELS = {
        0: 'Sin relacion',
        1: 'Rechazo unilateral',
        2: 'Preferencia unilateral',
        3: 'Rechazo mutuo',
        4: 'Match mutuo',
        5: 'Conflicto',
    };

    analysis.normalizeLivewirePayload = function normalizeLivewirePayload(payload) {
        return Array.isArray(payload) ? payload[0] : payload;
    };

    analysis.initBootstrapTooltips = function initBootstrapTooltips() {
        document.querySelectorAll('[data-bs-toggle="tooltip"]')
            .forEach(el => bootstrap.Tooltip.getOrCreateInstance(el));
    };

    analysis.refreshAnalysisUi = function refreshAnalysisUi() {
        analysis.initCentralidadesTable?.();
        analysis.initBootstrapTooltips();
    };

    analysis.cargarVisJS = function cargarVisJS() {
        return new Promise((ok, fail) => {
            if (window.vis) return ok();

            const css = document.createElement('link');
            css.rel = 'stylesheet';
            css.href = 'https://unpkg.com/vis-network/dist/vis-network.min.css';
            document.head.appendChild(css);

            const js = document.createElement('script');
            js.src = 'https://unpkg.com/vis-network/dist/vis-network.min.js';
            js.onload = ok;
            js.onerror = fail;
            document.head.appendChild(js);
        });
    };

    analysis.renderBar = function renderBar(canvasId, labels, data, esRechazos, intento = 0) {
        const canvas = document.getElementById(canvasId);

        if (!canvas) {
            if (intento < 10) {
                return setTimeout(
                    () => analysis.renderBar(canvasId, labels, data, esRechazos, intento + 1),
                    100
                );
            }

            return console.warn(`No se encontro ${canvasId}`);
        }

        Chart.getChart(canvas)?.destroy();

        const chart = new Chart(canvas, {
            type: 'bar',
            data: {
                labels,
                datasets: [{
                    label: esRechazos ? 'Veces rechazado' : 'Veces elegido',
                    data,
                    backgroundColor: esRechazos ? 'rgba(220,53,69,.7)' : 'rgba(99,102,241,.7)',
                    borderColor: esRechazos ? 'rgba(220,53,69,1)' : 'rgba(99,102,241,1)',
                    borderWidth: 1,
                    borderRadius: 4,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: { y: { beginAtZero: true, ticks: { precision: 0 } } },
                plugins: {
                    legend: { labels: { font: { family: 'Inter' } } },
                    tooltip: {
                        callbacks: {
                            label: ctx => ` ${ctx.formattedValue} ${esRechazos ? 'rechazos' : 'elecciones'}`,
                        },
                    },
                },
            },
        });

        if (esRechazos) {
            state.chartRech = chart;
        } else {
            state.chartPref = chart;
        }
    };

    analysis.renderMatrizReciprocidad = function renderMatrizReciprocidad(labels, rawData) {
        const canvas = document.getElementById('heatmapReciprocidad');
        if (!canvas) return;

        Chart.getChart(canvas)?.destroy();

        if (!labels?.length || !rawData?.length) {
            const ctx = canvas.getContext('2d');
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            ctx.font = '14px Inter';
            ctx.fillStyle = '#6c757d';
            ctx.textAlign = 'center';
            ctx.textBaseline = 'middle';
            ctx.fillText('No hay datos suficientes.', canvas.width / 2, canvas.height / 2);
            return;
        }

        const chartData = rawData.map(d => ({
            x: labels[d.x] ?? String(d.x),
            y: labels[d.y] ?? String(d.y),
            v: d.v,
        }));

        new Chart(canvas, {
            type: 'matrix',
            data: {
                datasets: [{
                    label: 'Reciprocidad',
                    data: chartData,
                    width: ({ chart }) => (chart.chartArea?.width ?? chart.width) / labels.length - 1,
                    height: ({ chart }) => (chart.chartArea?.height ?? chart.height) / labels.length - 1,
                    borderWidth: 1,
                    borderColor: '#fff',
                    backgroundColor: ctx => HEAT_COLORS[ctx.raw?.v ?? 0] ?? HEAT_COLORS[0],
                }],
            },
            options: {
                maintainAspectRatio: false,
                scales: {
                    x: {
                        type: 'category',
                        labels,
                        offset: true,
                        position: 'top',
                        grid: { display: false },
                        ticks: {
                            font: { family: 'Inter', size: 11 },
                            autoSkip: false,
                            maxRotation: 60,
                            color: '#495057',
                        },
                        title: {
                            display: true,
                            text: 'Destino ->',
                            font: { family: 'Inter', size: 11 },
                            color: '#6c757d',
                        },
                    },
                    y: {
                        type: 'category',
                        labels,
                        offset: true,
                        reverse: true,
                        grid: { display: false },
                        ticks: {
                            font: { family: 'Inter', size: 11 },
                            autoSkip: false,
                            color: '#495057',
                        },
                        title: {
                            display: true,
                            text: '<- Origen',
                            font: { family: 'Inter', size: 11 },
                            color: '#6c757d',
                        },
                    },
                },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            title: () => '',
                            label: ctx => `${ctx.raw.y} -> ${ctx.raw.x}: ${HEAT_LABELS[ctx.raw.v] ?? 'Desconocido'}`,
                        },
                    },
                },
            },
        });

        const wrapper = canvas.closest('.heatmap-wrapper') ?? canvas.parentElement;
        let legend = wrapper.querySelector('.heatmap-legend');

        if (!legend) {
            legend = document.createElement('div');
            legend.className = 'heatmap-legend d-flex flex-wrap gap-3 mt-3 justify-content-center';
            wrapper.appendChild(legend);
        }

        legend.innerHTML = Object.entries(HEAT_LABELS)
            .map(([value, label]) => `
                <span class="d-flex align-items-center gap-1 small">
                  <span style="width:14px;height:14px;border-radius:3px;display:inline-block;background:${HEAT_COLORS[value]};border:1px solid #dee2e6;"></span>
                  ${label}
                </span>
            `)
            .join('');
    };
})();

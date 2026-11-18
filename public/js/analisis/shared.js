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
        studentModal: null,
    };

    const state = analysis.state;

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

    let visReady;
    analysis.cargarVisJS = function cargarVisJS() {
        if (!visReady) {
            visReady = new Promise((resolve, reject) => {
                const script = document.createElement('script');
                script.src = '/vendor/vis-network/vis-network.min.js?v=9.1.9';
                script.onload = resolve;
                script.onerror = () => {
                    visReady = null;
                    reject(new Error('No se pudo cargar vis-network'));
                };
                document.head.appendChild(script);
            });
        }
        return visReady;
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

})();

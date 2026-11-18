(function () {
    const analysis = window.Analisis;
    if (!analysis?.state) return;

    const state = analysis.state;

    document.addEventListener('livewire:init', () => {
        Livewire.on('actualizarGraficoPreferencias', payload => {
            const data = analysis.normalizeLivewirePayload(payload);
            if (data?.labels && data?.data) {
                analysis.renderBar('graficoPreferencias', data.labels, data.data, false);
            }
        });

        Livewire.on('actualizarGraficoRechazos', payload => {
            const data = analysis.normalizeLivewirePayload(payload);
            if (data?.labels && data?.data) {
                analysis.renderBar('graficoRechazos', data.labels, data.data, true);
            }
        });

        Livewire.on('actualizarSociograma', () => {
            requestAnimationFrame(drawVisibleGraph);
            setTimeout(analysis.refreshAnalysisUi, 250);
        });

    });

    document.addEventListener('click', event => {
        const cell = event.target.closest('[data-matrix-cell]');
        if (cell) cell.closest('.reciprocity').querySelector('.matrix-detail').textContent = cell.title;
    });

    let currentContainer;
    let currentData;
    function drawVisibleGraph() {
        const container = document.getElementById('sociograma');
        const json = document.getElementById('soc-data')?.textContent;
        if (!container?.offsetWidth || !json) return;
        if (currentContainer === container && currentData === json && state.sociogramaNetwork) {
            state.sociogramaNetwork.redraw();
            state.sociogramaNetwork.fit();
            return;
        }
        currentContainer = container;
        currentData = json;
        const graph = JSON.parse(json);
        const metrics = new Map(graph.centralities.map(row => [row.id, row]));
        graph.nodes.forEach(node => { node.centrality = metrics.get(node.id); });
        analysis.renderizarSociograma(graph);
    }

    document.addEventListener('shown.bs.tab', event => {
        if (event.target.id === 'tab-sociograma-btn') {
            drawVisibleGraph();
        }

        if (event.target.id === 'tab-detalles-btn') {
            analysis.refreshAnalysisUi();
        }

    });
})();

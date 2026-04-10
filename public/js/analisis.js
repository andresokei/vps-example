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

        Livewire.on('actualizarSociograma', payload => {
            analysis.renderizarSociograma(payload);
            setTimeout(analysis.refreshAnalysisUi, 250);
        });

        Livewire.on('actualizarMatrizReciprocidad', payload => {
            const data = analysis.normalizeLivewirePayload(payload);
            if (!data?.labels || !data?.data) return;

            const canvas = document.getElementById('heatmapReciprocidad');
            if (!canvas || canvas.offsetParent === null) {
                state.heatmapPending = data;
                return;
            }

            analysis.renderMatrizReciprocidad(data.labels, data.data);
        });
    });

    document.addEventListener('shown.bs.tab', event => {
        if (event.target.id === 'tab-sociograma-btn' && state.sociogramaNetwork) {
            state.sociogramaNetwork.redraw();
            state.sociogramaNetwork.fit();
        }

        if (event.target.id === 'tab-detalles-btn') {
            analysis.refreshAnalysisUi();
        }

        if (event.target.id === 'tab-reciprocidad-btn' && state.heatmapPending) {
            analysis.renderMatrizReciprocidad(state.heatmapPending.labels, state.heatmapPending.data);
            state.heatmapPending = null;
        }
    });
})();

{{-- Sociograma --}}
<div class="card sociograma-card shadow-sm mb-4">
    <div class="card-header fw-semibold">Sociograma</div>

    {{-- Contenedor principal: barra + grafo --}}
    <div id="soc-wrapper" class="socio-wrapper gap-4 position-relative">

        {{-- Controles / sidebar --}}
        <aside id="barra-socio" class="socio-controls rounded-3 p-3">
            <label class="socio-label mb-1">Filtrar alumno(s)</label>
            <select id="soc-select" class="form-select form-select-sm mb-3" multiple></select>

            <div class="form-check form-switch mb-2 small">
                <input id="soc-onlyMatch" class="form-check-input" type="checkbox">
                <label class="form-check-label ms-2" for="soc-onlyMatch">Solo matches</label>
            </div>

            <div class="form-check form-switch mb-3 small">
                <input id="soc-markIsol" class="form-check-input" type="checkbox" checked>
                <label class="form-check-label ms-2" for="soc-markIsol">Marcar no‑elegidos</label>
            </div>

            <button id="soc-reset" class="btn btn-outline-primary w-100">Limpiar</button>
        </aside>

        {{-- Grafo --}}
        <div class="sociograma-container flex-grow-1">
            <div id="sociograma" class="w-100 h-100"></div>
        </div>
    </div>
</div>

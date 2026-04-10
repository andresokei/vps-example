(function () {
    const analysis = window.Analisis;
    if (!analysis?.state) return;

    const state = analysis.state;

    function getRoleMeta(studentId) {
        return state.roleById[studentId] ?? {
            label: 'Ninguno',
            className: 'bg-secondary',
        };
    }

    function buildRoleMap(nodes, roles = {}) {
        const roleByName = new Map();

        const registerRole = (names, label, className) => {
            names.forEach(name => {
                if (!roleByName.has(name)) {
                    roleByName.set(name, { label, className });
                }
            });
        };

        registerRole(roles.aislados ?? [], 'Aislado', 'bg-danger');
        registerRole(roles.puentes ?? [], 'Puente', 'bg-info text-dark');
        registerRole(roles.leaders ?? [], 'Lider', 'bg-warning text-dark');
        registerRole(roles.cohesivos ?? [], 'Cohesivo', 'bg-success');

        return nodes.reduce((acc, node) => {
            acc[node.id] = roleByName.get(node.label) ?? {
                label: 'Ninguno',
                className: 'bg-secondary',
            };

            return acc;
        }, {});
    }

    function buildNodeMap(nodes) {
        return nodes.reduce((acc, node) => {
            acc[node.id] = node;
            return acc;
        }, {});
    }

    function buildIncomingRelations(nodes, links) {
        const nameById = nodes.reduce((acc, node) => {
            acc[node.id] = node.label;
            return acc;
        }, {});

        return links.reduce((acc, link) => {
            const bucket = acc[link.target] ?? {
                preferidoPor: [],
                rechazadoPor: [],
            };

            const sourceName = nameById[link.source] ?? `#${link.source}`;

            if (link.tipo === 'preferido') {
                bucket.preferidoPor.push(sourceName);
            } else if (link.tipo === 'rechazado') {
                bucket.rechazadoPor.push(sourceName);
            }

            acc[link.target] = bucket;
            return acc;
        }, {});
    }

    function applySelectionHighlight(selectedId, nodes, edges) {
        const network = state.sociogramaNetwork;
        if (!network) return;

        const connected = new Set([selectedId, ...network.getConnectedNodes(selectedId)]);
        const nodeUpdates = [];
        const edgeUpdates = [];

        nodes.forEach(node => {
            nodeUpdates.push({
                id: node.id,
                opacity: connected.has(node.id) ? 1 : 0.28,
                font: { ...node.font, color: connected.has(node.id) ? '#111827' : '#9ca3af' },
            });
        });

        edges.forEach(edge => {
            edgeUpdates.push({
                id: edge.id,
                color: {
                    ...edge.color,
                    opacity: (connected.has(edge.from) && connected.has(edge.to)) ? (edge.isMatch ? 1 : 0.8) : 0.08,
                },
            });
        });

        if (nodeUpdates.length) nodes.update(nodeUpdates);
        if (edgeUpdates.length) edges.update(edgeUpdates);
    }

    function resetSelectionHighlight(nodes, edges) {
        const nodeUpdates = [];
        const edgeUpdates = [];

        nodes.forEach(node => {
            nodeUpdates.push({
                id: node.id,
                opacity: 1,
                font: { ...node.font, color: '#1f2937' },
            });
        });

        edges.forEach(edge => {
            edgeUpdates.push({
                id: edge.id,
                color: { ...edge.color, opacity: edge.isMatch ? 1 : 0.6 },
            });
        });

        if (nodeUpdates.length) nodes.update(nodeUpdates);
        if (edgeUpdates.length) edges.update(edgeUpdates);
    }

    analysis.openStudentDetail = function openStudentDetail(studentId) {
        const node = state.nodeMap[studentId];
        if (!node) return;

        const centrality = state.centralityMap[studentId] ?? {
            inDegree: 0,
            outDegree: 0,
            betweenness: 0,
            closeness: 0,
        };
        const metricas = node.metricas ?? {};
        const roleMeta = getRoleMeta(studentId);
        const incomingRelations = state.incomingRelationsByTarget[studentId] ?? {
            preferidoPor: [],
            rechazadoPor: [],
        };

        const initials = node.label
            .split(' ')
            .slice(0, 2)
            .map(word => word[0]?.toUpperCase() ?? '')
            .join('');

        document.getElementById('studentDetailModalLabel').textContent = node.label;

        const avatarEl = document.getElementById('sdm-avatar');
        avatarEl.textContent = initials;

        const badgeEl = document.getElementById('sdm-role-badge');
        badgeEl.textContent = roleMeta.label;
        badgeEl.className = `badge ${roleMeta.className}`;

        document.getElementById('sdm-metrics-row').innerHTML = [
            { icon: 'bi-hand-thumbs-up', color: 'success', label: 'Pref. recibidas', val: metricas.preferencias_recibidas ?? 0 },
            { icon: 'bi-hand-thumbs-down', color: 'danger', label: 'Rech. recibidos', val: metricas.rechazos_recibidos ?? 0 },
            { icon: 'bi-send', color: 'primary', label: 'Pref. emitidas', val: metricas.preferencias_emitidas ?? 0 },
            { icon: 'bi-send-x', color: 'warning', label: 'Rech. emitidos', val: metricas.rechazos_emitidos ?? 0 },
        ].map(item => `
            <div class="col-6 col-md-3">
              <div class="sdm-stat-card border rounded p-2 text-center">
                <i class="bi ${item.icon} text-${item.color} fs-5 mb-1 d-block"></i>
                <div class="fw-bold fs-5">${item.val}</div>
                <div class="text-muted" style="font-size:0.75rem;">${item.label}</div>
              </div>
            </div>
        `).join('');

        document.getElementById('sdm-centrality-row').innerHTML = [
            { label: 'In-Degree', val: centrality.inDegree },
            { label: 'Out-Degree', val: centrality.outDegree },
            { label: 'Betweenness', val: Number(centrality.betweenness).toFixed(2) },
            { label: 'Closeness', val: Number(centrality.closeness).toFixed(2) },
        ].map(item => `
            <div class="col-6 col-md-3">
              <div class="sdm-stat-card border rounded p-2 text-center bg-light">
                <div class="fw-bold fs-5">${item.val}</div>
                <div class="text-muted" style="font-size:0.75rem;">${item.label}</div>
              </div>
            </div>
        `).join('');

        const listHtml = (items, emptyText, className) => (
            items.length
                ? items.map(name => `<span class="badge ${className} me-1 mb-1 fw-normal">${name}</span>`).join('')
                : `<span class="text-muted" style="font-size:0.8rem;">${emptyText}</span>`
        );

        document.getElementById('sdm-relations-row').innerHTML = `
            <div class="col-md-6">
              <h6 class="fw-semibold mb-2" style="font-size:0.8rem;">
                <i class="bi bi-hand-thumbs-up-fill text-success me-1"></i>Elegido por
              </h6>
              <div>${listHtml(incomingRelations.preferidoPor, 'Nadie lo eligio', 'bg-success')}</div>
            </div>
            <div class="col-md-6">
              <h6 class="fw-semibold mb-2" style="font-size:0.8rem;">
                <i class="bi bi-hand-thumbs-down-fill text-danger me-1"></i>Rechazado por
              </h6>
              <div>${listHtml(incomingRelations.rechazadoPor, 'Nadie lo rechazo', 'bg-danger')}</div>
            </div>
        `;

        if (!state.studentModal) {
            const modalElement = document.getElementById('studentDetailModal');
            if (modalElement) {
                state.studentModal = new bootstrap.Modal(modalElement);
            }
        }

        state.studentModal?.show();
    };

    analysis.initCentralidadesTable = function initCentralidadesTable() {
        const table = document.getElementById('centralidades-table');
        if (!table) return;

        const tbody = table.querySelector('tbody');
        const rows = Array.from(tbody.querySelectorAll('tr[data-student-id]'));
        if (!rows.length) return;

        const rowData = rows.map(row => ({
            tr: row,
            studentId: parseInt(row.dataset.studentId, 10),
            inDeg: parseInt(row.dataset.inDegree ?? '0', 10),
            outDeg: parseInt(row.dataset.outDegree ?? '0', 10),
            btwn: parseFloat(row.dataset.betweenness ?? '0'),
        }));

        const sortedByIn = [...rowData].sort((a, b) => b.inDeg - a.inDeg);
        const sortedByBtn = [...rowData].sort((a, b) => b.btwn - a.btwn);
        const top3Ids = sortedByIn.slice(0, 3).filter(row => row.inDeg > 0).map(row => row.studentId);
        const topBridgeId = sortedByBtn[0]?.btwn > 0 ? sortedByBtn[0].studentId : null;
        const maxInDeg = sortedByIn[0]?.inDeg ?? 1;
        const maxOutDeg = Math.max(...rowData.map(row => row.outDeg), 1);

        rowData.forEach(({ tr, inDeg, outDeg, studentId }) => {
            const isIsolated = inDeg === 0 && outDeg === 0;
            tr.classList.remove('table-row-leader', 'table-row-bridge', 'table-row-isolated');

            if (isIsolated) {
                tr.classList.add('table-row-isolated');
            } else if (studentId === topBridgeId) {
                tr.classList.add('table-row-bridge');
            } else if (top3Ids.includes(studentId)) {
                tr.classList.add('table-row-leader');
            }

            const inFill = tr.querySelector('.indegree-bar-fill');
            const outFill = tr.querySelector('.outdegree-bar-fill');

            if (inFill) inFill.style.width = `${Math.round((inDeg / maxInDeg) * 100)}%`;
            if (outFill) outFill.style.width = `${Math.round((outDeg / maxOutDeg) * 100)}%`;

            tr.style.cursor = 'pointer';
            tr.onclick = () => analysis.openStudentDetail(studentId);
        });

        if (table.dataset.sortReady) return;
        table.dataset.sortReady = '1';

        let sortCol = null;
        let sortAsc = true;

        table.querySelectorAll('thead th[data-sort]').forEach(th => {
            th.addEventListener('click', () => {
                const col = th.dataset.sort;
                sortAsc = sortCol === col ? !sortAsc : true;
                sortCol = col;

                table.querySelectorAll('thead th[data-sort]')
                    .forEach(header => header.classList.remove('sort-asc', 'sort-desc'));

                th.classList.add(sortAsc ? 'sort-asc' : 'sort-desc');

                const factor = sortAsc ? 1 : -1;
                const sorted = Array.from(tbody.querySelectorAll('tr[data-student-id]')).sort((a, b) => {
                    const key = col;
                    const av = a.dataset[key] ?? '0';
                    const bv = b.dataset[key] ?? '0';
                    const an = parseFloat(av);
                    const bn = parseFloat(bv);

                    if (!Number.isNaN(an) && !Number.isNaN(bn)) {
                        return (an - bn) * factor;
                    }

                    return av.localeCompare(bv) * factor;
                });

                sorted.forEach(row => tbody.appendChild(row));
            });
        });
    };

    analysis.buildToolbar = function buildToolbar() {
        const wrapper = document.getElementById('soc-wrapper');
        if (!wrapper) return;

        const selAlumnos = wrapper.querySelector('#soc-select');
        const preguntasContainer = wrapper.querySelector('#soc-preguntas-filter-options');
        const toggleMatch = wrapper.querySelector('#soc-onlyMatch');
        const toggleIsol = wrapper.querySelector('#soc-markIsol');
        const resetButton = wrapper.querySelector('#soc-reset');
        const selectAllPreguntasButton = wrapper.querySelector('#select-all-preguntas');

        if (!selAlumnos || !preguntasContainer || !toggleMatch || !toggleIsol || !resetButton) return;

        selAlumnos.innerHTML = '';
        [...state.origNodes]
            .sort((a, b) => a.label.localeCompare(b.label))
            .forEach(node => selAlumnos.insertAdjacentHTML('beforeend', `<option value="${node.id}">${node.label}</option>`));

        selAlumnos.selectedIndex = -1;

        if (wrapper.dataset.listenersReady) return;
        wrapper.dataset.listenersReady = '1';

        selAlumnos.addEventListener('change', analysis.applyFilters);
        preguntasContainer.querySelectorAll('input[type="checkbox"]')
            .forEach(cb => cb.addEventListener('change', analysis.applyFilters));

        if (selectAllPreguntasButton) {
            selectAllPreguntasButton.addEventListener('click', event => {
                event.preventDefault();
                const checkboxes = [...preguntasContainer.querySelectorAll('input[type="checkbox"]')];
                const allChecked = checkboxes.every(cb => cb.checked);
                checkboxes.forEach(cb => {
                    cb.checked = !allChecked;
                });
                analysis.applyFilters();
            });
        }

        toggleMatch.addEventListener('change', analysis.applyFilters);
        toggleIsol.addEventListener('change', analysis.applyFilters);

        resetButton.addEventListener('click', () => {
            [...selAlumnos.options].forEach(option => {
                option.selected = false;
            });
            preguntasContainer.querySelectorAll('input[type="checkbox"]').forEach(cb => {
                cb.checked = false;
            });
            toggleMatch.checked = false;
            toggleIsol.checked = true;
            analysis.applyFilters();
        });
    };

    analysis.ensureToolbar = function ensureToolbar(intento = 0) {
        if (!document.getElementById('soc-wrapper')) {
            if (intento < 10) return setTimeout(() => analysis.ensureToolbar(intento + 1), 80);
            return;
        }

        analysis.buildToolbar();
        analysis.applyFilters();
    };

    analysis.applyFilters = function applyFilters() {
        if (!state.origLinks.length) {
            analysis.drawSociograma(state.origNodes, []);
            return;
        }

        const selAlumnos = document.getElementById('soc-select');
        const preguntasContainer = document.getElementById('soc-preguntas-filter-options');
        const toggleMatch = document.getElementById('soc-onlyMatch');
        const toggleIsol = document.getElementById('soc-markIsol');

        if (!selAlumnos || !preguntasContainer || !toggleMatch || !toggleIsol) return;

        const onlyMatch = toggleMatch.checked;
        const chosenStudents = [...selAlumnos.selectedOptions].map(option => +option.value);
        const chosenQuestions = [];

        preguntasContainer.querySelectorAll('input[type="checkbox"]').forEach(cb => {
            if (cb.id !== 'select-all-preguntas' && cb.checked) chosenQuestions.push(+cb.value);
        });

        const links = state.origLinks.filter(link =>
            (!onlyMatch || link.isMatch) &&
            (chosenStudents.length === 0 || chosenStudents.includes(link.source) || chosenStudents.includes(link.target)) &&
            (chosenQuestions.length === 0 || chosenQuestions.includes(link.pregunta_id))
        );

        const visibleIds = new Set();
        links.forEach(link => {
            visibleIds.add(link.source);
            visibleIds.add(link.target);
        });
        chosenStudents.forEach(id => visibleIds.add(id));

        analysis.drawSociograma(state.origNodes.filter(node => visibleIds.has(node.id)), links);
    };

    analysis.drawSociograma = function drawSociograma(nodesData, linksData) {
        const cont = document.getElementById('sociograma');
        if (!cont) return;

        state.sociogramaNetwork?.destroy();
        state.sociogramaNetwork = null;

        if (!nodesData.length || !linksData.length) {
            cont.innerHTML = '<div class="alert alert-info m-3">No hay datos suficientes para el filtro aplicado.</div>';
            return;
        }

        cont.innerHTML = '';

        const markIsoCheckbox = document.getElementById('soc-markIsol');
        const markIsolados = markIsoCheckbox ? markIsoCheckbox.checked : true;

        const nodes = new vis.DataSet(nodesData.map(node => ({
            id: node.id,
            label: node.label,
            group: (markIsolados && (node.metricas?.preferencias_recibidas ?? 0) + (node.metricas?.rechazos_recibidos ?? 0) === 0)
                ? 'isolated'
                : 'default',
            value: 10 + (node.metricas?.preferencias_recibidas ?? 0) + (node.metricas?.rechazos_recibidos ?? 0),
            title: node.label,
            metricas: node.metricas,
        })));

        const edges = new vis.DataSet(linksData.map(link => ({
            from: link.source,
            to: link.target,
            color: {
                color: link.isMatch ? '#00b050' : link.tipo === 'preferido' ? '#28a745' : '#dc3545',
                opacity: link.isMatch ? 1 : 0.6,
            },
            width: link.isMatch ? 4 : 2,
            dashes: link.isMatch ? false : [6, 4],
            arrows: { to: { enabled: true, scaleFactor: 0.55 } },
            tipo: link.tipo,
            isMatch: !!link.isMatch,
        })));

        const options = {
            nodes: {
                shape: 'dot',
                size: 20,
                borderWidth: 2,
                borderWidthSelected: 3,
                font: { size: 13, color: '#1f2937', face: 'Inter', strokeWidth: 4, strokeColor: '#ffffff' },
                scaling: { min: 14, max: 34, label: { enabled: true, min: 10, max: 16 } },
                shadow: { enabled: true, color: 'rgba(0,0,0,.12)', size: 7 },
            },
            edges: {
                smooth: { enabled: true, type: 'dynamic', roundness: 0.3 },
                selectionWidth: 3,
                shadow: { enabled: true, color: 'rgba(0,0,0,.08)', size: 2 },
            },
            groups: {
                default: { color: { background: '#6366f1', border: '#4f46e5', highlight: { background: '#4f46e5', border: '#4338ca' } } },
                isolated: { color: { background: '#ef4444', border: '#dc2626', highlight: { background: '#dc2626', border: '#b91c1c' } } },
            },
            physics: {
                enabled: true,
                barnesHut: { gravitationalConstant: -2500, centralGravity: 0.26, springLength: 170, springConstant: 0.028, damping: 0.1 },
                stabilization: { iterations: 1200, updateInterval: 50, fit: true },
            },
            interaction: { hover: true, dragNodes: true, zoomView: true, tooltipDelay: 80, hoverConnectedEdges: true, multiselect: true },
            layout: { improvedLayout: true },
        };

        state.sociogramaNetwork = new vis.Network(cont, { nodes, edges }, options);
        state.sociogramaNetwork.once('stabilizationIterationsDone', () => state.sociogramaNetwork?.setOptions({ physics: false }));

        let tip = null;
        let timer = null;

        const showTooltip = (x, y, html) => {
            tip?.remove();
            tip = document.createElement('div');
            tip.className = 'sociograma-tooltip';
            tip.innerHTML = html;
            tip.style.top = `${y + 10}px`;
            tip.style.left = `${x + 10}px`;
            document.body.appendChild(tip);
        };

        const hideTooltip = () => {
            clearTimeout(timer);
            tip?.remove();
            tip = null;
        };

        state.sociogramaNetwork.on('hoverNode', ({ node, event }) => {
            clearTimeout(timer);
            timer = setTimeout(() => {
                const hoveredNode = nodes.get(node);
                showTooltip(
                    event.clientX,
                    event.clientY,
                    `<strong>${hoveredNode.title}</strong><br>
                     Pref. recibidas: ${hoveredNode.metricas?.preferencias_recibidas ?? 0}<br>
                     Rech. recibidos: ${hoveredNode.metricas?.rechazos_recibidos ?? 0}`
                );
            }, 80);
        });
        state.sociogramaNetwork.on('blurNode', hideTooltip);

        state.sociogramaNetwork.on('hoverEdge', ({ edge, event }) => {
            clearTimeout(timer);
            timer = setTimeout(() => {
                const hoveredEdge = edges.get(edge);
                const fromName = nodes.get(hoveredEdge.from).title;
                const toName = nodes.get(hoveredEdge.to).title;
                const relationText = hoveredEdge.isMatch
                    ? 'tiene match mutuo con'
                    : hoveredEdge.tipo === 'preferido'
                        ? 'prefiere a'
                        : 'rechaza a';

                showTooltip(event.clientX, event.clientY, `${fromName} ${relationText} ${toName}`);
            }, 80);
        });
        state.sociogramaNetwork.on('blurEdge', hideTooltip);
        state.sociogramaNetwork.on('beforeDestroy', hideTooltip);

        state.sociogramaNetwork.on('selectNode', ({ nodes: selected }) => {
            const selectedId = selected?.[0];
            if (!selectedId) return;

            analysis.openStudentDetail(selectedId);
            window.requestAnimationFrame(() => applySelectionHighlight(selectedId, nodes, edges));
        });

        state.sociogramaNetwork.on('deselectNode', () => {
            window.requestAnimationFrame(() => resetSelectionHighlight(nodes, edges));
        });
    };

    analysis.renderizarSociograma = function renderizarSociograma(payload) {
        const data = analysis.normalizeLivewirePayload(payload);

        if (!data?.nodes || !data?.links || !data?.preguntas) {
            state.sociogramaNetwork?.destroy();
            state.sociogramaNetwork = null;
            const container = document.getElementById('sociograma');
            if (container) container.innerHTML = '<div class="alert alert-info m-3">No hay datos suficientes.</div>';
            return;
        }

        analysis.cargarVisJS()
            .then(() => {
                state.origNodes = data.nodes ?? [];
                state.origLinks = data.links ?? [];
                state.origPreguntas = data.preguntas ?? [];

                state.centralityMap = {};
                state.origNodes.forEach(node => {
                    if (node.centrality) state.centralityMap[node.id] = node.centrality;
                });

                state.nodeMap = buildNodeMap(state.origNodes);
                state.incomingRelationsByTarget = buildIncomingRelations(state.origNodes, state.origLinks);
                state.roleById = buildRoleMap(state.origNodes, data.roles ?? {});

                state.linkIndex = {};
                state.origLinks.forEach(link => {
                    state.linkIndex[`${link.source}-${link.target}`] = link;
                });

                state.studentModal = null;
                analysis.ensureToolbar();
            })
            .catch(error => {
                console.error('Error al cargar vis-network:', error);
                const container = document.getElementById('sociograma');
                if (container) container.innerHTML = '<div class="alert alert-danger m-3">Error al cargar la visualizacion.</div>';
            });
    };
})();

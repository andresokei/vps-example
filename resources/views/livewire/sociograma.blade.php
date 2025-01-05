<div>
    <!-- Contenedor para el sociograma -->
    <div id="sociograma" style="width: 100%; height: 500px; border: 1px solid lightgray;"></div>

    <script src="https://unpkg.com/vis-network/standalone/umd/vis-network.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Inicializa el sociograma al cargar la página con los datos iniciales
            initializeSociogram(@json($nodes), @json($links));
        });

        // Escucha el evento de Livewire para actualizar el sociograma
        Livewire.on('rerenderSociogram', function (data) {
            initializeSociogram(data.nodes, data.links);
        });

        function initializeSociogram(nodesData, linksData) {
            const container = document.getElementById('sociograma');

            const nodes = new vis.DataSet(
                nodesData.map(node => ({
                    ...node,
                    shape: 'box', // Aparece en forma de caja
                    size: 30,     // Tamaño del nodo
                    font: {
                        size: 16, 
                        color: 'black',
                        face: 'Arial'
                    },
                    borderWidth: 2,
                    borderWidthSelected: 3,
                    color: {
                        background: '#FFD700', // Fondo amarillo
                        border: '#000000',     // Borde negro
                        highlight: {
                            border: '#2B7CE9',
                            background: '#FFFFA3'
                        }
                    }
                }))
            );

            const edges = new vis.DataSet(
                linksData.map(link => ({
                    ...link,
                    arrows: 'to',       // Flecha en dirección de la relación
                    width: 2,           
                    smooth: {
                        type: 'continuous',
                        roundness: 0.5    
                    },
                    color: {
                        color: link.color.color,   // Color de la relación (verde para "preferido")
                        highlight: '#848484',      
                    }
                }))
            );

            const data = {
                nodes: nodes,
                edges: edges
            };

            const options = {
                physics: {
                    enabled: true,
                    stabilization: {
                        iterations: 2000,   // Mayor cantidad de iteraciones para lograr una disposición estable
                        updateInterval: 25
                    },
                    barnesHut: {
                        gravitationalConstant: -8000, // Separación fuerte entre nodos
                        centralGravity: 0.1,           // Menor atracción hacia el centro para mayor dispersión
                        springLength: 250,            // Aumenta la longitud entre nodos conectados
                        springConstant: 0.02          // Reduce la rigidez del resorte entre nodos conectados
                    },
                    repulsion: {
                        nodeDistance: 300,            // Distancia mínima entre nodos
                        damping: 0.09                 // Mayor amortiguación para reducir el movimiento
                    }
                },
                nodes: {
                    shapeProperties: {
                        useBorderWithImage: true
                    }
                },
                edges: {
                    smooth: {
                        type: 'dynamic'
                    }
                },
                interaction: {
                    hover: true,    // Resalta nodos al pasar el ratón
                    dragNodes: true // Permite arrastrar nodos
                }
            };

            // Limpiar el contenedor antes de renderizar el nuevo gráfico
            container.innerHTML = '';
            new vis.Network(container, data, options);
        }
    </script>
</div>

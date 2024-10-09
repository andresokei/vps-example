<div>
    <style>
        #sociograma {
            width: 100%;
            height: 800px; /* Aumentamos aún más la altura */
            border: 1px solid black;
        }

        .node text {
            font: 14px sans-serif;
        }

        .node circle {
            stroke: #fff;
            stroke-width: 1.5px;
        }

        .link {
            stroke-opacity: 0.6;
        }
    </style>

    <div id="sociograma"></div>

    <script src="https://d3js.org/d3.v7.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var relaciones = @json($relaciones); // Asegúrate de que esta variable contiene los datos correctos

            var width = document.getElementById('sociograma').offsetWidth;
            var height = 800; // Aumentamos más la altura del gráfico

            var svg = d3.select("#sociograma")
                .append("svg")
                .attr("width", width)
                .attr("height", height)
                .append("g");

            // Configurar la simulación de fuerzas
            var simulation = d3.forceSimulation()
                .force("link", d3.forceLink().id(function(d) { return d.id; }).distance(200)) // Aumentamos aún más la distancia entre los nodos
                .force("charge", d3.forceManyBody().strength(-600)) // Aumentamos la repulsión entre nodos
                .force("center", d3.forceCenter(width / 2, height / 2)) // Centrar los nodos
                .force("collision", d3.forceCollide().radius(50)) // Aumentamos el radio de colisión

            var nodes = [];
            var links = [];

            // Procesamos las relaciones
            relaciones.forEach(function(relacion) {
                nodes.push({ id: relacion.alumno_a_id, name: relacion.alumno_a.nombre });
                nodes.push({ id: relacion.alumno_b_id, name: relacion.alumno_b.nombre });

                links.push({
                    source: relacion.alumno_a_id,
                    target: relacion.alumno_b_id,
                    type: relacion.tipo_relacion
                });
            });

            // Eliminamos nodos duplicados
            nodes = Array.from(new Set(nodes.map(a => JSON.stringify(a)))).map(a => JSON.parse(a));

            var link = svg.append("g")
                .attr("class", "links")
                .selectAll("line")
                .data(links)
                .enter()
                .append("line")
                .attr("stroke-width", 2)
                .attr("stroke", function(d) { return d.type === 'preferido' ? 'green' : 'red'; });

            var node = svg.append("g")
                .attr("class", "nodes")
                .selectAll("g")
                .data(nodes)
                .enter()
                .append("g")
                .attr("class", "node")
                .call(d3.drag() // Hacemos que los nodos sean arrastrables
                    .on("start", dragstarted)
                    .on("drag", dragged)
                    .on("end", dragended));

            node.append("circle")
                .attr("r", 15) // Aumentamos el radio de los nodos
                .attr("fill", "blue");

            // Agregamos etiquetas para los nombres
            node.append("text")
                .attr("dx", 20) // Ajuste de la posición horizontal de las etiquetas
                .attr("dy", ".35em") // Ajuste de la posición vertical
                .text(function(d) { return d.name; });

            simulation.nodes(nodes).on("tick", ticked);
            simulation.force("link").links(links);

            function ticked() {
                link
                    .attr("x1", function(d) { return d.source.x; })
                    .attr("y1", function(d) { return d.source.y; })
                    .attr("x2", function(d) { return d.target.x; })
                    .attr("y2", function(d) { return d.target.y; });

                node.selectAll("circle")
                    .attr("cx", function(d) { return d.x; })
                    .attr("cy", function(d) { return d.y; });

                node.selectAll("text")
                    .attr("x", function(d) { return d.x; })
                    .attr("y", function(d) { return d.y; });
            }

            // Funciones para arrastrar nodos
            function dragstarted(event, d) {
                if (!event.active) simulation.alphaTarget(0.3).restart();
                d.fx = d.x;
                d.fy = d.y;
            }

            function dragged(event, d) {
                d.fx = event.x;
                d.fy = event.y;
            }

            function dragended(event, d) {
                if (!event.active) simulation.alphaTarget(0);
                d.fx = null;
                d.fy = null;
            }
        });
    </script>
</div>

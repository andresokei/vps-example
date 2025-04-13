<div>
    <div style="width: 80%; margin: 0 auto;">
        <div wire:ignore>
            <canvas id="simpleChart" width="400" height="200"></canvas>
        </div>
        <p class="mt-3">Este es un gráfico de prueba</p>
    </div>

    @push('scripts')
    <script>
        // Asegurarse de que este script se ejecute cuando el DOM esté listo
        document.addEventListener('DOMContentLoaded', function() {
            // Esperar un momento para asegurar que todo está disponible
            setTimeout(function() {
                console.log("Intentando crear gráfico de prueba");
                
                // Obtener el elemento canvas
                const canvas = document.getElementById('simpleChart');
                
                if (!canvas) {
                    console.error("Canvas 'simpleChart' no encontrado!");
                    return;
                }
                
                // Obtener el contexto 2D
                const ctx = canvas.getContext('2d');
                
                if (!ctx) {
                    console.error("No se pudo obtener el contexto 2D del canvas!");
                    return;
                }
                
                // Datos para el gráfico
                const data = {
                    labels: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo'],
                    datasets: [{
                        label: 'Datos de ejemplo',
                        data: [12, 19, 3, 5, 2],
                        backgroundColor: [
                            'rgba(255, 99, 132, 0.2)',
                            'rgba(54, 162, 235, 0.2)',
                            'rgba(255, 206, 86, 0.2)',
                            'rgba(75, 192, 192, 0.2)',
                            'rgba(153, 102, 255, 0.2)'
                        ],
                        borderColor: [
                            'rgba(255, 99, 132, 1)',
                            'rgba(54, 162, 235, 1)',
                            'rgba(255, 206, 86, 1)',
                            'rgba(75, 192, 192, 1)',
                            'rgba(153, 102, 255, 1)'
                        ],
                        borderWidth: 1
                    }]
                };
                
                // Configuración del gráfico
                const config = {
                    type: 'bar',
                    data: data,
                    options: {
                        responsive: true,
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                };
                
                try {
                    // Crear el gráfico
                    const myChart = new Chart(ctx, config);
                    console.log("Gráfico creado exitosamente!");
                } catch (error) {
                    console.error("Error al crear el gráfico:", error);
                }
            }, 500); // Esperar 500ms
        });
    </script>
    @endpush
</div>
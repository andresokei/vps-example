<div>
    <div class="row mb-3">
        <div class="col-12">
            @if($mensaje)
                <div class="alert alert-info">{{ $mensaje }}</div>
            @endif
        </div>
    </div>
    
    <div class="row">
        <div class="col-12">
            <div wire:ignore>
                <canvas id="graficoPreferencias" style="width: 100%; height: 300px;"></canvas>
            </div>
            <p class="mt-3 text-muted">Seleccione un grupo y un tipo de análisis, luego haga clic en "Procesar Análisis".</p>
        </div>
    </div>
    
    <div class="row mt-3" style="display: none;">
        <div class="col-12">
            <p>Debug - Labels: {{ json_encode($labels) }}</p>
            <p>Debug - Data: {{ json_encode($data) }}</p>
        </div>
    </div>

    @push('scripts')
    <script>
        // Variable global para el gráfico
        let preferenciasChart = null;
        
        // Función para inicializar o actualizar el gráfico
        function actualizarGraficoPreferencias() {
            console.log("Actualizando gráfico de preferencias");
            console.log("Labels:", @json($labels));
            console.log("Data:", @json($data));
            
            // Obtener el canvas
            const canvas = document.getElementById('graficoPreferencias');
            
            if (!canvas) {
                console.error("Canvas 'graficoPreferencias' no encontrado!");
                return;
            }
            
            // Obtener el contexto 2D
            const ctx = canvas.getContext('2d');
            
            // Destruir el gráfico anterior si existe
            if (preferenciasChart !== null) {
                preferenciasChart.destroy();
            }
            
            // Configuración del gráfico
            const config = {
                type: 'bar',
                data: {
                    labels: @json($labels),
                    datasets: [{
                        label: 'Veces que fue elegido',
                        data: @json($data),
                        backgroundColor: 'rgba(54, 162, 235, 0.5)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            };
            
            try {
                // Crear el nuevo gráfico
                preferenciasChart = new Chart(ctx, config);
                console.log("Gráfico de preferencias creado exitosamente!");
            } catch (error) {
                console.error("Error al crear el gráfico:", error);
            }
        }
        
        // Inicializar cuando Livewire esté listo
        document.addEventListener('livewire:init', function() {
            // Escuchar evento para actualizar el gráfico
            Livewire.on('actualizarGrafico', function() {
                // Esperar un momento para asegurar que el DOM está listo
                setTimeout(actualizarGraficoPreferencias, 100);
            });
        });
        
        // Intentar renderizar cuando el DOM esté listo
        document.addEventListener('DOMContentLoaded', function() {
            // Solo renderizar si hay datos
            if (@json($labels).length > 0 && @json($data).length > 0) {
                setTimeout(actualizarGraficoPreferencias, 500);
            }
        });
    </script>
    @endpush
</div>
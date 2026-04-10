{{-- Sociograma con grafo a pantalla completa --}}
<div class="card sociograma-card shadow-sm mb-4 border-0">
  <div class="card-header py-3 d-flex align-items-center">
    <h5 class="mb-0 fw-semibold">Sociograma</h5>
  </div>

  <div id="soc-wrapper" class="socio-wrapper">
    {{-- SIDEBAR MINIMIZADA inicialmente --}}
    <div class="sidebar-toggle-btn" id="toggle-sidebar">
      <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
    </div>
    
    <aside id="barra-socio" class="sidebar-controls">
      <div class="sidebar-header">
        <h6 class="mb-0">Filtros</h6>
        <button class="close-sidebar-btn" id="close-sidebar">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
        </button>
      </div>
      
      {{-- Secciones de filtros --}}
      <div class="filter-section">
        <label class="filter-label">Filtrar alumno(s)</label>
        <select id="soc-select" class="select-minimal" multiple></select>
      </div>

      <div class="filter-section">
        <label class="filter-label">Filtrar por pregunta</label>
        
        @php
          $preguntas = data_get($datos, 'sociograma.preguntas', []);
        @endphp

        @if(count($preguntas))
          <div id="soc-preguntas-filter-options" class="questions-container">
            @foreach ($preguntas as $preg)
              <div class="question-card" data-id="{{ $preg['id'] }}">
                <input type="checkbox" class="question-check" id="preg-{{ $preg['id'] }}" name="preguntas[]" value="{{ $preg['id'] }}">
                <label for="preg-{{ $preg['id'] }}" title="{{ $preg['texto'] }}">{{ $preg['texto'] }}</label>
              </div>
            @endforeach
            
            <div class="select-all-container">
              <button id="select-all-preguntas" class="select-all-btn">
                <span class="select-icon">
                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 11 12 14 22 4"></polyline><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path></svg>
                </span>
                Seleccionar todas
              </button>
            </div>
          </div>
        @else
          <div class="empty-state">No hay preguntas de test disponibles.</div>
        @endif
      </div>

      <div class="filter-section">
        <div class="options-container">
          <div class="option-toggle">
            <input type="checkbox" id="soc-onlyMatch" class="toggle-input">
            <label for="soc-onlyMatch">
              <div class="toggle-control"></div>
              <span>Solo matches</span>
            </label>
          </div>
          
          <div class="option-toggle">
            <input type="checkbox" id="soc-markIsol" class="toggle-input" checked>
            <label for="soc-markIsol">
              <div class="toggle-control"></div>
              <span>Marcar no-elegidos</span>
            </label>
          </div>
        </div>
      </div>

      <button id="soc-reset" class="reset-btn">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"></path><path d="M3 3v5h5"></path></svg>
        Limpiar filtros
      </button>
    </aside>

    {{-- GRAFO --}}
    <div class="sociograma-visualization">
      <div class="sociograma-legend" aria-label="Leyenda del sociograma">
        <span class="legend-item"><i class="legend-dot legend-dot--default"></i> Alumno</span>
        <span class="legend-item"><i class="legend-dot legend-dot--isolated"></i> No elegido</span>
        <span class="legend-item"><i class="legend-line legend-line--pref"></i> Preferencia</span>
        <span class="legend-item"><i class="legend-line legend-line--rech"></i> Rechazo</span>
        <span class="legend-item"><i class="legend-line legend-line--match"></i> Match mutuo</span>
      </div>
      <div id="sociograma"></div>
    </div>
  </div>
</div>

<style>
/* Estilos generales y variables */
:root {
  --primary: #4361ee;
  --primary-light: #edf2ff;
  --primary-hover: #3a56d4;
  --gray-50: #f8f9fa;
  --gray-100: #f1f3f5;
  --gray-200: #e9ecef;
  --gray-300: #dee2e6;
  --gray-400: #ced4da;
  --gray-500: #adb5bd;
  --gray-600: #868e96;
  --gray-700: #495057;
  --gray-800: #343a40;
  --gray-900: #212529;
  --radius-sm: 4px;
  --radius-md: 8px;
  --shadow-sm: 0 1px 3px rgba(0,0,0,0.05);
  --shadow-md: 0 1px 5px rgba(0,0,0,0.08);
  --shadow-lg: 0 2px 8px rgba(0,0,0,0.1);
  --transition: all 0.2s ease;
  --soc-panel-width: 280px;
  --font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen, Ubuntu, Cantarell, "Open Sans", "Helvetica Neue", sans-serif;
}

/* Reset y base */
* {
  box-sizing: border-box;
  margin: 0;
  padding: 0;
}

body {
  font-family: var(--font-family);
  color: var(--gray-800);
  background-color: var(--gray-50);
}

/* Tarjeta principal */
.sociograma-card {
  background-color: white;
  border-radius: var(--radius-md);
  overflow: hidden;
  box-shadow: var(--shadow-md);
  transition: var(--transition);
}

.sociograma-card .card-header {
  background-color: white;
  border-bottom: 1px solid var(--gray-200);
  padding: 1rem 1.5rem;
}

.sociograma-card .card-header h5 {
  color: var(--gray-800);
  font-weight: 600;
}

/* Layout principal - MODO PANTALLA COMPLETA */
.socio-wrapper {
  position: relative;
  height: 70vh; /* Altura fija para llenar gran parte de la pantalla */
  min-height: 500px;
}

/* Botón toggle sidebar */
.sidebar-toggle-btn {
  position: absolute;
  top: 1rem;
  left: 1rem;
  width: 40px;
  height: 40px;
  background-color: white;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: var(--shadow-md);
  z-index: 20;
  cursor: pointer;
  color: var(--gray-700);
  transition: var(--transition);
}

.sidebar-toggle-btn:hover {
  background-color: var(--primary-light);
  color: var(--primary);
}

/* Barra lateral - por defecto oculta en pantallas pequeñas */
.sidebar-controls {
  position: absolute;
  width: var(--soc-panel-width);
  height: 100%;
  background-color: white;
  border-right: 1px solid var(--gray-200);
  z-index: 10;
  left: -300px; /* Inicialmente oculta */
  transition: left 0.3s ease;
  display: flex;
  flex-direction: column;
  gap: 1rem;
  padding: 1.25rem;
  box-shadow: var(--shadow-lg);
  overflow-y: auto;
}

/* Clase para mostrar la barra lateral */
.sidebar-controls.show {
  left: 0;
}

/* Encabezado de la barra lateral */
.sidebar-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding-bottom: 0.5rem;
  border-bottom: 1px solid var(--gray-200);
  margin-bottom: 0.5rem;
}

.close-sidebar-btn {
  background: none;
  border: none;
  cursor: pointer;
  color: var(--gray-600);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 4px;
  border-radius: 50%;
  transition: var(--transition);
}

.close-sidebar-btn:hover {
  background-color: var(--gray-100);
  color: var(--gray-800);
}

/* Secciones de filtros */
.filter-section {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.filter-label {
  color: var(--gray-700);
  font-size: 0.875rem;
  font-weight: 600;
  margin-bottom: 0.25rem;
}

/* Select de alumnos */
.select-minimal {
  height: 120px;
  border: 1px solid var(--gray-300);
  border-radius: var(--radius-sm);
  padding: 0.5rem;
  background-color: white;
  box-shadow: var(--shadow-sm);
  font-size: 0.9rem;
  color: var(--gray-800);
  overflow-y: auto;
  scrollbar-width: thin;
  scrollbar-color: var(--gray-400) var(--gray-100);
}

.select-minimal:focus {
  border-color: var(--primary);
  outline: none;
  box-shadow: 0 0 0 2px rgba(67, 97, 238, 0.2);
}

/* Contenedor de preguntas */
.questions-container {
  display: flex;
  flex-direction: column;
  gap: 0.375rem;
  max-height: 180px;
  overflow-y: auto;
  scrollbar-width: thin;
  scrollbar-color: var(--gray-400) var(--gray-100);
  padding: 0.25rem;
}

/* Tarjeta de pregunta */
.question-card {
  position: relative;
  border-radius: var(--radius-sm);
  transition: var(--transition);
}

.question-card label {
  display: block;
  padding: 0.5rem 0.75rem;
  border-radius: var(--radius-sm);
  background-color: var(--gray-50);
  border: 1px solid var(--gray-300);
  color: var(--gray-700);
  cursor: pointer;
  font-size: 0.875rem;
  transition: var(--transition);
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.question-card:hover label {
  border-color: var(--gray-400);
}

.question-card input {
  position: absolute;
  opacity: 0;
  cursor: pointer;
  height: 0;
  width: 0;
}

.question-card input:checked + label {
  background-color: var(--primary-light);
  border-color: var(--primary);
  color: var(--primary);
  font-weight: 500;
}

/* Botón seleccionar todas */
.select-all-container {
  margin-top: 0.375rem;
}

.select-all-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  width: 100%;
  padding: 0.5rem;
  background-color: var(--primary-light);
  color: var(--primary);
  border: 1px solid var(--primary);
  border-radius: var(--radius-sm);
  font-size: 0.875rem;
  font-weight: 500;
  cursor: pointer;
  transition: var(--transition);
}

.select-all-btn:hover {
  background-color: var(--primary);
  color: white;
}

.select-icon {
  display: flex;
  align-items: center;
  justify-content: center;
}

/* Opciones de filtro adicionales */
.options-container {
  display: flex;
  flex-direction: column;
  gap: 0.625rem;
  margin-top: 0.5rem;
}

.option-toggle {
  display: flex;
  align-items: center;
}

.option-toggle label {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  font-size: 0.875rem;
  color: var(--gray-700);
  cursor: pointer;
}

.toggle-input {
  position: absolute;
  opacity: 0;
  height: 0;
  width: 0;
}

.toggle-control {
  position: relative;
  width: 36px;
  height: 20px;
  background-color: var(--gray-300);
  border-radius: 20px;
  transition: var(--transition);
}

.toggle-control:before {
  content: "";
  position: absolute;
  height: 16px;
  width: 16px;
  left: 2px;
  bottom: 2px;
  background-color: white;
  border-radius: 50%;
  transition: var(--transition);
  box-shadow: var(--shadow-sm);
}

.toggle-input:checked + label .toggle-control {
  background-color: var(--primary);
}

.toggle-input:checked + label .toggle-control:before {
  transform: translateX(16px);
}

/* Botón de reset */
.reset-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  margin-top: auto;
  padding: 0.625rem;
  background-color: var(--gray-50);
  color: var(--gray-700);
  border: 1px solid var(--gray-300);
  border-radius: var(--radius-sm);
  font-size: 0.875rem;
  font-weight: 500;
  cursor: pointer;
  transition: var(--transition);
}

.reset-btn:hover {
  background-color: var(--gray-200);
  color: var(--gray-800);
}

/* Contenedor principal del sociograma - Pantalla completa */
.sociograma-visualization {
  position: relative;
  width: 100%;
  height: 100%;
  background-color: white;
  display: flex;
  align-items: center;
  justify-content: center;
}

#sociograma {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
}

.sociograma-legend {
  position: absolute;
  right: 1rem;
  top: 1rem;
  z-index: 9;
  background: rgba(255, 255, 255, 0.92);
  backdrop-filter: blur(4px);
  border: 1px solid var(--gray-200);
  border-radius: var(--radius-md);
  box-shadow: var(--shadow-md);
  padding: 0.5rem 0.75rem;
  display: grid;
  grid-template-columns: 1fr;
  gap: 0.35rem;
  min-width: 190px;
}

.legend-item {
  display: inline-flex;
  align-items: center;
  gap: 0.45rem;
  color: var(--gray-700);
  font-size: 0.78rem;
  white-space: nowrap;
}

.legend-dot {
  width: 10px;
  height: 10px;
  border-radius: 50%;
  display: inline-block;
}

.legend-dot--default { background: #2196F3; }
.legend-dot--isolated { background: #F44336; }

.legend-line {
  width: 16px;
  height: 2px;
  display: inline-block;
  border-radius: 2px;
}

.legend-line--pref { background: #28a745; }
.legend-line--rech { background: #dc3545; }
.legend-line--match { background: #00b050; height: 3px; }

/* Matriz */
.matriz-container {
  width: 100%;
  height: 400px;
}

/* Estado vacío */
.empty-state {
  padding: 1rem;
  background-color: var(--gray-100);
  color: var(--gray-600);
  text-align: center;
  font-size: 0.875rem;
  border-radius: var(--radius-sm);
}

/* Scrollbars personalizados */
.select-minimal::-webkit-scrollbar,
.questions-container::-webkit-scrollbar,
.sidebar-controls::-webkit-scrollbar {
  width: 5px;
}

.select-minimal::-webkit-scrollbar-track,
.questions-container::-webkit-scrollbar-track,
.sidebar-controls::-webkit-scrollbar-track {
  background: var(--gray-100);
}

.select-minimal::-webkit-scrollbar-thumb,
.questions-container::-webkit-scrollbar-thumb,
.sidebar-controls::-webkit-scrollbar-thumb {
  background-color: var(--gray-400);
  border-radius: 5px;
}

.select-minimal::-webkit-scrollbar-thumb:hover,
.questions-container::-webkit-scrollbar-thumb:hover,
.sidebar-controls::-webkit-scrollbar-thumb:hover {
  background-color: var(--gray-500);
}

/* Tooltip personalizado */
.sociograma-tooltip {
  position: absolute;
  z-index: 1070;
  background-color: white;
  border-radius: var(--radius-sm);
  padding: 0.5rem 0.75rem;
  font-size: 0.875rem;
  box-shadow: var(--shadow-lg);
  pointer-events: none;
  max-width: 220px;
}

/* Responsivo */
@media (min-width: 992px) {
  .sidebar-controls {
    left: 0; /* Por defecto visible en pantallas grandes */
  }

  .sociograma-visualization {
    margin-left: var(--soc-panel-width);
    width: calc(100% - var(--soc-panel-width));
  }
  
  .sidebar-toggle-btn {
    display: none; /* Ocultar botón de toggle en pantallas grandes */
  }
}

@media (max-width: 992px) {
  .socio-wrapper {
    height: 60vh;
  }
}

@media (max-width: 576px) {
  .card-header {
    padding: 0.75rem 1rem;
  }
  
  .socio-wrapper {
    height: 50vh;
    min-height: 400px;
  }

  .sociograma-legend {
    right: 0.5rem;
    top: 0.5rem;
    min-width: 160px;
    padding: 0.45rem 0.6rem;
  }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
  // Gestión de la barra lateral
  const sidebar = document.getElementById('barra-socio');
  const toggleBtn = document.getElementById('toggle-sidebar');
  const closeBtn = document.getElementById('close-sidebar');
  
  // En móviles, la barra lateral está oculta por defecto
  // En pantallas grandes, comprobar si debe estar visible
  if (window.innerWidth >= 992) {
    sidebar.classList.add('show');
  }
  
  // Botón para mostrar la barra lateral
  if (toggleBtn) {
    toggleBtn.addEventListener('click', function() {
      sidebar.classList.add('show');
    });
  }
  
  // Botón para ocultar la barra lateral
  if (closeBtn) {
    closeBtn.addEventListener('click', function() {
      sidebar.classList.remove('show');
    });
  }
  
  // Lógica para el botón "Seleccionar todas"
  const selectAllBtn = document.getElementById('select-all-preguntas');
  if (selectAllBtn) {
    selectAllBtn.addEventListener('click', function() {
      const checkboxes = document.querySelectorAll('.question-check');
      // Verificar si todos están marcados
      const allChecked = [...checkboxes].every(cb => cb.checked);
      
      // Marcar o desmarcar todos según corresponda
      checkboxes.forEach(cb => {
        cb.checked = !allChecked;
      });
      
      // Llamar a la función que aplica los filtros
      if (typeof applyFilters === 'function') {
        applyFilters();
      }
    });
  }
  
  // Efecto de carga suave para el gráfico
  const sociograma = document.getElementById('sociograma');
  if (sociograma) {
    sociograma.style.opacity = '0';
    setTimeout(() => {
      sociograma.style.opacity = '1';
      sociograma.style.transition = 'opacity 0.5s ease';
    }, 300);
  }
  
  // Ajustar visibilidad de la barra lateral al cambiar el tamaño de la ventana
  window.addEventListener('resize', function() {
    if (window.innerWidth >= 992) {
      sidebar.classList.add('show');
    } else {
      sidebar.classList.remove('show');
    }
  });
});
</script>

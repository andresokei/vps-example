@extends('layouts.custom')

@section('content')

<div class="container d-flex justify-content-center align-items-center py-5" style="min-height: 100vh; background-color: #f8f9fa;"> {{-- Fondo general suave --}}
    <div class="col-lg-8 col-md-10">
        <div class="card shadow-lg" style="border-radius: 15px; border: none; overflow: hidden;">
            <div class="card-header text-center py-4" style="background: linear-gradient(45deg, #6bb9f0, #a0d6b4); color: #333;"> {{-- Gradiente suave, colores más cálidos/amigables --}}
                 <h2 class="mb-0" style="font-weight: bold;">{{ $test->nombre_test }}</h2>
                 @if($test->descripcion)<p class=" mb-0" style="color: #444;">{{ $test->descripcion }}</p>@endif {{-- Mostrar descripción si existe --}}
            </div>
            <div class="card-body p-5">

                {{-- Puedes añadir mensajes de éxito/error aquí si tu controlador los maneja --}}

                <form id="testForm" action="{{ route('test.submit', ['id' => $test->id]) }}" method="POST">
                    @csrf

                    <input type="hidden" name="asignacion_id" value="{{ $asignacion_id }}">

                    <div class="form-group mb-5">
                        <label for="estudiante_quien_responde" class="font-weight-bold mb-2" style="color: #333;">Selecciona el Estudiante que responde:</label>
                        <select name="estudiante_id" id="estudiante_quien_responde" class="form-control form-control-lg rounded-pill" required> {{-- Bordes más redondeados --}}
                            <option value="">-- Selecciona un estudiante --</option>
                            @foreach($estudiantes as $estudiante)
                                <option value="{{ $estudiante->id }}">{{ $estudiante->nombre }}</option>
                            @endforeach
                        </select>
                    </div>

                    @if($test && $test->preguntas && $test->preguntas->count())
                        @foreach ($test->preguntas as $pregunta)
                            <div class="question-block form-group mb-5 p-4 border rounded shadow-sm" data-pregunta-id="{{ $pregunta->id }}" style="border-color: #e0e0e0; background-color: #ffffff;"> {{-- Borde suave, fondo blanco --}}
                                <h4 class="mb-4" style="color: #555; border-bottom: 2px solid #eee; padding-bottom: 10px;">{{ $pregunta->texto_pregunta }}</h4>

                                <input type="hidden" name="tipo_relacion_{{ $pregunta->id }}" value="{{ $pregunta->tipo_pregunta }}">

                                <div class="selected-students-container mb-4 p-3 rounded" data-pregunta-id="{{ $pregunta->id }}" style="background-color: #eef7ff; border: 1px dashed #a0cfff;"> {{-- Fondo azul muy claro, borde punteado suave --}}
                                    <p class="text-muted mb-2 small">Seleccionados (<span class="selected-count">0</span>/3):</p>
                                    <ul class="list-inline mb-0 selected-students-list">
                                        {{-- Los nombres/tarjetas de los estudiantes seleccionados se añadirán aquí con JavaScript --}}
                                    </ul>
                                </div>

                                <div class="student-options-list d-flex flex-wrap justify-content-center">
                                    @foreach($estudiantes as $estudiante)
                                        {{-- Elemento cliqueable mejorado para cada estudiante --}}
                                        <div class="student-choice m-1 p-3 border rounded text-center d-flex flex-column align-items-center justify-content-center"
                                             data-student-id="{{ $estudiante->id }}"
                                             data-pregunta-id="{{ $pregunta->id }}"
                                             data-nombre-estudiante="{{ $estudiante->nombre }}"
                                             style="cursor: pointer; width: 100px; height: 80px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; transition: all 0.2s ease-in-out; position: relative; background-color: #fff; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.08);"> {{-- Estilos mejorados y bordes redondeados --}}

                                            {{-- Opcional: Placeholder para foto/icono --}}
                                             {{-- Si usas iconos y no fotos: --}}
                                             <i class="fas fa-user-circle fa-2x mb-1" style="color: #a0a0a0;"></i> {{-- Icono de usuario sutil --}}


                                            <span class="student-name font-weight-bold">{{ $estudiante->nombre }}</span>

                                            {{-- Icono de selección (inicialmente oculto) --}}
                                            <i class="selection-indicator fas fa-check-circle" style="position: absolute; top: 5px; right: 5px; color: #a0d6b4; font-size: 1.2em; display: none; text-shadow: 0 0 5px rgba(0,0,0,0.1);"></i> {{-- Color que contraste con la selección --}}

                                        </div>
                                    @endforeach
                                </div>

                                {{-- Necesitas un campo oculto por cada posible preferencia/rechazo (hasta 3) --}}
                                <input type="hidden" name="respuesta_{{ $pregunta->id }}_1" data-pregunta-id="{{ $pregunta->id }}" class="response-input" data-order="1" value="">
                                <input type="hidden" name="respuesta_{{ $pregunta->id }}_2" data-pregunta-id="{{ $pregunta->id }}" class="response-input" data-order="2" value="">
                                <input type="hidden" name="respuesta_{{ $pregunta->id }}_3" data-pregunta-id="{{ $pregunta->id }}" class="response-input" data-order="3" value="">
                                {{-- Si necesitas más de 3 opciones ordenadas por pregunta, añade más campos ocultos aquí --}}

                            </div>
                        @endforeach

                        {{-- Mensaje de validación general si falta alguna respuesta --}}
                        <div id="validationMessage" class="alert alert-danger d-none" role="alert">
                            <i class="fas fa-exclamation-circle mr-2"></i> Por favor, completa todas las selecciones requeridas en cada pregunta antes de enviar.
                        </div>

                    @else
                        <div class="alert alert-info text-center">No hay preguntas disponibles para este test.</div>
                    @endif

                    <button type="submit" class="btn btn-primary btn-lg btn-block mt-5 rounded-pill">Enviar Respuestas</button> {{-- Botón grande y redondeado --}}
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Asegúrate de tener Font Awesome para los iconos --}}
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">


{{-- === SECCIÓN DE ESTILOS CSS MEJORADOS Y MÁS CÁLIDOS/JUGUETONES === --}}
<style>
     body {
         background-color: #f8f9fa; /* Mantener el fondo suave */
         font-family: 'Nunito', sans-serif; /* Ejemplo de fuente más amigable (si la usas en tu layout) */
     }

    /* Estilo base para cada opción de estudiante (tarjeta cliqueable) */
    .student-choice {
        transition: all 0.2s ease-in-out;
        user-select: none;
        background-color: #ffffff; /* Fondo blanco */
        border: 1px solid #e0e0e0; /* Borde muy suave */
        color: #555; /* Color de texto gris medio */
         font-size: 0.9em;
        position: relative;
        box-shadow: 0 2px 5px rgba(0,0,0,0.05); /* Sombra más suave */
         overflow: hidden;
         text-overflow: ellipsis;
         white-space: nowrap;
         /* Flexbox ya está en el HTML para centrar contenido */
         border-radius: 8px; /* Bordes redondeados */
         padding: 15px 10px; /* Aumentar padding vertical */
         height: 80px; /* Mantener altura */
         width: 100px; /* Mantener ancho */
    }
     .student-choice .fa-user-circle {
         color: #a0a0a0; /* Color sutil del icono de usuario */
     }
      .student-choice .student-name {
         color: #333; /* Color del nombre */
      }


    /* Estilo al pasar el ratón (efecto "rotulador" mejorado y cálido) */
    .student-choice:hover:not(.selected) {
        border-color: #ffc107; /* Amarillo/naranja suave al pasar ratón */
        box-shadow: 0 4px 10px rgba(255, 193, 7, 0.4); /* Sombra más pronunciada con color */
        transform: translateY(-5px); /* Se levanta un poco más */
         background-color: #fff8e1; /* Fondo amarillo muy pálido */
         color: #222; /* Texto más oscuro */
    }
     .student-choice:hover:not(.selected) .fa-user-circle {
         color: #ff9800; /* Color naranja al pasar ratón sobre el icono */
     }
      .student-choice:hover:not(.selected) .student-name {
         color: #222;
      }


    /* Estilo cuando el estudiante ha sido seleccionado para una pregunta */
    .student-choice.selected {
        background-color: #81c784; /* Verde claro/suave para preferencia */
        color: white;
        border-color: #66bb6a; /* Borde verde un poco más oscuro */
        box-shadow: 0 0 8px rgba(76, 175, 80, 0.7); /* Sombra con color verde */
        cursor: pointer;
        pointer-events: auto;
        transform: scale(1.05);
        /* Ocultar icono de usuario y mostrar icono de check */
         /* .fa-user-circle ya está en HTML, .selection-indicator también */
    }
    /* Estilo específico para estudiantes seleccionados como rechazo (basado en el tipo de la pregunta) */
    /* Necesitarías añadir la clase 'is-rechazo' con JS basado en el tipo de pregunta de la pregunta */
    .student-choice.selected.is-rechazo {
        background-color: #e57373; /* Rojo claro/suave para rechazo */
        border-color: #ef5350; /* Borde rojo un poco más oscuro */
        box-shadow: 0 0 8px rgba(244, 67, 54, 0.7); /* Sombra con color rojo */
    }
     .student-choice.selected .fa-user-circle {
         display: none; /* Ocultar icono de usuario cuando está seleccionado */
     }
      .student-choice.selected .student-name {
         color: white; /* Asegurar texto blanco */
      }
     .student-choice.selected .selection-indicator {
         color: white !important; /* Asegurar icono de check blanco */
         display: block; /* Mostrar icono de check */
     }


    /* Estilo para estudiantes que no han sido seleccionados en la pregunta actual */
    /* (Atenuar a los no seleccionados una vez que se ha hecho alguna selección en la pregunta) */
     .question-block .student-options-list.has-selection .student-choice:not(.selected) {
         opacity: 0.4; /* Más atenuado */
         pointer-events: auto; /* Siguen siendo cliqueables para seleccionar */
         transform: scale(1);
         box-shadow: none;
         background-color: #f8f9fa; /* Fondo muy claro */
     }
      .question-block .student-options-list.has-selection .student-choice:not(.selected):hover {
           opacity: 0.8; /* Ligeramente menos atenuados al pasar el ratón */
           border-color: #ccc;
           box-shadow: 0 1px 3px rgba(0,0,0,0.1);
           transform: translateY(-2px);
           background-color: #f0f0f0;
           color: #333;
      }
       .question-block .student-options-list.has-selection .student-choice:not(.selected) .fa-user-circle {
           color: #ccc; /* Icono más atenuado */
       }


    /* Estilo para los elementos dentro del contenedor de seleccionados (tags/píldoras) */
    .selected-students-list .selected-student-item {
        background-color: #b3e5fc; /* Fondo azul claro/cálido */
        border-radius: 20px;
        padding: 6px 15px; /* Más padding */
        margin-right: 8px;
        margin-bottom: 8px;
        font-size: 0.9em;
        display: inline-flex;
        align-items: center;
        cursor: pointer;
        transition: background-color 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
        border: 1px solid #81d4fa; /* Borde a juego */
        color: #0277bd; /* Color de texto azul oscuro */
        font-weight: bold; /* Texto más grueso */
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }
     .selected-students-list .selected-student-item:hover {
         background-color: #81d4fa; /* Color más oscuro al pasar el ratón */
         box-shadow: 0 2px 5px rgba(0,0,0,0.2);
         color: white;
     }

    /* Estilo para el orden en el contenedor de seleccionados */
    .selected-students-list .selected-student-item .order {
        font-weight: bold;
        margin-right: 8px; /* Más margen */
        color: #01579b; /* Azul muy oscuro */
        font-size: 1.1em;
    }
     .selected-students-list .selected-student-item:hover .order {
         color: white; /* Orden blanco al pasar el ratón */
     }


    /* Estilo para el icono de check en la opción seleccionada */
    .student-choice .selection-indicator {
        display: none; /* Oculto por defecto */
    }
    /* Ya se muestra en .student-choice.selected en las reglas anteriores */


    /* Estilos para el mensaje de validación */
    #validationMessage {
        transition: all 0.3s ease-in-out;
        border-radius: 8px;
         box-shadow: 0 2px 8px rgba(220, 53, 69, 0.3);
         font-weight: bold;
    }

     /* Ajuste para el dropdown del estudiante que responde */
     #estudiante_quien_responde {
         box-shadow: 0 1px 3px rgba(0,0,0,0.05);
     }


</style>

{{-- === SECCIÓN DE SCRIPTS JAVASCRIPT (con Deselección) === --}}
{{-- El JavaScript es el mismo de la respuesta anterior, solo se actualiza la referencia a elementos y data attributes si cambiaron --}}
{{-- === SECCIÓN DE SCRIPTS JAVASCRIPT (con Deselección y Validación de Autoselección) === --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Mapa para almacenar las selecciones por pregunta: { preguntaId: { orden: { id: studentId, element: choiceElement }, ... }, ... }
        // Guardamos el ID del estudiante, el orden, y una referencia al elemento original `.student-choice`
        const selectedResponses = {};

        // Máximo número de selecciones permitidas por pregunta (ajusta si tus tests varían)
        const MAX_SELECTIONS_PER_QUESTION = 3; // Coincide con los campos ocultos _1, _2, _3

        // Obtener referencias a elementos clave
        const form = document.getElementById('testForm');
        const questionBlocks = document.querySelectorAll('.question-block');
        const validationMessage = document.getElementById('validationMessage');
        const studentOptions = document.querySelectorAll('.student-choice'); // Obtener todas las opciones de estudiante
        const estudianteQuienRespondeDropdown = document.getElementById('estudiante_quien_responde'); // Referencia al dropdown del estudiante que responde


        // Inicializar selectedResponses al cargar la página
        questionBlocks.forEach(block => {
            const preguntaId = parseInt(block.dataset.preguntaId); // Asegurarse de que sea número
            selectedResponses[preguntaId] = {}; // Inicializar estructura para la pregunta

            // TODO: Si permites guardar progreso y recargar, aquí necesitarías leer
            // los valores de los campos ocultos ya llenos y reconstruir el estado
            // de selectedResponses y la UI visual (.selected, .selected-students-list)
            // al cargar la página.
        });


        // --- Función para actualizar la interfaz visual de seleccionados y los campos ocultos ---
        function updateQuestionUI(preguntaId) {
            const questionBlock = document.querySelector(`.question-block[data-pregunta-id="${preguntaId}"]`);
            if (!questionBlock) return;

            const selectedStudentsList = questionBlock.querySelector('.selected-students-list');
            const selectedCountSpan = questionBlock.querySelector('.selected-count');
            const responseInputs = questionBlock.querySelectorAll('.response-input');
            const studentOptionsListContainer = questionBlock.querySelector('.student-options-list');

            // Limpiar visualización actual y campos ocultos
            selectedStudentsList.innerHTML = '';
            responseInputs.forEach(input => input.value = '');

            const currentSelections = selectedResponses[preguntaId];
            // Obtener IDs de estudiantes actualmente seleccionados para esta pregunta
            const selectedStudentIdsForThisQuestion = Object.values(currentSelections).map(item => item.id.toString()); // Convertir a string para comparación con data attribute


            // Reconstruir visualización de seleccionados y llenar campos ocultos según el orden actual
            let count = 0;
            // Ordenar por clave (el orden) para asegurar el orden correcto
            Object.keys(currentSelections).sort((a, b) => parseInt(a) - parseInt(b)).forEach(orderKey => {
                const selection = currentSelections[orderKey]; // selection es { id: studentId, element: choiceElement }

                if (selection && selection.id) { // Asegurarse de que la selección es válida
                    count++; // Este es el nuevo número de orden

                    const selectedItem = document.createElement('li');
                    selectedItem.classList.add('list-inline-item', 'selected-student-item');
                    // Añadir data attributes para identificar al deseleccionar
                    selectedItem.dataset.studentId = selection.id;
                    selectedItem.dataset.preguntaId = preguntaId;

                    selectedItem.innerHTML = `<span class="order">${count}.</span> ${selection.element.dataset.nombreEstudiante}`; // Usar el nombre del data attribute

                    selectedStudentsList.appendChild(selectedItem);

                    // Llenar campo oculto correspondiente con el NUEVO orden
                    const hiddenInput = questionBlock.querySelector(`.response-input[data-order="${count}"]`);
                    if (hiddenInput) {
                        hiddenInput.value = selection.id;
                    } else {
                        console.error(`❌ Campo oculto no encontrado para Pregunta ${preguntaId}, Nuevo Orden ${count}. Verifica si hay suficientes campos ocultos para MAX_SELECTIONS_PER_QUESTION.`);
                    }

                    // Asegurarse de que la opción original tenga la clase 'selected' y el icono visible
                    selection.element.classList.add('selected');
                     // Opcional: añadir clase de tipo si decides usar colores diferentes para seleccionados
                     // const tipoRelacion = questionBlock.querySelector('input[type="hidden"][name^="tipo_relacion_"]').value;
                     // selection.element.classList.toggle('is-preferencia', tipoRelacion === 'preferencia');
                     // selection.element.classList.toggle('is-rechazo', tipoRelacion === 'rechazo');
                     const indicator = selection.element.querySelector('.selection-indicator');
                     if(indicator) indicator.style.display = 'block'; // Mostrar icono

                } else {
                    // Si por alguna razón una entrada en currentSelections es inválida, limpiarla
                    delete currentSelections[orderKey];
                    console.warn(`⚠️ Entrada inválida en selectedResponses para pregunta ${preguntaId}, clave ${orderKey}`);
                }
            });

            // Actualizar contador visual
            selectedCountSpan.textContent = count;

            // Actualizar el estado del contenedor de opciones para aplicar estilos (como atenuar no seleccionados)
             if (count > 0) {
                 studentOptionsListContainer.classList.add('has-selection');
             } else {
                 studentOptionsListContainer.classList.remove('has-selection');
             }

            // Asegurarse de que las opciones NO seleccionadas NO tengan la clase 'selected'
             studentOptions.forEach(choice => {
                 // Solo procesar opciones de estudiante que pertenecen a esta pregunta
                 if (parseInt(choice.dataset.preguntaId) === parseInt(preguntaId)) {
                     // Si el ID del estudiante de esta opción NO está en la lista de IDs seleccionados para esta pregunta
                     // Usamos toString() porque data-student-id es una cadena
                     if (!selectedStudentIdsForThisQuestion.includes(choice.dataset.studentId)) {
                         choice.classList.remove('selected');
                          // También remover clases de tipo si las añadiste
                          // choice.classList.remove('is-preferencia', 'is-rechazo');
                          const indicator = choice.querySelector('.selection-indicator');
                          if(indicator) indicator.style.display = 'none'; // Ocultar icono
                     }
                 }
             });

            // Si el número máximo de selecciones ya fue alcanzado, podrías deshabilitar temporalmente
            // los elementos student-choice que no están seleccionados para esta pregunta.
            // Esto previene clics adicionales hasta que se deseleccione algo.
            // Por ahora, se dejan clickeables (con menor opacidad) para permitir deseleccionar y re-seleccionar otros.


        }


        // --- Añadir event listeners a cada opción de estudiante ---
        studentOptions.forEach(choiceElement => {
            const estudianteIdClicked = choiceElement.dataset.studentId; // ID del estudiante en la tarjeta cliqueada
            const preguntaId = parseInt(choiceElement.dataset.preguntaId); // ID de la pregunta

            choiceElement.addEventListener('click', function() {

                // === INICIO: VALIDACIÓN DE AUTOSELECCIÓN ===
                const estudianteQuienRespondeId = estudianteQuienRespondeDropdown.value; // ID del estudiante que responde (del dropdown)

                // Verificar si hay un estudiante seleccionado en el primer dropdown
                // Y si el estudiante cliqueado es el mismo que está respondiendo
                if (estudianteQuienRespondeId && estudianteIdClicked === estudianteQuienRespondeId) {
                    // Aquí puedes mostrar un mensaje de error al usuario
                    // alert('Error: No puedes seleccionarte a ti mismo en esta pregunta.'); // Una alerta simple
                    // O mejor, un mensaje dentro de la página, si tienes un elemento para ello.
                    // Por ahora, usaremos una alerta para que sea visible rápidamente.
                    alert('Error: No puedes seleccionarte a ti mismo en esta pregunta.');
                    console.warn(`🚫 Intento de autoselección: Estudiante ${estudianteIdClicked} intentó seleccionarse en Pregunta ${preguntaId}.`);
                    return; // Detiene la ejecución de la función, evitando que se seleccione a sí mismo
                }
                // === FIN: VALIDACIÓN DE AUTOSELECCIÓN ===


                // Obtener el tipo de relación de la pregunta para posible uso visual (ej: color)
                 // const tipoRelacion = this.closest('.question-block').querySelector('input[type="hidden"][name^="tipo_relacion_"]').value;


                // Si el elemento YA está seleccionado, lo deseleccionamos
                if (this.classList.contains('selected')) {
                    // Encontrar el orden actual del estudiante en selectedResponses[preguntaId]
                    let orderToRemove = null;
                     let originalChoiceElementToDeselect = null; // Esta variable no se necesita aquí realmente

                     // Recorrer el objeto selectedResponses[preguntaIdOfDeselection] para encontrar la entrada con este estudianteId
                     for (const orderKey in selectedResponses[preguntaId]) {
                          if (selectedResponses[preguntaId][orderKey].id === estudianteIdClicked) { // Usamos estudianteIdClicked aquí
                             orderToRemove = orderKey; // Encontramos la clave (orden) a eliminar
                             // La referencia al elemento original ya está en selectedResponses
                             break;
                          }
                     }

                    if (orderToRemove !== null) {
                         // No necesitamos la referencia al elemento original aquí, ya la obtenemos de selectedResponses
                         // const originalChoiceElementToDeselect = selectedResponses[preguntaId][orderToRemove].element;

                         delete selectedResponses[preguntaId][orderToRemove]; // Eliminar de la estructura JS

                         // Reindexar las selecciones restantes para esa pregunta
                         const reindexedSelections = {};
                         let newOrder = 1;
                         // Recorrer las selecciones restantes ordenando por su antigua clave (orden)
                         Object.keys(selectedResponses[preguntaId]).sort((a, b) => parseInt(a) - parseInt(b)).forEach(oldOrderKey => {
                              reindexedSelections[newOrder] = selectedResponses[preguntaId][oldOrderKey];
                              newOrder++;
                         });
                         selectedResponses[preguntaId] = reindexedSelections; // Reemplazar con la estructura reindexada

                         // La función updateQuestionUI se encargará de quitar la clase 'selected'
                         // y ocultar el icono en el elemento original (usando la referencia guardada en selectedResponses).

                         updateQuestionUI(preguntaId); // Actualizar la interfaz y campos ocultos para esta pregunta
                         console.log(`➖ Selección eliminada: Pregunta ${preguntaId}, Estudiante ${estudianteIdClicked}`);
                    }

                } else { // Si el elemento NO está seleccionado, intentamos seleccionarlo
                    const selectedCount = Object.keys(selectedResponses[preguntaId]).length; // Cuántos llevamos seleccionados para esta pregunta

                    // Verificar si ya se alcanzó el máximo de selecciones para esta pregunta
                    if (selectedCount >= MAX_SELECTIONS_PER_QUESTION) {
                        console.warn(`⚠️ Límite de selecciones (${MAX_SELECTIONS_PER_QUESTION}) alcanzado para la pregunta ${preguntaId}. No se puede seleccionar más.`);
                        // Puedes mostrar un mensaje al usuario aquí
                        return; // Salir de la función si ya se seleccionó el máximo
                    }

                     // Verificar si este estudiante ya fue seleccionado para esta pregunta (esto debería ser redundante con la clase 'selected' pero es una doble seguridad)
                     // Usamos some() para buscar si el id del estudiante ya existe en los valores del objeto
                     if (Object.values(selectedResponses[preguntaId]).some(selection => selection.id === estudianteIdClicked)) {
                         console.warn(`⚠️ Estudiante ${estudianteIdClicked} ya seleccionado para la pregunta ${preguntaId} (doble clic?).`);
                         return; // Salir si ya está seleccionado
                     }


                    // === Registrar la selección ===
                    const order = selectedCount + 1; // El orden es la siguiente posición disponible (1-basado)
                    selectedResponses[preguntaId][order] = { id: estudianteIdClicked, element: this }; // Guardar ID y referencia al elemento original

                    // Añadir clases de tipo si quieres diferenciarlos visualmente al seleccionar (depende del tipo de la pregunta)
                     // const tipoRelacion = this.closest('.question-block').querySelector('input[type="hidden"][name^="tipo_relacion_"]').value;
                     // this.classList.add('selected', tipoRelacion === 'preferencia' ? 'is-preferencia' : 'is-rechazo');

                    updateQuestionUI(preguntaId); // Actualizar la interfaz y campos ocultos para esta pregunta

                    console.log(`✅ Selección registrada: Pregunta ${preguntaId}, Orden ${order} = Estudiante ${estudianteIdClicked}`);

                }
            });
        });

         // --- Lógica para manejar clics en los elementos visuales de seleccionados (para deseleccionar) ---
         // Usamos event delegation en el contenedor de seleccionados
         document.querySelectorAll('.selected-students-list').forEach(selectedList => {
             selectedList.addEventListener('click', function(event) {
                 const targetItem = event.target.closest('.selected-student-item'); // Encontrar el elemento .selected-student-item clicado
                 if (targetItem) {
                     const estudianteIdToDeselect = targetItem.dataset.studentId;
                     const preguntaIdOfDeselection = parseInt(targetItem.dataset.preguntaId);

                     // Encontrar el orden actual del estudiante en selectedResponses[preguntaIdOfDeselection]
                     let orderToRemove = null;
                      // No necesitamos la referencia al elemento original aquí para eliminar, ya la obtenemos en updateQuestionUI
                      for (const orderKey in selectedResponses[preguntaIdOfDeselection]) {
                           if (selectedResponses[preguntaIdOfDeselection][orderKey].id === estudianteIdToDeselect) {
                              orderToRemove = orderKey; // Encontramos la clave (orden) a eliminar
                              break;
                           }
                      }

                     if (orderToRemove !== null) {
                          // La función updateQuestionUI se encargará de quitar la clase 'selected'
                          // y ocultar el icono en el elemento original usando la referencia guardada.
                          // Simplemente eliminamos la entrada de la estructura JS.
                          delete selectedResponses[preguntaIdOfDeselection][orderToRemove];

                          // Reindexar las selecciones restantes para esa pregunta
                          const reindexedSelections = {};
                          let newOrder = 1;
                          Object.keys(selectedResponses[preguntaIdOfDeselection]).sort((a, b) => parseInt(a) - parseInt(b)).forEach(oldOrderKey => {
                               reindexedSelections[newOrder] = selectedResponses[preguntaIdOfDeselection][oldOrderKey];
                               newOrder++;
                          });
                          selectedResponses[preguntaIdOfDeselection] = reindexedSelections; // Reemplazar con la estructura reindexada


                          updateQuestionUI(preguntaIdOfDeselection); // Actualizar la interfaz y campos ocultos para esta pregunta
                          console.log(`➖ Selección eliminada (clic en visual): Pregunta ${preguntaIdOfDeselection}, Estudiante ${estudianteIdToDeselect}`);
                     }
                 }
             });
         });


        // --- Validación antes de enviar el formulario ---
        form.addEventListener('submit', function(event) {
            let allQuestionsAnswered = true;
            questionBlocks.forEach(block => {
                const preguntaId = parseInt(block.dataset.preguntaId);
                const selectionsCount = Object.keys(selectedResponses[preguntaId]).length;
                // Validar si se han hecho el número REQUERIDO de selecciones por pregunta.
                // El número requerido se asume por el número de campos ocultos .response-input.
                 const requiredSelections = block.querySelectorAll('.response-input').length;

                 // Además de verificar el número de selecciones, también nos aseguramos de que el estudiante que responde NO esté seleccionado en NINGUNA PREGUNTA.
                 const estudianteQuienRespondeId = estudianteQuienRespondeDropdown.value;
                 let selfSelectedInQuestion = false;
                 if (estudianteQuienRespondeId) {
                     // Verificar si el estudiante que responde está en las selecciones de esta pregunta
                     const selectionsForThisQuestion = Object.values(selectedResponses[preguntaId]).map(item => item.id);
                     if (selectionsForThisQuestion.includes(estudianteQuienRespondeId)) {
                         selfSelectedInQuestion = true;
                         console.error(`❌ Autoselección detectada en la validación de envío para Pregunta ${preguntaId}.`);
                     }
                 }


                 // La validación falla si no se cumplen las selecciones requeridas O si el estudiante que responde se seleccionó a sí mismo en esta pregunta.
                 if ((selectionsCount !== requiredSelections && requiredSelections > 0) || selfSelectedInQuestion) {
                     allQuestionsAnswered = false;
                     // Opcional: Resaltar visualmente la pregunta incompleta o con error
                     block.style.border = '2px solid red';
                 } else {
                     block.style.border = '1px solid #e0e0e0'; // Restaurar borde si estaba rojo
                 }
            });

            // También validamos que el estudiante que responde esté seleccionado en el dropdown inicial
             const estudianteQuienResponde = document.getElementById('estudiante_quien_responde').value;
             if (!estudianteQuienResponde) {
                 allQuestionsAnswered = false;
                 // Opcional: Resaltar el dropdown de estudiante
                 document.getElementById('estudiante_quien_responde').style.border = '1px solid red';
             } else {
                 document.getElementById('estudiante_quien_responde').style.border = '';
             }


            if (!allQuestionsAnswered) {
                event.preventDefault(); // Detener el envío del formulario
                // Mostrar un mensaje de validación más general o específico si incluyes el check de autoselección aquí también
                // Por simplicidad, mantenemos el mensaje general por ahora.
                validationMessage.classList.remove('d-none'); // Mostrar mensaje de validación
                 // Scroll hasta arriba para que el usuario vea el mensaje
                 window.scrollTo({ top: 0, behavior: 'smooth' });
            } else {
                validationMessage.classList.add('d-none'); // Ocultar mensaje si todo está bien
            }
        });
    });
</script>

@endsection
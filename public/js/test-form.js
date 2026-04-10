(function () {
    const MAX_SELECTIONS_DEFAULT = 3;

    function getQuestionState(store, questionId) {
        if (!store.has(questionId)) {
            store.set(questionId, []);
        }

        return store.get(questionId);
    }

    function getQuestionBlocks(form) {
        return Array.from(form.querySelectorAll('[data-question-id]'));
    }

    function showMessage(container, message, type) {
        if (!container) {
            return;
        }

        const iconMap = {
            danger: 'fa-exclamation-circle',
            warning: 'fa-triangle-exclamation',
            success: 'fa-circle-check',
        };

        container.className = `public-alert public-alert--${type}`;
        container.innerHTML = `<i class="fas ${iconMap[type] ?? iconMap.danger}"></i><span>${message}</span>`;
        container.hidden = false;
    }

    function hideMessage(container) {
        if (!container) {
            return;
        }

        container.hidden = true;
        container.innerHTML = '';
        container.className = 'public-alert public-alert--danger';
    }

    function renderQuestion(block, store, maxSelections) {
        const questionId = block.dataset.questionId;
        const selections = getQuestionState(store, questionId);
        const hiddenInputs = Array.from(block.querySelectorAll('.response-input'));
        const list = block.querySelector('.selected-students-list');
        const count = block.querySelector('.selected-count');
        const questionType = block.dataset.questionType;
        const choiceButtons = Array.from(block.querySelectorAll('[data-student-choice]'));
        const uniqueSelections = selections.filter((studentId, index, array) => array.indexOf(studentId) === index).slice(0, maxSelections);

        store.set(questionId, uniqueSelections);

        hiddenInputs.forEach((input, index) => {
            input.value = uniqueSelections[index] ?? '';
        });

        if (count) {
            count.textContent = String(uniqueSelections.length);
        }

        if (list) {
            list.innerHTML = '';

            if (uniqueSelections.length === 0) {
                list.innerHTML = '<span class="test-form__selection-empty">Aun no has seleccionado a nadie.</span>';
            } else {
                uniqueSelections.forEach((studentId, index) => {
                    const choice = block.querySelector(`[data-student-choice][data-student-id="${studentId}"]`);
                    const name = choice?.dataset.studentName ?? `Alumno ${studentId}`;

                    list.insertAdjacentHTML(
                        'beforeend',
                        `<button type="button" class="selected-student-item" data-selected-chip data-question-id="${questionId}" data-student-id="${studentId}">
                            <span class="order">${index + 1}</span>
                            <span>${name}</span>
                        </button>`
                    );
                });
            }
        }

        choiceButtons.forEach((button) => {
            const isSelected = uniqueSelections.includes(button.dataset.studentId);
            const isDimmed = uniqueSelections.length > 0 && !isSelected;

            button.classList.toggle('is-selected', isSelected);
            button.classList.toggle('is-rechazo', isSelected && questionType === 'rechazo');
            button.classList.toggle('is-dimmed', isDimmed);
            button.setAttribute('aria-pressed', isSelected ? 'true' : 'false');
        });
    }

    function restoreInitialSelections(form, store, maxSelections) {
        getQuestionBlocks(form).forEach((block) => {
            const questionId = block.dataset.questionId;
            const initialSelections = Array.from(block.querySelectorAll('.response-input'))
                .map((input) => input.value)
                .filter(Boolean)
                .slice(0, maxSelections);

            store.set(questionId, initialSelections);
            renderQuestion(block, store, maxSelections);
        });
    }

    function clearRespondentConflicts(form, store, maxSelections, respondentId) {
        getQuestionBlocks(form).forEach((block) => {
            const questionId = block.dataset.questionId;
            const currentSelections = getQuestionState(store, questionId)
                .filter((studentId) => studentId !== respondentId);

            store.set(questionId, currentSelections);
            renderQuestion(block, store, maxSelections);
        });
    }

    function validateForm(form, store, maxSelections) {
        const respondent = form.querySelector('[data-respondent-select]');
        const respondentId = respondent?.value ?? '';
        let isValid = Boolean(respondentId);

        if (!respondentId) {
            respondent?.focus();
        }

        getQuestionBlocks(form).forEach((block) => {
            const questionId = block.dataset.questionId;
            const requiredSelections = block.querySelectorAll('.response-input').length;
            const selections = getQuestionState(store, questionId);
            const questionValid = selections.length === Math.min(requiredSelections, maxSelections);

            block.classList.toggle('is-invalid', !questionValid);
            isValid = isValid && questionValid;
        });

        return isValid;
    }

    document.addEventListener('DOMContentLoaded', () => {
        const form = document.querySelector('[data-test-form]');
        if (!form) {
            return;
        }

        const respondent = form.querySelector('[data-respondent-select]');
        const validationMessage = form.querySelector('[data-validation-message]');
        const submitButton = form.querySelector('[data-submit-button]');
        const maxSelections = Number(form.dataset.maxSelections || MAX_SELECTIONS_DEFAULT);
        const selectionStore = new Map();

        restoreInitialSelections(form, selectionStore, maxSelections);

        if (respondent?.value) {
            clearRespondentConflicts(form, selectionStore, maxSelections, respondent.value);
        }

        respondent?.addEventListener('change', () => {
            hideMessage(validationMessage);
            clearRespondentConflicts(form, selectionStore, maxSelections, respondent.value);
        });

        form.addEventListener('click', (event) => {
            const choice = event.target.closest('[data-student-choice]');
            if (choice) {
                const block = choice.closest('[data-question-id]');
                const questionId = block?.dataset.questionId;
                const respondentId = respondent?.value ?? '';

                if (!block || !questionId) {
                    return;
                }

                if (!respondentId) {
                    showMessage(validationMessage, 'Selecciona primero el estudiante que responde.', 'warning');
                    respondent?.focus();
                    return;
                }

                if (choice.dataset.studentId === respondentId) {
                    showMessage(validationMessage, 'No puedes seleccionarte a ti mismo en esta pregunta.', 'danger');
                    return;
                }

                hideMessage(validationMessage);

                const selections = getQuestionState(selectionStore, questionId);
                const selectedIndex = selections.indexOf(choice.dataset.studentId);

                if (selectedIndex >= 0) {
                    selections.splice(selectedIndex, 1);
                } else if (selections.length >= maxSelections) {
                    showMessage(validationMessage, `Solo puedes elegir ${maxSelections} companeros por pregunta.`, 'warning');
                    return;
                } else {
                    selections.push(choice.dataset.studentId);
                }

                selectionStore.set(questionId, selections);
                renderQuestion(block, selectionStore, maxSelections);
                block.classList.remove('is-invalid');
                return;
            }

            const chip = event.target.closest('[data-selected-chip]');
            if (!chip) {
                return;
            }

            const block = form.querySelector(`[data-question-id="${chip.dataset.questionId}"]`);
            const questionId = chip.dataset.questionId;
            const selections = getQuestionState(selectionStore, questionId)
                .filter((studentId) => studentId !== chip.dataset.studentId);

            selectionStore.set(questionId, selections);

            if (block) {
                renderQuestion(block, selectionStore, maxSelections);
                block.classList.remove('is-invalid');
            }
        });

        form.addEventListener('submit', (event) => {
            hideMessage(validationMessage);

            if (!validateForm(form, selectionStore, maxSelections)) {
                event.preventDefault();
                showMessage(validationMessage, 'Completa todas las selecciones requeridas antes de enviar el test.', 'danger');
                form.scrollIntoView({ behavior: 'smooth', block: 'start' });
                return;
            }

            if (submitButton) {
                submitButton.disabled = true;
                submitButton.classList.add('is-submitting');
            }
        });
    });
})();

document.addEventListener('DOMContentLoaded', () => {
    const typeSelect = document.querySelector('#question_type');
    const choicesContainer = document.querySelector('#choices-container');
    if (typeSelect && choicesContainer) {
        typeSelect.addEventListener('change', () => {
            if (typeSelect.value === 'MCQ') {
                choicesContainer.classList.remove('d-none');
            } else {
                choicesContainer.classList.add('d-none');
            }
        });
    }
});

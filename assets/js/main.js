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

    const sidebarToggle = document.querySelector('.sidebar-toggle');
    const body = document.body;
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', () => {
            body.classList.toggle('sidebar-open');
        });
    }

    document.querySelectorAll('.app-sidebar a').forEach(link => {
        link.addEventListener('click', () => {
            body.classList.remove('sidebar-open');
        });
    });
});

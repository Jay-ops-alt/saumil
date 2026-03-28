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

    const headerInput = document.getElementById('headerText');
    const headerDisplay = document.getElementById('headerDisplay');
    const watermarkInput = document.getElementById('watermarkText');
    const watermarkLayer = document.querySelector('.watermark');
    const candidateInput = document.getElementById('candidateNameInput');
    const candidateDisplay = document.getElementById('candidateDisplay');

    if (headerDisplay) {
        const updateHeader = () => {
            const fallback = headerDisplay.dataset.default || 'UNIVERSITY EXAMINATION';
            headerDisplay.textContent = (headerInput && headerInput.value.trim()) ? headerInput.value.trim() : fallback;
        };
        updateHeader();
        headerInput?.addEventListener('input', updateHeader);
    }

    if (watermarkLayer) {
        const updateWatermark = () => {
            const text = (watermarkInput && watermarkInput.value.trim()) ? watermarkInput.value.trim() : (watermarkInput?.placeholder || 'CONFIDENTIAL');
            watermarkLayer.textContent = text;
        };
        updateWatermark();
        watermarkInput?.addEventListener('input', updateWatermark);
    }

    if (candidateDisplay) {
        const updateCandidate = () => {
            const text = (candidateInput && candidateInput.value.trim()) ? candidateInput.value.trim() : '________________';
            candidateDisplay.textContent = text;
        };
        updateCandidate();
        candidateInput?.addEventListener('input', updateCandidate);
    }
});

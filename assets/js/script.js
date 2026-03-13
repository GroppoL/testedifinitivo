document.addEventListener('DOMContentLoaded', function() {
    // 1. Tab functionality
    const tabButtons = document.querySelectorAll('.tab-btn');
    tabButtons.forEach(button => {
        button.addEventListener('click', () => {
            const tabName = button.getAttribute('data-tab');
            switchTab(tabName);
        });
    });

    // 2. Booking type selector
    const typeOptions = document.querySelectorAll('.type-option');
    const tipoInput = document.getElementById('tipo_agendamento');
    const tattooFields = document.querySelector('.tattoo-fields');

    if (typeOptions.length > 0) {
        typeOptions.forEach(option => {
            option.addEventListener('click', () => {
                typeOptions.forEach(opt => opt.classList.remove('active'));
                option.classList.add('active');

                const type = option.getAttribute('data-type');
                if (tipoInput) tipoInput.value = type;

                if (tattooFields) {
                    const isTattoo = (type === 'tattoo');
                    tattooFields.style.display = isTattoo ? 'block' : 'none';

                    const inputs = tattooFields.querySelectorAll('select, input, textarea');
                    inputs.forEach(input => {
                        input.required = isTattoo;
                    });
                }
            });
        });
    }

    // 3. Lógica para manter a aba ativa após redirecionamento
    const urlParams = new URLSearchParams(window.location.search);
    const status = urlParams.get('status');
    const hash = window.location.hash.replace('#', '');

    if (status === 'sucesso' || hash === 'agendamentos') {
        switchTab('agendamentos');
        if (status === 'sucesso') {
            showNotification('Agendamento solicitado com sucesso!', 'success');
        }
    } else if (hash === 'salvas') {
        switchTab('salvas');
    }
});

// Tornamos a função global para que o onclick do HTML continue funcionando
window.switchTab = function(tabName) {
    const tabButtons = document.querySelectorAll('.tab-btn');
    const tabContents = document.querySelectorAll('.tab-content');

    tabButtons.forEach(btn => {
        btn.classList.toggle('active', btn.getAttribute('data-tab') === tabName);
    });

    tabContents.forEach(content => {
        content.classList.toggle('active', content.id === tabName);
    });
}

function removerTattoo(id) {
    if (confirm('Tem certeza que deseja remover esta referência?')) {
        window.location.href = 'remover-referencia.php?id=' + id;
    }
}

function showNotification(message, type) {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type}`;
    notification.textContent = message;
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 9999;
        min-width: 300px;
        padding: 15px;
        border-radius: 5px;
        background-color: ${type === 'success' ? '#d4edda' : '#f8d7da'};
        color: ${type === 'success' ? '#155724' : '#721c24'};
        border: 1px solid ${type === 'success' ? '#c3e6cb' : '#f5c6cb'};
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        transition: opacity 0.5s ease;
    `;

    document.body.appendChild(notification);

    setTimeout(() => {
        notification.style.opacity = '0';
        setTimeout(() => notification.remove(), 500);
    }, 3000);
}
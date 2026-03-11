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

                    // Seleciona todos os campos dentro da div de tattoo para alterar o required de uma vez
                    const inputs = tattooFields.querySelectorAll('select, input, textarea');
                    inputs.forEach(input => {
                        input.required = isTattoo;
                    });
                }
            });
        });
    }

    // 3. Show success message
    if (window.location.search.includes('sucesso=1')) {
        showNotification('Agendamento solicitado com sucesso!', 'success');
    }
});

function switchTab(tabName) {
    const tabButtons = document.querySelectorAll('.tab-btn');
    const tabContents = document.querySelectorAll('.tab-content');

    tabButtons.forEach(btn => {
        btn.classList.toggle('active', btn.getAttribute('data-tab') === tabName);
    });

    tabContents.forEach(content => {
        content.classList.toggle('active', content.id === tabName);
    });
}

// Faz a página abrir na aba de salvas se houver o #salvas na URL
window.addEventListener('load', function() {
    if (window.location.hash === '#salvas') {
        switchTab('salvas');
    }
});
function removerTattoo(id) {
    if (confirm('Tem certeza que deseja remover esta referência?')) {
        // Ajuste o nome do arquivo aqui para bater com o seu arquivo de exclusão
        window.location.href = 'remover-referencia.php?id=' + id;
    }
}

function showNotification(message, type) {
    const notification = document.createElement('div');
    // Adicionei classes de estilo básico caso seu CSS não tenha
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
    `;

    document.body.appendChild(notification);

    setTimeout(() => {
        notification.style.opacity = '0';
        notification.style.transition = 'opacity 0.5s ease';
        setTimeout(() => notification.remove(), 500);
    }, 3000);
}
<?php
session_start();

require_once('../includes/config.php');
require_once('../includes/funcoes.php');

verificarLogin();
verificarNivel('CLIENTE');

$usuario_id = $_SESSION['user_id'];
$usuario_nome = $_SESSION['user_nome'];

// Buscar idCliente
$stmt = $pdo->prepare("SELECT idCliente FROM cliente WHERE idUsuario = ?");
$stmt->execute([$usuario_id]);
$cliente = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$cliente) {
    die("Perfil de cliente não encontrado.");
}

$idCliente = $cliente['idCliente'];

// Buscar agendamentos
$stmt = $pdo->prepare("
    SELECT * FROM agendamento
    WHERE idCliente = ?
    ORDER BY criadoEm DESC
");
$stmt->execute([$idCliente]);
$agendamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);
 
// Buscar referências
$stmt = $pdo->prepare("
    SELECT * FROM referencia_salva
    WHERE idCliente = ?
    ORDER BY dataSalvo DESC
");
$stmt->execute([$idCliente]);
$referencias = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<link rel="stylesheet" href="./../assets/css/style.css">

<div class="client-area">
    <div class="container">
        <div class="client-header">
            <h1 class="client-title">
                Olá, <span class="text-red">
                    <?php echo htmlspecialchars($usuario_nome); ?>
                </span>!
            </h1>

            <p class="client-subtitle">Gerencie suas tatuagens e agendamentos</p>

            <div class="tabs">
                <button class="tab-btn active" data-tab="agendamentos">
                    📅 Meus Agendamentos (<?php echo count($agendamentos ?? []); ?>)
                </button>

                <button class="tab-btn" data-tab="salvas">
                    ❤️ Tatuagens Salvas (<?php echo count($referencias ?? []); ?>)
                </button>

                <button class="tab-btn" data-tab="novo">
                    ➕ Novo Agendamento
                </button>
            </div>

            <div class="tab-content active" id="agendamentos">
                <?php if (empty($agendamentos)): ?>
                    <div class="empty-state">
                        <div class="empty-icon">📅</div>
                        <p class="empty-text">Você ainda não tem agendamentos</p>
                        <button class="btn-primary" onclick="switchTab('novo')">Fazer Agendamento</button>
                    </div>
                <?php else: ?>
                    <div class="bookings-grid">
                        <?php foreach ($agendamentos as $agendamento): ?>
                            <div class="booking-card">
                                <div class="booking-header">
                                    <h3 class="booking-title">
                                        <?php echo $agendamento['tipo'] === 'tattoo' ? 'Agendamento de Tatuagem' : 'Consulta Presencial'; ?>
                                    </h3>
                                    <span class="status-badge status-<?php echo $agendamento['status']; ?>">
                                        <?php
                                        $status_labels = [
                                            'pendente' => '⏳ Pendente',
                                            'confirmado' => '✅ Confirmado',
                                            'cancelado' => '❌ Cancelado'
                                        ];
                                        echo $status_labels[$agendamento['status']] ?? $agendamento['status'];
                                        ?>
                                    </span>
                                </div>

                                <div class="booking-details">
                                    <div class="detail-item">
                                        <span class="detail-label">Data:</span>
                                        <span class="detail-value">
                                            <?php echo date('d/m/Y', strtotime($agendamento['data_agendamento'])); ?>
                                        </span>
                                    </div>
                                    <div class="detail-item">
                                        <span class="detail-label">Horário:</span>
                                        <span class="detail-value"><?php echo $agendamento['hora_agendamento']; ?></span>
                                    </div>

                                    <?php if ($agendamento['tipo'] === 'tattoo'): ?>
                                        <div class="detail-item">
                                            <span class="detail-label">Tipo:</span>
                                            <span class="detail-value"><?php echo ucfirst($agendamento['tipo_tatuagem']); ?></span>
                                        </div>
                                        <div class="detail-item">
                                            <span class="detail-label">Local:</span>
                                            <span class="detail-value"><?php echo htmlspecialchars($agendamento['parte_corpo']); ?></span>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <?php if ($agendamento['descricao']): ?>
                                    <div class="booking-description">
                                        <strong>Descrição:</strong>
                                        <p><?php echo nl2br(htmlspecialchars($agendamento['descricao'])); ?></p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="tab-content" id="salvas">
                <?php if (empty($referencias)): ?>
                    <div class="empty-state">
                        <div class="empty-icon">❤️</div>
                        <p class="empty-text">Você ainda não salvou nenhuma tatuagem</p>
                    </div>
                <?php else: ?>
                    <div class="gallery-grid">
                        <?php foreach ($referencias as $tattoo): ?>
                            <div class="saved-tattoo-card">
                                <img src="<?php echo htmlspecialchars($tattoo['imagem_url']); ?>"
                                     alt="<?php echo htmlspecialchars($tattoo['titulo'] ?? 'Tatuagem'); ?>">
                                <div class="saved-tattoo-info">
                                    <h4><?php echo htmlspecialchars($tattoo['titulo'] ?? 'Sem título'); ?></h4>
                                    <p class="saved-date">
                                        Salvo em <?php echo date('d/m/Y', strtotime($tattoo['dataSalvo'])); ?>
                                    </p>
                                </div>
                                <button class="btn-delete" onclick="removerTattoo(<?php echo $tattoo['idReferencia'] ?? $tattoo['id']; ?>)">
                                    🗑️
                                </button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="tab-content" id="novo">
                <div class="booking-form-container">
                    <h2 class="form-title">Novo Agendamento</h2>

                    <div class="booking-type-selector">
                        <div class="type-option active" data-type="tattoo">
                            <div class="type-icon">🎨</div>
                            <h3>Agendar Tatuagem</h3>
                            <p>Solicite um horário para fazer sua tattoo</p>
                        </div>
                        <div class="type-option" data-type="consulta">
                            <div class="type-icon">💬</div>
                            <h3>Consulta Presencial</h3>
                            <p>Tire suas dúvidas e conheça o estúdio</p>
                        </div>
                    </div>

                    <form method="POST" action="processa-agendamento.php" class="booking-form">
                        <input type="hidden" name="tipo" id="tipo_agendamento" value="tattoo">

                        <div class="tattoo-fields">
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="tipo_tatuagem">Tipo de Tatuagem</label>
                                    <select name="tipo_tatuagem" id="tipo_tatuagem" class="form-input">
                                        <option value="">Selecione...</option>
                                        <option value="nova">Nova Tatuagem</option>
                                        <option value="cobertura">Cobertura</option>
                                        <option value="fechamento">Fechamento</option>
                                        <option value="restauracao">Restauração</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="primeira_tatuagem">É sua primeira tatuagem?</label>
                                    <select name="primeira_tatuagem" id="primeira_tatuagem" class="form-input">
                                        <option value="">Selecione...</option>
                                        <option value="sim">Sim</option>
                                        <option value="nao">Não</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="parte_corpo">Parte do Corpo</label>
                                    <input type="text" name="parte_corpo" id="parte_corpo"
                                        class="form-input" placeholder="Ex: Braço, Costas...">
                                </div>

                                <div class="form-group">
                                    <label for="tamanho">Tamanho Aproximado</label>
                                    <select name="tamanho" id="tamanho" class="form-input">
                                        <option value="">Selecione...</option>
                                        <option value="pequeno">Pequeno (até 5cm)</option>
                                        <option value="medio">Médio (5-15cm)</option>
                                        <option value="grande">Grande (15-30cm)</option>
                                        <option value="muitogrande">Muito Grande (+30cm)</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="descricao">Descrição da Ideia</label>
                                <textarea name="descricao" id="descricao" rows="5"
                                    class="form-input"
                                    placeholder="Descreva sua ideia com o máximo de detalhes..."></textarea>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="data_agendamento">📅 Data Preferida</label>
                                <input type="date" name="data_agendamento" id="data_agendamento"
                                    required class="form-input"
                                    min="<?php echo date('Y-m-d'); ?>">
                            </div>

                            <div class="form-group">
                                <label for="hora_agendamento">🕐 Horário Preferido</label>
                                <select name="hora_agendamento" id="hora_agendamento" required class="form-input">
                                    <option value="">Selecione...</option>
                                    <option value="09:00">09:00</option>
                                    <option value="10:00">10:00</option>
                                    <option value="11:00">11:00</option>
                                    <option value="12:00">12:00</option>
                                    <option value="14:00">14:00</option>
                                    <option value="15:00">15:00</option>
                                    <option value="16:00">16:00</option>
                                    <option value="17:00">17:00</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-notice">
                            <p>✅ Ao enviar, você concorda que as informações são verdadeiras e o agendamento está sujeito à confirmação.</p>
                        </div>

                        <button type="submit" class="btn-submit">Solicitar Agendamento</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div style="padding: 20px; text-align: center;">
        <a href="../logout.php" style="color: red; text-decoration: none;">Sair</a>
    </div>
</div>

<script>
// Função principal para trocar as abas
function switchTab(tabId) {
    // Remove active de todos os botões
    document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
    // Remove active de todos os conteúdos
    document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));

    // Adiciona active no botão certo e no conteúdo certo
    const activeBtn = document.querySelector(`[data-tab="${tabId}"]`);
    if(activeBtn) activeBtn.classList.add('active');

    const activeContent = document.getElementById(tabId);
    if(activeContent) activeContent.classList.add('active');
}

// Inicializa os eventos quando a página carrega
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.tab-btn').forEach(button => {
        button.addEventListener('click', () => {
            const tabId = button.getAttribute('data-tab');
            switchTab(tabId);
        });
    });

    // Seletor de tipo de agendamento (Tattoo ou Consulta)
    const typeOptions = document.querySelectorAll('.type-option');
    const inputTipo = document.getElementById('tipo_agendamento');
    const tattooFields = document.querySelector('.tattoo-fields');

    typeOptions.forEach(option => {
        option.addEventListener('click', () => {
            typeOptions.forEach(opt => opt.classList.remove('active'));
            option.classList.add('active');
            
            const tipo = option.getAttribute('data-type');
            inputTipo.value = tipo;

            // Esconde campos de tattoo se for apenas consulta
            if(tipo === 'consulta') {
                tattooFields.style.display = 'none';
            } else {
                tattooFields.style.display = 'block';
            }
        });
    });
});

function removerTattoo(id) {
    if(confirm('Deseja realmente remover esta referência?')) {
        window.location.href = 'remover-referencia.php?id=' + id;
    }
}
</script>
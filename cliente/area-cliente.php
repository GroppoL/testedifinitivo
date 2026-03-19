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
$stmt = $pdo->prepare("SELECT * FROM agendamento WHERE idCliente = ? ORDER BY criadoEm DESC");
$stmt->execute([$idCliente]);
$agendamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Buscar referências (Tatuagens Salvas)
$stmt = $pdo->prepare("SELECT * FROM referencia_salva WHERE idCliente = ? ORDER BY dataSalvo DESC");
$stmt->execute([$idCliente]);
$tatuagens_salvas = $stmt->fetchAll(PDO::FETCH_ASSOC);

include '../includes/header.php'; 
?>

<div class="client-area">
    <?php if (isset($_GET['status']) && $_GET['status'] == 'sucesso'): ?>
        <div style="background-color: #d4edda; color: #155724; padding: 15px; margin: 20px auto; max-width: 1200px; border-radius: 5px; text-align: center; border: 1px solid #c3e6cb; font-weight: bold;">
            📅 Solicitação enviada! Aguarde a confirmação do tatuador.
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['cancelado']) && $_GET['cancelado'] == 'sucesso'): ?>
        <div style="background-color: #fff3cd; color: #856404; padding: 15px; margin: 20px auto; max-width: 1200px; border-radius: 5px; text-align: center; border: 1px solid #ffeeba; font-weight: bold;">
            ❌ Agendamento cancelado com sucesso.
        </div>
    <?php endif; ?>

    <div class="container">
        <div class="client-header">
            <h1 class="client-title">
                Olá, <span class="text-red"><?php echo htmlspecialchars($usuario_nome); ?></span>!
            </h1>
            <p class="client-subtitle">Gerencie suas tatuagens e agendamentos</p>

            <div class="tabs">
                <button class="tab-btn active" data-tab="agendamentos">
                    📅 Meus Agendamentos (<?php echo count($agendamentos); ?>)
                </button>
                <button class="tab-btn" data-tab="salvas">
                    ❤️ Tatuagens Salvas (<?php echo count($tatuagens_salvas); ?>)
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
                                        <?php echo $agendamento['tipoAgendamento'] === 'tattoo' ? 'Agendamento de Tatuagem' : 'Consulta Presencial'; ?>
                                    </h3>
                                    
                                    <div style="display: flex; flex-direction: column; align-items: flex-end; gap: 8px;">
                                        <span class="status-badge status-<?php echo strtolower($agendamento['status']); ?>">
                                            <?php
                                            $status_labels = [
                                                'PENDENTE' => '⏳ Pendente',
                                                'CONFIRMADO' => '✅ Confirmado',
                                                'CONCLUIDO' => '✔️ Concluído',
                                                'CANCELADO' => '❌ Cancelado'
                                            ];
                                            echo $status_labels[$agendamento['status']] ?? $agendamento['status'];
                                            ?>
                                        </span>

                                        <?php if ($agendamento['status'] == 'PENDENTE' || $agendamento['status'] == 'CONFIRMADO'): ?>
                                            <button onclick="confirmarCancelamento(<?php echo $agendamento['idAgendamento']; ?>)" 
                                                    style="background: none; border: 1px solid #ff3b3b; color: #ff3b3b; padding: 4px 8px; border-radius: 4px; cursor: pointer; font-size: 11px; font-weight: bold; transition: 0.3s;">
                                                Cancelar
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="booking-details">
                                    <div class="detail-item">
                                        <span class="detail-label">Data:</span>
                                        <span class="detail-value"><?php echo date('d/m/Y', strtotime($agendamento['dataAgendamento'])); ?></span>
                                    </div>
                                    <div class="detail-item">
                                        <span class="detail-label">Horário:</span>
                                        <span class="detail-value"><?php echo substr($agendamento['horaAgendamento'], 0, 5); ?></span>
                                    </div>

                                    <?php if ($agendamento['tipoAgendamento'] === 'tattoo'): ?>
                                        <div class="detail-item">
                                            <span class="detail-label">Tipo:</span>
                                            <span class="detail-value"><?php echo htmlspecialchars($agendamento['tipoTatuagem'] ?? ''); ?></span>
                                        </div>
                                        <div class="detail-item">
                                            <span class="detail-label">Local:</span>
                                            <span class="detail-value"><?php echo htmlspecialchars($agendamento['parteCorpo'] ?? ''); ?></span>
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
                </div>

            <div class="tab-content" id="novo">
                </div>

            <div style="margin-top: 30px; border-top: 1px solid #333; padding-top: 20px;">
                 <a href="../logout.php" style="color: #ff3b3b; text-decoration: none; font-weight: bold;"><i class="fas fa-sign-out-alt"></i> Sair da Conta</a>
            </div>
        </div> 
    </div> 
</div>

<script>
// Função para confirmação de cancelamento
function confirmarCancelamento(id) {
    if (confirm("Tem certeza que deseja cancelar este agendamento? Esta ação não pode ser desfeita.")) {
        window.location.href = "cancelar-agendamento.php?id=" + id;
    }
}
</script>

<script src="../assets/js/script.js"></script>
<?php include '../includes/footer.php'; ?>
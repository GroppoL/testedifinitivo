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

// Buscar agendamentos - Corrigido para usar a coluna correta de ordenação se necessário
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

include '../includes/header.php'; 
?>

<div class="client-area">
    <?php if (isset($_GET['status']) && $_GET['status'] == 'sucesso'): ?>
        <div style="background-color: #d4edda; color: #155724; padding: 15px; margin: 20px auto; max-width: 1200px; border-radius: 5px; text-align: center; border: 1px solid #c3e6cb; font-weight: bold;">
            📅 Solicitação enviada! Aguarde a confirmação do tatuador.
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['status']) && $_GET['status'] == 'removido'): ?>
        <div style="background-color: #d4edda; color: #155724; padding: 15px; margin: 20px auto; max-width: 1200px; border-radius: 5px; text-align: center; border: 1px solid #c3e6cb; font-weight: bold;">
            ✅ Referência removida com sucesso!
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
                                        <?php echo $agendamento['tipoAgendamento'] === 'tattoo' ? 'Agendamento de Tatuagem' : 'Consulta Presencial'; ?>
                                    </h3>
                                    <span class="status-badge status-<?php echo $agendamento['status']; ?>">
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
                                </div>

                                <div class="booking-details">
                                    <div class="detail-item">
                                        <span class="detail-label">Data:</span>
                                        <span class="detail-value"><?php echo date('d/m/Y', strtotime($agendamento['dataAgendamento'])); ?></span>
                                    </div>
                                    <div class="detail-item">
                                        <span class="detail-label">Horário:</span>
                                        <span class="detail-value"><?php echo $agendamento['horaAgendamento']; ?></span>
                                    </div>

                                    <?php if ($agendamento['tipoAgendamento'] === 'tattoo'): ?>
                                        <div class="detail-item">
                                            <span class="detail-label">Tipo:</span>
                                            <span class="detail-value"><?php echo htmlspecialchars($agendamento['tipoTatuagem']); ?></span>
                                        </div>
                                        <div class="detail-item">
                                            <span class="detail-label">Local:</span>
                                            <span class="detail-value"><?php echo htmlspecialchars($agendamento['parteCorpo']); ?></span>
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

            ...
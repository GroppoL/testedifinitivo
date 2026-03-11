<?php
session_start();

require_once '../config.php';
require_once '../funcoes.php';

verificarLogin();
verificarNivel('TATUADOR');

$usuario_id = $_SESSION['user_id'];
$usuario_nome = $_SESSION['user_nome'];

// Buscar idTatuador
$stmt = $pdo->prepare("SELECT idTatuador FROM tatuador WHERE idUsuario = ?");
$stmt->execute([$usuario_id]);
$tatuador = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$tatuador) {
    die("Perfil de tatuador não encontrado.");
}

$idTatuador = $tatuador['idTatuador'];

// Buscar agendamentos
$stmt = $pdo->prepare("
    SELECT a.*, u.nome AS nomeCliente
    FROM agendamento a
    JOIN cliente c ON a.idCliente = c.idCliente
    JOIN usuario u ON c.idUsuario = u.idUsuario
    WHERE a.idTatuador = ?
    ORDER BY a.dataHora ASC
");

$stmt->execute([$idTatuador]);
$agendamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h1>Área do Tatuador</h1>
<p>Bem-vindo, <?php echo htmlspecialchars($usuario_nome); ?></p>

<hr>

<h2>📅 Meus Agendamentos</h2>

<?php if (empty($agendamentos)): ?>
    <p>Nenhum agendamento encontrado.</p>
<?php else: ?>
    <?php foreach ($agendamentos as $ag): ?>
        <div style="border:1px solid #ccc; padding:10px; margin-bottom:10px;">
            <strong>Cliente:</strong> <?php echo htmlspecialchars($ag['nomeCliente']); ?><br>
            <strong>Data:</strong> <?php echo date('d/m/Y H:i', strtotime($ag['dataHora'])); ?><br>
            <strong>Tipo:</strong> <?php echo htmlspecialchars($ag['tipoTatuagem']); ?><br>
            <strong>Status:</strong> <?php echo $ag['status']; ?><br>
            <strong>Descrição:</strong><br>
            <?php echo nl2br(htmlspecialchars($ag['descricao'])); ?>

            <br><br>

            <?php if ($ag['status'] === 'PENDENTE'): ?>
                <a href="atualizar-status.php?id=<?php echo $ag['idAgendamento']; ?>&status=CONFIRMADO">✅ Confirmar</a> |
                <a href="atualizar-status.php?id=<?php echo $ag['idAgendamento']; ?>&status=CANCELADO">❌ Cancelar</a>
            <?php elseif ($ag['status'] === 'CONFIRMADO'): ?>
                <a href="atualizar-status.php?id=<?php echo $ag['idAgendamento']; ?>&status=CONCLUIDO">✔ Concluir</a>
            <?php endif; ?>

        </div>
    <?php endforeach; ?>
<?php endif; ?>

<br>
<a href="../logout.php">Sair</a>
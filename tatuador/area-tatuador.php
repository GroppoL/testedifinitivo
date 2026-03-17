<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/funcoes.php';

verificarLogin();
verificarNivel('TATUADOR');

$usuario_id = $_SESSION['user_id'];
$usuario_nome = $_SESSION['user_nome'];

// 1. Buscar idTatuador
$stmtTattoo = $pdo->prepare("SELECT idTatuador FROM tatuador WHERE idUsuario = ?");
$stmtTattoo->execute([$usuario_id]);
$tatuador = $stmtTattoo->fetch(PDO::FETCH_ASSOC);

if (!$tatuador) {
    die("Perfil de tatuador não encontrado.");
}

$idTatuador = $tatuador['idTatuador'];

// 2. Capturar Filtro de Data
$filtro_data = filter_input(INPUT_GET, 'data_busca', FILTER_SANITIZE_SPECIAL_CHARS);

// 3. Buscar agendamentos com JOIN para pegar telefone do usuário
$sql = "SELECT a.*, u.nome AS nomeCliente, u.email, u.login as telefone
        FROM agendamento a
        JOIN cliente c ON a.idCliente = c.idCliente
        JOIN usuario u ON c.idUsuario = u.idUsuario
        WHERE a.idTatuador = ?";

if ($filtro_data) {
    $sql .= " AND a.dataAgendamento = ?";
}

$sql .= " ORDER BY a.dataAgendamento ASC, a.horaAgendamento ASC";

$stmt = $pdo->prepare($sql);
$params = $filtro_data ? [$idTatuador, $filtro_data] : [$idTatuador];
$stmt->execute($params);
$agendamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Painel Roosevelt - Studio Sombra</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .filter-section { background: #1e1e1e; padding: 20px; border-radius: 10px; margin-bottom: 30px; border: 1px solid #333; }
        .card-agendamento { background: #1e1e1e; border: 1px solid #333; padding: 20px; margin-bottom: 15px; border-radius: 8px; position: relative; }
        .status-badge { padding: 5px 10px; border-radius: 4px; font-size: 12px; font-weight: bold; text-transform: uppercase; }
        .PENDENTE { background: #ffa50033; color: #ffa500; }
        .CONFIRMADO { background: #25d36633; color: #25d366; }
        .btn-whats { background: #25d366; color: #fff; padding: 8px 15px; border-radius: 5px; text-decoration: none; display: inline-block; margin-top: 10px; font-size: 14px; }
        .btn-acao { text-decoration: none; padding: 8px 12px; border-radius: 4px; font-size: 13px; margin-right: 5px; display: inline-block; }
        .btn-confirmar { background: #fff; color: #000; }
        .btn-cancelar { background: #ff3b3b; color: #fff; }
    </style>
</head>
<body>

<div class="container" style="max-width: 900px; margin: 50px auto; padding: 0 20px;">
    <h1>Painel do Tatuador</h1>
    <p>Bem-vindo, <strong><?php echo htmlspecialchars($usuario_nome); ?></strong> | <a href="../logout.php" style="color: #ff3b3b;">Sair</a></p>

    <div class="filter-section">
        <form method="GET">
            <label>Filtrar agendamentos por data:</label><br><br>
            <input type="date" name="data_busca" value="<?php echo $filtro_data; ?>" style="padding: 10px; border-radius: 5px; border: 1px solid #444; background: #000; color: #fff;">
            <button type="submit" class="btn-submit" style="width: auto; padding: 10px 20px;">Filtrar</button>
            <?php if($filtro_data): ?>
                <a href="area-tatuador.php" style="color: #888; margin-left: 15px;">Limpar</a>
            <?php endif; ?>
        </form>
    </div>

    <?php if (empty($agendamentos)): ?>
        <p>Nenhum agendamento encontrado para este critério.</p>
    <?php else: ?>
        <?php foreach ($agendamentos as $ag): 
            // Montar link do WhatsApp (Ajuste 'telefone' se o campo no seu banco for diferente)
            // Na sua imagem vi que o login é o email, verifique se tem campo de telefone no banco.
            $telCliente = preg_replace('/\D/', '', $ag['telefone']); // Limpa o número
            $textoWhats = "Olá " . $ag['nomeCliente'] . ", aqui é o Roosevelt do Studio Sombra! Vi seu agendamento para o dia " . date('d/m/Y', strtotime($ag['dataAgendamento'])) . ".";
            $linkWhats = "https://wa.me/" . $telCliente . "?text=" . urlencode($textoWhats);
        ?>
            <div class="card-agendamento">
                <span class="status-badge <?php echo $ag['status']; ?>"><?php echo $ag['status']; ?></span>
                <h3><?php echo htmlspecialchars($ag['nomeCliente']); ?></h3>
                <p>📅 <strong>Data:</strong> <?php echo date('d/m/Y', strtotime($ag['dataAgendamento'])); ?> às <?php echo $ag['horaAgendamento']; ?></p>
                <p>🖋️ <strong>Tipo:</strong> <?php echo htmlspecialchars($ag['tipoTatuagem']); ?> (<?php echo htmlspecialchars($ag['parteCorpo']); ?>)</p>
                <p>📝 <strong>Obs:</strong> <?php echo nl2br(htmlspecialchars($ag['descricao'])); ?></p>

                <div style="margin-top: 20px; border-top: 1px solid #333; padding-top: 15px;">
                    <?php if ($ag['status'] === 'PENDENTE'): ?>
                        <a href="atualizar-status.php?id=<?php echo $ag['idAgendamento']; ?>&status=CONFIRMADO" class="btn-acao btn-confirmar">Confirmar</a> 
                        <a href="atualizar-status.php?id=<?php echo $ag['idAgendamento']; ?>&status=CANCELADO" class="btn-acao btn-cancelar">Cancelar</a>
                    <?php elseif ($ag['status'] === 'CONFIRMADO'): ?>
                        <a href="atualizar-status.php?id=<?php echo $ag['idAgendamento']; ?>&status=CONCLUIDO" class="btn-acao btn-confirmar">Marcar como Concluído</a>
                    <?php endif; ?>

                    <a href="<?php echo $linkWhats; ?>" target="_blank" class="btn-whats">💬 Chamar no WhatsApp</a>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

</body>
</html>
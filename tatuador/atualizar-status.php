<?php
session_start();

require_once '../includes/config.php'; 
require_once '../includes/funcoes.php';

// Garante que apenas tatuadores logados acessem este script
verificarLogin();
verificarNivel('TATUADOR');

// 1. Captura e limpeza dos dados vindos da URL
$idAgendamento = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
$status = filter_input(INPUT_GET, 'status', FILTER_SANITIZE_SPECIAL_CHARS);

// Se não houver ID ou Status, volta para o painel
if (!$idAgendamento || !$status) {
    header("Location: area-tatuador.php");
    exit;
}

// 2. Identificação do Tatuador logado (Segurança)
$usuario_id = $_SESSION['user_id'];

// Busca o idTatuador real para garantir que ele só mexa nos próprios agendamentos
$stmtTattoo = $pdo->prepare("SELECT idTatuador FROM tatuador WHERE idUsuario = ?");
$stmtTattoo->execute([$usuario_id]);
$tatuador = $stmtTattoo->fetch(PDO::FETCH_ASSOC);

if (!$tatuador) {
    die("Erro crítico: Perfil de tatuador não vinculado ao seu usuário.");
}

$idTatuadorReal = $tatuador['idTatuador'];

// 3. Validação do Status (Whitelist)
$statusPermitidos = ['CONFIRMADO', 'CANCELADO', 'CONCLUIDO'];

if (!in_array($status, $statusPermitidos)) {
    header("Location: area-tatuador.php?erro=status_invalido");
    exit;
}

// 4. Execução da Atualização com trava de segurança
// Só atualiza se o ID do agendamento pertencer ao ID do tatuador logado
try {
    $stmt = $pdo->prepare("
        UPDATE agendamento
        SET status = ?
        WHERE idAgendamento = ? AND idTatuador = ?
    ");

    $stmt->execute([$status, $idAgendamento, $idTatuadorReal]);

    // Verifica se alguma linha foi realmente alterada
    if ($stmt->rowCount() > 0) {
        // Sucesso total
        header("Location: area-tatuador.php?sucesso=status_atualizado");
    } else {
        // O ID pode não existir ou não pertence a este tatuador
        header("Location: area-tatuador.php?erro=nao_autorizado_ou_inexistente");
    }

} catch (PDOException $e) {
    // Erro de banco de dados (ex: conexão perdida)
    header("Location: area-tatuador.php?erro=falha_sistema");
}
exit;
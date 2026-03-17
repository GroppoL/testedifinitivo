<?php
session_start();
require_once('../includes/config.php');
require_once('../includes/funcoes.php');

verificarLogin();

$idAgendamento = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
$usuario_id = $_SESSION['user_id'];

if ($idAgendamento) {
    // 1. Verificar se o agendamento pertence ao cliente logado (Segurança)
    $stmt = $pdo->prepare("
        SELECT a.idAgendamento 
        FROM agendamento a 
        JOIN cliente c ON a.idCliente = c.idCliente 
        WHERE a.idAgendamento = ? AND c.idUsuario = ?
    ");
    $stmt->execute([$idAgendamento, $usuario_id]);
    
    if ($stmt->fetch()) {
        // 2. Atualizar o status para CANCELADO
        $update = $pdo->prepare("UPDATE agendamento SET status = 'CANCELADO' WHERE idAgendamento = ?");
        $update->execute([$idAgendamento]);
        
        header("Location: area-cliente.php?cancelado=sucesso");
        exit;
    }
}

header("Location: area-cliente.php?erro=cancelamento");
exit;
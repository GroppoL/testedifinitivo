<?php
session_start();

require_once '../includes/config.php'; // Ajuste o caminho se necessário
require_once '../includes/funcoes.php';

verificarLogin();
verificarNivel('TATUADOR');

// 1. Usar filter_input para uma captura mais limpa dos dados
$idAgendamento = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
$status = filter_input(INPUT_GET, 'status', FILTER_SANITIZE_SPECIAL_CHARS);

if (!$idAgendamento || !$status) {
    header("Location: area-tatuador.php");
    exit;
}

// 2. Pegar o idUsuario da sessão para garantir que o tatuador só altere o DELE
$usuario_id = $_SESSION['user_id'];

// Buscar o idTatuador vinculado a esse usuário
$stmtTattoo = $pdo->prepare("SELECT idTatuador FROM tatuador WHERE idUsuario = ?");
$stmtTattoo->execute([$usuario_id]);
$tatuador = $stmtTattoo->fetch();

if (!$tatuador) {
    die("Erro: Perfil de tatuador não encontrado.");
}

$idTatuadorReal = $tatuador['idTatuador'];

// 3. Permitir apenas esses status
$statusPermitidos = ['CONFIRMADO', 'CANCELADO', 'CONCLUIDO'];

if (!in_array($status, $statusPermitidos)) {
    header("Location: area-tatuador.php?erro=status_invalido");
    exit;
}

// 4. Atualizar COM SEGURANÇA: 
// O agendamento deve ser do tatuador que está logado (idTatuador = ?)
$stmt = $pdo->prepare("
    UPDATE agendamento
    SET status = ?
    WHERE idAgendamento = ? AND idTatuador = ?
");

if ($stmt->execute([$status, $idAgendamento, $idTatuadorReal])) {
    header("Location: area-tatuador.php?sucesso=status_atualizado");
} else {
    header("Location: area-tatuador.php?erro=falha_ao_atualizar");
}
exit;
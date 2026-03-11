<?php
session_start();

require_once '../includes/config.php';
require_once '../includes/funcoes.php';

verificarLogin();
verificarNivel('CLIENTE');

$usuario_id = $_SESSION['user_id'];

// Buscar idCliente
$stmt = $pdo->prepare("SELECT idCliente FROM cliente WHERE idUsuario = ?");
$stmt->execute([$usuario_id]);
$cliente = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$cliente) {
    die("Cliente não encontrado.");
}

$idCliente = $cliente['idCliente'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $tipo = limparDados($_POST['tipo']); 
    $tipoTatuagem = limparDados($_POST['tipoTatuagem'] ?? null);
    $parteCorpo = limparDados($_POST['parteCorpo'] ?? null);
    $tamanho = limparDados($_POST['tamanho'] ?? null);
    $descricao = limparDados($_POST['descricao'] ?? null);

    $dataAgendada = $_POST['dataAgendamento'];
    $horaAgendada = $_POST['horaAgendamento'];

    // tatuador fixo por enquanto
    $idTatuador = 1;

    $stmt = $pdo->prepare("
        INSERT INTO agendamento 
        (
            idCliente,
            idTatuador,
            dataAgendada,
            horaAgendada,
            descricao,
            tipoTatuagem,
            parteCorpo,
            tamanho,
            tipo,
            status
        )
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'PENDENTE')
    ");

    $stmt->execute([
        $idCliente,
        $idTatuador,
        $dataAgendada,
        $horaAgendada,
        $descricao,
        $tipoTatuagem,
        $parteCorpo,
        $tamanho,
        $tipo
    ]);

    header("Location: area-cliente.php");
    exit;
}
?>
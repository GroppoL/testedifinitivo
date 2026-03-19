<?php
require_once 'includes/config.php';
require_once 'includes/funcoes.php';

verificarLogin();


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario_id = $_SESSION['usuario_id'];
    $tipo = limparDados($_POST['tipo']);
    $data = limparDados($_POST['dataAgendamento']);
    $hora = limparDados($_POST['horaAgendamento']);
    $tipo_tatuagem = isset($_POST['tipoTatuagem']) ? limparDados($_POST['tipoTatuagem']) : null;
    $primeira_tatuagem = isset($_POST['primeiraTatuagem']) ? limparDados($_POST['primeiraTatuagem']) : null;    $parte_corpo = isset($_POST['parteCorpo']) ? limparDados($_POST['parteCorpo']) : null;
    $tamanho = isset($_POST['tamanho']) ? limparDados($_POST['tamanho']) : null;
    $descricao = isset($_POST['descricao']) ? limparDados($_POST['descricao']) : null;

    $stmt = $pdo->prepare("

        INSERT INTO agendamentos 
        (usuario_id, tipo, tipoTatuagem, primeiraTatuagem, parteCorpo, tamanho, descricao, dataAgendamento, horaAgendamento) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    if ($stmt->execute([$usuario_id, $tipo, $tipo_tatuagem, $primeira_tatuagem, $parte_corpo, $tamanho, $descricao, $data, $hora])) {
        $_SESSION['mensagem'] = 'Agendamento solicitado com sucesso!';
        $_SESSION['tipo_mensagem'] = 'sucesso';
    } else {
        $_SESSION['mensagem'] = 'Erro ao processar agendamento.';
        $_SESSION['tipo_mensagem'] = 'erro';
    }
}

header('Location: area-cliente.php');
exit;
?>
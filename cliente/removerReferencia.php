<?php
session_start();
require_once('../includes/config.php');
require_once('../includes/funcoes.php');

// Verifica se o usuário está logado
verificarLogin();

if (isset($_GET['id'])) {
    $idReferencia = $_GET['id'];
    $usuario_id = $_SESSION['user_id'];

    // 1. Busca o idCliente vinculado ao usuário logado (Segurança)
    $stmt = $pdo->prepare("SELECT idCliente FROM cliente WHERE idUsuario = ?");
    $stmt->execute([$usuario_id]);
    $cliente = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($cliente) {
        $idCliente = $cliente['idCliente'];

        // 2. Executa o DELETE garantindo que a referência pertence a esse cliente
        $delete = $pdo->prepare("DELETE FROM referencia_salva WHERE idReferencia = ? AND idCliente = ?");
        $delete->execute([$idReferencia, $idCliente]);
    }
}

// Redireciona de volta com o status para mostrar o alerta e abre a aba correta
header("Location: area-cliente.php?status=removido#salvas");
exit;
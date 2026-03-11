<?php
session_start(); 

require_once 'includes/config.php';
require_once 'includes/funcoes.php';

verificarLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario_id = $_SESSION['user_id']; 

    $stmtCli = $pdo->prepare("SELECT idCliente FROM cliente WHERE idUsuario = ?");
    $stmtCli->execute([$usuario_id]);
    $cliente = $stmtCli->fetch(PDO::FETCH_ASSOC);

    if (!$cliente) {
        die("Erro: Perfil de cliente não encontrado.");
    }

    $idCliente = $cliente['idCliente'];
    
    // Pegando os dados do formulário
    // Note que usei o nome da coluna do banco para a variável para não confundir
    $tipoAgendamento = limparDados($_POST['tipo']); 
    $data = $_POST['dataAgendamento'];
    $hora = $_POST['horaAgendamento'];
    $tipoTatuagem = !empty($_POST['tipoTatuagem']) ? $_POST['tipoTatuagem'] : null;
    $primeiraTatuagem = !empty($_POST['primeiraTatuagem']) ? ucfirst($_POST['primeiraTatuagem']) : null; // Sim ou Não
    $parteCorpo = limparDados($_POST['parteCorpo'] ?? null);
    $tamanho = limparDados($_POST['tamanho'] ?? null);
    $descricao = limparDados($_POST['descricao'] ?? null);

    // SQL corrigido: troquei 'tipo' por 'tipoAgendamento' e adicionei 'primeiraTatuagem'
    $sql = "INSERT INTO agendamento 
            (idCliente, idTatuador, dataAgendamento, horaAgendamento, descricao, tipoTatuagem, primeiraTatuagem, parteCorpo, tamanho, tipoAgendamento, status) 
            VALUES (?, 1, ?, ?, ?, ?, ?, ?, ?, ?, 'PENDENTE')";
    
    $stmt = $pdo->prepare($sql);

    if ($stmt->execute([
        $idCliente,
        $data,
        $hora,
        $descricao,
        $tipoTatuagem,
        $primeiraTatuagem,
        $parteCorpo,
        $tamanho,
        $tipoAgendamento
    ])) {
        header('Location: cliente/area-cliente.php?status=sucesso');
    } else {
        header('Location: cliente/area-cliente.php?status=erro');
    }
    exit;
}
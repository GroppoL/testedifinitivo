<?php
session_start(); 

require_once 'includes/config.php';
require_once 'includes/funcoes.php';

verificarLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario_id = $_SESSION['user_id']; 

    // 1. Buscar o idCliente vinculado ao usuário logado
    $stmtCli = $pdo->prepare("SELECT idCliente FROM cliente WHERE idUsuario = ?");
    $stmtCli->execute([$usuario_id]);
    $cliente = $stmtCli->fetch(PDO::FETCH_ASSOC);

    if (!$cliente) {
        die("Erro: Perfil de cliente não encontrado.");
    }

    $idCliente = $cliente['idCliente'];
    
    // 2. ID do Tatuador (Roosevelt fixo como 1 conforme seu banco)
    $idTatuador = 1;

    // 3. Pegando os dados do formulário (Ajustado para os names exatos do seu HTML)
    // Se no seu formulário HTML estiver sem underline, mantenha assim:
    $tipoAgendamento = limparDados($_POST['tipo'] ?? 'tattoo'); 
    $data = $_POST['data_agendamento'] ?? null; // Aqui é o name do input no HTML
    $hora = $_POST['hora_agendamento'] ?? null; // Aqui é o name do input no HTML
    
    $tipoTatuagem = !empty($_POST['tipo_tatuagem']) ? $_POST['tipo_tatuagem'] : null;
    $primeiraTatuagem = !empty($_POST['primeira_tatuagem']) ? ucfirst($_POST['primeira_tatuagem']) : 'Nao';
    $parteCorpo = limparDados($_POST['parte_corpo'] ?? null);
    $tamanho = limparDados($_POST['tamanho'] ?? null);
    $descricao = limparDados($_POST['descricao'] ?? null);

    // 4. SQL de Inserção (Colunas SEM underline conforme seu banco)
    $sql = "INSERT INTO agendamento 
            (idCliente, idTatuador, dataAgendamento, horaAgendamento, descricao, tipoTatuagem, primeiraTatuagem, parteCorpo, tamanho, tipoAgendamento, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'PENDENTE')";
    
    $stmt = $pdo->prepare($sql);

    try {
        if ($stmt->execute([
            $idCliente,
            $idTatuador,
            $data,
            $hora,
            $descricao,
            $tipoTatuagem,
            $primeiraTatuagem,
            $parteCorpo,
            $tamanho,
            $tipoAgendamento
        ])) {
            // Redireciona de volta para a área do cliente (ajuste o caminho se necessário)
            header('Location: cliente/area-cliente.php?status=sucesso');
        } else {
            header('Location: cliente/area-cliente.php?status=erro');
        }
    } catch (PDOException $e) {
        die("Erro ao salvar no banco (Verifique se os nomes das colunas estão corretos): " . $e->getMessage());
    }
    exit;
}
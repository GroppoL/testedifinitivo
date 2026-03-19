<?php
session_start();
require_once '../includes/config.php'; 
require_once '../includes/funcoes.php';

verificarLogin();
verificarNivel('TATUADOR');

// 1. Processamento via POST (Vindo do formulário com Valor e Obs)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idAgendamento = filter_input(INPUT_POST, 'idAgendamento', FILTER_SANITIZE_NUMBER_INT);
    $status = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_SPECIAL_CHARS);
    
    // Converte vírgula em ponto para o banco de dados aceitar decimais
    $vEstimado = !empty($_POST['valorEstimado']) ? str_replace(',', '.', $_POST['valorEstimado']) : null;
    $vFinal = !empty($_POST['valorFinal']) ? str_replace(',', '.', $_POST['valorFinal']) : null;
    $obs = filter_input(INPUT_POST, 'observacoes', FILTER_SANITIZE_SPECIAL_CHARS);

    if ($idAgendamento) {
        try {
            // Atualiza todos os campos. O cliente verá o 'status' alterado na área dele.
            $sql = "UPDATE agendamento SET 
                    status = ?, 
                    valorEstimado = ?, 
                    valorFinal = ?, 
                    observacoes = ?, 
                    atualizadoEm = NOW() 
                    WHERE idAgendamento = ?";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$status, $vEstimado, $vFinal, $obs, $idAgendamento]);

            header("Location: area-tatuador.php?sucesso=1");
            exit;
        } catch (PDOException $e) {
            die("Erro ao salvar no banco de dados: " . $e->getMessage());
        }
    }
}

// 2. Processamento via GET (Vindo dos botões rápidos: Confirmar/Cancelar)
$idGet = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
$statusGet = filter_input(INPUT_GET, 'status', FILTER_SANITIZE_SPECIAL_CHARS);

if ($idGet && $statusGet) {
    try {
        $sql = "UPDATE agendamento SET status = ?, atualizadoEm = NOW() WHERE idAgendamento = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$statusGet, $idGet]);
        
        header("Location: area-tatuador.php?sucesso=1");
        exit;
    } catch (PDOException $e) {
        header("Location: area-tatuador.php?erro=falha_status");
        exit;
    }
}

// Se chegar aqui sem dados válidos, apenas volta
header("Location: area-tatuador.php");
exit;
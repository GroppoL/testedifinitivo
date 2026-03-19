<?php
session_start();
require_once '../../includes/config.php';

// Verifica se está logado
if (!isset($_SESSION['idTatuador'])) {
    header("Location: ../login.php");
    exit;
}

// Verifica se veio o ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: listar.php");
    exit;
}

$id = intval($_GET['id']);
$idTatuador = $_SESSION['idTatuador'];

try {

    // Busca a tatuagem SOMENTE se for do tatuador logado
    $sql = "SELECT imagemVideo 
            FROM portfolio 
            WHERE idPortfolio = :id 
            AND idTatuador = :idTatuador
            LIMIT 1";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':id' => $id,
        ':idTatuador' => $idTatuador
    ]);

    $tatuagem = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$tatuagem) {
        header("Location: listar.php");
        exit;
    }

    // Remove arquivo da pasta
    if (!empty($tatuagem['imagemVideo'])) {
        $caminho = '../Imagens/' . $tatuagem['imagemVideo'];
        if (file_exists($caminho)) {
            unlink($caminho);
        }
    }

    // Exclui do banco
    $sql = "DELETE FROM portfolio 
            WHERE idPortfolio = :id 
            AND idTatuador = :idTatuador";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':id' => $id,
        ':idTatuador' => $idTatuador
    ]);

    header("Location: listar.php");
    exit;

} catch (PDOException $e) {
    echo "Erro ao excluir: " . $e->getMessage();
}
?>
<?php
session_start();
require_once '../../includes/config.php';

if (!isset($_SESSION['idTatuador'])) {
    header("Location: ../login.php");
    exit;
}

$id = $_GET['id'] ?? null;
$idTatuador = $_SESSION['idTatuador'];

// Busca e valida se a tatuagem pertence ao tatuador logado
$sql = "SELECT * FROM portfolio WHERE idPortfolio = :id AND idTatuador = :idTatuador";
$stmt = $pdo->prepare($sql);
$stmt->execute([':id' => $id, ':idTatuador' => $idTatuador]);
$tatuagem = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$tatuagem) {
    header("Location: listar.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = trim($_POST['titulo']);
    $descricao = trim($_POST['descricao']);
    $imagem = $tatuagem['imagemVideo'];
    $pasta = "../Imagens/";

    if (!empty($_FILES['imagemVideo']['name'])) {
        $ext = strtolower(pathinfo($_FILES['imagemVideo']['name'], PATHINFO_EXTENSION));
        $nomeArquivo = uniqid() . "." . $ext;

        if (move_uploaded_file($_FILES['imagemVideo']['tmp_name'], $pasta . $nomeArquivo)) {
            if (file_exists($pasta . $tatuagem['imagemVideo'])) {
                unlink($pasta . $tatuagem['imagemVideo']);
            }
            $imagem = $nomeArquivo;
        }
    }

    $sql = "UPDATE portfolio SET titulo = :titulo, descricao = :descricao, imagemVideo = :imagem WHERE idPortfolio = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':titulo' => $titulo, ':descricao' => $descricao, ':imagem' => $imagem, ':id' => $id]);

    echo "<script>alert('Atualizado!'); window.location='listar.php';</script>";
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Editar Tatuagem</title>
    <link rel="stylesheet" href="../../assets/CSS/style.css">
</head>
<body>
<div class="card-editar">
    <h1>Editar Publicação</h1>
    <form method="POST" enctype="multipart/form-data">
        <label>Título</label>
        <input type="text" name="titulo" required value="<?= htmlspecialchars($tatuagem['titulo']) ?>">
        
        <label>Descrição</label>
        <textarea name="descricao" required><?= htmlspecialchars($tatuagem['descricao']) ?></textarea>

        <label>Substituir Arquivo (Opcional)</label>
        <input type="file" name="imagemVideo" accept="image/*,video/*">

        <div style="margin: 15px 0; text-align:center;">
            <p>Arquivo Atual:</p>
            <?php 
            $ext = pathinfo($tatuagem['imagemVideo'], PATHINFO_EXTENSION);
            if (in_array($ext, ['mp4', 'webm', 'mov'])): ?>
                <video src="../Imagens/<?= $tatuagem['imagemVideo'] ?>" style="max-width:200px;" controls></video>
            <?php else: ?>
                <img src="../Imagens/<?= $tatuagem['imagemVideo'] ?>" style="max-width:200px; border-radius:8px;">
            <?php endif; ?>
        </div>

        <button type="submit" class="btn">Salvar Alterações</button>
        <a href="listar.php" class="btn-voltar">Voltar</a>
    </form>
</div>
</body>
</html>
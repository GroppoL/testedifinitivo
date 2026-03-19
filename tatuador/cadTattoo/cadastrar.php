<?php
session_start();
require_once '../includes/config.php'; // Certifique-se que o nome do arquivo de config está correto

if (!isset($_SESSION['idTatuador'])) {
    header("Location: ../login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idTatuador = $_SESSION['idTatuador'];
    $titulo = trim($_POST['titulo']);
    $descricao = trim($_POST['descricao']);
    $imagem = null;

    if (isset($_FILES['imagemVideo']) && $_FILES['imagemVideo']['error'] === 0) {
        $pasta = '../Imagens/';
        if (!is_dir($pasta)) {
            mkdir($pasta, 0777, true);
        }

        $extensao = strtolower(pathinfo($_FILES['imagemVideo']['name'], PATHINFO_EXTENSION));
        $permitidos = ['jpg', 'jpeg', 'png', 'gif', 'mp4', 'mov', 'webm'];

        if (in_array($extensao, $permitidos)) {
            $nomeArquivo = uniqid() . "." . $extensao;
            if (move_uploaded_file($_FILES['imagemVideo']['tmp_name'], $pasta . $nomeArquivo)) {
                $imagem = $nomeArquivo;
            }
        } else {
            echo "<script>alert('Formato de arquivo não permitido!');</script>";
        }
    }

    if ($imagem) {
        try {
            $sql = "INSERT INTO portfolio (idTatuador, titulo, descricao, imagemVideo, dataPublicacao) 
                    VALUES (:idTatuador, :titulo, :descricao, :imagemVideo, NOW())";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':idTatuador' => $idTatuador,
                ':titulo' => $titulo,
                ':descricao' => $descricao,
                ':imagemVideo' => $imagem
            ]);

            echo "<script>alert('Cadastrado com sucesso!'); window.location.href = 'listar.php';</script>";
        } catch (PDOException $e) {
            echo "<script>alert('Erro ao salvar no banco: " . $e->getMessage() . "');</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Cadastrar Tatuagem</title>
    <link rel="stylesheet" href="../CSS/style.css">
</head>
<body>
<div class="container">
    <h1>Cadastrar Nova Arte</h1>
    <form method="POST" enctype="multipart/form-data">
        <input type="text" name="titulo" placeholder="Título da tatuagem" required>
        <textarea name="descricao" placeholder="Descrição ou detalhes da técnica" required></textarea>
        <label>Selecione Foto ou Vídeo:</label>
        <input type="file" name="imagemVideo" accept="image/*,video/*" required>
        <button type="submit">Publicar no Portfólio</button>
        <a href="listar.php" style="display:block; text-align:center; margin-top:10px; color:#666;">Cancelar</a>
    </form>
</div>
</body>
</html>
<?php
require '../conexao.php';
session_start();

if (!isset($_SESSION['idTatuador'])) {
    header("Location: ../login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $idTatuador = $_SESSION['idTatuador'];
    $titulo = trim($_POST['titulo']);
    $descricao = trim($_POST['descricao']);

    $imagem = null;

    // Upload da imagem
    if (isset($_FILES['imagemVideo']) && $_FILES['imagemVideo']['error'] === 0) {

        if (!is_dir('../Imagens')) {
            mkdir('../Imagens', 0777, true);
        }

        $extensao = pathinfo($_FILES['imagemVideo']['name'], PATHINFO_EXTENSION);
        $nomeArquivo = uniqid() . "." . $extensao;
        $caminho = "../Imagens/" . $nomeArquivo;

        if (move_uploaded_file($_FILES['imagemVideo']['tmp_name'], $caminho)) {
            $imagem = $nomeArquivo;
        } else {
            echo "<script>alert('Erro ao salvar a imagem!');</script>";
        }
    }

    try {

        $sql = "INSERT INTO portfolio 
                (idTatuador, titulo, descricao, imagemVideo) 
                VALUES (:idTatuador, :titulo, :descricao, :imagemVideo)";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':idTatuador' => $idTatuador,
            ':titulo' => $titulo,
            ':descricao' => $descricao,
            ':imagemVideo' => $imagem
        ]);

        echo "<script>
                alert('Tatuagem cadastrada com sucesso!');
                window.location.href = 'listar.php';
              </script>";
        exit;

    } catch (PDOException $e) {
        echo "<script>alert('Erro: ".$e->getMessage()."');</script>";
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
    <h1>Cadastrar Tatuagem</h1>

    <form method="POST" enctype="multipart/form-data">

        <input type="text" name="titulo" placeholder="Título da tatuagem" required>

        <textarea name="descricao" placeholder="Descrição da tatuagem" required></textarea>

        <input type="file" name="imagemVideo" accept="image/*,video/*">

        <button type="submit">Cadastrar</button>

    </form>
</div>

</body>
</html>
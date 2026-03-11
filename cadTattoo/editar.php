<?php
require '../conexao.php';
session_start();

// Verifica login
if (!isset($_SESSION['idTatuador'])) {
    header("Location: ../login.php");
    exit;
}

$id = $_GET['id'] ?? null;

if (!$id) {
    header("Location: listar.php");
    exit;
}

// BUSCAR TATUAGEM
$sql = "SELECT * FROM portfolio WHERE idPortfolio = :id";
$stmt = $pdo->prepare($sql);
$stmt->execute([':id' => $id]);
$tatuagem = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$tatuagem) {
    header("Location: listar.php");
    exit;
}

// ATUALIZAÇÃO
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $titulo = trim($_POST['titulo']);
    $descricao = trim($_POST['descricao']);
    $imagem = $tatuagem['imagemVideo'];

    $pasta = "../Imagens/";

    // Upload nova imagem
    if (!empty($_FILES['imagemVideo']['name'])) {

        if (!is_dir($pasta)) {
            mkdir($pasta, 0777, true);
        }

        $ext = strtolower(pathinfo($_FILES['imagemVideo']['name'], PATHINFO_EXTENSION));
        $nomeArquivo = uniqid() . "." . $ext;

        if (move_uploaded_file($_FILES['imagemVideo']['tmp_name'], $pasta . $nomeArquivo)) {

            // Remove imagem antiga
            if (!empty($tatuagem['imagemVideo']) && file_exists($pasta . $tatuagem['imagemVideo'])) {
                unlink($pasta . $tatuagem['imagemVideo']);
            }

            $imagem = $nomeArquivo;
        }
    }

    $sql = "UPDATE portfolio SET 
                titulo = :titulo,
                descricao = :descricao,
                imagemVideo = :imagem
            WHERE idPortfolio = :id";

    $stmt = $pdo->prepare($sql);

    if ($stmt->execute([
        ':titulo' => $titulo,
        ':descricao' => $descricao,
        ':imagem' => $imagem,
        ':id' => $id
    ])) {

        echo "<script>
                alert('Tatuagem atualizada com sucesso!');
                window.location='listar.php';
              </script>";
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Editar Tatuagem</title>
<link rel="stylesheet" href="../CSS/style.css">
</head>

<body>

<div class="card-editar">

    <h1>Editar Tatuagem</h1>

    <form method="POST" enctype="multipart/form-data">

        <div class="input-group">
            <label>Título</label>
            <input type="text" name="titulo" required value="<?= htmlspecialchars($tatuagem['titulo']) ?>">
        </div>

        <div class="input-group">
            <label>Descrição</label>
            <textarea name="descricao" required><?= htmlspecialchars($tatuagem['descricao']) ?></textarea>
        </div>

        <div class="input-group">
            <label>Imagem ou Vídeo</label>
            <input type="file" name="imagemVideo" accept="image/*,video/*">
            <small>Deixe vazio para manter o arquivo atual</small>
        </div>

        <?php if (!empty($tatuagem['imagemVideo'])) { ?>
            <div style="text-align:center;margin-bottom:15px;">
                <img src="../Imagens/<?= $tatuagem['imagemVideo'] ?>" style="max-width:140px;border-radius:8px;">
            </div>
        <?php } ?>

        <button type="submit" class="btn">Salvar Alterações</button>
        <a href="listar.php" class="btn-voltar">Voltar</a>

    </form>

</div>

</body>
</html>
<?php
session_start();
require_once '../../includes/config.php';

if (!isset($_SESSION['idTatuador'])) {
    header("Location: ../login.php");
    exit;
}

$idTatuador = $_SESSION['idTatuador'];
$sql = "SELECT * FROM portfolio WHERE idTatuador = :idTatuador ORDER BY dataPublicacao DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute([':idTatuador' => $idTatuador]);
$tatuagens = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Minhas Tatuagens</title>
    <link rel="stylesheet" href="../../assets/CSS/style.css">
    <style>
        .preview-media { width: 80px; height: 80px; object-fit: cover; border-radius: 6px; background: #eee; }
        .btn-novo { display: inline-block; padding: 10px; background: #28a745; color: #fff; text-decoration: none; border-radius: 5px; margin-bottom: 15px; }
    </style>
</head>
<body>

<div class="lista-container">
    <h1>Meu Portfólio</h1>

    <div style="display:flex; justify-content: space-between;">
        <a class="btn-voltar" href="../painel.php">Painel</a>
        <a class="btn-novo" href="cadastrar.php">+ Cadastrar Tatuagem</a>
    </div>

    <table class="tabela-usuarios">
        <thead>
            <tr>
                <th>Mídia</th>
                <th>Título</th>
                <th>Descrição</th>
                <th>Data</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($tatuagens as $t): ?>
            <tr>
                <td>
                    <?php 
                    $ext = pathinfo($t['imagemVideo'], PATHINFO_EXTENSION);
                    if (in_array($ext, ['mp4', 'webm', 'mov'])): ?>
                        <video src="../Imagens/<?= $t['imagemVideo'] ?>" class="preview-media"></video>
                    <?php else: ?>
                        <img src="../Imagens/<?= $t['imagemVideo'] ?>" class="preview-media">
                    <?php endif; ?>
                </td>
                <td><strong><?= htmlspecialchars($t['titulo']) ?></strong></td>
                <td><?= mb_strimwidth(htmlspecialchars($t['descricao']), 0, 50, "...") ?></td>
                <td><?= date('d/m/Y', strtotime($t['dataPublicacao'])) ?></td>
                <td>
                    <a class="btn-editar" href="editar.php?id=<?= $t['idPortfolio'] ?>">Editar</a>
                    <a class="btn-excluir" href="excluir.php?id=<?= $t['idPortfolio'] ?>" onclick="return confirm('Excluir permanentemente?')">Excluir</a>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($tatuagens)): ?>
                <tr><td colspan="5">Nenhuma tatuagem cadastrada.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
</body>
</html>
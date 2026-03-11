<?php
require '../conexao.php';
session_start();

// Verifica login
if (!isset($_SESSION['idTatuador'])) {
    header("Location: ../login.php");
    exit;
}

$idTatuador = $_SESSION['idTatuador'];

// Busca apenas tatuagens do tatuador logado
$sql = "SELECT * FROM portfolio 
        WHERE idTatuador = :idTatuador
        ORDER BY dataPublicacao DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute([':idTatuador' => $idTatuador]);

$tatuagens = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Minhas Tatuagens</title>
<link rel="stylesheet" href="../CSS/style.css">

<style>
.btn-voltar {
    display: inline-block;
    padding: 10px 18px;
    background-color: #555;
    color: white;
    border-radius: 5px;
    text-decoration: none;
    font-weight: bold;
    margin-bottom: 15px;
}
.btn-voltar:hover {
    background-color: #333;
}

img.preview {
    width: 80px;
    border-radius: 6px;
}
</style>

</head>
<body>

<div class="lista-container">
    <h1>Minhas Tatuagens</h1>

    <a class="btn-voltar" href="../painel.php">Voltar ao Painel</a>

    <table class="tabela-usuarios">
        <thead>
            <tr>
                <th>ID</th>
                <th>Imagem</th>
                <th>Título</th>
                <th>Descrição</th>
                <th>Data</th>
                <th>Ações</th>
            </tr>
        </thead>

        <tbody>
            <?php if (count($tatuagens) > 0): ?>
                <?php foreach ($tatuagens as $t): ?>
                <tr>
                    <td><?= $t['idPortfolio'] ?></td>

                    <td>
                        <?php if (!empty($t['imagemVideo'])): ?>
                            <img src="../Imagens/<?= $t['imagemVideo'] ?>" class="preview">
                        <?php else: ?>
                            Sem arquivo
                        <?php endif; ?>
                    </td>

                    <td><?= htmlspecialchars($t['titulo']) ?></td>

                    <td><?= htmlspecialchars($t['descricao']) ?></td>

                    <td><?= date('d/m/Y H:i', strtotime($t['dataPublicacao'])) ?></td>

                    <td>
                        <a class="btn-editar" href="editar.php?id=<?= $t['idPortfolio'] ?>">Editar</a>
                        <a class="btn-excluir" 
                           href="excluir.php?id=<?= $t['idPortfolio'] ?>" 
                           onclick="return confirm('Deseja realmente excluir esta tatuagem?')">
                           Excluir
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6">Nenhuma tatuagem cadastrada.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>
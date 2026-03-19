<?php
session_start();
require_once '../../includes/config.php'; 

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login.php");
    exit;
}

$usuario_id = $_SESSION['user_id'];

// 1. Busca o ID do Tatuador
$stmtTatuador = $pdo->prepare("SELECT idTatuador FROM tatuador WHERE idUsuario = ?");
$stmtTatuador->execute([$usuario_id]);
$tatuador = $stmtTatuador->fetch(PDO::FETCH_ASSOC);
$idTatuadorReal = $tatuador['idTatuador'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo    = trim($_POST['titulo']);
    $descricao = trim($_POST['descricao']);
    $tipo      = $_POST['tipo']; // Novo campo: Estilo da tatuagem
    $imagem    = null;

    if (isset($_FILES['imagemVideo']) && $_FILES['imagemVideo']['error'] === 0) {
        $pastaDestino = '../../Imagens/'; 
        
        if (!is_dir($pastaDestino)) {
            mkdir($pastaDestino, 0777, true);
        }

        $extensao = strtolower(pathinfo($_FILES['imagemVideo']['name'], PATHINFO_EXTENSION));
        $nomeArquivo = uniqid() . "." . $extensao;

        if (move_uploaded_file($_FILES['imagemVideo']['tmp_name'], $pastaDestino . $nomeArquivo)) {
            $imagem = $nomeArquivo;
        }
    }

    if ($imagem) {
        try {
            // AJUSTE: Usando 'arquivo' e 'tipo' conforme seu banco de dados
            $sql = "INSERT INTO portfolio (idTatuador, titulo, descricao, arquivo, tipo, dataPublicacao) 
                    VALUES (:idTatuador, :titulo, :descricao, :arquivo, :tipo, NOW())";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':idTatuador' => $idTatuadorReal,
                ':titulo'    => $titulo,
                ':descricao' => $descricao,
                ':arquivo'   => $imagem, // Nome da coluna corrigido
                ':tipo'      => $tipo    // Estilo selecionado
            ]);

            header("Location: ../area-tatuador.php?aba=portfolio");
            exit;

        } catch (PDOException $e) {
            $erro = "Erro no banco: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Cadastrar Tatuagem</title>
    <link rel="stylesheet" href="../../assets/CSS/style.css">
</head>
<body style="background:#000; color:#fff; font-family: sans-serif;">
<div class="container" style="max-width: 500px; margin: 50px auto; padding: 20px; background: #111; border-radius: 8px;">
    <h1>Cadastrar Nova Arte</h1>
    
    <?php if(isset($erro)): ?>
        <div style="background: rgba(255,0,0,0.2); color: #ff3b3b; padding: 10px; border-radius: 5px; margin-bottom: 20px;">
            <?php echo $erro; ?>
        </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <label>Título:</label>
        <input type="text" name="titulo" required style="width:100%; padding:10px; margin: 10px 0; background:#222; border:1px solid #333; color:#fff;">
        
        <label>Estilo (Tipo):</label>
        <select name="tipo" required style="width:100%; padding:10px; margin: 10px 0; background:#222; border:1px solid #333; color:#fff;">
            <option value="Manga">Manga</option>
            <option value="Blackwork">Blackwork</option>
            <option value="Realismo">Realismo</option>
            <option value="Grande Porte">Grande Porte</option>
            <option value="Geométrico">Geométrico</option>
            <option value="Floral">Floral</option>
            <option value="Outros">Outros</option>
        </select>

        <label>Descrição:</label>
        <textarea name="descricao" required style="width:100%; padding:10px; margin: 10px 0; background:#222; border:1px solid #333; color:#fff; height: 80px;"></textarea>
        
        <label>Selecione Foto ou Vídeo:</label>
        <input type="file" name="imagemVideo" accept="image/*,video/*" required style="margin: 15px 0;">
        
        <div style="display:flex; gap: 10px; margin-top: 20px;">
            <button type="submit" style="flex:1; background:#ff3b3b; color:#fff; border:none; padding:12px; cursor:pointer; font-weight:bold; border-radius:5px;">Publicar</button>
            <a href="../area-tatuador.php?aba=portfolio" style="flex:1; background:#333; color:#fff; text-decoration:none; text-align:center; padding:12px; border-radius:5px;">Cancelar</a>
        </div>
    </form>
</div>
</body>
</html>
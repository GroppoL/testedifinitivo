<?php
session_start();
require_once 'includes/config.php';

if (!isset($_GET['token'])) {
    die("Token inválido.");
}

$token = $_GET['token'];

$stmt = $pdo->prepare("
    SELECT * FROM usuario 
    WHERE reset_token = ? AND reset_expira > NOW()
");
$stmt->execute([$token]);

$user = $stmt->fetch();

if (!$user) {
    die("Token inválido ou expirado.");
}

// FORM
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $senha = password_hash($_POST['senha'], PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("
        UPDATE usuario 
        SET senha = ?, reset_token = NULL, reset_expira = NULL
        WHERE idUsuario = ?
    ");

    $stmt->execute([$senha, $user['idUsuario']]);

    echo "Senha alterada com sucesso!";
    exit;
}
?>

<form method="POST">
    <input type="password" name="senha" placeholder="Nova senha" required>
    <button type="submit">Salvar nova senha</button>
</form>
<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/funcoes.php';

if (!isset($_GET['token'])) {
    die("Token inválido");
}

$token = $_GET['token'];

$stmt = $pdo->prepare("
    SELECT * FROM usuario 
    WHERE reset_token = ? 
    AND reset_expira > NOW()
");
$stmt->execute([$token]);

$user = $stmt->fetch();

if (!$user) {
    die("Token inválido ou expirado");
}

$mensagem = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $senha = password_hash($_POST['senha'], PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("
        UPDATE usuario 
        SET senha = ?, reset_token = NULL, reset_expira = NULL
        WHERE idUsuario = ?
    ");

    $stmt->execute([$senha, $user['idUsuario']]);

    $mensagem = "Senha alterada com sucesso! <br><a href='login.php'>Ir para login</a>";
}
?>

<?php require_once 'includes/header.php'; ?>

<div class="auth-container">
    <div class="auth-card">
        <div class="auth-header">
            <div class="auth-icon">🔒</div>
            <h2 class="auth-title">Nova Senha</h2>
            <p class="auth-subtitle">Digite sua nova senha</p>
        </div>

        <?php if ($mensagem): ?>
            <div class="alert alert-success"><?= $mensagem ?></div>
        <?php endif; ?>

        <form method="POST" class="auth-form">
            <div class="form-group">
                <label>Nova senha</label>
                <input type="password" name="senha" required class="form-input">
            </div>

            <button type="submit" class="btn-submit">Salvar nova senha</button>
        </form>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
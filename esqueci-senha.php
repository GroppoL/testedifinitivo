<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/funcoes.php';

$mensagem = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = limparDados($_POST['email']);

    $stmt = $pdo->prepare("SELECT * FROM usuario WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {

        $token = bin2hex(random_bytes(32));
        $expira = date('Y-m-d H:i:s', strtotime('+1 hour'));

        $stmt = $pdo->prepare("
            UPDATE usuario 
            SET reset_token = ?, reset_expira = ?
            WHERE idUsuario = ?
        ");
        $stmt->execute([$token, $expira, $user['idUsuario']]);

        // 🔥 LINK (depois vamos mandar por email)
        $link = "http://localhost/seu-projeto/resetar-senha.php?token=$token";

        $mensagem = "Link de recuperação:<br><a href='$link'>$link</a>";

    } else {
        $mensagem = "Email não encontrado!";
    }
}
?>

<?php require_once 'includes/header.php'; ?>

<div class="auth-container">
    <div class="auth-card">
        <div class="auth-header">
            <div class="auth-icon">🔑</div>
            <h2 class="auth-title">Recuperar Senha</h2>
            <p class="auth-subtitle">Digite seu email para recuperar acesso</p>
        </div>

        <?php if ($mensagem): ?>
            <div class="alert alert-success"><?= $mensagem ?></div>
        <?php endif; ?>

        <form method="POST" class="auth-form">
            <div class="form-group">
                <label>E-mail</label>
                <input type="email" name="email" required class="form-input">
            </div>

            <button type="submit" class="btn-submit">Enviar link</button>
        </form>

        <div class="auth-footer">
            <a href="login.php" class="link-red">Voltar ao login</a>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
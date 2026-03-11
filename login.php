<?php
session_start();
require_once './includes/config.php';
require_once './includes/funcoes.php';

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = limparDados($_POST['email']);
    $senha = $_POST['senha'];

    $stmt = $pdo->prepare("SELECT * FROM usuario WHERE email = ?");
    $stmt->execute([$email]);

    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario && password_verify($senha, $usuario['senha'])) {

        // Criar sessão
        $_SESSION['user_id'] = $usuario['idUsuario'];
        $_SESSION['user_nome'] = $usuario['nome'];
        $_SESSION['user_nivel'] = $usuario['nivel'];

        // 🔥 AQUI É O REDIRECIONAMENTO POR NÍVEL
        if ($usuario['nivel'] === 'TATUADOR') {
            header("Location: tatuador/area-tatuador.php");
        } elseif ($usuario['nivel'] === 'ADMIN') {
            header("Location: admin/dashboard.php");
        } else {
            header("Location: cliente/area-cliente.php");
        }

        exit;

    } else {
        $erro = "Email ou senha inválidos!";
    }
}
?>
<?php require_once 'includes/header.php'?>

<div class="auth-container">
    <div class="auth-card">
        <div class="auth-header">
            <div class="auth-icon">🔐</div>
            <h2 class="auth-title">Bem-vindo de volta</h2>
            <p class="auth-subtitle">Acesse sua conta para continuar</p>
        </div>

        <?php if ($erro): ?>
            <div class="alert alert-error"><?php echo $erro; ?></div>
        <?php endif; ?>

        <form method="POST" class="auth-form">
            <div class="form-group">
                <label for="email">E-mail</label>
                <input type="email" id="email" name="email" required class="form-input">
            </div>

            <div class="form-group">
                <label for="senha">Senha</label>
                <input type="password" id="senha" name="senha" required class="form-input">
            </div>

            <button type="submit" class="btn-submit">Entrar</button>
        </form>

        <div class="auth-footer">
            <p>Ainda não tem conta? <a href="cadastro.php" class="link-red">Cadastre-se</a></p>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
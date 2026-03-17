<?php
session_start();
require_once './includes/config.php';
require_once './includes/funcoes.php';

$erro = '';

// if ($_SERVER['REQUEST_METHOD'] === 'POST') {

//     $email = trim(limparDados($_POST['email']));
//     $senha = $_POST['senha'];

//     $stmt = $pdo->prepare("SELECT * FROM usuario WHERE email = ?");
//     $stmt->execute([$email]);

//     $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

//     if ($usuario && password_verify($senha, $usuario['senha'])) {

//         $_SESSION['user_id'] = $usuario['idUsuario'];
//         $_SESSION['user_nome'] = $usuario['nome'];
//         $_SESSION['user_nivel'] = $usuario['nivel'];

//         if ($usuario['nivel'] === 'TATUADOR') {
//             header("Location: tatuador/area-tatuador.php");
//         } elseif ($usuario['nivel'] === 'ADMIN') {
//             header("Location: admin/dashboard.php");
//         } else {
//             header("Location: cliente/area-cliente.php");
//         }

//         exit;

//     } else {
//         $erro = "Email ou senha inválidos!";
//     }
// }
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Limpa qualquer saída acidental (espaços, echos, var_dumps)
    ob_start();

    $email = trim(limparDados($_POST['email']));
    $senha = $_POST['senha'];

    $stmt = $pdo->prepare("SELECT * FROM usuario WHERE email = ?");
    $stmt->execute([$email]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario && password_verify($senha, $usuario['senha'])) {
        // 2. Registra a sessão
        $_SESSION['user_id'] = $usuario['idUsuario'];
        $_SESSION['user_nome'] = $usuario['nome'];
        $_SESSION['user_nivel'] = trim($usuario['nivel']); // trim para garantir que não há espaços

        // 3. Define o destino
        $destino = 'cliente/area-cliente.php';
        if ($_SESSION['user_nivel'] === 'TATUADOR') {
            $destino = 'tatuador/area-tatuador.php';
        } elseif ($_SESSION['user_nivel'] === 'ADMIN') {
            $destino = 'admin/dashboard.php';
        }

        // 4. Tenta redirecionar por PHP, se falhar, vai por JS
        if (!headers_sent()) {
            header("Location: " . $destino);
            exit;
        } else {
            echo '<script type="text/javascript">window.location.href="' . $destino . '";</script>';
            exit;
        }

    } else {
        $erro = "Email ou senha inválidos!";
    }
    ob_end_flush();
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
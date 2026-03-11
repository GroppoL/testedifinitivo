<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/funcoes.php';

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nome  = limparDados($_POST['nome']);
    $email = limparDados($_POST['email']);
    $senha = $_POST['senha'];
    $confirma_senha = $_POST['confirma_senha'];

    // 🔹 Verifica se senhas coincidem
    if ($senha !== $confirma_senha) {
        $erro = 'As senhas não coincidem!';
    } else {

        // 🔹 Verifica se email já existe na tabela usuario
        $stmt = $pdo->prepare("
            SELECT idUsuario 
            FROM usuario 
            WHERE email = ?
            LIMIT 1
        ");
        $stmt->execute([$email]);

        if ($stmt->fetch()) {
            $erro = 'Este email já está cadastrado!';
        } else {

            $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

            // 🔹 Cria usuário na tabela usuario
            $stmt = $pdo->prepare("
                INSERT INTO usuario (nome, email, login, senha, nivel)
                VALUES (?, ?, ?, ?, 'CLIENTE')
            ");

            $stmt->execute([
                $nome,
                $email,       // email
                $email,       // login (vamos usar email como login)
                $senhaHash
            ]);

            $idUsuario = $pdo->lastInsertId();

            // 🔹 Cria registro na tabela cliente
            $stmt = $pdo->prepare("
                INSERT INTO cliente (idUsuario)
                VALUES (?)
            ");
            $stmt->execute([$idUsuario]);

            // 🔹 Cria sessão
            $_SESSION['user_id'] = $idUsuario;
            $_SESSION['user_nome'] = $nome;
            $_SESSION['user_nivel'] = 'CLIENTE';

            header('Location: cliente/area-cliente.php');
            exit;
        }
    }
}

require_once 'includes/header.php';
?>

<div class="auth-container">
    <div class="auth-card">
        <div class="auth-header">
            <div class="auth-icon">✍️</div>
            <h2 class="auth-title">Criar Conta</h2>
            <p class="auth-subtitle">Junte-se a nós e agende sua tatuagem</p>
        </div>

        <?php if ($erro): ?>
            <div class="alert alert-error">
                <?= htmlspecialchars($erro) ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="auth-form">
            <div class="form-group">
                <label for="nome">Nome completo</label>
                <input type="text" id="nome" name="nome" required class="form-input">
            </div>

            <div class="form-group">
                <label for="email">E-mail</label>
                <input type="email" id="email" name="email" required class="form-input">
            </div>

            <div class="form-group">
                <label for="senha">Senha</label>
                <input type="password" id="senha" name="senha" required class="form-input">
            </div>

            <div class="form-group">
                <label for="confirma_senha">Confirmar senha</label>
                <input type="password" id="confirma_senha" name="confirma_senha" required class="form-input">
            </div>

            <button type="submit" class="btn-submit">Criar conta</button>
        </form>

        <div class="auth-footer">
            <p>
                Já tem conta?
                <a href="login.php" class="link-red">Faça login</a>
            </p>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
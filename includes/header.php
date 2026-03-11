<?php
if (!isset($pdo)) {
    // Mantendo a lógica de inclusão original
    require_once 'config.php'; 
    require_once 'funcoes.php';
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Studio Sombra - Tatuagens</title>
    <link rel="stylesheet" href="/test_tatto/assets/css/style.css">
</head>

<body>
    <header class="header">
        <div class="container">
            <div class="header-content">
                <a href="/test_tatto/index.php" class="logo">
                    <span class="logo-icon">📅</span>
                    <span class="logo-text">Studio Sombra Tattoo</span>
                </a>

                <nav class="nav">
                    <a href="/test_tatto/index.php" class="nav-link">Início</a>
                    
                    <?php if (isset($_SESSION['usuario_id'])): ?>
                        <a href="/test_tatto/cliente/area-cliente.php" class="nav-link">
                            👤 <?php echo htmlspecialchars($_SESSION['usuario_nome']); ?>
                        </a>
                        <a href="/test_tatto/logout.php" class="nav-link">Sair</a>
                    <?php else: ?>
                        <a href="/test_tatto/login.php" class="nav-link">Login</a>
                        <a href="/test_tatto/cadastro.php" class="btn-primary">Cadastre-se</a>
                    <?php endif; ?>
                </nav>
            </div>
        </div>
    </header>
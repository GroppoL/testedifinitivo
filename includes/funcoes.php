<?php

// 🔹 Limpar dados
function limparDados($dados) {
    return htmlspecialchars(trim($dados));
}

// 🔹 Verifica se está logado
function verificarLogin() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit;
    }
}

// 🔹 Verifica nível de acesso
function verificarNivel($nivelPermitido) {
    if (!isset($_SESSION['user_nivel']) || $_SESSION['user_nivel'] !== $nivelPermitido) {
        header("Location: login.php");
        exit;
    }
}

// 🔹 Logout
function logout() {
    session_start();
    session_destroy();
    header("Location: login.php");
    exit;
}

?>
<?php
try {
    $pdo = new PDO("mysql:host=localhost;dbname=test_sombra2", "root", "");
    echo "Conectado com sucesso!";
} catch(PDOException $e) {
    echo "Erro: " . $e->getMessage();
}
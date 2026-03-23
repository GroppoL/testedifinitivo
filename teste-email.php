<?php
require_once 'includes/email.php';

$resultado = enviarEmail(
    'SEUEMAIL@gmail.com',
    'Teste do sistema 🔥',
    '<h1>Funcionando!</h1><p>Seu sistema de email tá pronto 🚀</p>'
);

if ($resultado) {
    echo "EMAIL ENVIADO COM SUCESSO ✅";
} else {
    echo "ERRO AO ENVIAR ❌";
}
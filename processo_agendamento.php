<?php
session_start(); 

require_once 'includes/config.php';
require_once 'includes/funcoes.php';

verificarLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario_id = $_SESSION['user_id']; 

    // 1. Buscar dados do cliente (ID e Nome para a mensagem)
    $stmtCli = $pdo->prepare("SELECT c.idCliente, u.nome FROM cliente c JOIN usuario u ON c.idUsuario = u.idUsuario WHERE u.idUsuario = ?");
    $stmtCli->execute([$usuario_id]);
    $cliente = $stmtCli->fetch(PDO::FETCH_ASSOC);

    if (!$cliente) {
        die("Erro: Perfil de cliente não encontrado.");
    }

    $idCliente = $cliente['idCliente'];
    $nomeCliente = $cliente['nome'];
    $idTatuador = 1; // ID do Roosevelt na sua tabela 'tatuador'

    // 2. Coletando dados do formulário (Names vindos do seu HTML)
    $tipoAgendamento = limparDados($_POST['tipo'] ?? 'tattoo'); 
    $data = $_POST['data_agendamento'] ?? null;
    $hora = $_POST['hora_agendamento'] ?? null;
    $tipoTatuagem = $_POST['tipo_tatuagem'] ?? 'Nova';
    $primeiraTatuagem = $_POST['primeira_tatuagem'] ?? 'Não informado';
    $parteCorpo = limparDados($_POST['parte_corpo'] ?? 'Não informada');
    $tamanho = limparDados($_POST['tamanho'] ?? 'Não informado');
    $descricao = limparDados($_POST['descricao'] ?? 'Sem descrição adicional');

    // Validação de segurança
    if (!$data || !$hora) {
        header('Location: cliente/area-cliente.php?status=erro_dados');
        exit;
    }

    // 3. SQL de Inserção (Colunas exatas do seu banco de dados)
    $sql = "INSERT INTO agendamento 
            (idCliente, idTatuador, dataAgendamento, horaAgendamento, descricao, tipoTatuagem, primeiraTatuagem, parteCorpo, tamanho, tipoAgendamento, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'PENDENTE')";
    
    $stmt = $pdo->prepare($sql);

    try {
        if ($stmt->execute([
            $idCliente,
            $idTatuador,
            $data,
            $hora,
            $descricao,
            $tipoTatuagem,
            $primeiraTatuagem,
            $parteCorpo,
            $tamanho,
            $tipoAgendamento
        ])) {
            
            // --- CONFIGURAÇÃO DO WHATSAPP ---
            $telefoneTatuador = "5513997503892"; 
            
            // Montagem da mensagem formatada
            $texto = "🔥 *NOVO AGENDAMENTO* 🔥\n\n";
            $texto .= "*Cliente:* " . $nomeCliente . "\n";
            $texto .= "*Serviço:* " . ($tipoAgendamento == 'tattoo' ? 'Tatuagem' : 'Consulta') . "\n";
            $texto .= "*Data:* " . date('d/m/Y', strtotime($data)) . "\n";
            $texto .= "*Hora:* " . $hora . "\n";
            
            if($tipoAgendamento == 'tattoo') {
                $texto .= "*Local:* " . $parteCorpo . "\n";
                $texto .= "*Estilo:* " . $tipoTatuagem . "\n";
            }

            $texto .= "\n*Descrição:* " . $descricao;

            // Link usando wa.me (mais estável)
            $linkWhatsapp = "https://wa.me/" . $telefoneTatuador . "?text=" . urlencode($texto);
            
            // 4. Tela Intermediária de Sucesso (Evita erro de conexão)
            ?>
            <!DOCTYPE html>
            <html lang="pt-br">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Agendamento Confirmado - Studio Sombra</title>
                <style>
                    body { 
                        background: #121212; 
                        color: white; 
                        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
                        display: flex; 
                        justify-content: center; 
                        align-items: center; 
                        height: 100vh; 
                        margin: 0; 
                    }
                    .card { 
                        background: #1e1e1e; 
                        padding: 40px; 
                        border-radius: 15px; 
                        box-shadow: 0 10px 30px rgba(0,0,0,0.5); 
                        max-width: 450px; 
                        width: 90%; 
                        text-align: center;
                        border: 1px solid #333;
                    }
                    .check-icon { 
                        font-size: 60px; 
                        color: #25d366; 
                        margin-bottom: 20px; 
                    }
                    h2 { color: #fff; margin-bottom: 15px; }
                    p { color: #aaa; line-height: 1.6; margin-bottom: 30px; }
                    .btn-whats { 
                        background: #25d366; 
                        color: white; 
                        text-decoration: none; 
                        padding: 16px 30px; 
                        border-radius: 8px; 
                        font-weight: bold; 
                        display: block; 
                        font-size: 18px;
                        transition: 0.3s;
                    }
                    .btn-whats:hover { 
                        background: #1da851; 
                        transform: translateY(-3px);
                    }
                    .back-link {
                        display: inline-block;
                        margin-top: 20px;
                        color: #666;
                        text-decoration: none;
                        font-size: 14px;
                    }
                    .back-link:hover { color: #888; }
                </style>
            </head>
            <body>
                <div class="card">
                    <div class="check-icon">✔</div>
                    <h2>Agendamento Realizado!</h2>
                    <p>Os dados foram salvos com sucesso em nosso sistema.<br><strong>Agora você precisa avisar o tatuador clicando abaixo:</strong></p>
                    
                    <a href="<?php echo $linkWhatsapp; ?>" class="btn-whats">📱 Avisar via WhatsApp</a>
                    
                    <a href="cliente/area-cliente.php" class="back-link">Voltar para meu painel</a>
                </div>
            </body>
            </html>
            <?php
            exit;

        } else {
            header('Location: cliente/area-cliente.php?status=erro');
        }
    } catch (PDOException $e) {
        // Log de erro para o desenvolvedor
        die("Erro crítico no banco de dados: " . $e->getMessage());
    }
    exit;
}
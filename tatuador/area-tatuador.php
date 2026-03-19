<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/funcoes.php';

verificarLogin();
verificarNivel('TATUADOR');

$usuario_id = $_SESSION['user_id'];
$usuario_nome = $_SESSION['user_nome'];

// 1. Identificar aba ativa (adicionada a opção 'portfolio')
$aba = filter_input(INPUT_GET, 'aba', FILTER_SANITIZE_SPECIAL_CHARS) ?: 'pendentes';

// buscando dados do cliente e agendamento
$sql = "SELECT a.*, u.nome AS nomeCliente, u.login as telefone
        FROM agendamento a
        JOIN cliente c ON a.idCliente = c.idCliente
        JOIN usuario u ON c.idUsuario = u.idUsuario";

if ($aba === 'confirmados') {
    $sql .= " WHERE a.status IN ('CONFIRMADO', 'CONCLUIDO')";
} else {
    $sql .= " WHERE a.status = 'PENDENTE'";
}

$sql .= " ORDER BY a.dataAgendamento ASC, a.horaAgendamento ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute();
$agendamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ADICIONADO: Lógica para carregar fotos se a aba for portfolio
$fotos = [];
if ($aba === 'portfolio') {
    $stmtTatuador = $pdo->prepare("SELECT idTatuador FROM tatuador WHERE idUsuario = ?");
    $stmtTatuador->execute([$usuario_id]);
    $tatuador = $stmtTatuador->fetch(PDO::FETCH_ASSOC);
    $idTatuadorReal = $tatuador['idTatuador'] ?? 0;

    $stmtFotos = $pdo->prepare("SELECT * FROM portfolio WHERE idTatuador = ? ORDER BY dataPublicacao DESC");
    $stmtFotos->execute([$idTatuadorReal]);
    $fotos = $stmtFotos->fetchAll(PDO::FETCH_ASSOC);
}

// Contagem para as abas
$countPendentes = $pdo->query("SELECT COUNT(*) FROM agendamento WHERE status = 'PENDENTE'")->fetchColumn();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Painel Roosevelt - Studio Sombra</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { background-color: #000; color: #fff; font-family: sans-serif; }
        .container { max-width: 1000px; margin: 40px auto; padding: 20px; }
        
        /* Estilo das Abas */
        .tabs { display: flex; gap: 20px; border-bottom: 1px solid #333; margin-bottom: 30px; }
        .tab-item { 
            padding: 10px 0; color: #888; text-decoration: none; font-size: 14px; 
            display: flex; align-items: center; gap: 8px; border-bottom: 2px solid transparent;
        }
        .tab-item.active { color: #ff3b3b; border-bottom-color: #ff3b3b; }
        .badge { background: #333; color: #fff; padding: 2px 8px; border-radius: 10px; font-size: 12px; }

        /* MANTIDO: Card Estilo Horizontal */
        .card { 
            background: #1e1e1e; border-radius: 12px; padding: 25px; margin-bottom: 20px; 
            display: flex; flex-direction: column; gap: 20px; border: 1px solid #2a2a2a;
        }
        .card-header { display: flex; justify-content: space-between; align-items: center; }
        .card-header h3 { margin: 0; font-size: 20px; color: #fff; }
        
        .status-pill { 
            padding: 5px 12px; border-radius: 20px; font-size: 11px; font-weight: bold;
            background: rgba(255, 165, 0, 0.1); color: #ffa500; border: 1px solid rgba(255, 165, 0, 0.3);
        }
        .CONFIRMADO { background: rgba(37, 211, 102, 0.1); color: #25d366; border-color: rgba(37, 211, 102, 0.3); }

        .card-body { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px; }
        .info-group label { display: block; color: #666; font-size: 12px; margin-bottom: 5px; }
        .info-group span { font-size: 14px; font-weight: bold; }

        /* Área de Edição */
        .edit-area { 
            display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px; 
            padding-top: 20px; border-top: 1px solid #333; margin-top: 5px;
        }
        .input-dark { 
            background: #121212; border: 1px solid #333; color: #fff; 
            padding: 10px; border-radius: 6px; width: 100%; box-sizing: border-box;
        }
        
        .btn-save { 
            background: #fff; color: #000; border: none; padding: 12px; 
            border-radius: 8px; font-weight: bold; cursor: pointer; transition: 0.3s;
        }
        .btn-save:hover { background: #ccc; }
        
        .btn-whats { 
            background: #25d366; color: #fff; text-decoration: none; padding: 12px; 
            border-radius: 8px; text-align: center; font-weight: bold; display: flex; 
            align-items: center; justify-content: center; gap: 8px;
        }

        /* ADICIONADO: Estilos simples para a Grade do Portfólio */
        .grid-portfolio { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 15px; }
        .foto-item { background: #1e1e1e; border-radius: 8px; overflow: hidden; border: 1px solid #333; }
        .foto-item img, .foto-item video { width: 100%; height: 180px; object-fit: cover; }
        .btn-nova-arte { background: #ff3b3b; color: #fff; padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: bold; display: inline-block; margin-bottom: 20px; }
    </style>
</head>
<body>

<div class="container">
    <div style="margin-bottom: 40px;">
        <h1 style="font-size: 32px; margin-bottom: 5px;">Olá, <span style="color: #ff3b3b;"><?php echo htmlspecialchars($usuario_nome); ?></span>!</h1>
        <p style="color: #888;">Gerencie a agenda e os orçamentos do studio.</p>
    </div>

    <div class="tabs">
        <a href="?aba=pendentes" class="tab-item <?php echo $aba === 'pendentes' ? 'active' : ''; ?>">
            <i class="fas fa-inbox"></i> Meus Agendamentos 
            <?php if($countPendentes > 0): ?><span class="badge"><?php echo $countPendentes; ?></span><?php endif; ?>
        </a>
        <a href="?aba=confirmados" class="tab-item <?php echo $aba === 'confirmados' ? 'active' : ''; ?>">
            <i class="fas fa-calendar-check"></i> Agenda Confirmada
        </a>
        <a href="?aba=portfolio" class="tab-item <?php echo $aba === 'portfolio' ? 'active' : ''; ?>">
            <i class="fas fa-camera"></i> Meu Portfólio
        </a>
        <a href="../logout.php" class="tab-item" style="margin-left: auto; color: #ff3b3b;">
            <i class="fas fa-sign-out-alt"></i> Sair
        </a>
    </div>

    <?php if ($aba === 'portfolio'): ?>
        <a href="cadTattoo/cadastrar.php" class="btn-nova-arte"><i class="fas fa-plus"></i> Adicionar Nova Arte</a>
        
        <?php if (empty($fotos)): ?>
            <div style="text-align: center; color: #666; padding: 50px;">Nenhuma foto cadastrada no seu portfólio.</div>
        <?php else: ?>
            <div class="grid-portfolio">
                <?php foreach ($fotos as $f): ?>
                    <div class="foto-item">
                        <img src="../Imagens/<?php echo $f['arquivo']; ?>">
                        <div style="padding: 10px; font-size: 13px;">
                            <?php echo htmlspecialchars($f['titulo']); ?>
                            <div style="margin-top: 5px;">
                                <a href="cadTattoo/excluir.php?id=<?php echo $f['idPortfolio']; ?>" style="color: #ff3b3b; text-decoration: none;">Excluir</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    <?php else: ?>
        <?php if (empty($agendamentos)): ?>
            <div style="text-align: center; color: #666; padding: 50px;">Nenhum registro encontrado nesta aba.</div>
        <?php else: ?>
            <?php foreach ($agendamentos as $ag): 
                $tel = preg_replace('/\D/', '', $ag['telefone']);
                $whats = "https://wa.me/55" . $tel;
            ?>
                <div class="card">
                    <div class="card-header">
                        <h3><?php echo htmlspecialchars($ag['nomeCliente']); ?></h3>
                        <span class="status-pill <?php echo $ag['status']; ?>"><i class="fas fa-hourglass-half"></i> <?php echo $ag['status']; ?></span>
                    </div>

                    <div class="card-body">
                        <div class="info-group">
                            <label>DATA:</label>
                            <span><?php echo date('d/m/Y', strtotime($ag['dataAgendamento'])); ?></span>
                        </div>
                        <div class="info-group">
                            <label>HORÁRIO:</label>
                            <span><?php echo $ag['horaAgendamento']; ?></span>
                        </div>
                        <div class="info-group">
                            <label>TIPO:</label>
                            <span><?php echo htmlspecialchars($ag['tipoAgendamento'] ?: 'Tattoo'); ?></span>
                        </div>
                        <div class="info-group">
                            <label>LOCAL:</label>
                            <span><?php echo htmlspecialchars($ag['parteCorpo'] ?: 'N/A'); ?></span>
                        </div>
                    </div>

                    <form action="atualizar-status.php" method="POST">
                        <input type="hidden" name="idAgendamento" value="<?php echo $ag['idAgendamento']; ?>">
                        
                        <div class="edit-area">
                            <div class="info-group">
                                <label>VALOR FINAL (R$)</label>
                                <input type="text" name="valorFinal" value="<?php echo $ag['valorFinal']; ?>" class="input-dark" placeholder="R$ 0,00">
                            </div>
                            <div class="info-group">
                                <label>MUDAR STATUS</label>
                                <select name="status" class="input-dark">
                                    <option value="PENDENTE" <?php echo $ag['status'] == 'PENDENTE' ? 'selected' : ''; ?>>Pendente</option>
                                    <option value="CONFIRMADO" <?php echo $ag['status'] == 'CONFIRMADO' ? 'selected' : ''; ?>>Confirmado</option>
                                    <option value="CONCLUIDO" <?php echo $ag['status'] == 'CONCLUIDO' ? 'selected' : ''; ?>>Concluído</option>
                                    <option value="CANCELADO" <?php echo $ag['status'] == 'CANCELADO' ? 'selected' : ''; ?>>Cancelado</option>
                                </select>
                            </div>
                            <div class="info-group">
                                <label>OBSERVAÇÕES</label>
                                <input type="text" name="observacoes" value="<?php echo htmlspecialchars($ag['observacoes']); ?>" class="input-dark">
                            </div>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-top: 15px;">
                            <button type="submit" class="btn-save"><i class="fas fa-save"></i> Salvar Alterações</button>
                            <a href="<?php echo $whats; ?>" target="_blank" class="btn-whats"><i class="fab fa-whatsapp"></i> Chamar Cliente</a>
                        </div>
                    </form>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    <?php endif; ?>
</div>

</body>
</html>
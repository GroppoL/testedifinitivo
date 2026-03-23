<?php
session_start();

require_once('../includes/config.php');
require_once('../includes/funcoes.php');

verificarLogin();
verificarNivel('CLIENTE');

$usuario_id = $_SESSION['user_id'];
$usuario_nome = $_SESSION['user_nome'];

// Buscar idCliente
$stmt = $pdo->prepare("SELECT idCliente FROM cliente WHERE idUsuario = ?");
$stmt->execute([$usuario_id]);
$cliente = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$cliente) {
    die("Perfil de cliente não encontrado.");
}

$idCliente = $cliente['idCliente'];

// Buscar agendamentos
$stmt = $pdo->prepare("SELECT * FROM agendamento WHERE idCliente = ? ORDER BY criadoEm DESC");
$stmt->execute([$idCliente]);
$agendamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Buscar referências (Tatuagens Salvas)
$stmt = $pdo->prepare("SELECT * FROM referencia_salva WHERE idCliente = ? ORDER BY dataSalvo DESC");
$stmt->execute([$idCliente]);
$tatuagens_salvas = $stmt->fetchAll(PDO::FETCH_ASSOC);

include '../includes/header.php'; 
?>

<div class="client-area">
    <?php if (isset($_GET['status']) && $_GET['status'] == 'sucesso'): ?>
        <div style="background-color: #d4edda; color: #155724; padding: 15px; margin: 20px auto; max-width: 1200px; border-radius: 5px; text-align: center; border: 1px solid #c3e6cb; font-weight: bold;">
            📅 Solicitação enviada! Aguarde a confirmação do tatuador.
        </div>
    <?php endif; ?>

    <div class="container">
        <div class="client-header">
            <h1 class="client-title">
                Olá, <span class="text-red"><?php echo htmlspecialchars($usuario_nome); ?></span>!
            </h1>
            <p class="client-subtitle">Gerencie suas tatuagens e agendamentos</p>

            <div class="tabs">
                <button class="tab-btn active" data-tab="agendamentos">
                    📅 Meus Agendamentos (<?php echo count($agendamentos); ?>)
                </button>
                <button class="tab-btn" data-tab="salvas">
                    ❤️ Tatuagens Salvas (<?php echo count($tatuagens_salvas); ?>)
                </button>
                <button class="tab-btn" data-tab="novo">
                    ➕ Novo Agendamento
                </button>
            </div>

            <div class="tab-content active" id="agendamentos">
                <?php if (empty($agendamentos)): ?>
                    <div class="empty-state">
                        <div class="empty-icon">📅</div>
                        <p class="empty-text">Você ainda não tem agendamentos</p>
                        <button class="btn-primary" onclick="switchTab('novo')">Fazer Agendamento</button>
                    </div>
                <?php else: ?>
                    <div class="bookings-grid">
                        <?php foreach ($agendamentos as $agendamento): ?>
                            <div class="booking-card">
                                <div class="booking-header">
                                    <h3><?php echo $agendamento['tipoAgendamento'] === 'tattoo' ? 'Tatuagem' : 'Consulta'; ?></h3>
                                    <span class="status-badge"><?php echo $agendamento['status']; ?></span>
                                </div>
                                <p>Data: <?php echo date('d/m/Y', strtotime($agendamento['dataAgendamento'])); ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="tab-content" id="salvas">
                </div>

            <div class="tab-content" id="novo">
                <div class="booking-form-container">
                    <h2 class="form-title">Novo Agendamento</h2>
                    <div class="booking-type-selector" style="display: flex; gap: 20px; margin-bottom: 30px;">
                        <div class="type-option active" data-type="tattoo" style="cursor:pointer; flex:1; padding:20px; border:2px solid #333; border-radius:10px; text-align:center;">
                            <div class="type-icon">🎨</div>
                            <h3>Agendar Tatuagem</h3>
                            <p>Solicite um horário para fazer sua tattoo</p>
                        </div>
                        <div class="type-option" data-type="consulta" style="cursor:pointer; flex:1; padding:20px; border:2px solid #333; border-radius:10px; text-align:center;">
                            <div class="type-icon">💬</div>
                            <h3>Consulta Presencial</h3>
                            <p>Tire suas dúvidas e conheça o estúdio</p>
                        </div>
                    </div>

                    <form method="POST" action="./agendar.php" class="booking-form">
                        <input type="hidden" name="tipo" id="tipo_agendamento" value="tattoo">
                        
                        <div class="tattoo-fields">
                            <div class="form-row" style="display:flex; gap:15px; margin-bottom:15px;">
                                <div class="form-group" style="flex:1;">
                                    <label>Tipo de Tatuagem</label>
                                    <select name="tipo_tatuagem" class="form-input" style="width:100%; padding:10px; background:#111; color:#fff; border:1px solid #333;">
                                        <option value="nova">Nova Tatuagem</option>
                                        <option value="cobertura">Cobertura</option>
                                        <option value="fechamento">Fechamento</option>
                                        <option value="restauracao">Restauração</option>
                                    </select>
                                </div>
                                <div class="form-group" style="flex:1;">
                                    <label>Primeira tatuagem?</label>
                                    <select name="primeira_tatuagem" class="form-input" style="width:100%; padding:10px; background:#111; color:#fff; border:1px solid #333;">
                                        <option value="sim">Sim</option>
                                        <option value="nao">Não</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-row" style="display:flex; gap:15px; margin-bottom:15px;">
                                <div class="form-group" style="flex:1;">
                                    <label>Parte do Corpo</label>
                                    <input type="text" name="parte_corpo" class="form-input" placeholder="Ex: Braço" style="width:100%; padding:10px; background:#111; color:#fff; border:1px solid #333;">
                                </div>
                                <div class="form-group" style="flex:1;">
                                    <label>Tamanho Aproximado</label>
                                    <select name="tamanho" class="form-input" style="width:100%; padding:10px; background:#111; color:#fff; border:1px solid #333;">
                                        <option value="pequeno">Pequeno (até 5cm)</option>
                                        <option value="medio">Médio (5-15cm)</option>
                                        <option value="grande">Grande (15-30cm)</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group" style="margin-bottom:15px;">
                                <label>Descrição da Ideia</label>
                                <textarea name="descricao" rows="4" class="form-input" style="width:100%; padding:10px; background:#111; color:#fff; border:1px solid #333;"></textarea>
                            </div>
                        </div>

                        <div class="form-row" style="display:flex; gap:15px; margin-bottom:20px;">
                            <div class="form-group" style="flex:1;">
                                <label>📅 Data Preferida</label>
                                <input type="date" name="data_agendamento" required class="form-input" style="width:100%; padding:10px; background:#111; color:#fff; border:1px solid #333;">
                            </div>
                            <div class="form-group" style="flex:1;">
                                <label>🕐 Horário Preferido</label>
                                <select name="hora_agendamento" required class="form-input" style="width:100%; padding:10px; background:#111; color:#fff; border:1px solid #333;">
                                    <option value="09:00">09:00</option>
                                    <option value="14:00">14:00</option>
                                    <option value="16:00">16:00</option>
                                </select>
                            </div>
                        </div>
                        <button type="submit" class="btn-submit" style="width:100%; padding:15px; background:#ff3b3b; color:#fff; border:none; font-weight:bold; cursor:pointer;">Solicitar Agendamento</button>
                    </form>
                </div>
            </div>

            <div style="margin-top: 30px; border-top: 1px solid #333; padding-top: 20px;">
                 <a href="../logout.php" style="color: #ff3b3b; text-decoration: none; font-weight: bold;"><i class="fas fa-sign-out-alt"></i> Sair da Conta</a>
            </div>
        </div> 
    </div> 
</div>

<script>
// Mantendo sua lógica de abas
document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
        btn.classList.add('active');
        document.getElementById(btn.dataset.tab).classList.add('active');
    });
});

// Lógica para os cards de tipo de agendamento
document.querySelectorAll('.type-option').forEach(option => {
    option.addEventListener('click', function() {
        document.querySelectorAll('.type-option').forEach(opt => opt.style.borderColor = '#333');
        this.style.borderColor = '#ff3b3b';
        const type = this.getAttribute('data-type');
        document.getElementById('tipo_agendamento').value = type;
        
        // Esconde campos de tattoo se for consulta
        const tattooFields = document.querySelector('.tattoo-fields');
        tattooFields.style.display = (type === 'consulta') ? 'none' : 'block';
    });
});
</script>

<?php include '../includes/footer.php'; ?>
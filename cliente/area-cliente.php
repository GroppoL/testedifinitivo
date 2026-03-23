<?php
session_start();
require_once('../includes/config.php');
require_once('../includes/funcoes.php');
 
verificarLogin();
verificarNivel('CLIENTE');
 
$usuario_id = $_SESSION['user_id'];
$usuario_nome = $_SESSION['user_nome'];

// 1. Buscar idCliente
$stmt = $pdo->prepare("SELECT idCliente FROM cliente WHERE idUsuario = ?");
$stmt->execute([$usuario_id]);
$cliente = $stmt->fetch(PDO::FETCH_ASSOC);
$idCliente = $cliente['idCliente'];

// 2. Buscar agendamentos
$stmt = $pdo->prepare("SELECT * FROM agendamento WHERE idCliente = ? ORDER BY criadoEm DESC");
$stmt->execute([$idCliente]);
$agendamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 3. Buscar Tatuagens Salvas (Pinterest)
$stmt = $pdo->prepare("SELECT * FROM referencia_salva WHERE idCliente = ? ORDER BY dataSalvo DESC");
$stmt->execute([$idCliente]);
$tatuagens_salvas = $stmt->fetchAll(PDO::FETCH_ASSOC);
 
include '../includes/header.php';
?>
 
<div class="client-area">
    <div class="container">
        <div class="client-header">
            <h1 class="client-title">Olá, <span class="text-red"><?php echo htmlspecialchars($usuario_nome); ?></span>!</h1>
            <div class="tabs">
                <button class="tab-btn active" data-tab="agendamentos">📅 Meus Agendamentos</button>
                <button class="tab-btn" data-tab="salvas">❤️ Tatuagens Salvas</button>
                <button class="tab-btn" data-tab="novo">➕ Novo Agendamento</button>
            </div>
        </div>
        <div class="tab-content active" id="agendamentos">
            <?php if (empty($agendamentos)): ?>
                <p>Você ainda não possui agendamentos.</p>
            <?php else: ?>
                <div class="bookings-grid">
                    <?php foreach ($agendamentos as $ag): ?>
                        <div class="booking-card" style="background: #1a1a1a; padding: 15px; border-radius: 8px; margin-bottom: 10px; border-left: 4px solid #ff3b3b;">
                            <strong><?php echo ($ag['tipoAgendamento'] == 'tattoo') ? 'Tatuagem' : 'Consulta'; ?></strong>
                            <p>Data: <?php echo date('d/m/Y', strtotime($ag['dataAgendamento'])); ?> às <?php echo $ag['horaAgendamento']; ?></p>
                            <span class="status-badge"><?php echo $ag['status']; ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        <div class="tab-content" id="salvas">
            <?php if (empty($tatuagens_salvas)): ?>
                <p>Você ainda não salvou nenhuma referência.</p>
            <?php else: ?>
                <div class="portfolio-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 15px;">
                    <?php foreach ($tatuagens_salvas as $ref): ?>
                        <div class="portfolio-item">
                            <img src="../Imagens/<?php echo htmlspecialchars($ref['arquivo']); ?>" style="width: 100%; border-radius: 8px;">
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        <div class="tab-content" id="novo">
            <div class="booking-form-container">
                <h2 class="form-title">Novo Agendamento</h2>
                <div class="booking-type-selector">
                    <div class="type-option active" data-type="tattoo">
                        <div class="type-icon">🎨</div>
                        <h3>Agendar Tatuagem</h3>
                        <p>Solicite um horário para fazer sua tattoo</p>
                    </div>
                    <div class="type-option" data-type="consulta">
                        <div class="type-icon">💬</div>
                        <h3>Consulta Presencial</h3>
                        <p>Tire suas dúvidas e conheça o estúdio</p>
                    </div>
                </div>


                <form method="POST" action="../processo_agendamento.php" class="booking-form">
                    <input type="hidden" name="tipo" id="tipo_agendamento" value="tattoo">
                    
                    <div class="tattoo-fields">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="tipo_tatuagem">Tipo de Tatuagem</label>
                                <select name="tipo_tatuagem" id="tipo_tatuagem" class="form-input">
                                    <option value="">Selecione...</option>
                                    <option value="Nova Tatuagem">Nova Tatuagem</option>
                                    <option value="Cobertura">Cobertura</option>
                                    <option value="Fechamento">Fechamento</option>
                                    <option value="Restauração">Restauração</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="primeira_tatuagem">É sua primeira tatuagem?</label>
                                <select name="primeira_tatuagem" id="primeira_tatuagem" class="form-input">
                                    <option value="">Selecione...</option>
                                    <option value="Sim">Sim</option>
                                    <option value="Não">Não</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="parte_corpo">Parte do Corpo</label>
                                <input type="text" name="parte_corpo" id="parte_corpo" class="form-input" placeholder="Ex: Braço, Costas...">
                            </div>
                            <div class="form-group">
                                <label for="tamanho">Tamanho Aproximado</label>
                                <select name="tamanho" id="tamanho" class="form-input">
                                    <option value="">Selecione...</option>
                                    <option value="Pequeno (até 5cm)">Pequeno (até 5cm)</option>
                                    <option value="Médio (5-15cm)">Médio (5-15cm)</option>
                                    <option value="Grande (15-30cm)">Grande (15-30cm)</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="descricao">Descrição da Ideia</label>
                            <textarea name="descricao" id="descricao" rows="5" class="form-input" placeholder="Descreva sua ideia..."></textarea>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="data_agendamento">📅 Data Preferida</label>
                            <input type="date" name="data_agendamento" id="data_agendamento" required class="form-input" min="<?php echo date('Y-m-d'); ?>">
                        </div>
                        <div class="form-group">
                            <label for="hora_agendamento">🕐 Horário Preferido</label>
                            <select name="hora_agendamento" id="hora_agendamento" required class="form-input">
                                <option value="09:00">09:00</option>
                                <option value="11:00">11:00</option>
                                <option value="14:00">14:00</option>
                                <option value="16:00">16:00</option>
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="btn-submit">Solicitar Agendamento</button>
                </form>
            </div>
        </div>
    </div>
</div>
 
<script>
// Lógica de abas
document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
        btn.classList.add('active');
        document.getElementById(btn.dataset.tab).classList.add('active');
    });
});

// Lógica de seleção do tipo
document.querySelectorAll('.type-option').forEach(option => {
    option.addEventListener('click', function() {
        document.querySelectorAll('.type-option').forEach(opt => opt.classList.remove('active'));
        this.classList.add('active');
        const type = this.getAttribute('data-type');
        document.getElementById('tipo_agendamento').value = type;

        const tattooFields = document.querySelector('.tattoo-fields');
        tattooFields.style.display = (type === 'consulta') ? 'none' : 'block';
    });
});
</script>
<?php include '../includes/footer.php'; ?>
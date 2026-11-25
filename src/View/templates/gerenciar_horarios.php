<?php
require_once ROOT_DIR . '/src/View/templates/includes/cabecalhoInterno.inc.php';

[
    'periodos_disponiveis' => $periodos_disponiveis,
    'horarios_nomeados' => $horarios_nomeados,
] = $dadosView;

?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card shadow-lg border-0 rounded-lg">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h3 class="font-weight-light my-3">Gerenciar Horários Pré-definidos</h3>
                    <button class="btn btn-light text-primary fw-bold" data-bs-toggle="modal" data-bs-target="#horarioModal" onclick="prepararNovoModal()">
                        <i class="bi bi-plus-circle-fill me-1"></i> Novo Horário
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-dark">
                                <tr>
                                    <th>Nome do Horário</th>
                                    <th>Horário</th>
                                    <th>Período</th>
                                    <th class="text-center">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($horarios_nomeados)): ?>
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4">Nenhum horário pré-definido cadastrado.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($horarios_nomeados as $horario): ?>
                                        <tr>
                                            <td class="fw-bold"><?php echo htmlspecialchars($horario['nome']); ?></td>
                                            <td><?php echo date('H:i', strtotime($horario['horario'])); ?></td>
                                            <td><span class="badge bg-secondary"><?php echo htmlspecialchars($horario['periodo_nome']); ?></span></td>
                                            <td class="text-center">
                                                <button class="btn btn-sm btn-outline-primary edit-btn"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#horarioModal"
                                                    data-id="<?php echo $horario['id']; ?>"
                                                    data-nome="<?php echo htmlspecialchars($horario['nome']); ?>"
                                                    data-horario="<?php echo $horario['horario']; ?>"
                                                    data-periodo-id="<?php echo $horario['periodo_id']; ?>">
                                                    Editar
                                                </button>
                                                <form action="<?php echo BASE_URL; ?>acoes/controllerHorarios.php" method="POST" class="d-inline" onsubmit="return confirm('Tem certeza que deseja excluir este horário? A exclusão falhará se ele estiver em uso em alguma prescrição ativa.');">
                                                    <?php echo \Abdm\Infraestrutura\Sessao::campoTokenCsrf(); ?>
                                                    <input type="hidden" name="acao" value="excluir_horario">
                                                    <input type="hidden" name="id" value="<?php echo $horario['id']; ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">Excluir</button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Adicionar/Editar Horário -->
<div class="modal fade" id="horarioModal" tabindex="-1" aria-labelledby="horarioModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="horarioModalLabel">Novo Horário Pré-definido</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="horarioForm" action="<?php echo BASE_URL; ?>acoes/controllerHorarios.php" method="POST">
            <?php echo \Abdm\Infraestrutura\Sessao::campoTokenCsrf(); ?>
        <div class="modal-body">
            <input type="hidden" name="acao" id="form_acao" value="criar_horario">
            <input type="hidden" name="id" id="form_id">

            <div class="mb-3">
                <label for="form_nome" class="form-label">Nome do Horário</label>
                <input type="text" class="form-control" id="form_nome" name="nome" placeholder="Ex: Antes do Almoço" required>
            </div>

            <div class="mb-3">
                <label for="form_horario" class="form-label">Horário</label>
                <input type="time" class="form-control" id="form_horario" name="horario" required>
            </div>

            <div class="mb-3">
                <label for="form_periodo" class="form-label">Período de Agrupamento</label>
                <select class="form-select" id="form_periodo" name="periodo_id" required>
                    <option value="" disabled selected>Selecione um período</option>
                    <?php foreach ($periodos_disponiveis as $periodo): ?>
                        <option value="<?php echo $periodo['id']; ?>"><?php echo htmlspecialchars($periodo['nome']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-primary">Salvar</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const horarioModal = document.getElementById('horarioModal');
    const modalTitle = document.getElementById('horarioModalLabel');
    const form = document.getElementById('horarioForm');
    const formAcao = document.getElementById('form_acao');
    const formId = document.getElementById('form_id');
    const formNome = document.getElementById('form_nome');
    const formHorario = document.getElementById('form_horario');
    const formPeriodo = document.getElementById('form_periodo');

    // Função para preparar o modal para um novo horário
    window.prepararNovoModal = function() {
        form.reset();
        modalTitle.textContent = 'Novo Horário Pré-definido';
        formAcao.value = 'criar_horario';
        formId.value = '';
    };

    // Event listener para os botões de editar
    horarioModal.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;
        if (button && button.classList.contains('edit-btn')) {
            const id = button.dataset.id;
            const nome = button.dataset.nome;
            const horario = button.dataset.horario;
            const periodoId = button.dataset.periodoId;

            modalTitle.textContent = 'Editar Horário Pré-definido';
            formAcao.value = 'editar_horario';
            formId.value = id;
            formNome.value = nome;
            formHorario.value = horario;
            formPeriodo.value = periodoId;
        }
    });
});
</script>

<?php
require_once ROOT_DIR . '/src/View/templates/includes/rodapeInterno.inc.php';
?>

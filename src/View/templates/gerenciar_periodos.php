<?php
require_once ROOT_DIR . '/src/View/templates/includes/cabecalhoInterno.inc.php';

[
    'periodos' => $periodos,
] = $dadosView;

?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card shadow-lg border-0 rounded-lg">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h3 class="font-weight-light my-3">Gerenciar Períodos de Dispensação</h3>
                    <button class="btn btn-light text-primary fw-bold" data-bs-toggle="modal" data-bs-target="#periodoModal" onclick="prepararNovoModal()">
                        <i class="bi bi-plus-circle-fill me-1"></i> Novo Período
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-dark">
                                <tr>
                                    <th>Nome do Período</th>
                                    <th>Ordem de Exibição</th>
                                    <th class="text-center">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($periodos)): ?>
                                    <tr>
                                        <td colspan="3" class="text-center text-muted py-4">Nenhum período cadastrado.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($periodos as $periodo): ?>
                                        <tr>
                                            <td class="fw-bold"><?php echo htmlspecialchars($periodo['nome']); ?></td>
                                            <td><?php echo $periodo['ordem']; ?></td>
                                            <td class="text-center">
                                                <button class="btn btn-sm btn-outline-primary edit-btn"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#periodoModal"
                                                    data-id="<?php echo $periodo['id']; ?>"
                                                    data-nome="<?php echo htmlspecialchars($periodo['nome']); ?>"
                                                    data-ordem="<?php echo $periodo['ordem']; ?>">
                                                    Editar
                                                </button>
                                                <form action="<?php echo BASE_URL; ?>acoes/controllerPeriodos.php" method="POST" class="d-inline" onsubmit="return confirm('Tem certeza que deseja excluir este período? A exclusão falhará se ele estiver em uso.');">
                                                    <?php echo \Abdm\Infraestrutura\Sessao::campoTokenCsrf(); ?>
                                                    <input type="hidden" name="acao" value="excluir_periodo">
                                                    <input type="hidden" name="id" value="<?php echo $periodo['id']; ?>">
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

<!-- Modal para Adicionar/Editar Período -->
<div class="modal fade" id="periodoModal" tabindex="-1" aria-labelledby="periodoModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="periodoModalLabel">Novo Período</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="periodoForm" action="<?php echo BASE_URL; ?>acoes/controllerPeriodos.php" method="POST">
            <?php echo \Abdm\Infraestrutura\Sessao::campoTokenCsrf(); ?>
        <div class="modal-body">
            <input type="hidden" name="acao" id="form_acao" value="criar_periodo">
            <input type="hidden" name="id" id="form_id">

            <div class="mb-3">
                <label for="form_nome" class="form-label">Nome do Período</label>
                <input type="text" class="form-control" id="form_nome" name="nome" placeholder="Ex: Lanche da Tarde" required>
            </div>

            <div class="mb-3">
                <label for="form_ordem" class="form-label">Ordem de Exibição</label>
                <input type="number" class="form-control" id="form_ordem" name="ordem" required placeholder="Ex: 10, 20, 30...">
                <div class="form-text">Números menores aparecem primeiro. Use 10, 20, 30... para facilitar a inserção de novos períodos entre os existentes.</div>
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
    const periodoModal = document.getElementById('periodoModal');
    const modalTitle = document.getElementById('periodoModalLabel');
    const form = document.getElementById('periodoForm');
    const formAcao = document.getElementById('form_acao');
    const formId = document.getElementById('form_id');
    const formNome = document.getElementById('form_nome');
    const formOrdem = document.getElementById('form_ordem');

    window.prepararNovoModal = function() {
        form.reset();
        modalTitle.textContent = 'Novo Período';
        formAcao.value = 'criar_periodo';
        formId.value = '';
    };

    periodoModal.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;
        if (button && button.classList.contains('edit-btn')) {
            const id = button.dataset.id;
            const nome = button.dataset.nome;
            const ordem = button.dataset.ordem;

            modalTitle.textContent = 'Editar Período';
            formAcao.value = 'editar_periodo';
            formId.value = id;
            formNome.value = nome;
            formOrdem.value = ordem;
        }
    });
});
</script>

<?php
require_once ROOT_DIR . '/src/View/templates/includes/rodapeInterno.inc.php';
?>

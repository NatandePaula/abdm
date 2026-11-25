<?php
$controleAcesso ??= new \Abdm\Infraestrutura\ControleAcesso($pdo);
$controleAcesso->exigirPermissao('dispensacao');
(new \Abdm\Controller\TelaDispensacaoController($pdo))->atualizarPrescricoesExpiradas();
require_once ROOT_DIR . '/src/View/templates/includes/cabecalhoInterno.inc.php';

// --- Lógica da Página ---

// 1. Obter data e acolhidos ativos
[
    'data_selecionada' => $data_selecionada,
    'acolhidos_ativos' => $acolhidos_ativos,
    'presc' => $presc,
    'horarios_das_linhas' => $horarios_das_linhas,
    'horario_display_names' => $horario_display_names,
    'display_name' => $display_name,
    'disp' => $disp,
    'quadro_dispensacao' => $quadro_dispensacao,
    'pendencias_por_horario' => $pendencias_por_horario,
    'horario' => $horario,
    'item' => $item,
] = (new \Abdm\Controller\TelaDispensacaoController($pdo))->preparar();

?>

<div class="container-fluid my-5 px-4">
    <div class="row justify-content-center">
        <div class="col-lg-12">
            <div class="card shadow-lg border-0 rounded-lg">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h3 class="font-weight-light my-3">Quadro de Dispensação de Medicamentos</h3>
                    <form class="d-flex align-items-center">
                        <label for="data" class="me-2 text-white">Data:</label>
                        <input type="date" id="data" name="data" class="form-control" value="<?php echo htmlspecialchars($data_selecionada, ENT_QUOTES, 'UTF-8'); ?>" onchange="this.form.submit()">
                    </form>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle text-center mb-0">
                            <thead class="table-dark" style="position: sticky; top: 0; z-index: 1;">
                                <tr>
                                    <th scope="col" class="text-start" style="position: sticky; left: 0; z-index: 3; min-width: 220px;">Acolhido</th>
                                    <?php foreach ($horarios_das_linhas as $horario): ?>
                                        <?php $display_name = $horario_display_names[$horario]; ?>
                                        <th scope="col" style="min-width: 210px;">
                                            <?php echo htmlspecialchars($display_name); ?>
                                            <?php if ($display_name !== $horario): ?>
                                                <small class="d-block text-white-50"><?php echo htmlspecialchars($horario); ?></small>
                                            <?php endif; ?>
                                            <?php if (!empty($pendencias_por_horario[$horario])): ?>
                                                <button type="button" class="btn btn-sm btn-outline-light mt-1 w-100"
                                                    data-bs-toggle="modal" data-bs-target="#dispensarTodosModal"
                                                    data-horario-lote="<?php echo htmlspecialchars($horario, ENT_QUOTES, 'UTF-8'); ?>"
                                                    data-quantidade-pendente="<?php echo (int)$pendencias_por_horario[$horario]; ?>">
                                                    Dispensar em lote (<?php echo (int)$pendencias_por_horario[$horario]; ?>)
                                                </button>
                                            <?php endif; ?>
                                        </th>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($quadro_dispensacao)): ?>
                                    <tr>
                                        <td colspan="<?php echo count($horarios_das_linhas) + 1; ?>" class="text-center py-5 text-muted">Nenhuma medicação agendada para esta data.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($acolhidos_ativos as $acolhido): ?>
                                        <tr>
                                            <th scope="row" class="text-start bg-body text-body" style="position: sticky; left: 0; z-index: 2; min-width: 220px;">
                                                <?php if ($controleAcesso->usuarioPode('prescricoes')): ?>
                                                    <a href="<?php echo BASE_URL; ?>views/prescricoes.php?id_acolhido=<?php echo (int)$acolhido['id']; ?>" class="text-body text-decoration-none" title="Ver prescrições de <?php echo htmlspecialchars($acolhido['nome']); ?>">
                                                        <?php echo htmlspecialchars($acolhido['nome']); ?>
                                                        <i class="bi bi-box-arrow-up-right small"></i>
                                                    </a>
                                                <?php else: ?>
                                                    <?php echo htmlspecialchars($acolhido['nome']); ?>
                                                <?php endif; ?>
                                            </th>
                                            <?php foreach ($horarios_das_linhas as $horario): ?>
                                                <td>
                                                    <?php if (isset($quadro_dispensacao[$horario][$acolhido['id']])): ?>
                                                        <?php foreach ($quadro_dispensacao[$horario][$acolhido['id']] as $item):
                                                            $presc = $item['prescricao'];
                                                            $disp = $item['dispensado'];
                                                        ?>
                                                            <div class="mb-2 p-2 rounded border <?php echo $disp ? ($disp['status'] == 'dispensado' ? 'border-success' : 'border-danger') : 'border-light'; ?>">
                                                                <div class="fw-bold"><?php echo htmlspecialchars($presc['nome_comercial']); ?></div>
                                                                <small class="text-muted d-block"><?= htmlspecialchars(\Abdm\Model\HistoricoSaude::saudeFormatarDose($item['quantidade_por_dose'], $item['unidade_dose'])) ?></small>
                                                                <?php if ($disp): ?>
                                                                    <div class="mt-1 small badge <?php echo $disp['status'] == 'dispensado' ? 'text-bg-success' : 'text-bg-danger'; ?>">
                                                                        <?php echo ucfirst($disp['status']); ?> às <?php echo date('H:i', strtotime($disp['horario_dispensado'])); ?>
                                                                        <br>por <?php echo htmlspecialchars($disp['nome_responsavel'] ?: $disp['usuario_responsavel'], ENT_QUOTES, 'UTF-8'); ?>
                                                                    </div>
                                                                <?php else: ?>
                                                                    <button class="dispense-item-button btn btn-sm btn-primary mt-1 w-100" data-bs-toggle="modal" data-bs-target="#dispensarModal"
                                                                        data-presc-horario-id="<?php echo $item['horario_id']; ?>"
                                                                        data-presc-id="<?php echo $presc['prescricao_id']; ?>"
                                                                        data-horario-previsto="<?php echo htmlspecialchars($horario, ENT_QUOTES, 'UTF-8'); ?>"
                                                                        data-acolhido-id="<?php echo (int)$acolhido['id']; ?>"
                                                                        data-acolhido-nome="<?php echo htmlspecialchars($acolhido['nome'], ENT_QUOTES, 'UTF-8'); ?>"
                                                                        data-medicamento-id="<?php echo (int)$presc['medicamento_id']; ?>"
                                                                        data-medicamento-nome="<?php echo htmlspecialchars($presc['nome_comercial'], ENT_QUOTES, 'UTF-8'); ?>"
                                                                        data-quantidade="<?= htmlspecialchars((string)$item['quantidade_por_dose']) ?>"
                                                                        data-exige-equivalencia="<?= $item['exige_equivalencia'] ? '1' : '0' ?>"
                                                                        data-unidade-dose="<?php echo htmlspecialchars($item['unidade_dose'], ENT_QUOTES, 'UTF-8'); ?>"
                                                                    >Dispensar</button>
                                                                <?php endif; ?>
                                                            </div>
                                                        <?php endforeach; ?>
                                                    <?php endif; ?>
                                                </td>
                                            <?php endforeach; ?>
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

<!-- Modal de Dispensação -->
<div class="modal fade" id="dispensarModal" tabindex="-1" aria-labelledby="dispensarModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="dispensarModalLabel">Registrar Dispensação</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="<?php echo BASE_URL; ?>acoes/controllerMedicamento.php" method="POST">
        <?php echo \Abdm\Infraestrutura\Sessao::campoTokenCsrf(); ?>
        <div class="modal-body">
            <input type="hidden" name="acao" value="registrar_dispensacao">
            <input type="hidden" name="data_dispensacao" value="<?php echo $data_selecionada; ?>">
            <input type="hidden" name="prescricao_horario_id" id="disp_prescricao_horario_id">
            <input type="hidden" name="prescricao_id" id="disp_prescricao_id">
            <input type="hidden" name="horario_previsto" id="disp_horario_previsto">
            <input type="hidden" name="acolhido_id" id="disp_acolhido_id">
            <input type="hidden" name="medicamento_id" id="disp_medicamento_id">
            <input type="hidden" name="quantidade_dispensada" id="disp_quantidade">
            <div id="disp_equivalencia_pendente" class="alert alert-warning" hidden>Cadastre a equivalência em gotas por mL no medicamento para registrar a baixa.</div>

            <p>Acolhido: <strong id="disp_acolhido_nome"></strong></p>
            <p>Medicamento: <strong id="disp_medicamento_nome"></strong></p>
            <p>Quantidade: <strong id="disp_quantidade_label"></strong></p>
            
            <div class="mb-3">
                <label for="status" class="form-label">Status da Dispensação</label>
                <select id="status" name="status" class="form-select" required>
                    <option value="dispensado" selected>Dispensado com sucesso</option>
                    <option value="recusado">Acolhido recusou</option>
                    <option value="nao_administrado">Não administrado (outro motivo)</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="observacao" class="form-label">Observação (opcional)</label>
                <textarea class="form-control" name="observacao" rows="3"></textarea>
            </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-primary">Confirmar</button>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="modal fade" id="dispensarTodosModal" tabindex="-1" aria-labelledby="dispensarTodosModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="dispensarTodosModalLabel">Dispensar em lote neste horário</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
      </div>
      <form action="<?php echo BASE_URL; ?>acoes/controllerMedicamento.php" method="POST" id="dispensarTodosForm">
        <?php echo \Abdm\Infraestrutura\Sessao::campoTokenCsrf(); ?>
        <div class="modal-body">
          <input type="hidden" name="acao" value="registrar_dispensacoes_lote">
          <input type="hidden" name="data_dispensacao" value="<?php echo htmlspecialchars($data_selecionada, ENT_QUOTES, 'UTF-8'); ?>">
          <input type="hidden" name="horario_lote" id="dispensarTodosHorario">
          <input type="hidden" name="items_json" id="dispensarTodosItems">
          <p id="dispensarTodosResumo" class="mb-0"></p>
          <p class="small text-muted mt-2 mb-0">Todas as doses pendentes serão registradas como dispensadas e o estoque será atualizado. Se faltar estoque para algum medicamento, o lote inteiro será cancelado.</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-primary">Confirmar dispensação</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const dispensarModal = document.getElementById('dispensarModal');
    dispensarModal.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        
        // Extrai dados do botão
        const prescHorarioId = button.getAttribute('data-presc-horario-id');
        const prescId = button.getAttribute('data-presc-id');
        const horarioPrevisto = button.getAttribute('data-horario-previsto');
        const acolhidoId = button.getAttribute('data-acolhido-id');
        const acolhidoNome = button.getAttribute('data-acolhido-nome');
        const medId = button.getAttribute('data-medicamento-id');
        const medNome = button.getAttribute('data-medicamento-nome');
        const quantidade = button.getAttribute('data-quantidade');
        dispensarModal.querySelector('form').reset();

        dispensarModal.querySelector('#disp_equivalencia_pendente').hidden = button.dataset.exigeEquivalencia !== '1';
        const unidadeDose = button.getAttribute('data-unidade-dose');

        // Preenche o modal
        // Limpa campos antes de preencher
        dispensarModal.querySelector('#disp_prescricao_horario_id').value = '';
        dispensarModal.querySelector('#disp_prescricao_id').value = '';
        dispensarModal.querySelector('#disp_horario_previsto').value = '';

        if (prescHorarioId) { // É horário fixo
            dispensarModal.querySelector('#disp_prescricao_horario_id').value = prescHorarioId;
        } else { // É intervalo
            dispensarModal.querySelector('#disp_prescricao_id').value = prescId;
            dispensarModal.querySelector('#disp_horario_previsto').value = horarioPrevisto;
        }

        dispensarModal.querySelector('#disp_acolhido_id').value = acolhidoId;
        dispensarModal.querySelector('#disp_medicamento_id').value = medId;
        dispensarModal.querySelector('#disp_quantidade').value = quantidade;
        
        dispensarModal.querySelector('#disp_acolhido_nome').textContent = acolhidoNome;
        dispensarModal.querySelector('#disp_medicamento_nome').textContent = medNome;
        dispensarModal.querySelector('#disp_quantidade_label').textContent = `${parseFloat(quantidade)} ${unidadeDose}(s)`;
    });

    const dispensarTodosModal = document.getElementById('dispensarTodosModal');
    dispensarTodosModal.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        const horario = button.dataset.horarioLote;
        const itens = Array.from(document.querySelectorAll('.dispense-item-button'))
            .filter(itemButton => itemButton.dataset.horarioPrevisto === horario && itemButton.dataset.exigeEquivalencia !== '1')
            .map(itemButton => ({
                prescricao_horario_id: itemButton.dataset.prescHorarioId || '',
                prescricao_id: itemButton.dataset.prescId || '',
                horario_previsto: itemButton.dataset.horarioPrevisto || ''
            }));

        if (itens.length === 0) {
            event.preventDefault();
            return;
        }

        document.getElementById('dispensarTodosHorario').value = horario;
        document.getElementById('dispensarTodosItems').value = JSON.stringify(itens);
        document.getElementById('dispensarTodosResumo').textContent =
            `Confirma dispensar ${itens.length} dose(s) disponível(is) para dispensação em lote às ${horario}?`;
    });
});
</script>

<?php
require_once ROOT_DIR . '/src/View/templates/includes/rodapeInterno.inc.php';
?>

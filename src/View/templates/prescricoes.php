<?php
$controleAcesso ??= new \Abdm\Infraestrutura\ControleAcesso($pdo);
$controleAcesso->exigirPermissao('prescricoes');
$telasPrescricao = new \Abdm\Controller\TelasPrescricaoController($pdo);
$telasPrescricao->atualizarPrescricoesExpiradas();
require_once ROOT_DIR . '/src/View/templates/includes/cabecalhoInterno.inc.php';

if (!isset($_GET['id_acolhido'])) {
    $acolhidosParaPrescricao = $telasPrescricao->acolhidosParaPrescricao();
    ?>
    <main class="container my-5">
        <div class="mb-4">
            <h1 class="h2">Prescrições</h1>
            <p class="text-body-secondary">Selecione um acolhido para consultar ou gerenciar as prescrições.</p>
        </div>
        <div class="list-group shadow-sm">
            <?php foreach ($acolhidosParaPrescricao as $acolhidoPrescricao): ?>
                <a class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" href="<?php echo BASE_URL; ?>views/prescricoes.php?id_acolhido=<?php echo (int)$acolhidoPrescricao['id']; ?>">
                    <span><?php echo htmlspecialchars($acolhidoPrescricao['nome'], ENT_QUOTES, 'UTF-8'); ?></span>
                    <small class="text-body-secondary">Matrícula: <?php echo htmlspecialchars((string)$acolhidoPrescricao['matricula'], ENT_QUOTES, 'UTF-8'); ?></small>
                </a>
            <?php endforeach; ?>
            <?php if (!$acolhidosParaPrescricao): ?>
                <div class="list-group-item text-body-secondary">Nenhum acolhido ativo cadastrado.</div>
            <?php endif; ?>
        </div>
    </main>
    <?php
    require_once ROOT_DIR . '/src/View/templates/includes/rodapeInterno.inc.php';
    exit;
}

$id_acolhido = (int)$_GET['id_acolhido'];
$quadro = $telasPrescricao->quadroDoAcolhido($id_acolhido);
if ($quadro === null) {
    echo "<div class='container my-5 alert alert-danger'>Acolhido não encontrado.</div>";
    require_once ROOT_DIR . '/src/View/templates/includes/rodapeInterno.inc.php';
    exit;
}
[
    'acolhido' => $acolhido,
    'prescricoes' => $prescricoes,
    'horariosPorPrescricao' => $horarios_por_prescricao,
    'periodos' => $periodos_disponiveis,
    'horariosNomeados' => $horarios_nomeados,
    'medicamentos' => $medicamentos_ativos,
] = $quadro;

$formatarDose = static function ($quantidade, $unidade): string {
    if (!is_numeric($quantidade) || (float)$quantidade <= 0) {
        return 'Dose não informada';
    }
    $numero = rtrim(rtrim(number_format((float)$quantidade, 2, '.', ''), '0'), '.');
    $unidade = trim((string)$unidade);
    $formasUnidade = [
        'comprimido' => ['comprimido', 'comprimidos'],
        'capsula' => ['cápsula', 'cápsulas'],
        'ml' => ['ml', 'ml'],
        'gota' => ['gota', 'gotas'],
        'unidade' => ['unidade', 'unidades'],
    ];
    if ($unidade === '') {
        return $numero;
    }
    $formas = $formasUnidade[$unidade] ?? [$unidade, $unidade . 's'];
    return $numero . ' ' . $formas[(float)$quantidade === 1.0 ? 0 : 1];
};

?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card shadow-lg border-0 rounded-lg" id="card-prescricoes">
                <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
                    <h3 class="font-weight-light my-3">Prescrições de <?php echo htmlspecialchars($acolhido['nome']); ?></h3>
                    <div>
                        <div class="btn-group">
                            <button type="button" class="btn btn-light text-secondary fw-bold dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-printer-fill me-1"></i> Imprimir
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="#" onclick="imprimirPrescricao('plano'); return false;">Plano de Medicação</a></li>
                                <li><a class="dropdown-item" href="#" onclick="imprimirPrescricao('receituario'); return false;">Receituário Individual</a></li>
                            </ul>
                        </div>
                        <a href="<?php echo BASE_URL; ?>views/prescricoes_suspensas_acolhido.php?id_acolhido=<?php echo $id_acolhido; ?>" class="btn btn-warning text-dark fw-bold">
                            <i class="bi bi-clock-history me-1"></i> Ver Suspensas
                        </a>
                        <a href="<?php echo BASE_URL; ?>views/gerenciar_horarios.php" class="btn btn-info text-white fw-bold">
                            <i class="bi bi-gear-fill me-1"></i> Gerenciar Horários
                        </a>
                        <button class="btn btn-light text-primary fw-bold" data-bs-toggle="modal" data-bs-target="#prescricaoModal">
                            <i class="bi bi-plus-circle-fill me-1"></i> Nova Prescrição
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (empty($prescricoes)): ?>
                        <p class="text-center text-muted">Nenhuma prescrição cadastrada para este acolhido.</p>
                    <?php else: ?>
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Medicamento</th>
                                    <th>Uso</th>
                                    <th>Posologia</th>
                                    <th>Status</th>
                                    <th class="text-center">Opções</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($prescricoes as $presc): ?>
                                    <?php
                                        $horarios_da_prescricao = $horarios_por_prescricao[$presc['id']] ?? [];
                                    ?>
                                    <tr class="clickable-row" data-bs-toggle="collapse" data-bs-target="#details-<?php echo $presc['id']; ?>" style="cursor: pointer;">
                                        <td>
                                            <strong><?php echo htmlspecialchars($presc['nome_comercial']); ?></strong><br>
                                            <small class="text-muted"><?php echo htmlspecialchars($presc['apresentacao']); ?></small>
                                        </td>
                                        <td><?php echo htmlspecialchars(\Abdm\Model\Prescricao::prescricaoRotuloTipoUso($presc['tipo_uso'] ?? null), ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td>
                                            <?php if ($presc['tipo_posologia'] === 'intervalo'): ?>
                                                <div class="fw-semibold">Dose: <?php echo htmlspecialchars($formatarDose($presc['quantidade_por_dose'] ?? null, $presc['unidade_dose'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></div>
                                                <small class="text-body-secondary">A cada <?php echo htmlspecialchars($presc['intervalo_horas']); ?> horas, a partir das <?php echo !empty($presc['intervalo_inicio']) ? date('H:i', strtotime($presc['intervalo_inicio'])) : '—'; ?></small>
                                            <?php else: ?>
                                                <?php if ($horarios_da_prescricao): ?>
                                                    <div class="vstack gap-1">
                                                        <?php foreach ($horarios_da_prescricao as $horarioDose): ?>
                                                            <?php $doseHorario = $formatarDose($horarioDose['quantidade_por_dose'] ?? $presc['quantidade_por_dose'] ?? null, $horarioDose['unidade_dose'] ?? $presc['unidade_dose'] ?? ''); ?>
                                                            <div><strong><?php echo date('H:i', strtotime($horarioDose['horario'])); ?></strong> — Dose: <strong><?php echo htmlspecialchars($doseHorario, ENT_QUOTES, 'UTF-8'); ?></strong></div>
                                                        <?php endforeach; ?>
                                                    </div>
                                                <?php elseif (!empty($presc['quantidade_por_dose'])): ?>
                                                    Dose: <strong><?php echo htmlspecialchars($formatarDose($presc['quantidade_por_dose'], $presc['unidade_dose'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></strong><br>
                                                    <small class="text-body-secondary">Nenhum horário fixo cadastrado</small>
                                                <?php else: ?>
                                                    <span class="text-body-secondary">Nenhum horário cadastrado</span>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </td>
                                        <td><span class="badge text-bg-<?php echo $presc['status'] == 'ativa' ? 'success' : ($presc['status'] == 'suspensa' ? 'warning' : 'secondary'); ?>"><?php echo ucfirst($presc['status']); ?></span></td>
                                        <td class="text-center">
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                    Opções
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li><a class="dropdown-item" data-bs-toggle="collapse" href="#details-<?php echo $presc['id']; ?>">Ver Detalhes</a></li>
                                                    <li><button class="dropdown-item edit-presc-btn" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#prescricaoModal" 
                                                        data-presc-id="<?php echo $presc['id']; ?>"
                                                        data-prescricao='<?php echo htmlspecialchars(json_encode($presc), ENT_QUOTES, 'UTF-8'); ?>'
                                                        data-horarios='<?php echo htmlspecialchars(json_encode($horarios_da_prescricao), ENT_QUOTES, 'UTF-8'); ?>'
                                                    >Editar</button></li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <?php if ($presc['status'] == 'ativa'): ?>
                                                        <li><button class="dropdown-item text-warning" data-bs-toggle="modal" data-bs-target="#suspenderModal" data-presc-id="<?php echo $presc['id']; ?>" data-presc-nome="<?php echo htmlspecialchars($presc['nome_comercial']); ?>">Suspender</button></li>
                                                    <?php elseif ($presc['status'] == 'suspensa'): ?>
                                                        <li>
                                                            <form action="<?php echo BASE_URL; ?>acoes/controllerMedicamento.php" method="POST" class="d-inline">
                                                                <?php echo \Abdm\Infraestrutura\Sessao::campoTokenCsrf(); ?>
                                                                <input type="hidden" name="acao" value="alterar_status_prescricao">
                                                                <input type="hidden" name="prescricao_id" value="<?php echo $presc['id']; ?>">
                                                                <input type="hidden" name="acolhido_id" value="<?php echo $id_acolhido; ?>">
                                                                <input type="hidden" name="novo_status" value="ativa">
                                                                <button type="submit" class="dropdown-item text-success">Reativar</button>
                                                            </form>
                                                        </li>
                                                    <?php endif; ?>
                                                    <li>
                                                        <form action="<?php echo BASE_URL; ?>acoes/controllerMedicamento.php" method="POST" class="d-inline" onsubmit="return confirm('Tem certeza que deseja excluir esta prescrição permanentemente?');">
                                                            <?php echo \Abdm\Infraestrutura\Sessao::campoTokenCsrf(); ?>
                                                            <input type="hidden" name="acao" value="excluir_prescricao">
                                                            <input type="hidden" name="prescricao_id" value="<?php echo $presc['id']; ?>">
                                                            <input type="hidden" name="acolhido_id" value="<?php echo $id_acolhido; ?>">
                                                            <button type="submit" class="dropdown-item text-danger">Excluir</button>
                                                        </form>
                                                    </li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="5" class="p-0" style="border: none;">
                                            <div class="collapse" id="details-<?php echo $presc['id']; ?>" data-bs-parent="#card-prescricoes">
                                                <div class="p-3 bg-body-tertiary border rounded m-2">
                                                    <h6 class="mb-2 fw-bold">Horários e Doses:</h6>
                                                    <?php if(empty($horarios_da_prescricao)): ?>
                                                        <?php if($presc['tipo_posologia'] === 'intervalo'): ?>
                                                            <p class="mb-2 small">Tomar <strong><?php echo htmlspecialchars($formatarDose($presc['quantidade_por_dose'] ?? null, $presc['unidade_dose'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></strong> a cada <strong><?php echo htmlspecialchars($presc['intervalo_horas']); ?> horas</strong>, iniciando às <strong><?php echo !empty($presc['intervalo_inicio']) ? date('H:i', strtotime($presc['intervalo_inicio'])) : '—'; ?></strong>.</p>
                                                        <?php else: ?>
                                                            <p class="mb-2 text-muted small">Nenhum horário fixo cadastrado.</p>
                                                        <?php endif; ?>
                                                    <?php else: ?>
                                                        <ul class="list-group list-group-flush mb-2">
                                                        <?php foreach($horarios_da_prescricao as $horario): ?>
                                                            <li class="list-group-item bg-transparent d-flex justify-content-between">
                                                                <span><strong><?php echo date('H:i', strtotime($horario['horario'])); ?></strong></span>
                                                                <span class="fw-bold"><?php echo htmlspecialchars($formatarDose($horario['quantidade_por_dose'] ?? $presc['quantidade_por_dose'] ?? null, $horario['unidade_dose'] ?? $presc['unidade_dose'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></span>
                                                            </li>
                                                        <?php endforeach; ?>
                                                        </ul>
                                                    <?php endif; ?>
                                                    <hr class="my-2">
                                                    <p class="mb-1 small"><strong>Período de Uso:</strong> <?php echo date('d/m/Y', strtotime($presc['data_inicio'])); ?> a <?php echo $presc['data_fim'] ? date('d/m/Y', strtotime($presc['data_fim'])) : 'Contínuo'; ?></p>
                                                    <p class="mb-0 small"><strong>Via de Adm.:</strong> <?php echo htmlspecialchars($presc['via_administracao'] ?: 'N/A'); ?> | <strong>Médico:</strong> <?php echo htmlspecialchars($presc['medico_responsavel'] ?: 'N/A'); ?></p>
                                                    <p class="mb-0 small"><strong>Instrução:</strong> <?php echo htmlspecialchars($presc['instrucao_posologia'] ?: 'Nenhuma.'); ?> | <strong>Obs:</strong> <?php echo htmlspecialchars($presc['observacoes'] ?: 'Nenhuma.'); ?></p>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Nova/Editar Prescrição -->
<div class="modal fade" id="prescricaoModal" tabindex="-1" aria-labelledby="prescricaoModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="prescricaoModalLabel">Nova Prescrição</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="<?php echo BASE_URL; ?>acoes/controllerMedicamento.php" method="POST">
            <?php echo \Abdm\Infraestrutura\Sessao::campoTokenCsrf(); ?>
        <div class="modal-body">
            <input type="hidden" name="acao" value="adicionar_prescricao">
            <input type="hidden" name="acolhido_id" value="<?php echo $id_acolhido; ?>">
            
            <div class="mb-3">
                <label for="medicamento_id" class="form-label">Medicamento</label>
                <select name="medicamento_id" id="medicamento_id" class="form-select" required>
                    <option value="">Selecione...</option>
                    <?php
                        foreach ($medicamentos_ativos as $med) {
                            echo "<option value='{$med['id']}' data-unidade-base='{$med['unidade_base']}'>" . htmlspecialchars($med['nome_comercial'] . " - " . $med['principio_ativo'] . " (" . $med['apresentacao'] . ")") . "</option>";
                        }
                    ?>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Tipo de Posologia</label>
                <div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="tipo_posologia" id="posologia_fixo" value="horario_fixo" checked>
                        <label class="form-check-label" for="posologia_fixo">Horários Fixos</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="tipo_posologia" id="posologia_intervalo" value="intervalo">
                        <label class="form-check-label" for="posologia_intervalo">Intervalo de Horas</label>
                    </div>
                </div>
            </div>

            <div class="p-3 border rounded">
                <!-- Container para Horários Fixos -->
                <p class="form-text">Para comprimidos, use quantidades em múltiplos de 0,5, como 0,5 ou 14,5.</p>
                <div id="container-horarios-fixos">
                    <div class="d-flex justify-content-between align-items-center">
                        <label class="form-label fw-bold">Horários</label>
                        <span class="badge bg-primary" id="total-diario">Total Diário: 0</span>
                    </div>
                    <hr>
                    <div class="row gx-2 mb-2 text-muted small">
                        <div class="col-md-4">Horário Nomeado</div>
                        <div class="col-md-2">Hora</div>
                        <div class="col-md-2">Qtd.</div>
                        <div class="col-md-2">Unidade</div>
                        <div class="col-md-2">Período</div>
                    </div>
                    <div id="horarios-container"></div>
                    <button type="button" id="add-horario" class="btn btn-sm btn-outline-success mt-2">Adicionar Horário</button>
                </div>
                <!-- Container para Intervalo -->
                <div id="container-intervalo" style="display: none;">
                    <label class="form-label fw-bold">Configuração do Intervalo</label>
                    <hr>
                    <div class="row mb-3"><div class="col-md-6"><label class="form-label">Quantidade por Dose</label><input type="number" name="quantidade_por_dose" class="form-control" step="0.01"></div><div class="col-md-6"><label class="form-label">Unidade da Dose</label><select name="unidade_dose" class="form-select"><option value="comprimido">comprimido(s)</option><option value="capsula">cápsula(s)</option><option value="ml">ml(s)</option><option value="gota">gota(s)</option><option value="unidade">unidade(s)</option></select></div></div>
                    <div class="row">
                        <div class="col-md-6"><label class="form-label">A cada (horas)</label><input type="number" name="intervalo_horas" class="form-control" placeholder="Ex: 8"></div>
                        <div class="col-md-6"><label class="form-label">Iniciando às</label><input type="time" name="intervalo_inicio" class="form-control"></div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3"><label class="form-label">Tipo de Uso</label><select name="tipo_uso" class="form-select" required><option value="continuo">Contínuo</option><option value="eventual">Eventual (compatibilidade)</option><option value="periodo_definido">Período Definido</option><option value="se_necessario">Se Necessário</option></select></div>
                <div class="col-md-6 mb-3"><label class="form-label">Via de Administração</label><input type="text" name="via_administracao" class="form-control" placeholder="Ex: Oral, Tópico"></div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3"><label class="form-label">Data de Início</label><input type="date" name="data_inicio" class="form-control" value="<?php echo date('Y-m-d'); ?>" required></div>
                <div class="col-md-6 mb-3"><label class="form-label">Data de Fim (se aplicável)</label><input type="date" name="data_fim" class="form-control"></div>
            </div>
            <div class="mb-3"><label class="form-label">Instrução de Posologia</label><input type="text" name="instrucao_posologia" class="form-control" placeholder="Ex: Tomar com água, após refeição"></div>
            <div class="mb-3"><label class="form-label">Médico Responsável</label><input type="text" name="medico_responsavel" class="form-control" placeholder="Nome do médico que prescreveu"></div>
            <div class="mb-3"><label class="form-label">Observações Gerais</label><textarea name="observacoes" class="form-control" rows="2"></textarea></div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-primary">Salvar Prescrição</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal de Suspensão -->
<div class="modal fade" id="suspenderModal" tabindex="-1" aria-labelledby="suspenderModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="suspenderModalLabel">Suspender Prescrição</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="<?php echo BASE_URL; ?>acoes/controllerMedicamento.php" method="POST">
        <?php echo \Abdm\Infraestrutura\Sessao::campoTokenCsrf(); ?>
        <div class="modal-body">
            <input type="hidden" name="acao" value="alterar_status_prescricao">
            <input type="hidden" name="novo_status" value="suspensa">
            <input type="hidden" name="prescricao_id" id="suspender_prescricao_id">
            <input type="hidden" name="acolhido_id" value="<?php echo $id_acolhido; ?>">
            <p>Você está suspendendo o medicamento: <strong id="suspender_prescricao_nome"></strong></p>
            <div class="mb-3">
                <label for="motivo_suspensao" class="form-label">Qual o motivo da suspensão?</label>
                <textarea class="form-control" name="motivo_suspensao" id="motivo_suspensao" rows="3" required></textarea>
            </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-warning">Confirmar Suspensão</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const todasAsPrescricoes = <?php echo json_encode($prescricoes); ?>;
    const prescricaoModal = document.getElementById('prescricaoModal');
    const form = prescricaoModal.querySelector('form');
    const modalTitle = prescricaoModal.querySelector('.modal-title');
    const horariosContainer = document.getElementById('horarios-container');
    const medSelect = document.getElementById('medicamento_id');
    const totalDiarioSpan = document.getElementById('total-diario');
    const suspenderModal = document.getElementById('suspenderModal');

    // --- LÓGICA DE POSOLOGIA (FIXO vs INTERVALO) ---
    const containerFixo = document.getElementById('container-horarios-fixos');
    const containerIntervalo = document.getElementById('container-intervalo');
    document.querySelectorAll('input[name="tipo_posologia"]').forEach(radio => {
        radio.addEventListener('change', function() {
            const isFixo = this.value === 'horario_fixo';
            containerFixo.style.display = isFixo ? 'block' : 'none';
            containerIntervalo.style.display = isFixo ? 'none' : 'block';
            // Habilita/desabilita campos para o submit
            containerFixo.querySelectorAll('input, select, button').forEach(el => el.disabled = !isFixo);
            containerIntervalo.querySelectorAll('input, select').forEach(el => el.disabled = isFixo);
        });
    });

    // Adiciona uma verificação final no momento do envio do formulário
    form.addEventListener('submit', function(e) {
        const acao = form.querySelector('[name="acao"]').value;
        const medId = medSelect.value;

        // A verificação só se aplica ao tentar adicionar uma nova prescrição
        if (acao === 'adicionar_prescricao' && medId) {
            const existingPresc = todasAsPrescricoes.find(p => p.medicamento_id == medId && p.status === 'ativa');
            if (existingPresc) {
                // 1. Impede o envio do formulário para evitar a duplicidade
                e.preventDefault(); 
                
                // 2. Mostra a mensagem e pergunta se o usuário quer editar a prescrição existente
                const querEditar = confirm("Este acolhido já possui uma prescrição ativa para este medicamento.\n\nNão é possível salvar uma nova. Deseja editar a prescrição existente?");
                
                if (querEditar) {
                    // 3. Se sim, preenche o formulário com os dados da prescrição existente
                    const editBtn = document.querySelector(`.edit-presc-btn[data-presc-id="${existingPresc.id}"]`);
                    if (editBtn) {
                        const prescData = JSON.parse(editBtn.dataset.prescricao);
                        const horariosData = JSON.parse(editBtn.dataset.horarios);
                        alternarParaModoEdicao(prescData, horariosData);
                    }
                }
            }
        }
    });

    // Função centralizada para mudar o modal para o modo de edição
    function alternarParaModoEdicao(presc, horarios) {
        form.reset();
        form.querySelector('[name="prescricao_id"]')?.remove(); // Limpa ID antigo

        modalTitle.textContent = 'Editar Prescrição';
        form.querySelector('[name="acao"]').value = 'editar_prescricao';

        const idInput = document.createElement('input');
        idInput.type = 'hidden';
        idInput.name = 'prescricao_id';
        idInput.value = presc.id;
        form.appendChild(idInput);

        // Preenche campos principais
        Object.keys(presc).forEach(key => {
            const field = form.querySelector(`[name="${key}"]`);
            if (field) field.value = presc[key];
        });

        // Preenche tipo de posologia e seus campos
        const tipoPosologia = presc.tipo_posologia || 'horario_fixo';
        const radio = form.querySelector(`input[name="tipo_posologia"][value="${tipoPosologia}"]`);
        
        if (radio) {
            radio.checked = true;
            radio.dispatchEvent(new Event('change')); // Dispara o evento para mostrar/esconder os containers
        }
        form.querySelector('[name="quantidade_por_dose"]').value = presc.quantidade_por_dose;
        form.querySelector('[name="unidade_dose"]').value = presc.unidade_dose;

        // Preenche os horários fixos, se houver
        horariosContainer.innerHTML = (tipoPosologia === 'horario_fixo' && horarios.length > 0) ? horarios.map((h, i) => criarHtmlLinhaHorario(i, h)).join('') : criarHtmlLinhaHorario(0);
        calcularTotalDiario();
    }

    // --- EVENT LISTENERS PARA HORÁRIOS ---
    horariosContainer.addEventListener('change', function(e) {
        if (e.target.classList.contains('horario-nomeado-select')) {
            const selectedOption = e.target.options[e.target.selectedIndex];
            const row = e.target.closest('.horario-row');
            const timeInput = row.querySelector('.horario-time-input');
            const periodoSelect = row.querySelector('.horario-periodo-select');
            const unidadeSelect = row.querySelector('.horario-unidade-select');

            if (selectedOption.value) { // Se um horário padrão foi selecionado
                timeInput.value = selectedOption.dataset.horario;
                periodoSelect.value = selectedOption.dataset.periodoId;
                calcularTotalDiario();
                if (medSelect.value) unidadeSelect.value = medSelect.options[medSelect.selectedIndex].dataset.unidadeBase;
            }
        }
    });

    horariosContainer.addEventListener('input', function(e) {
        if (e.target.matches('[name*="[hora]"]')) {
            const timeInput = e.target;
            const select = timeInput.closest('.horario-row').querySelector('.horario-label-select');
            const matchingOption = Array.from(select.options).find(opt => opt.value === timeInput.value);
            select.value = matchingOption ? timeInput.value : "";
            calcularTotalDiario();
        }
    });

    horariosContainer.addEventListener('input', function(e) {
        if (e.target.classList.contains('horario-qtd-input') || e.target.classList.contains('horario-unidade-select')) {
            calcularTotalDiario();
        }
    });


    // Função para verificar se já existe uma prescrição ativa e perguntar se o usuário quer editá-la
    function verificarPrescricaoExistente(medicamentoId) {
        // A verificação só deve ocorrer ao tentar adicionar uma nova prescrição
        if (!medicamentoId || form.querySelector('[name="acao"]').value !== 'adicionar_prescricao') {
            return;
        }

        const existingPresc = todasAsPrescricoes.find(p => p.medicamento_id == medicamentoId && p.status === 'ativa');
        
        if (existingPresc) {
            // Usar setTimeout para garantir que a UI do select atualize antes do confirm
            setTimeout(() => {
                const querEditar = confirm("Este acolhido já possui uma prescrição ativa para este medicamento.\n\nDeseja editar a prescrição existente em vez de criar uma nova?");
                
                if (querEditar) {
                    // Encontra o botão de editar para obter os dados
                    const editBtn = document.querySelector(`.edit-presc-btn[data-presc-id="${existingPresc.id}"]`);
                    if (editBtn) {
                        const prescData = JSON.parse(editBtn.dataset.prescricao);
                        const horariosData = JSON.parse(editBtn.dataset.horarios);
                        alternarParaModoEdicao(prescData, horariosData);
                    }
                }
            }, 100);
        }
    }

    // Função global para ser acessível pelo onclick
    window.imprimirPrescricao = function(tipo) {
        const acolhidoId = <?php echo $id_acolhido; ?>;
        // A nova URL aponta para o gerador de PDF
        const pdfUrl = `<?php echo BASE_URL; ?>views/gerar_prescricao_pdf.php?id_acolhido=${acolhidoId}&tipo=${tipo}`;
        // Abre o PDF em uma nova aba. O visualizador de PDF do navegador cuidará da impressão.
        window.abrirPreviaPdf(pdfUrl);
    }

    function criarHtmlLinhaHorario(index, data = {}) {
        const horaBanco = typeof data.horario === 'string' ? data.horario : '';
        const hora = horaBanco.replace(/^(\d{2}:\d{2}):00$/, '$1');
        const passo = hora.length > 5 ? 1 : 60;
        const horarioNomeadoOptions = `
            <option value="">-- Selecione um Horário Padrão --</option>
            <?php foreach($horarios_nomeados as $hn): ?>
                <option value="<?php echo $hn['id']; ?>" data-horario="<?php echo date('H:i', strtotime($hn['horario'])); ?>" data-periodo-id="<?php echo $hn['periodo_id']; ?>">
                    ${<?= json_encode(htmlspecialchars($hn['nome'] . ' (' . date('H:i', strtotime($hn['horario'])) . ')'), JSON_HEX_TAG | JSON_INVALID_UTF8_SUBSTITUTE) ?>}
                </option>
            <?php endforeach; ?>
        `;
        const periodoOptions = `
            <option value="" disabled selected>Selecione...</option>
            <?php foreach($periodos_disponiveis as $p): ?>
                <option value="<?php echo $p['id']; ?>">
                    ${<?= json_encode(htmlspecialchars($p['nome']), JSON_HEX_TAG | JSON_INVALID_UTF8_SUBSTITUTE) ?>}
                </option>
            <?php endforeach; ?>
        `;
        const unidadeOptions = `
            <option value="comprimido">comprimido(s)</option>
            <option value="capsula">cápsula(s)</option>
            <option value="ml">ml(s)</option>
            <option value="gota">gota(s)</option>
            <option value="unidade">unidade(s)</option>
        `;

        const removeButton = index > 0 
            ? '<button type="button" class="btn btn-outline-danger remove-horario w-100">X</button>'
            : '<button type="button" class="btn btn-outline-secondary w-100" disabled>X</button>';

        return `
            <div class="row gx-2 mb-2 horario-row align-items-center">
                <div class="col-md-5"><select class="form-select horario-nomeado-select">${horarioNomeadoOptions}</select></div>
                <div class="col-md-3"><input type="time" name="horarios[${index}][hora]" class="form-control horario-time-input" value="${hora}" step="${passo}" required></div>
                <div class="col-md-2"><input type="number" name="horarios[${index}][quantidade_por_dose]" class="form-control horario-qtd-input" step="0.01" placeholder="Qtd" value="${data.quantidade_por_dose || '1'}" required></div>
                <div class="col-md-2"><select name="horarios[${index}][unidade_dose]" class="form-select horario-unidade-select" required>${unidadeOptions}</select></div>
                <div class="col-md-2"><select name="horarios[${index}][periodo_id]" class="form-select horario-periodo-select" required>${periodoOptions}</select></div>
                <div class="col-md-1">${removeButton}</div>
            </div>
        `;
    }

    function calcularTotalDiario() {
        const totais = {};
        horariosContainer.querySelectorAll('.horario-row').forEach(row => {
            const qtd = parseFloat(row.querySelector('.horario-qtd-input').value) || 0;
            const unidade = row.querySelector('.horario-unidade-select').value;
            if (qtd > 0 && unidade) {
                totais[unidade] = (totais[unidade] || 0) + qtd;
            }
        });
        const textoTotal = Object.entries(totais).map(([unidade, total]) => `${parseFloat(total.toFixed(2))} ${unidade}(s)`).join(' + ');
        totalDiarioSpan.textContent = 'Total Diário: ' + (textoTotal || '0');
    }

    // --- EVENT LISTENERS ---
    document.getElementById('add-horario').addEventListener('click', function() {
        const index = horariosContainer.children.length;
        horariosContainer.insertAdjacentHTML('beforeend', criarHtmlLinhaHorario(index));
    });

    horariosContainer.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-horario')) {
            e.target.closest('.horario-row').remove();
            calcularTotalDiario();
        }
    });

    medSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const unidadeBase = selectedOption.getAttribute('data-unidade-base');
        const selectedMedId = this.value;
        
        verificarPrescricaoExistente(selectedMedId);

        if (unidadeBase) {
            // Atualiza a unidade em todas as linhas de horário existentes
            horariosContainer.querySelectorAll('.horario-unidade-select').forEach(select => {
                select.value = unidadeBase;
            });
        }
    });

    // --- LÓGICA DO MODAL (NOVO vs EDITAR) ---
    prescricaoModal.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;

        if (button && button.classList.contains('edit-presc-btn')) {
            // Se o modal foi aberto por um botão de editar, já entra no modo de edição
            const presc = JSON.parse(button.dataset.prescricao);
            const horarios = JSON.parse(button.dataset.horarios);
            alternarParaModoEdicao(presc, horarios);
        } else {
            // Caso contrário (botão "Nova Prescrição" ou aberto via URL), reseta para o modo "Nova"
            form.reset();
            form.querySelector('[name="prescricao_id"]')?.remove();
            modalTitle.textContent = 'Nova Prescrição';
            form.querySelector('[name="acao"]').value = 'adicionar_prescricao';
            // Garante que o modo de horário fixo seja o padrão
            const radioFixo = form.querySelector('#posologia_fixo');
            if(radioFixo) {
                radioFixo.checked = true;
                radioFixo.dispatchEvent(new Event('change'));
            }
            horariosContainer.innerHTML = criarHtmlLinhaHorario(0);
            calcularTotalDiario();
        }
    });

    // Lógica do Modal de Suspensão
    suspenderModal.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;
        const prescId = button.getAttribute('data-presc-id');
        const prescNome = button.getAttribute('data-presc-nome');
        suspenderModal.querySelector('#suspender_prescricao_id').value = prescId;
        suspenderModal.querySelector('#suspender_prescricao_nome').textContent = prescNome;
    });

    // Auto-open modal se solicitado pela URL (vindo da página de estoque)
    const urlParams = new URLSearchParams(window.location.search);
    const medIdToOpen = urlParams.get('med_id');
    const shouldOpenModal = urlParams.get('open_modal');

    if (shouldOpenModal && medIdToOpen) {
        const prescModal = new bootstrap.Modal(document.getElementById('prescricaoModal'));
        
        // Garante que o modal está em modo "Nova Prescrição"
        form.reset();
        form.querySelector('[name="prescricao_id"]')?.remove();
        modalTitle.textContent = 'Nova Prescrição';
        form.querySelector('[name="acao"]').value = 'adicionar_prescricao';
        const radioFixo = form.querySelector('#posologia_fixo');
        if(radioFixo) {
            radioFixo.checked = true;
            radioFixo.dispatchEvent(new Event('change'));
        }
        horariosContainer.innerHTML = criarHtmlLinhaHorario(0);
        
        // Pré-seleciona o medicamento
        medSelect.value = medIdToOpen;
        
        // Dispara a verificação de prescrição existente
        verificarPrescricaoExistente(medIdToOpen);

        prescModal.show();
        
        // Limpa os parâmetros da URL para evitar reabrir o modal ao recarregar
        history.replaceState(null, '', window.location.pathname + '?id_acolhido=<?php echo $id_acolhido; ?>');
    }
});
</script>

<?php
require_once ROOT_DIR . '/src/View/templates/includes/rodapeInterno.inc.php';
?>

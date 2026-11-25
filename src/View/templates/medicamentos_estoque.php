<?php
['acolhidosAtivos' => $acolhidosAtivos, 'itensEstoque' => $itensEstoque] = $dadosView;
require_once ROOT_DIR . '/src/View/templates/includes/cabecalhoInterno.inc.php';

?>

<style>
    /* Corrige a legibilidade do texto em tabelas de alerta no tema escuro */
    [data-bs-theme="dark"] .table-warning,
    [data-bs-theme="dark"] .table-danger {
        --bs-table-color: var(--bs-black);
        --bs-table-striped-color: var(--bs-black);
    }

    /* Estilos para o submenu no dropdown de ações */
    .dropdown-menu .dropdown-submenu {
        position: relative;
    }
    .dropdown-menu .dropdown-submenu .dropdown-menu {
        top: -5px; /* Alinha com o item pai */
        left: auto;
        right: 100%;
    }
    .dropdown-menu .dropdown-submenu:hover > .dropdown-menu {
        display: block;
    }
</style>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-12">
            <div class="card shadow-lg border-0 rounded-lg">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h3 class="font-weight-light my-3 mb-0 d-flex align-items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="currentColor" class="bi bi-box2-heart-fill me-2" viewBox="0 0 16 16">
                            <path d="M3.75 0a1 1 0 0 0-1 1v.5a.5.5 0 0 0 .5.5h1.5a.5.5 0 0 0 .5-.5V1a1 1 0 0 0-1-1h-1zM11.25 0a1 1 0 0 0-1 1v.5a.5.5 0 0 0 .5.5h1.5a.5.5 0 0 0 .5-.5V1a1 1 0 0 0-1-1h-1zM.082 3.565A1.5 1.5 0 0 1 1.5 3h13a1.5 1.5 0 0 1 1.418.565l-1.532 2.451a.5.5 0 0 1-.418.234H1.532a.5.5 0 0 1-.418-.234L.082 3.565zM15.5 7h-15a.5.5 0 0 0-.5.5v6a.5.5 0 0 0 .5.5h15a.5.5 0 0 0 .5-.5v-6a.5.5 0 0 0-.5-.5zM8 12.993c.002-.002.005-.005.007-.007l.002-.002.002-.002A3.495 3.495 0 0 0 11.5 9.5a3.495 3.495 0 0 0-6.993 0c0 .867.312 1.668.83 2.292l.002.002.002.002.007.007c.002.002.005.005.007.007a.498.498 0 0 0 .706 0z"/>
                        </svg>
                        Estoque de Medicamentos
                    </h3>
                    <div>
                        <a href="<?php echo BASE_URL; ?>views/cadastrar_medicamento.php" class="btn btn-light text-primary fw-bold">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-plus-circle-fill me-1" viewBox="0 0 16 16">
                                <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM8.5 4.5a.5.5 0 0 0-1 0v3h-3a.5.5 0 0 0 0 1h3v3a.5.5 0 0 0 1 0v-3h3a.5.5 0 0 0 0-1h-3v-3z"/>
                            </svg>
                            Novo Medicamento
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-3 px-3">
                        <div class="col-md-8">
                            <input type="search" id="busca-medicamento" class="form-control" placeholder="Buscar por nome ou princípio ativo...">
                        </div>
                        <div class="col-md-4">
                            <select id="filtro-estoque" class="form-select">
                                <option value="todos">Mostrar Todos</option>
                                <option value="alerta">Mostrar Apenas em Alerta</option>
                                <option value="critico">Mostrar Críticos/Zerados</option>
                            </select>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover align-middle">
                            <thead class="table-dark">
                                <tr>
                                    <th style="cursor: pointer;" data-sort="nome">Medicamento &#x2195;</th>
                                    <th style="cursor: pointer;" data-sort="principio">Princípio Ativo / Apresentação &#x2195;</th>
                                    <th class="text-center" style="cursor: pointer;" data-sort="estoque">Estoque Atual &#x2195;</th>
                                    <th class="text-center" style="cursor: pointer;" data-sort="consumo">Consumo Diário &#x2195;</th>
                                    <th class="text-center" style="cursor: pointer;" data-sort="previsao">Previsão &#x2195;</th>
                                    <th class="text-center">Ações</th>
                                </tr>
                            </thead>
                            <tbody id="tabela-medicamentos">
                                <?php if(count($itensEstoque) > 0): ?>
                                    <?php foreach($itensEstoque as $info): ?>
                                        <tr class="<?php echo $info['alerta']; ?>" 
                                            data-nome="<?php echo htmlspecialchars(strtolower($info['dados']['nome_comercial'])); ?>"
                                            data-principio="<?php echo htmlspecialchars(strtolower($info['dados']['principio_ativo'])); ?>"
                                            data-estoque-status="<?php echo $info['alerta']; ?>">
                                            <td class="fw-bold"><?php echo htmlspecialchars($info['dados']['nome_comercial']); ?></td>
                                        <td><?php echo htmlspecialchars($info['dados']['principio_ativo']); ?><br><small><?php echo htmlspecialchars($info['dados']['apresentacao']); ?></small></td>
                                            <td class="text-center"><?= htmlspecialchars((string)$info['total']) ?> <small><?= $info['dados']['unidade_base'] ?>(s)</small></td>
                                            <td class="text-center"><?= number_format((float)$info['consumo_diario'], 2, '.', '') ?> <small><?= $info['dados']['unidade_base'] ?>/dia</small></td>
                                            <td class="text-center fw-bold">
                                                <?php 
                                                    if ($info['previsao_dias'] > 0) {
                                                        echo $info['previsao_dias'] . ' dias';
                                                    } elseif ($info['total'] > 0) {
                                                        echo 'Uso eventual';
                                                    } else {
                                                        echo 'Zerado';
                                                    }
                                                ?>
                                            </td>
                                            <td class="text-center">
                                                <div class="btn-group">
                                                    <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#entradaModal" data-unidade-base="<?= htmlspecialchars($info['dados']['unidade_base'], ENT_QUOTES, 'UTF-8') ?>" data-id="<?php echo $info['dados']['id']; ?>" data-nome="<?php echo htmlspecialchars($info['dados']['nome_comercial']); ?>" title="Registrar Entrada de Estoque"><i class="bi bi-plus-lg fw-bold"></i></button>
                                                    <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#saidaModal" data-unidade-base="<?= $info['dados']['unidade_base'] ?>" data-id="<?php echo $info['dados']['id']; ?>" data-nome="<?php echo htmlspecialchars($info['dados']['nome_comercial']); ?>" title="Registrar Saída Avulsa de Estoque"><i class="bi bi-dash-lg fw-bold"></i></button>
                                                    <div class="btn-group" role="group">
                                                        <button type="button" class="btn btn-sm btn-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false" title="Mais Opções">
                                                            <i class="bi bi-three-dots-vertical"></i>
                                                        </button>
                                                        <ul class="dropdown-menu dropdown-menu-end">
                                                            <li>
                                                                <a class="dropdown-item" href="<?php echo BASE_URL; ?>views/cadastrar_medicamento.php?editar=<?php echo (int)$info['dados']['id']; ?>">
                                                                    <i class="bi bi-pencil-square me-2" aria-hidden="true"></i>Editar cadastro
                                                                </a>
                                                            </li>
                                                            <li><hr class="dropdown-divider"></li>
                                                            <?php if ($controleAcesso->usuarioPode('prescricoes')): ?>
                                                            <li class="dropdown-submenu">
                                                                <a class="dropdown-item dropdown-toggle" href="#"><i class="bi bi-prescription2 me-2"></i>Prescrever para</a>
                                                                <ul class="dropdown-menu">
                                                                    <?php if(empty($acolhidosAtivos)): ?>
                                                                        <li><span class="dropdown-item disabled fst-italic">Nenhum acolhido ativo</span></li>
                                                                    <?php else: ?>
                                                                        <?php foreach($acolhidosAtivos as $acolhido_item): ?>
                                                                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>views/prescricoes.php?id_acolhido=<?php echo $acolhido_item['id']; ?>&med_id=<?php echo $info['dados']['id']; ?>&open_modal=1"><?php echo htmlspecialchars($acolhido_item['nome']); ?></a></li>
                                                                        <?php endforeach; ?>
                                                                    <?php endif; ?>
                                                                </ul>
                                                            </li>
                                                            <li><hr class="dropdown-divider"></li>
                                                            <?php endif; ?>
                                                            <?php if ($controleAcesso->usuarioPode('prescricoes')): ?>
                                                            <li>
                                                                <button type="button" class="dropdown-item" 
                                                                    data-bs-toggle="modal" data-bs-target="#acolhidosModal" 
                                                                    data-medicamento-nome="<?php echo htmlspecialchars($info['dados']['nome_comercial']); ?>"
                                                                    data-acolhidos="<?php echo htmlspecialchars($info['nomes_acolhidos']); ?>"
                                                                    <?php echo empty($info['nomes_acolhidos']) ? 'disabled' : ''; ?>>
                                                                    <i class="bi bi-people me-2"></i>Ver quem faz uso
                                                                </button>
                                                            </li>
                                                            <li><hr class="dropdown-divider"></li>
                                                            <?php endif; ?>
                                                            <li>
                                                                <form action="<?php echo BASE_URL; ?>acoes/controllerMedicamento.php" method="POST" class="d-inline" onsubmit="return confirm('Atenção! Esta ação é irreversível. Deseja realmente tentar excluir este medicamento?');">
                                                                    <?php echo \Abdm\Infraestrutura\Sessao::campoTokenCsrf(); ?>
                                                                    <input type="hidden" name="acao" value="excluir_medicamento">
                                                                    <input type="hidden" name="medicamento_id" value="<?php echo $info['dados']['id']; ?>">
                                                                    <button type="submit" class="dropdown-item text-danger" <?php echo ($info['total'] > 0) ? 'disabled' : ''; ?>>
                                                                        <i class="bi bi-trash me-2"></i>Excluir
                                                                    </button>
                                                                </form>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted">Nenhum medicamento cadastrado. <a href="<?php echo BASE_URL; ?>views/cadastrar_medicamento.php">Cadastre o primeiro</a>.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer text-center py-3">
                    <div class="text-muted small">Total de medicamentos cadastrados: <?= count($itensEstoque) ?></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Entrada -->
<div class="modal fade" id="entradaModal" tabindex="-1" aria-labelledby="entradaModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="entradaModalLabel">Registrar Entrada de Medicamento</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="<?php echo BASE_URL; ?>acoes/controllerMedicamento.php" method="POST">
            <?php echo \Abdm\Infraestrutura\Sessao::campoTokenCsrf(); ?>
        <div class="modal-body">
            <input type="hidden" name="acao" value="registrar_entrada">
            <input type="hidden" name="medicamento_id" id="entrada_medicamento_id">
            <h6 class="mb-3">Medicamento: <strong id="entrada_medicamento_nome"></strong></h6>
            <div class="mb-3">
                <label for="modo_entrada" class="form-label">Como deseja registrar?</label>
                <select id="modo_entrada" name="modo_entrada" class="form-select">
                    <option value="direta">Total em unidade cadastrada</option>
                    <option value="embalagem">Por embalagem</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="quantidade_entrada" id="entrada_quantidade_label" class="form-label">Quantidade recebida</label>
                <input type="number" min="1" step="1" class="form-control" id="quantidade_entrada" name="quantidade_entrada" required>
            </div>
            <div id="entrada_dados_embalagem" hidden>
                <div class="mb-3">
                    <label for="fator_conversao" class="form-label"><span id="entrada_unidade_base"></span> por embalagem</label>
                    <input type="number" min="1" step="1" class="form-control" id="fator_conversao"
                           name="fator_conversao" required disabled>
                </div>
            </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-success">Registrar Entrada</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal de Saída -->
<div class="modal fade" id="saidaModal" tabindex="-1" aria-labelledby="saidaModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="saidaModalLabel">Registrar Saída Avulsa de Medicamento</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="<?php echo BASE_URL; ?>acoes/controllerMedicamento.php" method="POST">
            <?php echo \Abdm\Infraestrutura\Sessao::campoTokenCsrf(); ?>
        <div class="modal-body">
            <input type="hidden" name="acao" value="registrar_saida">
            <input type="hidden" name="medicamento_id" id="saida_medicamento_id">
            <h6 class="mb-3">Medicamento: <strong id="saida_medicamento_nome"></strong></h6>
            <div class="mb-3">
                <label for="saida_quantidade" class="form-label">Quantidade em <span id="saida_unidade_base">unidade base</span></label>
                <input type="number" class="form-control" id="saida_quantidade" name="quantidade"
                       min="1" max="999999999999.99" step="1" required>
            </div>
            <div class="mb-3">
                <label for="motivo" class="form-label">Motivo da Saída</label>
                <select name="motivo" class="form-select" required>
                    <option value="doacao">Doação</option>
                    <option value="ajuste">Ajuste de Estoque (Perda)</option>
                </select>
            </div>
            <div class="mb-3"><label for="observacao" class="form-label">Observação</label><textarea class="form-control" name="observacao" rows="3"></textarea></div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-danger">Registrar Saída</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal Acolhidos -->
<div class="modal fade" id="acolhidosModal" tabindex="-1" aria-labelledby="acolhidosModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="acolhidosModalLabel">Acolhidos Utilizando Medicamento</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <h6 id="modal-medicamento-nome" class="fw-bold"></h6>
        <ul id="modal-lista-acolhidos" class="list-group"></ul>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const entradaModal = document.getElementById('entradaModal');
    const modoEntrada = entradaModal.querySelector('#modo_entrada');
    const dadosEmbalagem = entradaModal.querySelector('#entrada_dados_embalagem');
    const quantidadeLabel = entradaModal.querySelector('#entrada_quantidade_label');
    const unidadeBaseLabel = entradaModal.querySelector('#entrada_unidade_base');

    const atualizarModoEntrada = () => {
        const porEmbalagem = modoEntrada.value === 'embalagem';
        dadosEmbalagem.hidden = !porEmbalagem;
        dadosEmbalagem.querySelectorAll('input').forEach(campo => {
            campo.disabled = !porEmbalagem;
        });
        quantidadeLabel.textContent = porEmbalagem
            ? 'Quantidade de embalagens'
            : `Quantidade recebida em ${unidadeBaseLabel.textContent}`;
    };

    modoEntrada.addEventListener('change', atualizarModoEntrada);
    entradaModal.addEventListener('show.bs.modal', event => {
        const botao = event.relatedTarget;
        entradaModal.querySelector('form').reset();
        entradaModal.querySelector('#entrada_medicamento_id').value = botao.dataset.id;
        entradaModal.querySelector('#entrada_medicamento_nome').textContent = botao.dataset.nome;
        entradaModal.querySelector('#entrada_unidade_base').textContent =
            botao.dataset.unidadeBase === 'ml' ? 'mL' : botao.dataset.unidadeBase;
        atualizarModoEntrada();
    });

    const saidaModal = document.getElementById('saidaModal');
    const quantidadeSaida = saidaModal.querySelector('#saida_quantidade');
    quantidadeSaida.addEventListener('input', () => {
        const valor = quantidadeSaida.value;
        quantidadeSaida.setCustomValidity(valor && !/^[0-9]{1,12}(?:\.[0-9]{1,2})?$/.test(valor)
            ? 'Informe uma quantidade com até duas casas decimais.'
            : '');
    });
    saidaModal.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        const medId = button.getAttribute('data-id');
        const medNome = button.getAttribute('data-nome');
        
        const modalIdInput = saidaModal.querySelector('#saida_medicamento_id');
        const modalNome = saidaModal.querySelector('#saida_medicamento_nome');
        
        modalIdInput.value = medId;
        modalNome.textContent = medNome;
        const unidade = button.dataset.unidadeBase;
        quantidadeSaida.value = '';
        quantidadeSaida.setCustomValidity('');
        quantidadeSaida.min = quantidadeSaida.step = unidade === 'ml' ? '0.01' : '1';
        saidaModal.querySelector('#saida_unidade_base').textContent = unidade;
    });

    const buscaInput = document.getElementById('busca-medicamento');
    const filtroSelect = document.getElementById('filtro-estoque');
    const tabelaBody = document.getElementById('tabela-medicamentos');
    const linhas = tabelaBody.getElementsByTagName('tr');

    // 1. Lógica de Busca e Filtro
    function filtrarTabela() {
        const termoBusca = buscaInput.value.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "");
        const filtroStatus = filtroSelect.value;

        for (let i = 0; i < linhas.length; i++) {
            const linha = linhas[i];
            const nome = linha.dataset.nome || '';
            const principio = linha.dataset.principio || '';
            const statusEstoque = linha.dataset.estoqueStatus || '';

            const matchBusca = nome.includes(termoBusca) || principio.includes(termoBusca);
            
            let matchFiltro = false;
            if (filtroStatus === 'todos') {
                matchFiltro = true;
            } else if (filtroStatus === 'alerta') {
                matchFiltro = statusEstoque === 'table-warning' || statusEstoque === 'table-danger';
            } else if (filtroStatus === 'critico') {
                matchFiltro = statusEstoque === 'table-danger';
            }

            linha.style.display = (matchBusca && matchFiltro) ? '' : 'none';
        }
    }

    buscaInput.addEventListener('keyup', filtrarTabela);
    buscaInput.addEventListener('search', filtrarTabela); // Para o botão 'x' do input de busca
    filtroSelect.addEventListener('change', filtrarTabela);

    // 2. Lógica de Ordenação
    document.querySelectorAll('th[data-sort]').forEach(headerCell => {
        headerCell.addEventListener('click', () => {
            const table = headerCell.closest('table');
            const tbody = table.querySelector('tbody');
            const column = headerCell.dataset.sort;
            const isAsc = headerCell.classList.contains('sort-asc');
            const direction = isAsc ? 'desc' : 'asc';

            table.querySelectorAll('th[data-sort]').forEach(th => th.classList.remove('sort-asc', 'sort-desc'));
            headerCell.classList.toggle('sort-asc', direction === 'asc');
            headerCell.classList.toggle('sort-desc', direction === 'desc');

            const rows = Array.from(tbody.querySelectorAll('tr'));

            rows.sort((a, b) => {
                const cellA = a.querySelector(`td:nth-child(${headerCell.cellIndex + 1})`).textContent.trim();
                const cellB = b.querySelector(`td:nth-child(${headerCell.cellIndex + 1})`).textContent.trim();

                const isNumeric = ['estoque', 'previsao', 'consumo'].includes(column);
                const valA = isNumeric ? (parseFloat(cellA) || 0) : cellA.toLowerCase();
                const valB = isNumeric ? (parseFloat(cellB) || 0) : cellB.toLowerCase();
                
                if (valA < valB) return direction === 'asc' ? -1 : 1;
                if (valA > valB) return direction === 'asc' ? 1 : -1;
                return 0;
            });

            rows.forEach(row => tbody.appendChild(row));
        });
    });

    // 3. Lógica do Modal "Quem Utiliza"
    const acolhidosModal = document.getElementById('acolhidosModal');
    acolhidosModal.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        const medNome = button.getAttribute('data-medicamento-nome');
        const acolhidosStr = button.getAttribute('data-acolhidos');
        
        acolhidosModal.querySelector('#modal-medicamento-nome').textContent = medNome;
        const modalListEl = acolhidosModal.querySelector('#modal-lista-acolhidos');
        modalListEl.innerHTML = '';

        if (acolhidosStr) {
            acolhidosStr.split(', ').forEach(nome => {
                const item = document.createElement('li');
                item.className = 'list-group-item';
                item.textContent = nome;
                modalListEl.appendChild(item);
            });
        } else {
            modalListEl.innerHTML = '<li class="list-group-item text-muted">Nenhum acolhido utilizando este medicamento no momento.</li>';
        }
    });
});
</script>

<?php
require_once ROOT_DIR . '/src/View/templates/includes/rodapeInterno.inc.php';
?>

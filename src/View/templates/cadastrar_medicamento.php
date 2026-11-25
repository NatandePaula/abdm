<?php

[
    'medicamentoEditar' => $medicamentoEditar,
    'aceitaEquivalencia' => $aceitaEquivalencia,
    'modoEdicao' => $modoEdicao,
    'valorFormulario' => $valorFormulario,
] = $dadosView;

require_once ROOT_DIR . '/src/View/templates/includes/cabecalhoInterno.inc.php';
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-lg border-0 rounded-lg">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h3 class="font-weight-light my-3 mb-0 d-flex align-items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-capsule-pill me-2" viewBox="0 0 16 16"><path d="M11.02 5.213a2.5 2.5 0 0 0-3.536-3.535L2.27 6.893a2.5 2.5 0 1 0 3.536 3.535l5.213-5.215ZM10.317 4.5a1.5 1.5 0 1 1-2.121-2.121l2.121 2.121Z"/><path d="M2.393 13.312a2.5 2.5 0 0 0 3.535 0l5.213-5.213a2.5 2.5 0 0 0-3.535-3.536L2.393 9.777a2.5 2.5 0 0 0 0 3.535Z"/></svg>
                        <?php echo $modoEdicao ? 'Editar Medicamento' : 'Cadastrar Novo Medicamento'; ?>
                    </h3>
                    <a href="<?php echo BASE_URL; ?>views/medicamentos_estoque.php" class="btn btn-light text-primary fw-bold">Voltar</a>
                </div>
                <div class="card-body">
                    <form action="<?php echo BASE_URL; ?>acoes/controllerMedicamento.php" method="POST">
                        <?php echo \Abdm\Infraestrutura\Sessao::campoTokenCsrf(); ?>
                        <input type="hidden" name="acao" value="<?php echo $modoEdicao ? 'atualizar_medicamento' : 'cadastrar_medicamento'; ?>">
                        <?php if ($modoEdicao): ?>
                            <input type="hidden" name="medicamento_id" value="<?= (int)$medicamentoEditar['id'] ?>">
                        <?php endif; ?>

                        <?php if (!$modoEdicao): ?>
                        <div class="form-floating mb-3">
                            <input type="text" class="form-control" id="busca_medicamento" placeholder="Digite para buscar...">
                            <label for="busca_medicamento">Buscar Medicamento (Nome ou Princípio Ativo)</label>
                            <div id="csv_results" class="list-group mt-1 position-absolute w-100" style="z-index: 1000; max-height: 400px; overflow-y: auto;"></div>
                        </div>
                        <?php endif; ?>

                        <div class="form-floating mb-3">
                            <input type="text" class="form-control" id="nome_comercial" name="nome_comercial" maxlength="255" value="<?php echo $valorFormulario('nome_comercial'); ?>" required>
                            <label for="nome_comercial">Nome Comercial</label>
                        </div>

                        <div class="form-floating mb-3">
                            <input type="text" class="form-control" id="principio_ativo" name="principio_ativo" maxlength="255" value="<?php echo $valorFormulario('principio_ativo'); ?>" required>
                            <label for="principio_ativo">Princípio Ativo</label>
                        </div>

                        <div class="form-floating mb-3">
                            <input type="text" class="form-control" id="apresentacao" name="apresentacao" maxlength="255" value="<?php echo $valorFormulario('apresentacao'); ?>">
                            <label for="apresentacao">Apresentação (Ex: 500mg, 10mg/ml)</label>
                        </div>

                        <div class="form-floating mb-3">
                            <select class="form-select" id="unidade_base" name="unidade_base" required>
                                <option value="comprimido" <?php echo ($medicamentoEditar['unidade_base'] ?? '') === 'comprimido' ? 'selected' : ''; ?>>Comprimido</option>
                                <option value="capsula" <?php echo ($medicamentoEditar['unidade_base'] ?? '') === 'capsula' ? 'selected' : ''; ?>>Cápsula</option>
                                <option value="ml" <?php echo ($medicamentoEditar['unidade_base'] ?? '') === 'ml' ? 'selected' : ''; ?>>ml (Mililitro)</option>
                                <option value="gota" <?php echo ($medicamentoEditar['unidade_base'] ?? '') === 'gota' ? 'selected' : ''; ?>>Gota</option>
                                <option value="unidade" <?php echo ($medicamentoEditar['unidade_base'] ?? '') === 'unidade' ? 'selected' : ''; ?>>Unidade (Ex: Adesivo)</option>
                            </select>
                            <label for="unidade_base">Unidade Base Contável</label>
                        </div>
                        <div class="mb-3" id="equivalencia_gotas_cadastro" <?= ($medicamentoEditar['unidade_base'] ?? '') === 'ml' ? '' : 'hidden' ?>>
                            <?php if ($aceitaEquivalencia): ?>
                                <label for="gotas_por_ml" class="form-label">Gotas por mL (opcional)</label>
                                <input type="number" class="form-control" id="gotas_por_ml" name="gotas_por_ml"
                                       min="1" max="10000" step="1" value="<?= $valorFormulario('gotas_por_ml') ?>">
                                <div class="form-text">Use a equivalência indicada na bula ou no rótulo desta apresentação.</div>
                            <?php else: ?>
                                <p class="form-text">A atualização do banco habilita o cadastro opcional de gotas por mL.</p>
                            <?php endif; ?>
                        </div>

                        <div class="mt-5 mb-0">
                            <button type="submit" class="btn btn-primary btn-block w-100 py-3 fs-5">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-floppy-fill me-2" viewBox="0 0 16 16"><path d="M0 1.5A1.5 1.5 0 0 1 1.5 0H3v5.5A1.5 1.5 0 0 0 4.5 7h7A1.5 1.5 0 0 0 13 5.5V0h.086a1.5 1.5 0 0 1 1.06.44l1.415 1.414A1.5 1.5 0 0 1 16 2.914V14.5a1.5 1.5 0 0 1-1.5 1.5H14v-5.5A1.5 1.5 0 0 0 12.5 9h-9A1.5 1.5 0 0 0 2 10.5V16h-.5A1.5 1.5 0 0 1 0 14.5z"/><path d="M3 16h10v-5.5a.5.5 0 0 0-.5-.5h-9a.5.5 0 0 0-.5.5zm9-16H4v5.5a.5.5 0 0 0 .5.5h7a.5.5 0 0 0 .5-.5zM9 1h2v4H9z"/></svg>
                                <?php echo $modoEdicao ? 'Salvar Alterações' : 'Salvar Medicamento'; ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if (!$modoEdicao): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const inputBusca = document.getElementById('busca_medicamento');
    const resultsContainer = document.getElementById('csv_results');

    const nomeComercialInput = document.getElementById('nome_comercial');
    const principioAtivoInput = document.getElementById('principio_ativo');
    const apresentacaoInput = document.getElementById('apresentacao');
    const unidadeBaseSelect = document.getElementById('unidade_base');
    const grupoEquivalencia = document.getElementById('equivalencia_gotas_cadastro');
    const campoEquivalencia = document.getElementById('gotas_por_ml');
    const atualizarEquivalencia = () => {
        const emMl = unidadeBaseSelect.value === 'ml';
        grupoEquivalencia.hidden = !emMl;
        if (campoEquivalencia) campoEquivalencia.disabled = !emMl;
    };
    unidadeBaseSelect.addEventListener('change', atualizarEquivalencia);
    atualizarEquivalencia();

    let medicamentosDB = []; // Armazenará toda a base de medicamentos
    const loadingDiv = document.createElement('div');
    loadingDiv.className = 'list-group-item disabled';

    // 1. Carrega todos os medicamentos do servidor ao iniciar a página
    async function carregarMedicamentos() {
        resultsContainer.innerHTML = '';
        loadingDiv.textContent = 'Carregando base de medicamentos...';
        resultsContainer.appendChild(loadingDiv);
        try {
            const response = await fetch(`<?php echo BASE_URL; ?>dao/get_medicamentos_json.php`);
            if (!response.ok) throw new Error(`Erro na rede: ${response.statusText}`);
            
            medicamentosDB = await response.json();

            if (medicamentosDB.length > 0) {
                loadingDiv.textContent = 'Base de dados carregada. Digite para pesquisar.';
                setTimeout(() => {
                    // Limpa a mensagem de "carregado" se o usuário não digitar nada
                    if (inputBusca.value.length < 3) {
                        resultsContainer.innerHTML = '';
                    }
                }, 2500);
            } else {
                loadingDiv.textContent = 'Nenhum medicamento encontrado na base de dados.';
            }
        } catch (error) {
            console.error('Erro ao carregar a base de medicamentos:', error);
            loadingDiv.className = 'list-group-item list-group-item-danger';
            loadingDiv.textContent = 'Erro ao carregar a base de medicamentos.';
        }
    }

    carregarMedicamentos(); // Inicia o carregamento

    let debounceTimeout;
    inputBusca.addEventListener('keyup', function() {
        clearTimeout(debounceTimeout);
        const query = inputBusca.value.trim();

        debounceTimeout = setTimeout(() => {
            if (query.length < 3) {
                resultsContainer.innerHTML = '';
                return;
            }

            if (medicamentosDB.length === 0) {
                resultsContainer.innerHTML = '<div class="list-group-item disabled">A base de dados ainda está carregando.</div>';
                return;
            }

            // Normaliza o termo de busca para ser igual à normalização feita no PHP
            const normalizedQuery = query.toUpperCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "");

            // 2. Filtra o array local em vez de fazer uma nova requisição
            const filteredResults = medicamentosDB.filter(med => 
                med.search_np.includes(normalizedQuery) || med.search_pa.includes(normalizedQuery)
            ).map(med => {
                let score = 0;
                if (med.search_np === normalizedQuery) {
                    score = 6; // Prioridade máxima: Nome do produto exato
                } else if (med.search_np.startsWith(normalizedQuery)) {
                    score = 5; // Nome do produto começa com o termo
                } else if (med.search_pa === normalizedQuery) {
                    score = 4; // Princípio ativo exato
                } else if (med.search_pa.startsWith(normalizedQuery)) {
                    score = 3; // Princípio ativo começa com o termo
                } else if (med.search_np.includes(normalizedQuery)) {
                    score = 2; // Nome do produto contém o termo
                } else if (med.search_pa.includes(normalizedQuery)) {
                    score = 1; // Princípio ativo contém o termo
                }
                return { ...med, score };
            });

            // Ordena por score de relevância (maior primeiro) e depois alfabeticamente
            filteredResults.sort((a, b) => {
                if (b.score !== a.score) return b.score - a.score;
                return a.nome_produto.localeCompare(b.nome_produto, 'pt-BR', { sensitivity: 'base' });
            });

            const finalResults = filteredResults.slice(0, 20); // Limita a 20 resultados para não poluir a tela

            // 3. Exibe os resultados
            resultsContainer.innerHTML = '';
            if (finalResults.length > 0) {
                finalResults.forEach(med => {
                    const item = document.createElement('a');
                    item.href = '#';
                    item.className = 'list-group-item list-group-item-action';
                    const nome = document.createElement('strong');
                    nome.textContent = med.nome_produto;
                    const detalhes = document.createElement('small');
                    detalhes.textContent = `${med.principio_ativo} - ${med.apresentacao}`;
                    item.append(nome, document.createElement('br'), detalhes);
                    item.addEventListener('click', (e) => {
                        e.preventDefault();
                        nomeComercialInput.value = med.nome_produto;
                        principioAtivoInput.value = med.principio_ativo;
                        apresentacaoInput.value = med.apresentacao;
                        if (med.unidade_base_sugerida) unidadeBaseSelect.value = med.unidade_base_sugerida;
                        atualizarEquivalencia();
                        resultsContainer.innerHTML = '';
                        inputBusca.value = '';
                    });
                    resultsContainer.appendChild(item);
                });
            } else {
                resultsContainer.innerHTML = '<div class="list-group-item disabled">Nenhum resultado encontrado.</div>';
            }
        }, 300); // Um pequeno atraso para melhorar a fluidez da digitação
    });
});
</script>
<?php endif; ?>

<?php
require_once ROOT_DIR . '/src/View/templates/includes/rodapeInterno.inc.php';
?>

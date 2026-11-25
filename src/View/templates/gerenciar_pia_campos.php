<?php
$controleAcesso ??= new \Abdm\Infraestrutura\ControleAcesso($pdo);
$controleAcesso->exigirPermissao('gerenciar_pia_campos');

[
    'titulosSecoes' => $titulosSecoes,
    'configuracaoServico' => $configuracaoServico,
    'servicoConfiguracaoDisponivel' => $servicoConfiguracaoDisponivel,
    'secoes' => $secoes,
    'secoesGerenciaveis' => $secoesGerenciaveis,
    'ordemSecao' => $ordemSecao,
    'camposPorSecao' => $camposPorSecao,
    'campo' => $campo,
    'flash' => $flash,
    'nomesTipos' => $nomesTipos,
] = (new \Abdm\Controller\TelasPiaController($pdo))->campos();

require_once ROOT_DIR . '/src/View/templates/includes/cabecalhoInterno.inc.php';
?>

<style>
    .pia-campo { min-width: 0; }
    .pia-opcoes { min-height: 2.5rem; }
    .pia-campo-inativo { opacity: .72; }
    .pia-actions { min-width: 8.5rem; }
    .pia-section-toggle .bi-chevron-down { transition: transform .2s ease; }
    .pia-section-toggle[aria-expanded="true"] .bi-chevron-down { transform: rotate(180deg); }
</style>

<main class="container py-4">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <a class="text-decoration-none" href="<?php echo BASE_URL; ?>views/pia.php"><i class="bi bi-arrow-left me-1" aria-hidden="true"></i>Voltar ao PIA</a>
            <h1 class="h2 mt-2 mb-1">Gerenciar campos do PIA</h1>
            <p class="text-body-secondary mb-0">As seções 1 a 5 e os campos originais do modelo podem ser ocultados e reativados, sem exclusão permanente. Campos criados a partir da seção 5 e seções personalizadas podem ser editados, ocultados ou excluídos conforme as regras do modelo.</p>
        </div>
    </div>

    <?php if (is_array($flash) && !empty($flash['message'])): ?>
        <div class="alert alert-<?php echo \Abdm\Model\Pia::piaEscaparHtml($flash['type'] ?? 'info'); ?> alert-dismissible fade show" role="alert">
            <?php echo \Abdm\Model\Pia::piaEscaparHtml($flash['message']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
        </div>
    <?php endif; ?>

    <section class="card shadow-sm mb-4" id="painelConfiguracaoServico">
        <div class="card-header">
            <button class="btn btn-link pia-section-toggle text-decoration-none p-0 fw-semibold collapsed d-flex justify-content-between align-items-center w-100 text-start" type="button" data-bs-toggle="collapse" data-bs-target="#conteudoConfiguracaoServico" aria-expanded="false" aria-controls="conteudoConfiguracaoServico">
                <span class="h5 mb-0">Informações do serviço de acolhimento</span>
                <i class="bi bi-chevron-down ms-2" aria-hidden="true"></i>
            </button>
        </div>
        <div class="card-body collapse" id="conteudoConfiguracaoServico">
            <?php if (!$servicoConfiguracaoDisponivel): ?>
                <div class="alert alert-warning" role="alert">
                    As informações do serviço de acolhimento não estão disponíveis para edição nesta instalação. Solicite suporte ao administrador.
                </div>
            <?php endif; ?>
            <form action="<?php echo BASE_URL; ?>acoes/controllerPia.php" method="post" class="row g-3">
                <?php echo \Abdm\Infraestrutura\Sessao::campoTokenCsrf(); ?><input type="hidden" name="acao" value="salvar_configuracao_servico">
                <div class="col-12 col-lg-6">
                    <label class="form-label" for="servicoNome">Nome do serviço</label>
                    <input class="form-control" id="servicoNome" name="nome" maxlength="180" value="<?php echo \Abdm\Model\Pia::piaEscaparHtml($configuracaoServico['nome']); ?>" required>
                </div>
                <div class="col-12 col-lg-6">
                    <label class="form-label" for="servicoTelefone">Telefone</label>
                    <input class="form-control" id="servicoTelefone" name="telefone" maxlength="60" value="<?php echo \Abdm\Model\Pia::piaEscaparHtml($configuracaoServico['telefone']); ?>">
                </div>
                <div class="col-12">
                    <label class="form-label" for="servicoEndereco">Endereço</label>
                    <input class="form-control" id="servicoEndereco" name="endereco" maxlength="255" value="<?php echo \Abdm\Model\Pia::piaEscaparHtml($configuracaoServico['endereco']); ?>" required>
                </div>
                <div class="col-12 d-flex justify-content-end">
                    <button class="btn btn-primary" type="submit" <?php echo $servicoConfiguracaoDisponivel ? '' : 'disabled'; ?>>
                        <i class="bi bi-save me-1" aria-hidden="true"></i>Salvar informações do serviço
                    </button>
                </div>
            </form>
        </div>
    </section>

    <section class="card shadow-sm mb-4" id="painelCriarSecao">
        <div class="card-header">
            <button class="btn btn-link pia-section-toggle text-decoration-none p-0 fw-semibold collapsed d-flex justify-content-between align-items-center w-100 text-start" type="button" data-bs-toggle="collapse" data-bs-target="#conteudoCriarSecao" aria-expanded="false" aria-controls="conteudoCriarSecao">
                <span class="h5 mb-0">Criar seção personalizada</span>
                <i class="bi bi-chevron-down ms-2" aria-hidden="true"></i>
            </button>
        </div>
        <div class="card-body collapse" id="conteudoCriarSecao">
            <form action="<?php echo BASE_URL; ?>acoes/controllerPia.php" method="post" class="row g-3 align-items-end">
                <?php echo \Abdm\Infraestrutura\Sessao::campoTokenCsrf(); ?><input type="hidden" name="acao" value="criar_secao">
                <div class="col-12 col-lg-9"><label class="form-label" for="tituloSecaoNova">Título da seção</label><input class="form-control" id="tituloSecaoNova" name="titulo" maxlength="180" required placeholder="Ex.: 11. ACOMPANHAMENTO ESCOLAR"></div>
                <div class="col-12 col-lg-3 d-grid"><button class="btn btn-primary" type="submit"><i class="bi bi-plus-circle me-1" aria-hidden="true"></i>Criar seção</button></div>
            </form>
        </div>
    </section>

    <section class="card shadow-sm mb-4" id="painelVisibilidadeSecoes">
        <div class="card-header">
            <button class="btn btn-link pia-section-toggle text-decoration-none p-0 fw-semibold collapsed d-flex justify-content-between align-items-center w-100 text-start" type="button" data-bs-toggle="collapse" data-bs-target="#conteudoVisibilidadeSecoes" aria-expanded="false" aria-controls="conteudoVisibilidadeSecoes">
                <span class="h5 mb-0">Visibilidade e títulos das seções</span>
                <i class="bi bi-chevron-down ms-2" aria-hidden="true"></i>
            </button>
        </div>
        <div class="list-group list-group-flush collapse" id="conteudoVisibilidadeSecoes">
            <?php foreach ($secoesGerenciaveis as $secao): ?>
                <?php $secaoFixa = (int)$secao['fixa'] === 1 || (int)$secao['ordem'] <= 50; $secaoAtiva = (int)$secao['ativo'] === 1; ?>
                <div class="list-group-item <?php echo $secaoAtiva ? '' : 'pia-campo-inativo'; ?>">
                    <div class="row align-items-center g-3">
                        <div class="col-12 col-xl">
                            <span class="badge <?php echo $secaoAtiva ? 'text-bg-success' : 'text-bg-secondary'; ?> mb-2"><?php echo $secaoAtiva ? 'Visível' : 'Oculta'; ?></span>
                            <?php if (!$secaoFixa): ?><form action="<?php echo BASE_URL; ?>acoes/controllerPia.php" method="post" class="d-flex flex-wrap gap-2">
                                <?php echo \Abdm\Infraestrutura\Sessao::campoTokenCsrf(); ?><input type="hidden" name="acao" value="editar_secao"><input type="hidden" name="chave" value="<?php echo \Abdm\Model\Pia::piaEscaparHtml($secao['chave']); ?>">
                                <label class="visually-hidden" for="tituloSecao<?php echo \Abdm\Model\Pia::piaEscaparHtml($secao['chave']); ?>">Título</label><input class="form-control form-control-sm flex-grow-1" id="tituloSecao<?php echo \Abdm\Model\Pia::piaEscaparHtml($secao['chave']); ?>" name="titulo" maxlength="180" value="<?php echo \Abdm\Model\Pia::piaEscaparHtml($secao['titulo']); ?>" required>
                                <button class="btn btn-sm btn-outline-primary" type="submit"><i class="bi bi-save me-1" aria-hidden="true"></i>Salvar título</button>
                            </form><?php else: ?><strong><?php echo \Abdm\Model\Pia::piaEscaparHtml($secao['titulo']); ?></strong><span class="badge text-bg-light border ms-2">Seção fixa</span><?php endif; ?>
                        </div>
                        <div class="col-12 col-xl-auto d-flex gap-2">
                            <form action="<?php echo BASE_URL; ?>acoes/controllerPia.php" method="post">
                                <?php echo \Abdm\Infraestrutura\Sessao::campoTokenCsrf(); ?><input type="hidden" name="acao" value="alternar_secao"><input type="hidden" name="chave" value="<?php echo \Abdm\Model\Pia::piaEscaparHtml($secao['chave']); ?>">
                                <button class="btn btn-sm <?php echo $secaoAtiva ? 'btn-outline-secondary' : 'btn-outline-success'; ?>" type="submit"><i class="bi <?php echo $secaoAtiva ? 'bi-eye-slash' : 'bi-eye'; ?> me-1" aria-hidden="true"></i><?php echo $secaoAtiva ? 'Ocultar' : 'Exibir'; ?></button>
                            </form>
                            <?php if (!$secaoFixa): ?><form action="<?php echo BASE_URL; ?>acoes/controllerPia.php" method="post" onsubmit="return confirm('Excluir permanentemente esta seção e os campos associados?');">
                                <?php echo \Abdm\Infraestrutura\Sessao::campoTokenCsrf(); ?><input type="hidden" name="acao" value="excluir_secao"><input type="hidden" name="chave" value="<?php echo \Abdm\Model\Pia::piaEscaparHtml($secao['chave']); ?>">
                                <button class="btn btn-sm btn-outline-danger" type="submit"><i class="bi bi-trash me-1" aria-hidden="true"></i>Excluir</button>
                            </form><?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="card shadow-sm mb-4" id="painelNovoCampo">
        <div class="card-header">
            <button class="btn btn-link pia-section-toggle text-decoration-none p-0 fw-semibold collapsed d-flex justify-content-between align-items-center w-100 text-start" type="button" data-bs-toggle="collapse" data-bs-target="#conteudoNovoCampo" aria-expanded="false" aria-controls="conteudoNovoCampo">
                <span class="h5 mb-0">Adicionar novo campo</span>
                <i class="bi bi-chevron-down ms-2" aria-hidden="true"></i>
            </button>
        </div>
        <div class="card-body collapse" id="conteudoNovoCampo">
            <form action="<?php echo BASE_URL; ?>acoes/controllerPia.php" method="post" class="row g-3" id="formNovoCampo">
                <?php echo \Abdm\Infraestrutura\Sessao::campoTokenCsrf(); ?>
                <input type="hidden" name="acao" value="criar_campo">
                <div class="col-12 col-lg-4">
                    <label class="form-label" for="novoCampoSecao">Seção</label>
                    <select class="form-select" name="secao" id="novoCampoSecao" required>
                        <?php foreach ($secoes as $chave => $titulo): ?>
                            <?php if (($ordemSecao[$chave] ?? 999) >= 50): ?><option value="<?php echo \Abdm\Model\Pia::piaEscaparHtml($chave); ?>"><?php echo \Abdm\Model\Pia::piaEscaparHtml($titulo); ?></option><?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12 col-lg-4">
                    <label class="form-label" for="novoCampoRotulo">Título do campo</label>
                    <input class="form-control" id="novoCampoRotulo" name="rotulo" maxlength="180" required>
                </div>
                <div class="col-12 col-lg-4">
                    <label class="form-label" for="novoCampoOrigem">Onde preencher</label>
                    <select class="form-select" id="novoCampoOrigem" name="origem" required><option value="cadastro">Cadastro do acolhido</option><option value="planejamento">Planejamento do PIA</option></select>
                </div>
                <div class="col-12"><div class="form-text">Novos campos podem ser adicionados a partir da seção 5. Campos das seções fixas 1 a 5 podem ser ocultados sem apagar os dados.</div></div>
                <div class="col-12 col-lg-4">
                    <label class="form-label" for="novoCampoTipo">Tipo de resposta</label>
                    <select class="form-select" id="novoCampoTipo" name="tipo" required>
                        <option value="text">Texto curto</option>
                        <option value="textarea">Texto longo</option>
                        <option value="date">Data</option>
                        <option value="select">Seleção única</option>
                        <option value="multi">Múltipla seleção</option>
                    </select>
                </div>
                <div class="col-12 d-none" id="novoCampoOpcoesWrap">
                    <label class="form-label" for="novoCampoOpcoes">Opções (uma por linha)</label>
                    <textarea class="form-control pia-opcoes" id="novoCampoOpcoes" name="opcoes" rows="3" placeholder="Exemplo:&#10;Sim&#10;Não"></textarea>
                    <div class="form-text">Use pelo menos duas opções. Este campo só é usado para seleção única ou múltipla.</div>
                </div>
                <div class="col-12 d-flex justify-content-end">
                    <button class="btn btn-primary" type="submit"><i class="bi bi-plus-circle me-1" aria-hidden="true"></i>Adicionar campo</button>
                </div>
            </form>
        </div>
    </section>

    <section class="card shadow-sm mb-4" id="painelCampos">
            <div class="card-header">
                <button class="btn btn-link pia-section-toggle text-decoration-none p-0 fw-semibold collapsed d-flex justify-content-between align-items-center w-100 text-start" type="button" data-bs-toggle="collapse" data-bs-target="#conteudoCampos" aria-expanded="false" aria-controls="conteudoCampos">
                    <span><span class="h5 mb-1 d-block">Campos por seção</span><span class="small text-body-secondary">Abra uma seção para ver seus campos, saber o que pode ser editado e acessar as ações disponíveis.</span></span>
                    <i class="bi bi-chevron-down ms-2" aria-hidden="true"></i>
                </button>
            </div>
        <div class="collapse" id="conteudoCampos">
            <div class="card-body">
    <?php foreach ($titulosSecoes as $chaveSecao => $tituloSecao): ?>
        <section class="card shadow-sm mb-4">
            <div class="card-header">
                <button class="btn btn-link pia-section-toggle text-decoration-none p-0 fw-semibold collapsed d-flex justify-content-between align-items-center w-100 text-start" type="button" data-bs-toggle="collapse" data-bs-target="#camposSecao<?php echo \Abdm\Model\Pia::piaEscaparHtml($chaveSecao); ?>" aria-expanded="false" aria-controls="camposSecao<?php echo \Abdm\Model\Pia::piaEscaparHtml($chaveSecao); ?>">
                    <span><?php echo \Abdm\Model\Pia::piaEscaparHtml($tituloSecao); ?> <span class="badge text-bg-light border ms-1"><?php echo count($camposPorSecao[$chaveSecao] ?? []); ?> campos</span></span>
                    <i class="bi bi-chevron-down ms-2" aria-hidden="true"></i>
                </button>
            </div>
            <div class="list-group list-group-flush collapse" id="camposSecao<?php echo \Abdm\Model\Pia::piaEscaparHtml($chaveSecao); ?>">
                <?php foreach ($camposPorSecao[$chaveSecao] ?? [] as $campo): ?>
                    <?php $ativo = (int)$campo['ativo'] === 1; $temOpcoes = in_array($campo['tipo'], ['select', 'multi'], true); $ordemAtualSecao = $ordemSecao[$campo['secao']] ?? 999; $campoModeloProtegido = $ordemAtualSecao <= 50 && empty($campo['criado_por_login']); $fixo = $campoModeloProtegido || ($campo['secao'] === 'acolhido' && ($campo['origem'] ?? 'cadastro') === 'cadastro'); $podeExcluirCampo = $ordemAtualSecao >= 50 && !$campoModeloProtegido; $rotuloCampo = $campo['chave'] === 'dst' ? 'Possui alguma IST?' : ($campo['chave'] === 'qual_dst' ? 'Qual IST?' : $campo['rotulo']); ?>
                    <div class="list-group-item <?php echo $ativo ? '' : 'pia-campo-inativo'; ?>">
                        <div class="row align-items-center g-3">
                            <div class="col-12 col-xl">
                                <span class="badge <?php echo $ativo ? 'text-bg-success' : 'text-bg-secondary'; ?> mb-2"><?php echo $ativo ? 'Visível' : 'Oculto'; ?></span>
                                <span class="badge text-bg-light border mb-2"><?php echo \Abdm\Model\Pia::piaEscaparHtml($nomesTipos[$campo['tipo']] ?? $campo['tipo']); ?></span>
                                <span class="badge text-bg-info mb-2"><?php echo ($campo['origem'] ?? 'cadastro') === 'planejamento' ? 'Planejamento do PIA' : 'Cadastro do acolhido'; ?></span>
                                <span class="badge <?php echo $fixo ? 'text-bg-secondary' : 'text-bg-primary'; ?> mb-2"><?php echo $fixo ? 'Rótulo protegido' : 'Editável'; ?></span>
                                <?php if ($podeExcluirCampo): ?><span class="badge text-bg-warning mb-2">Exclusão permitida</span><?php endif; ?>
                                <?php if ($fixo): ?><p class="small text-body-secondary mb-0">Campo original do modelo. Pode ser ocultado e reexibido, sem exclusão permanente.</p><?php else: ?><form action="<?php echo BASE_URL; ?>acoes/controllerPia.php" method="post" class="row g-2">
                                    <?php echo \Abdm\Infraestrutura\Sessao::campoTokenCsrf(); ?>
                                    <input type="hidden" name="acao" value="editar_campo">
                                    <input type="hidden" name="id_campo" value="<?php echo (int)$campo['idCampo']; ?>">
                                    <div class="col-12 <?php echo $temOpcoes ? 'col-lg-6' : ''; ?>">
                                        <label class="visually-hidden" for="campoRotulo<?php echo (int)$campo['idCampo']; ?>">Título</label>
                                        <input class="form-control form-control-sm" id="campoRotulo<?php echo (int)$campo['idCampo']; ?>" name="rotulo" value="<?php echo \Abdm\Model\Pia::piaEscaparHtml($rotuloCampo); ?>" maxlength="180" required>
                                    </div>
                                    <?php if ($temOpcoes): ?>
                                        <div class="col-12 col-lg-6">
                                            <label class="visually-hidden" for="campoOpcoes<?php echo (int)$campo['idCampo']; ?>">Opções</label>
                                            <textarea class="form-control form-control-sm pia-opcoes" id="campoOpcoes<?php echo (int)$campo['idCampo']; ?>" name="opcoes" rows="2" aria-label="Opções, uma por linha"><?php echo \Abdm\Model\Pia::piaEscaparHtml(str_replace('|', "\n", (string)$campo['opcoes'])); ?></textarea>
                                        </div>
                                    <?php endif; ?>
                                    <div class="col-12">
                                        <button class="btn btn-sm btn-outline-primary" type="submit"><i class="bi bi-save me-1" aria-hidden="true"></i>Salvar título<?php echo $temOpcoes ? ' e opções' : ''; ?></button>
                                    </div>
                                </form><?php endif; ?>
                            </div>
                            <div class="col-12 col-xl-auto pia-actions d-flex flex-column gap-2">
                                <form action="<?php echo BASE_URL; ?>acoes/controllerPia.php" method="post">
                                    <?php echo \Abdm\Infraestrutura\Sessao::campoTokenCsrf(); ?>
                                    <input type="hidden" name="acao" value="alternar_campo">
                                    <input type="hidden" name="id_campo" value="<?php echo (int)$campo['idCampo']; ?>">
                                    <button class="btn btn-sm w-100 <?php echo $ativo ? 'btn-outline-danger' : 'btn-outline-success'; ?>" type="submit">
                                        <i class="bi <?php echo $ativo ? 'bi-eye-slash' : 'bi-eye'; ?> me-1" aria-hidden="true"></i><?php echo $ativo ? 'Ocultar' : 'Exibir'; ?>
                                    </button>
                                </form>
                                <?php if ($podeExcluirCampo): ?><form action="<?php echo BASE_URL; ?>acoes/controllerPia.php" method="post" onsubmit="return confirm('Excluir permanentemente este campo? Os PIAs já preenchidos não serão apagados.');">
                                    <?php echo \Abdm\Infraestrutura\Sessao::campoTokenCsrf(); ?><input type="hidden" name="acao" value="excluir_campo"><input type="hidden" name="id_campo" value="<?php echo (int)$campo['idCampo']; ?>">
                                    <button class="btn btn-sm btn-outline-danger w-100" type="submit"><i class="bi bi-trash me-1" aria-hidden="true"></i>Excluir</button>
                                </form><?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
                <?php if (empty($camposPorSecao[$chaveSecao])): ?>
                    <div class="list-group-item text-body-secondary">Nenhum campo cadastrado nesta seção.</div>
                <?php endif; ?>
            </div>
        </section>
    <?php endforeach; ?>
            </div>
        </div>
    </section>
</main>

<script>
    (() => {
        const tipo = document.getElementById('novoCampoTipo');
        const secao = document.getElementById('novoCampoSecao');
        const origem = document.getElementById('novoCampoOrigem');
        const opcoes = document.getElementById('novoCampoOpcoesWrap');
        const campoOpcoes = document.getElementById('novoCampoOpcoes');
        const atualizarOrigem = () => {
        const secaoDoCadastro = secao.value === 'acolhido';
            const opcaoCadastro = origem.querySelector('option[value="cadastro"]');
            const opcaoPlanejamento = origem.querySelector('option[value="planejamento"]');
            opcaoCadastro.disabled = !secaoDoCadastro;
            opcaoPlanejamento.disabled = secaoDoCadastro;
            origem.value = secaoDoCadastro ? 'cadastro' : 'planejamento';
        };
        const atualizarOpcoes = () => {
            const precisaOpcoes = ['select', 'multi'].includes(tipo.value);
            opcoes.classList.toggle('d-none', !precisaOpcoes);
            campoOpcoes.required = precisaOpcoes;
        };
        tipo.addEventListener('change', atualizarOpcoes);
        secao.addEventListener('change', atualizarOrigem);
        atualizarOrigem();
        atualizarOpcoes();
    })();
</script>
<?php require_once ROOT_DIR . '/src/View/templates/includes/rodapeInterno.inc.php'; ?>

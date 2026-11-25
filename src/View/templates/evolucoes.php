<?php
$controleAcesso ??= new \Abdm\Infraestrutura\ControleAcesso($pdo);
$controleAcesso->exigirUsuarioAutenticado();
if (!$controleAcesso->usuarioPode('registrar_evolucao_acolhido') && !$controleAcesso->usuarioPode('consultar_minhas_evolucoes') && !$controleAcesso->usuarioPode('consultar_todas_evolucoes') && !$controleAcesso->usuarioPode('imprimir_evolucao_acolhido') && !$controleAcesso->usuarioPode('imprimir_minhas_evolucoes') && !$controleAcesso->usuarioPode('imprimir_evolucoes_profissional') && !$controleAcesso->usuarioPode('imprimir_evolucoes_todos')) {
    http_response_code(403);
    exit('Você não tem permissão para consultar evoluções.');
}

[
    'h' => $h,
    'hoje' => $hoje,
    'acolhidos' => $acolhidos,
    'podeRegistrarEvolucao' => $podeRegistrarEvolucao,
    'podeConsultarTodasEvolucoes' => $podeConsultarTodasEvolucoes,
    'podeConsultarEvolucoes' => $podeConsultarEvolucoes,
    'profissionais' => $profissionais,
    'acolhido' => $acolhido,
    'acolhidoId' => $acolhidoId,
    'inicio' => $inicio,
    'fim' => $fim,
    'termo' => $termo,
    'flash' => $flash,
    'form' => $form,
    'evolucoes' => $evolucoes,
    'acolhidoSelecionado' => $acolhidoSelecionado,
] = (new \Abdm\Controller\TelasEvolucaoController($pdo))->registros($controleAcesso);

require_once ROOT_DIR . '/src/View/templates/includes/cabecalhoInterno.inc.php';
?>
<main class="container my-5">
    <?php if (is_array($flash) && !empty($flash['message'])): ?>
        <div class="alert alert-<?php echo $h($flash['type'] ?? 'info'); ?> alert-dismissible fade show" role="status">
            <?php echo $h($flash['message']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
        </div>
    <?php endif; ?>

    <section class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-primary text-white"><h1 class="h3 mb-0"><i class="bi bi-journal-medical me-2" aria-hidden="true"></i>Evolução dos acolhidos</h1></div>
        <div class="card-body"><p class="mb-0 text-body-secondary">Selecione o acolhido para acessar as opções de evolução liberadas para sua conta.</p></div>
    </section>

    <?php if ($acolhidoId === null && $controleAcesso->usuarioPode('imprimir_minhas_evolucoes')): ?>
        <section class="card shadow-sm mb-4" aria-labelledby="tituloImprimirMinhasEvolucoes">
            <div class="card-header bg-body-tertiary"><h2 class="h5 mb-0" id="tituloImprimirMinhasEvolucoes"><i class="bi bi-printer me-2" aria-hidden="true"></i>Imprimir minhas evoluções</h2></div>
            <div class="card-body">
                <p class="small text-body-secondary">Imprima seus registros de todos os acolhidos. Você também pode selecionar um acolhido abaixo para imprimir somente as evoluções feitas por você para aquela pessoa.</p>
                <form method="get" action="<?php echo $h(BASE_URL); ?>views/gerar_evolucoes_pdf.php" class="row g-3 align-items-end" id="formImprimirMinhasEvolucoes" data-evolucao-print-form>
                    <input type="hidden" name="modo" value="meus">
                    <div class="col-md-4 col-lg-3"><label class="form-label" for="escopoMinhasEvolucoes">Período</label><select class="form-select" id="escopoMinhasEvolucoes" name="escopo" data-escopo-select><option value="dia">Um dia</option><option value="periodo">Intervalo</option><option value="todos">Todo o histórico</option></select></div>
                    <div class="col-md-4 col-lg-3 campo-data-impressao-evolucao" data-escopo="dia"><label class="form-label" for="dataMinhasEvolucoes">Data</label><input class="form-control" type="date" id="dataMinhasEvolucoes" name="data" value="<?php echo $h($hoje); ?>" max="<?php echo $h($hoje); ?>"></div>
                    <div class="col-md-4 col-lg-3 campo-data-impressao-evolucao" data-escopo="periodo"><label class="form-label" for="inicioMinhasEvolucoes">De</label><input class="form-control" type="date" id="inicioMinhasEvolucoes" name="inicio" value="<?php echo $h($hoje); ?>" max="<?php echo $h($hoje); ?>"></div>
                    <div class="col-md-4 col-lg-3 campo-data-impressao-evolucao" data-escopo="periodo"><label class="form-label" for="fimMinhasEvolucoes">Até</label><input class="form-control" type="date" id="fimMinhasEvolucoes" name="fim" value="<?php echo $h($hoje); ?>" max="<?php echo $h($hoje); ?>"></div>
                    <div class="col-12 d-flex justify-content-end"><button class="btn btn-outline-primary" type="submit"><i class="bi bi-printer me-1" aria-hidden="true"></i>Imprimir todas as minhas evoluções</button></div>
                </form>
            </div>
        </section>
    <?php endif; ?>

    <section class="card shadow-sm mb-4" aria-labelledby="tituloSelecionarAcolhido">
        <div class="card-header bg-body-tertiary"><h2 class="h5 mb-0" id="tituloSelecionarAcolhido">Acolhido</h2></div>
        <div class="card-body">
            <form method="get" class="row g-3 align-items-end">
                <div class="col-md-8 col-lg-6">
                    <label class="form-label" for="acolhidoEvolucao">Selecione um acolhido</label>
                    <select class="form-select" id="acolhidoEvolucao" name="acolhido" required>
                        <option value="">Selecione um acolhido</option>
                        <?php foreach ($acolhidos as $acolhido): ?>
                            <option value="<?php echo (int)$acolhido['id']; ?>" <?php echo $acolhidoId === (int)$acolhido['id'] ? 'selected' : ''; ?>><?php echo $h($acolhido['nome']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4 col-lg-3"><button class="btn btn-primary w-100" type="submit"><i class="bi bi-folder2-open me-1" aria-hidden="true"></i>Abrir evolução</button></div>
            </form>
        </div>
    </section>

    <?php if ($acolhidoSelecionado !== null): ?>
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
            <h2 class="h4 mb-0"><?php echo $podeConsultarEvolucoes ? 'Histórico de ' : 'Evolução de '; ?><?php echo $h($acolhidoSelecionado['nome']); ?></h2>
        </div>

        <?php if ($podeConsultarEvolucoes): ?>
        <section class="card shadow-sm mb-4" aria-labelledby="tituloFiltrosEvolucao">
            <div class="card-header bg-body-tertiary"><h3 class="h5 mb-0" id="tituloFiltrosEvolucao">Filtrar histórico</h3></div>
            <div class="card-body">
                <form method="get" class="row g-3 align-items-end">
                    <input type="hidden" name="acolhido" value="<?php echo (int)$acolhidoId; ?>">
                    <div class="col-md-4 col-lg-3"><label class="form-label" for="inicioEvolucao">De</label><input class="form-control" type="date" id="inicioEvolucao" name="inicio" value="<?php echo $h($inicio); ?>" max="<?php echo $h($hoje); ?>"></div>
                    <div class="col-md-4 col-lg-3"><label class="form-label" for="fimEvolucao">Até</label><input class="form-control" type="date" id="fimEvolucao" name="fim" value="<?php echo $h($fim); ?>" max="<?php echo $h($hoje); ?>"></div>
                    <div class="col-md-8 col-lg-4"><label class="form-label" for="termoEvolucao">Pesquisar no texto</label><input class="form-control" id="termoEvolucao" name="termo" value="<?php echo $h($termo); ?>" maxlength="120" placeholder="Profissional ou texto da evolução"></div>
                    <div class="col-md-4 col-lg-2 d-flex gap-2"><a class="btn btn-outline-secondary flex-fill" href="<?php echo $h(BASE_URL); ?>views/evolucoes.php?acolhido=<?php echo (int)$acolhidoId; ?>">Limpar</a><button class="btn btn-primary flex-fill" type="submit" aria-label="Filtrar evoluções"><i class="bi bi-search" aria-hidden="true"></i></button></div>
                </form>
            </div>
        </section>
        <?php endif; ?>

        <?php if ($controleAcesso->usuarioPode('imprimir_minhas_evolucoes') || $controleAcesso->usuarioPode('imprimir_evolucao_acolhido') || $controleAcesso->usuarioPode('imprimir_evolucoes_profissional') || $controleAcesso->usuarioPode('imprimir_evolucoes_todos')): ?>
            <section class="card shadow-sm mb-4" aria-labelledby="tituloImprimirEvolucao">
                <div class="card-header bg-body-tertiary"><h3 class="h5 mb-0" id="tituloImprimirEvolucao">Imprimir histórico</h3></div>
                <div class="card-body">
                    <form method="get" action="<?php echo $h(BASE_URL); ?>views/gerar_evolucoes_pdf.php" class="row g-3 align-items-end" id="formImprimirEvolucoes" data-evolucao-print-form>
                        <input type="hidden" name="acolhido" value="<?php echo (int)$acolhidoId; ?>">
                        <div class="col-md-6 col-lg-3"><label class="form-label" for="modoImpressaoEvolucao">Registros</label><select class="form-select" id="modoImpressaoEvolucao" name="modo" required>
                            <?php if ($controleAcesso->usuarioPode('imprimir_minhas_evolucoes')): ?><option value="meus">Minhas evoluções deste acolhido</option><?php endif; ?>
                            <?php if ($controleAcesso->usuarioPode('imprimir_evolucao_acolhido')): ?><option value="acolhido">Deste acolhido</option><?php endif; ?>
                            <?php if ($controleAcesso->usuarioPode('imprimir_evolucoes_profissional')): ?><option value="profissional">De um profissional</option><?php endif; ?>
                            <?php if ($controleAcesso->usuarioPode('imprimir_evolucoes_todos')): ?><option value="todos">De todos os profissionais</option><?php endif; ?>
                        </select></div>
                        <div class="col-md-6 col-lg-3" id="profissionalImpressaoEvolucao" data-profissional-wrap hidden><label class="form-label" for="profissionalEvolucaoId">Profissional</label><select class="form-select" id="profissionalEvolucaoId" name="profissional" disabled><option value="">Selecione</option><?php foreach ($profissionais as $profissional): ?><option value="<?php echo (int)$profissional['idLogin']; ?>"><?php echo $h($profissional['nome']); ?><?php echo (int)$profissional['ativo'] !== 1 ? ' (acesso desativado)' : ''; ?></option><?php endforeach; ?></select></div>
                        <div class="col-md-6 col-lg-3"><label class="form-label" for="escopoImpressaoEvolucao">Período</label><select class="form-select" id="escopoImpressaoEvolucao" name="escopo" data-escopo-select><option value="dia">Um dia</option><option value="periodo">Intervalo</option><option value="todos">Todo o histórico</option></select></div>
                        <div class="col-md-6 col-lg-3 campo-data-impressao-evolucao" data-escopo="dia"><label class="form-label" for="dataImpressaoEvolucao">Data</label><input class="form-control" type="date" id="dataImpressaoEvolucao" name="data" value="<?php echo $h($hoje); ?>" max="<?php echo $h($hoje); ?>"></div>
                        <div class="col-md-6 col-lg-3 campo-data-impressao-evolucao" data-escopo="periodo"><label class="form-label" for="inicioImpressaoEvolucao">De</label><input class="form-control" type="date" id="inicioImpressaoEvolucao" name="inicio" value="<?php echo $h($hoje); ?>" max="<?php echo $h($hoje); ?>"></div>
                        <div class="col-md-6 col-lg-3 campo-data-impressao-evolucao" data-escopo="periodo"><label class="form-label" for="fimImpressaoEvolucao">Até</label><input class="form-control" type="date" id="fimImpressaoEvolucao" name="fim" value="<?php echo $h($hoje); ?>" max="<?php echo $h($hoje); ?>"></div>
                        <div class="col-12 d-flex justify-content-end"><button class="btn btn-outline-primary" id="botaoImprimirEvolucoes" type="submit"><i class="bi bi-printer me-1" aria-hidden="true"></i>Preparar impressão</button></div>
                    </form>
                </div>
            </section>
        <?php endif; ?>

        <?php if ($podeRegistrarEvolucao): ?>
            <section class="card border-primary shadow-sm mb-4" aria-labelledby="tituloNovaEvolucao">
                <div class="card-header bg-body-tertiary"><h3 class="h5 mb-0" id="tituloNovaEvolucao">Registrar nova evolução</h3></div>
                <div class="card-body">
                    <form method="post" action="<?php echo $h(BASE_URL); ?>acoes/controllerEvolucoes.php" class="row g-3">
                        <?php echo \Abdm\Infraestrutura\Sessao::campoTokenCsrf(); ?>
                        <input type="hidden" name="acolhido_id" value="<?php echo (int)$acolhidoId; ?>">
                        <div class="col-md-4 col-lg-3"><label class="form-label" for="dataEvolucao">Data</label><input class="form-control" type="date" id="dataEvolucao" name="data" value="<?php echo $h($form['data'] ?? $hoje); ?>" max="<?php echo $h($hoje); ?>" required></div>
                        <div class="col-md-4 col-lg-3"><label class="form-label" for="horaEvolucao">Horário</label><input class="form-control" type="time" id="horaEvolucao" name="horario" value="<?php echo $h($form['horario'] ?? date('H:i')); ?>" required></div>
                        <div class="col-12"><label class="form-label" for="registroEvolucao">Registro de evolução</label><textarea class="form-control" id="registroEvolucao" name="registro" rows="5" maxlength="12000" required><?php echo $h($form['registro'] ?? ''); ?></textarea></div>
                        <div class="col-12 d-flex justify-content-end"><button class="btn btn-primary" type="submit"><i class="bi bi-check2-circle me-1" aria-hidden="true"></i>Salvar evolução</button></div>
                    </form>
                </div>
            </section>
        <?php endif; ?>

        <?php if ($podeConsultarEvolucoes): ?>
        <section aria-labelledby="tituloHistoricoEvolucoes">
            <div class="d-flex justify-content-between align-items-center mb-3"><h3 class="h4 mb-0" id="tituloHistoricoEvolucoes">Evoluções registradas</h3><span class="badge text-bg-secondary"><?php echo count($evolucoes); ?> registro(s)</span></div>
            <?php if (!$podeConsultarTodasEvolucoes): ?><p class="small text-body-secondary">Este histórico mostra somente as evoluções registradas pela sua conta.</p><?php endif; ?>
            <?php if (!$evolucoes): ?>
                <div class="alert alert-info">Nenhuma evolução corresponde aos filtros informados.</div>
            <?php else: ?>
                <div class="vstack gap-3">
                    <?php foreach ($evolucoes as $evolucao): ?>
                        <article class="card shadow-sm">
                            <div class="card-header bg-body-tertiary d-flex flex-wrap justify-content-between gap-2"><strong><?php echo $h((new DateTimeImmutable($evolucao['registrado_em']))->format('d/m/Y \à\s H:i')); ?></strong><span class="text-body-secondary"><?php echo $h($evolucao['profissional_nome']); ?></span></div>
                            <div class="card-body"><div class="text-break"><?php echo nl2br($h($evolucao['registro'])); ?></div></div>
                            <div class="card-footer bg-transparent small text-body-secondary text-center">
                                <strong><?php echo $h($evolucao['profissional_nome']); ?></strong>
                                <?php if (!empty($evolucao['atuacao_profissional'])): ?> · <?php echo $h($evolucao['atuacao_profissional']); ?><?php endif; ?>
                                <?php if (!empty($evolucao['registro_profissional'])): ?> · Registro: <?php echo $h($evolucao['registro_profissional']); ?><?php endif; ?>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
        <?php elseif (!$podeRegistrarEvolucao): ?>
            <div class="alert alert-info">Sua conta pode imprimir evoluções, mas não tem permissão para consultar o histórico nesta tela.</div>
        <?php endif; ?>
    <?php else: ?>
        <div class="alert alert-info"><i class="bi bi-info-circle me-2" aria-hidden="true"></i>Escolha um acolhido acima para abrir as opções de evolução autorizadas. A impressão das suas evoluções gerais fica disponível sem selecionar uma pessoa.</div>
    <?php endif; ?>
</main>
<script>
document.querySelectorAll('[data-evolucao-print-form]').forEach((form) => {
    const modo = form.querySelector('select[name="modo"]');
    const escopo = form.querySelector('[data-escopo-select]');
    const profissional = form.querySelector('[name="profissional"]');
    const profissionalWrap = profissional?.closest('[data-profissional-wrap]');
    const camposData = form.querySelectorAll('.campo-data-impressao-evolucao');
    const atualizarCampos = () => {
        const precisaProfissional = modo?.value === 'profissional';
        if (profissional && profissionalWrap) {
            profissionalWrap.hidden = !precisaProfissional;
            profissional.disabled = !precisaProfissional;
            profissional.required = precisaProfissional;
        }
        camposData.forEach((campo) => {
            const input = campo.querySelector('input');
            const ativo = campo.dataset.escopo === escopo?.value;
            campo.hidden = !ativo;
            input.disabled = !ativo;
            input.required = ativo;
        });
        if (modo?.tagName === 'SELECT') {
            const rotulos = {
                meus: 'Imprimir minhas evoluções deste acolhido',
                acolhido: 'Imprimir evoluções deste acolhido',
                profissional: 'Imprimir evoluções do profissional',
                todos: 'Imprimir evoluções de todos os profissionais'
            };
            const botao = form.querySelector('button[type="submit"]');
            if (botao?.lastChild) botao.lastChild.textContent = rotulos[modo.value] || 'Preparar impressão';
        }
    };
    modo?.addEventListener('change', atualizarCampos);
    escopo?.addEventListener('change', atualizarCampos);
    form.addEventListener('submit', (event) => {
        event.preventDefault();
        const parametros = new URLSearchParams(new FormData(form));
        window.abrirPreviaPdf(form.action + '?' + parametros.toString());
    });
    atualizarCampos();
});
</script>
<?php require_once ROOT_DIR . '/src/View/templates/includes/rodapeInterno.inc.php'; ?>

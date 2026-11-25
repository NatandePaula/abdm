<?php

[
    'h' => $h,
    'dataHoje' => $dataHoje,
    'escopo' => $escopo,
    'dataInicial' => $dataInicial,
    'dataFinal' => $dataFinal,
    'acolhidos' => $acolhidos,
    'acolhidosPorId' => $acolhidosPorId,
    'acolhido' => $acolhido,
    'filtroAcolhidoId' => $filtroAcolhidoId,
    'termoBusca' => $termoBusca,
    'suporteEdicaoRelatos' => $suporteEdicaoRelatos,
    'relatos' => $relatos,
    'mencoesPorRelato' => $mencoesPorRelato,
    'relato' => $relato,
    'mencao' => $mencao,
    'nomeAutorAtual' => $nomeAutorAtual,
    'conteudoFormulario' => $conteudoFormulario,
    'periodoFormulario' => $periodoFormulario,
    'dataFormulario' => $dataFormulario,
    'formCadastroAberto' => $formCadastroAberto,
    'acolhidosMarcados' => $acolhidosMarcados,
    'id' => $id,
    'edicoesRetentativa' => $edicoesRetentativa,
    'relatosPorData' => $relatosPorData,
    'rotuloPeriodo' => $rotuloPeriodo,
    'urlDiaAnterior' => $urlDiaAnterior,
    'mostrarProximoDia' => $mostrarProximoDia,
    'urlProximoDia' => $urlProximoDia,
    'urlHoje' => $urlHoje,
    'dadosAcolhidosJson' => $dadosAcolhidosJson,
] = $dadosView;

require_once ROOT_DIR . '/src/View/templates/includes/cabecalhoInterno.inc.php';
?>

<style>
    .relatorio-autocomplete { position: relative; }
    .relatorio-sugestoes {
        position: absolute; z-index: 1050; top: 100%; left: 0; right: 0; max-height: 17rem;
        overflow-y: auto; background: var(--bs-body-bg); border: 1px solid var(--bs-border-color);
        border-radius: .375rem; box-shadow: 0 .5rem 1rem rgba(0, 0, 0, .15);
    }
    .relatorio-sugestao { display: block; width: 100%; padding: .55rem .75rem; text-align: left; border: 0; background: transparent; color: var(--bs-body-color); }
    .relatorio-sugestao:hover, .relatorio-sugestao[aria-selected="true"] { background: var(--bs-tertiary-bg); }
    .relatorio-registro { border-bottom: 1px solid var(--bs-border-color); padding: 1rem 0; }
    .relatorio-registro:last-child { border-bottom: 0; }
    .relatorio-conteudo { white-space: normal; overflow-wrap: anywhere; }
    .relatorio-menu-toggle .bi-chevron-down { transition: transform .2s ease; }
    .relatorio-menu-toggle[aria-expanded="true"] .bi-chevron-down { transform: rotate(180deg); }
    .relatorio-cabecalho-impressao, .print-only-layout { display: none; }

    @media print {
        @page { size: A4; margin: 1cm 1.5cm; }
        html, body { margin: 0 !important; height: auto !important; overflow: visible !important; background: #fff !important; color: #000 !important; font: 10pt "Times New Roman", Times, serif !important; }
        body > header, .bd-mode-toggle, #bd-theme, .no-print, footer, .modal { display: none !important; }
        body > .container.mt-4 { display: none !important; }
        .relatorio-container { width: 100% !important; max-width: none !important; margin: 0 !important; padding: 0 !important; }
        #resultadoOcorrencias { display: none !important; }
        .print-only-layout { display: table !important; width: 100% !important; border-collapse: collapse !important; table-layout: fixed; color: #000 !important; }
        .print-only-layout td { color: #000 !important; }
        .print-only-layout tbody td { padding: 4px 0; border: 0; vertical-align: top; }
        .print-report-title { text-align: center; font-size: 18pt; font-weight: bold; margin: 8px 0 4px; }
        .print-report-summary { text-align: center; font-size: 9pt; margin: 0 0 12px; }
        .print-date-heading { font-size: 13pt; font-weight: bold; border-bottom: 1px solid #333; padding-bottom: 4px; margin: 14px 0 8px; page-break-after: avoid; }
        .print-shift-heading { font-size: 10pt; font-weight: bold; text-transform: uppercase; margin: 8px 0 4px; page-break-after: avoid; }
        .print-occurrence { border-bottom: 1px solid #999; padding: 7px 0 9px; }
        .print-occurrence-meta { width: 100%; margin-bottom: 4px; font-size: 9pt; }
        .print-occurrence-content { line-height: 1.4; text-align: justify; overflow-wrap: anywhere; }
        .print-occurrence-mentions { margin-top: 4px; font-size: 9pt; }
        .print-empty { text-align: center; padding: 18px; border: 1px solid #999; }
        .print-only-layout .footer-content { color: #555 !important; }
        .print-main-layout tfoot { display: table-footer-group !important; }
        .text-body-secondary, .text-primary { color: #000 !important; }
    }
</style>

<main class="container my-5 relatorio-container">
    <div class="relatorio-cabecalho-impressao" id="cabecalhoImpressao">
        <div class="d-flex justify-content-between align-items-center gap-3">
            <img src="<?php echo $h(BASE_URL . 'assets/imagens/Logo2 colorida.png'); ?>" alt="Identificação institucional">
            <div class="text-end">
                <h1 class="h3 mb-1">Relatório de Ocorrências</h1>
                <div><?php echo $h($rotuloPeriodo); ?></div>
            </div>
        </div>
        <div class="small mt-2">
            Pesquisa: <?php echo $termoBusca !== '' ? '“' . $h($termoBusca) . '”' : 'sem termo textual'; ?>
            <?php if ($filtroAcolhidoId !== null): ?>
                · Acolhido: <?php echo $h($acolhidosPorId[$filtroAcolhidoId]['nome']); ?>
            <?php endif; ?>
            · <?php echo count($relatos); ?> ocorrência(s)
        </div>
        <div class="small mt-1">Impresso por <?php echo $h($nomeAutorAtual); ?> em <?php echo $h(date('d/m/Y H:i')); ?></div>
    </div>

    <section class="card shadow-sm border-0 mb-4 no-print" aria-labelledby="tituloRelatorio">
        <div class="card-header bg-primary text-white d-flex flex-wrap justify-content-between align-items-center gap-2">
            <h1 class="h3 my-2 mb-0" id="tituloRelatorio"><i class="bi bi-journal-text me-2" aria-hidden="true"></i>Relatório de Ocorrências</h1>
            <span class="badge text-bg-light"><?php echo $h($rotuloPeriodo); ?></span>
        </div>
        <div class="card-body">
            <p class="mb-0 text-body-secondary">Registro cronológico dos fatos e providências dos turnos diurno e noturno. Cada ocorrência identifica o responsável pelo lançamento e o respectivo horário.</p>
        </div>
    </section>

    <section class="card shadow-sm mb-4 no-print" aria-labelledby="filtrosOcorrencias">
        <div class="card-header bg-body-tertiary">
            <h2 class="h5 mb-0" id="filtrosOcorrencias">
                <button class="btn btn-outline-primary relatorio-menu-toggle collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#menuConsultaOcorrencias" aria-expanded="false" aria-controls="menuConsultaOcorrencias">
                    <i class="bi bi-funnel me-2" aria-hidden="true"></i>Consultar ocorrências <i class="bi bi-chevron-down ms-1" aria-hidden="true"></i>
                </button>
            </h2>
        </div>
        <div class="collapse" id="menuConsultaOcorrencias">
        <div class="card-body">
            <form method="get" action="<?php echo $h(BASE_URL); ?>views/relatorio.php" id="filtroRelatorio" class="row g-3 align-items-end">
                <div class="col-sm-6 col-lg-2">
                    <label class="form-label" for="escopoRelatorio">Período da consulta</label>
                    <select class="form-select" id="escopoRelatorio" name="escopo">
                        <option value="dia" <?php echo $escopo === 'dia' ? 'selected' : ''; ?>>Um dia</option>
                        <option value="periodo" <?php echo $escopo === 'periodo' ? 'selected' : ''; ?>>Intervalo</option>
                        <option value="todos" <?php echo $escopo === 'todos' ? 'selected' : ''; ?>>Todo o período</option>
                    </select>
                </div>
                <div class="col-sm-6 col-lg-2 campo-data-consulta" data-escopo="dia">
                    <label class="form-label" for="dataRelatorio">Data</label>
                    <input class="form-control" type="date" id="dataRelatorio" name="data" value="<?php echo $h($dataInicial); ?>" max="<?php echo $h($dataHoje); ?>">
                </div>
                <div class="col-sm-6 col-lg-2 campo-data-consulta" data-escopo="periodo">
                    <label class="form-label" for="inicioRelatorio">De</label>
                    <input class="form-control" type="date" id="inicioRelatorio" name="inicio" value="<?php echo $h($dataInicial); ?>" max="<?php echo $h($dataHoje); ?>">
                </div>
                <div class="col-sm-6 col-lg-2 campo-data-consulta" data-escopo="periodo">
                    <label class="form-label" for="fimRelatorio">At&eacute;</label>
                    <input class="form-control" type="date" id="fimRelatorio" name="fim" value="<?php echo $h($dataFinal); ?>" max="<?php echo $h($dataHoje); ?>">
                </div>
                <div class="col-sm-6 col-lg-3">
                    <label class="form-label" for="termoRelatorio">Termo no texto</label>
                    <input class="form-control" type="search" id="termoRelatorio" name="termo" maxlength="120" value="<?php echo $h($termoBusca); ?>" placeholder="Pesquisar nos relatos">
                </div>
                <div class="col-sm-6 col-lg-3">
                    <label class="form-label" for="acolhidoRelatorio">Acolhido mencionado</label>
                    <select class="form-select" id="acolhidoRelatorio" name="acolhido">
                        <option value="">Todos</option>
                        <?php foreach ($acolhidos as $acolhido): ?>
                            <option value="<?php echo (int)$acolhido['id']; ?>" <?php echo $filtroAcolhidoId === (int)$acolhido['id'] ? 'selected' : ''; ?>><?php echo $h($acolhido['nome']); ?><?php echo $acolhido['status'] === 'ativo' ? '' : ' (desligado)'; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12 d-flex flex-wrap justify-content-end gap-2">
                    <a class="btn btn-outline-secondary" href="<?php echo $h(BASE_URL); ?>views/relatorio.php"><i class="bi bi-arrow-counterclockwise me-1" aria-hidden="true"></i>Limpar filtros</a>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-search me-1" aria-hidden="true"></i>Pesquisar</button>
                </div>
            </form>
            <p class="form-text mb-0 mt-3">Pesquise por um dia, um intervalo ou todo o histórico. A busca por termo considera o texto integral dos relatos; para consultar um único dia, selecione “Um dia”.</p>
        </div>
        </div>
    </section>

    <section class="card shadow-sm border-primary mb-4 no-print" id="novoRelato" aria-labelledby="tituloNovoRelato">
        <div class="card-header bg-body-tertiary">
            <h2 class="h4 mb-0" id="tituloNovoRelato">
                <button class="btn btn-outline-primary relatorio-menu-toggle<?php echo $formCadastroAberto ? '' : ' collapsed'; ?>" type="button" data-bs-toggle="collapse" data-bs-target="#formularioNovoRelato" aria-expanded="<?php echo $formCadastroAberto ? 'true' : 'false'; ?>" aria-controls="formularioNovoRelato">
                    <i class="bi bi-journal-plus me-2" aria-hidden="true"></i>Registrar ocorrência <i class="bi bi-chevron-down ms-1" aria-hidden="true"></i>
                </button>
            </h2>
        </div>
        <div class="collapse<?php echo $formCadastroAberto ? ' show' : ''; ?>" id="formularioNovoRelato">
        <div class="card-body">
            <p class="small text-body-secondary">Respons&aacute;vel pelo registro: <strong><?php echo $h($nomeAutorAtual); ?></strong>. O hor&aacute;rio real do lan&ccedil;amento ser&aacute; registrado automaticamente.</p>
            <form method="post" action="<?php echo $h(BASE_URL); ?>acoes/controllerRelatorio.php" class="vstack gap-3">
                <?php echo \Abdm\Infraestrutura\Sessao::campoTokenCsrf(); ?>
                <input type="hidden" name="acao" value="criar_relato">
                <div class="row g-3">
                    <div class="col-md-4 col-lg-3">
                        <label class="form-label" for="dataRelato">Data da ocorr&ecirc;ncia</label>
                        <input class="form-control" type="date" id="dataRelato" name="data_relato" value="<?php echo $h($dataFormulario); ?>" max="<?php echo $h($dataHoje); ?>" required>
                        <div class="form-text">Preenchida com hoje. Se o sistema ou a internet ficaram indispon&iacute;veis, selecione a data anterior. O hor&aacute;rio do lan&ccedil;amento fica registrado separadamente.</div>
                    </div>
                    <div class="col-md-4 col-lg-3">
                        <label class="form-label" for="periodoRelato">Turno da ocorr&ecirc;ncia</label>
                        <select class="form-select" id="periodoRelato" name="periodo" required>
                            <option value="dia" <?php echo $periodoFormulario === 'dia' ? 'selected' : ''; ?>>Diurno</option>
                            <option value="noite" <?php echo $periodoFormulario === 'noite' ? 'selected' : ''; ?>>Noturno</option>
                        </select>
                    </div>
                </div>
                <div class="relatorio-autocomplete" data-relatorio-autocomplete>
                    <label class="form-label" for="conteudoRelato">Descri&ccedil;&atilde;o objetiva da ocorr&ecirc;ncia</label>
                    <textarea class="form-control" id="conteudoRelato" name="conteudo" rows="6" maxlength="10000" required aria-describedby="ajudaMencao" placeholder="Descreva os fatos de forma objetiva, as providências adotadas e os encaminhamentos realizados."><?php echo $h($conteudoFormulario); ?></textarea>
                    <div class="relatorio-sugestoes" id="sugestoesAcolhidos" role="listbox" aria-label="Sugestões de acolhidos" hidden></div>
                    <div class="form-text" id="ajudaMencao">Para mencionar um acolhido, digite <strong>@</strong> e parte do nome, depois selecione a pessoa na lista. Exemplo: <code>@Ana Pereira</code>. As men&ccedil;&otilde;es ficar&atilde;o vinculadas para consulta.</div>
                    <div class="d-flex flex-wrap gap-2 mt-2" id="acolhidosMencionados" data-lista-mencoes aria-live="polite"></div>
                    <div id="idsAcolhidosMencionados" data-ids-mencoes>
                        <?php foreach ($acolhidosMarcados as $acolhidoId): ?>
                            <input type="hidden" name="acolhidos[]" value="<?php echo (int)$acolhidoId; ?>" data-mencao-id="<?php echo (int)$acolhidoId; ?>">
                        <?php endforeach; ?>
        </div>
                </div>
                <div class="d-flex justify-content-end">
                    <button class="btn btn-primary" type="submit"><i class="bi bi-journal-check me-1" aria-hidden="true"></i>Registrar ocorrência</button>
                </div>
            </form>
        </div>
        </div>
    </section>

    <section id="resultadoOcorrencias" aria-labelledby="tituloResultados">
        <?php if (!$suporteEdicaoRelatos): ?>
            <div class="alert alert-warning no-print" role="status">A edição de ocorrências ainda não está habilitada porque a atualização do banco de dados está pendente.</div>
        <?php endif; ?>
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <div>
                <h2 class="h3 mb-1" id="tituloResultados">Ocorr&ecirc;ncias localizadas</h2>
                <p class="text-body-secondary mb-0"><?php echo $h($rotuloPeriodo); ?><?php if ($termoBusca !== ''): ?> · termo: “<?php echo $h($termoBusca); ?>”<?php endif; ?></p>
            </div>
            <div class="d-flex align-items-center gap-2">
                <a class="btn btn-outline-secondary no-print" href="<?php echo $h($urlDiaAnterior); ?>"><i class="bi bi-calendar-minus me-1" aria-hidden="true"></i>Dia anterior</a>
                <?php if ($mostrarProximoDia): ?>
                    <a class="btn btn-outline-secondary no-print" href="<?php echo $h($urlProximoDia); ?>"><i class="bi bi-calendar-plus me-1" aria-hidden="true"></i>Pr&oacute;ximo dia</a>
                <?php endif; ?>
                <?php if ($escopo !== 'dia' || $dataInicial !== $dataHoje): ?>
                    <a class="btn btn-outline-secondary no-print" href="<?php echo $h($urlHoje); ?>"><i class="bi bi-calendar-check me-1" aria-hidden="true"></i>Hoje</a>
                <?php endif; ?>
                <span class="badge text-bg-secondary"><?php echo count($relatos); ?> registro(s)</span>
                <?php if ($relatos): ?>
                    <button type="button" id="imprimirRelatorio" class="btn btn-outline-primary no-print" data-pdf-url="<?php echo $h(BASE_URL . 'views/gerar_relatorio_pdf.php'); ?>"><i class="bi bi-printer me-1" aria-hidden="true"></i>Visualizar e imprimir resultados</button>
                <?php endif; ?>
            </div>
        </div>

        <?php if (!$relatos): ?>
            <div class="alert alert-info">Nenhuma ocorr&ecirc;ncia corresponde aos filtros informados.</div>
        <?php else: ?>
            <?php foreach ($relatosPorData as $dataGrupo => $turnos): ?>
                <section class="card shadow-sm mb-4" aria-label="Ocorrências de <?php echo $h((new DateTimeImmutable($dataGrupo))->format('d/m/Y')); ?>">
                    <div class="card-header bg-body-tertiary d-flex justify-content-between align-items-center">
                        <h3 class="h5 mb-0"><i class="bi bi-calendar-event me-2" aria-hidden="true"></i><?php echo $h((new DateTimeImmutable($dataGrupo))->format('d/m/Y')); ?></h3>
                        <span class="small text-body-secondary">Data de refer&ecirc;ncia</span>
        </div>
                    <div class="card-body py-2">
                        <?php foreach (['dia' => 'Turno diurno', 'noite' => 'Turno noturno'] as $codigoTurno => $nomeTurno): ?>
                            <?php if (!empty($turnos[$codigoTurno])): ?>
                                <section class="mb-3">
                                    <h4 class="h6 text-uppercase border-bottom pb-2 mb-2"><?php echo $h($nomeTurno); ?></h4>
                                    <?php foreach ($turnos[$codigoTurno] as $relato): ?>
                                        <?php
                                        $idRelato = (int)$relato['idRelato'];
                                        $dataHora = strtotime($relato['criado_em']);
                                        $tempoDesdeLancamento = microtime(true) - (float)$dataHora;
                                        $podeEditarRelato = $suporteEdicaoRelatos
                                            && (int)$relato['autorLogin_idLogin'] === (int)$_SESSION['id_login']
                                            && $tempoDesdeLancamento >= 0
                                            && $tempoDesdeLancamento <= 86400;
                                        $mencoesRelato = $mencoesPorRelato[$idRelato] ?? [];
                                        $edicaoRetentativa = $edicoesRetentativa[$idRelato] ?? null;
                                        $edicaoEstaAberta = is_array($edicaoRetentativa);
                                        $periodoEdicao = is_array($edicaoRetentativa) && in_array($edicaoRetentativa['periodo'] ?? '', ['dia', 'noite'], true)
                                            ? $edicaoRetentativa['periodo']
                                            : (string)$relato['periodo'];
                                        $conteudoEdicao = is_array($edicaoRetentativa) && is_string($edicaoRetentativa['conteudo'] ?? null)
                                            ? $edicaoRetentativa['conteudo']
                                            : (string)$relato['conteudo'];
                                        $idsMencoesExistentes = array_map(static fn(array $mencao): int => (int)$mencao['id'], $mencoesRelato);
                                        $idsEdicao = $edicaoEstaAberta && is_array($edicaoRetentativa['acolhidos'] ?? null)
                                            ? array_values(array_filter(
                                                array_map('intval', $edicaoRetentativa['acolhidos']),
                                                static fn(int $id): bool => isset($acolhidosPorId[$id]) || in_array($id, $idsMencoesExistentes, true)
                                            ))
                                            : $idsMencoesExistentes;
                                        $conteudoExibicao = \Abdm\View\TextoRelatorio::formatarConteudoRelatorioHtml((string)$relato['conteudo'], $mencoesPorRelato[$idRelato] ?? [], $termoBusca);
                                        ?>
                                        <article class="relatorio-registro" id="relato-<?php echo $idRelato; ?>">
                                            <div class="d-flex flex-wrap justify-content-between gap-2 mb-2">
                                                <div class="small">
                                                    <strong>Registro n.&ordm; <?php echo $idRelato; ?></strong>
                                                    <span class="text-body-secondary ms-2">Respons&aacute;vel: <strong><?php echo $h($relato['nome_autor']); ?></strong></span>
                                                </div>
                                                <div class="d-flex flex-column align-items-end gap-1">
                                                    <time class="small text-body-secondary" datetime="<?php echo $h(date(DATE_ATOM, $dataHora)); ?>">
                                                        Lan&ccedil;ado em <?php echo $h(date('d/m/Y \à\s H:i', $dataHora)); ?>
                                                    </time>
                                                    <?php if (!empty($relato['editado_em'])): ?>
                                                        <?php $dataEdicao = strtotime($relato['editado_em']); ?>
                                                        <span class="small text-body-secondary">Editado pelo autor em <?php echo $h(date('d/m/Y \à\s H:i', $dataEdicao)); ?></span>
                                                    <?php endif; ?>
                                                    <?php if ($podeEditarRelato): ?>
                                                        <button class="btn btn-sm btn-outline-secondary no-print <?php echo $edicaoEstaAberta ? '' : 'collapsed'; ?>" type="button" data-bs-toggle="collapse" data-bs-target="#editarRelato<?php echo $idRelato; ?>" aria-expanded="<?php echo $edicaoEstaAberta ? 'true' : 'false'; ?>" aria-controls="editarRelato<?php echo $idRelato; ?>"><i class="bi bi-pencil-square me-1" aria-hidden="true"></i>Editar registro</button>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            <?php if ($podeEditarRelato): ?>
                                                <div class="collapse <?php echo $edicaoEstaAberta ? 'show' : ''; ?> no-print mb-3" id="editarRelato<?php echo $idRelato; ?>">
                                                    <form method="post" action="<?php echo $h(BASE_URL); ?>acoes/controllerRelatorio.php" class="border rounded p-3 vstack gap-3">
                                                        <?php echo \Abdm\Infraestrutura\Sessao::campoTokenCsrf(); ?>
                                                        <input type="hidden" name="acao" value="editar_relato">
                                                        <input type="hidden" name="relato_id" value="<?php echo $idRelato; ?>">
                                                        <div class="col-md-4 col-lg-3">
                                                            <label class="form-label" for="periodoRelatoEdicao<?php echo $idRelato; ?>">Turno da ocorr&ecirc;ncia</label>
                                                            <select class="form-select" id="periodoRelatoEdicao<?php echo $idRelato; ?>" name="periodo" required>
                                                                <option value="dia" <?php echo $periodoEdicao === 'dia' ? 'selected' : ''; ?>>Diurno</option>
                                                                <option value="noite" <?php echo $periodoEdicao === 'noite' ? 'selected' : ''; ?>>Noturno</option>
                                                            </select>
                                                        </div>
                                                        <div class="relatorio-autocomplete" data-relatorio-autocomplete>
                                                            <label class="form-label" for="conteudoRelatoEdicao<?php echo $idRelato; ?>">Descri&ccedil;&atilde;o da ocorr&ecirc;ncia</label>
                                                            <textarea class="form-control" id="conteudoRelatoEdicao<?php echo $idRelato; ?>" name="conteudo" rows="5" maxlength="10000" required><?php echo $h($conteudoEdicao); ?></textarea>
                                                            <div class="relatorio-sugestoes" role="listbox" aria-label="Sugestões de acolhidos" hidden></div>
                                                            <div class="form-text">Digite <strong>@</strong> e parte do nome para adicionar ou ajustar uma men&ccedil;&atilde;o.</div>
                                                            <div class="d-flex flex-wrap gap-2 mt-2" data-lista-mencoes aria-live="polite"></div>
                                                            <div data-ids-mencoes>
                                                                <?php foreach ($idsEdicao as $acolhidoIdEdicao): ?>
                                                                    <input type="hidden" name="acolhidos[]" value="<?php echo (int)$acolhidoIdEdicao; ?>" data-mencao-id="<?php echo (int)$acolhidoIdEdicao; ?>">
                                                                <?php endforeach; ?>
                                                            </div>
                                                        </div>
                                                        <div class="d-flex justify-content-end gap-2">
                                                            <button class="btn btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#editarRelato<?php echo $idRelato; ?>">Cancelar</button>
                                                            <button class="btn btn-primary" type="submit"><i class="bi bi-check2 me-1" aria-hidden="true"></i>Salvar altera&ccedil;&otilde;es</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            <?php endif; ?>
                                            <div class="relatorio-conteudo"><?php echo $conteudoExibicao; ?></div>
                                            <?php if (!empty($mencoesRelato)): ?>
                                                <div class="d-flex flex-wrap align-items-center gap-1 mt-2">
                                                    <span class="small text-body-secondary me-1">Acolhidos mencionados:</span>
                                                    <?php foreach ($mencoesRelato as $mencao): ?>
                                                        <span class="badge text-bg-light border"><strong><?php echo $h($mencao['nome']); ?></strong><?php echo $mencao['status'] === 'ativo' ? '' : ' (desligado)'; ?></span>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php endif; ?>
                                        </article>
                                    <?php endforeach; ?>
                                </section>
                            <?php endif; ?>
                        <?php endforeach; ?>
        </div>
                </section>
            <?php endforeach; ?>
        <?php endif; ?>
    </section>

    <table class="print-main-layout print-only-layout" id="layoutImpressao">
        <thead>
            <tr>
                <td>
                    <div class="print-header-img">
            <img src="<?php echo $h(BASE_URL . 'assets/imagens/cabecalho.jpg'); ?>" alt="Cabeçalho institucional">
        </div>
                </td>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    <h1 class="print-report-title">Relatório de Ocorrências</h1>
                    <p class="print-report-summary">
                        <?php echo $h($rotuloPeriodo); ?> · <?php echo count($relatos); ?> ocorrência(s)
                        <?php if ($termoBusca !== ''): ?> · Termo: “<?php echo $h($termoBusca); ?>”<?php endif; ?>
                        <?php if ($filtroAcolhidoId !== null): ?> · Acolhido: <?php echo $h($acolhidosPorId[$filtroAcolhidoId]['nome']); ?><?php endif; ?>
                    </p>
                </td>
            </tr>
            <?php if (!$relatos): ?>
                <tr><td><div class="print-empty">Nenhuma ocorrência encontrada para os filtros informados.</div></td></tr>
            <?php else: ?>
                <?php foreach ($relatosPorData as $dataGrupo => $turnos): ?>
                    <tr>
                        <td>
                            <h2 class="print-date-heading">Data de referência: <?php echo $h((new DateTimeImmutable($dataGrupo))->format('d/m/Y')); ?></h2>
                            <?php foreach (['dia' => 'Turno diurno', 'noite' => 'Turno noturno'] as $codigoTurno => $nomeTurno): ?>
                                <?php if (!empty($turnos[$codigoTurno])): ?>
                                    <h3 class="print-shift-heading"><?php echo $h($nomeTurno); ?></h3>
                                    <?php foreach ($turnos[$codigoTurno] as $relato): ?>
                                        <?php
                                        $idRelato = (int)$relato['idRelato'];
                                        $dataHora = strtotime($relato['criado_em']);
                                        ?>
                                        <div class="print-occurrence">
                                            <table class="print-occurrence-meta">
                                                <tr>
                                                    <td><strong>Registro n.º <?php echo $idRelato; ?></strong></td>
                                                    <td align="right">Lançado em <?php echo $h(date('d/m/Y \à\s H:i', $dataHora)); ?></td>
                                                </tr>
                                                <tr><td colspan="2"><strong>Responsável:</strong> <strong><?php echo $h($relato['nome_autor']); ?></strong></td></tr>
                                                <?php if (!empty($relato['editado_em'])): ?>
                                                    <?php $dataEdicaoImpressao = strtotime($relato['editado_em']); ?>
                                                    <tr><td colspan="2">Editado pelo autor em <?php echo $h(date('d/m/Y \à\s H:i', $dataEdicaoImpressao)); ?></td></tr>
                                                <?php endif; ?>
                                            </table>
                                            <div class="print-occurrence-content"><?php echo \Abdm\View\TextoRelatorio::formatarConteudoRelatorioHtml((string)$relato['conteudo'], $mencoesPorRelato[$idRelato] ?? [], $termoBusca); ?></div>
                                            <?php if (!empty($mencoesPorRelato[$idRelato])): ?>
                                                <div class="print-occurrence-mentions"><strong>Acolhidos mencionados:</strong>
                                                    <?php foreach ($mencoesPorRelato[$idRelato] as $indiceMencao => $mencao): ?><?php echo $indiceMencao > 0 ? ', ' : ''; ?><strong><?php echo $h($mencao['nome']); ?></strong><?php echo $mencao['status'] === 'ativo' ? '' : ' (desligado)'; ?><?php endforeach; ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
        <tfoot>
            <tr>
                <td>
        <div class="print-footer-img">
            <img src="<?php echo $h(BASE_URL . 'assets/imagens/rodape.jpg'); ?>" alt="Rodapé institucional">
        </div>
        <div class="footer-content">Impresso por <?php echo $h($nomeAutorAtual); ?> · <?php echo $h(date('d/m/Y H:i:s')); ?></div>

                </td>
            </tr>
        </tfoot>
    </table>
</main>

<script type="application/json" id="dadosAcolhidosRelatorio"><?php echo $dadosAcolhidosJson; ?></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const botaoImprimir = document.getElementById('imprimirRelatorio');
    if (botaoImprimir) {
        botaoImprimir.addEventListener('click', () => {
            const urlImpressao = new URL(botaoImprimir.dataset.pdfUrl, window.location.href);
            urlImpressao.search = window.location.search;
            window.abrirPreviaPdf(urlImpressao.toString());
        });
    }

    const filtro = document.getElementById('filtroRelatorio');
    const escopo = document.getElementById('escopoRelatorio');
    const camposData = document.querySelectorAll('.campo-data-consulta');
    const atualizarCamposData = () => {
        camposData.forEach((campo) => {
            const ativo = campo.dataset.escopo === escopo.value;
            campo.hidden = !ativo;
            campo.querySelector('input').disabled = !ativo;
        });
    };
    if (filtro && escopo) {
        escopo.addEventListener('change', atualizarCamposData);
        atualizarCamposData();
    }

    const dados = document.getElementById('dadosAcolhidosRelatorio');
    if (!dados) return;

    const acolhidos = JSON.parse(dados.textContent || '[]');
    const normalizar = (texto) => texto.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLocaleLowerCase('pt-BR');

    document.querySelectorAll('[data-relatorio-autocomplete]').forEach((widget) => {
        const textarea = widget.querySelector('textarea');
        const sugestoes = widget.querySelector('.relatorio-sugestoes');
        const listaMencoes = widget.querySelector('[data-lista-mencoes]');
        const idsMencoes = widget.querySelector('[data-ids-mencoes]');
        if (!textarea || !sugestoes || !listaMencoes || !idsMencoes) return;

        let opcoesVisiveis = [];
        let opcaoAtiva = -1;
        let posicaoMencao = null;

        const adicionarMencaoVisual = (acolhido) => {
            if (!idsMencoes.querySelector(`[data-mencao-id="${acolhido.id}"]`)) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'acolhidos[]';
                input.value = acolhido.id;
                input.dataset.mencaoId = acolhido.id;
                idsMencoes.appendChild(input);
            }
            if (!listaMencoes.querySelector(`[data-chip-id="${acolhido.id}"]`)) {
                const chip = document.createElement('span');
                chip.className = 'badge text-bg-info';
                chip.dataset.chipId = acolhido.id;
                chip.textContent = `@${acolhido.nome}${acolhido.status === 'ativo' ? '' : ' (desligado)'}`;
                listaMencoes.appendChild(chip);
            }
        };

        const ocultarSugestoes = () => {
            sugestoes.hidden = true;
            sugestoes.replaceChildren();
            opcoesVisiveis = [];
            opcaoAtiva = -1;
        };

        const selecionarAcolhido = (acolhido) => {
            if (!posicaoMencao) return;
            const {inicio, fim} = posicaoMencao;
            const textoAtual = textarea.value;
            const proximoCaractere = textoAtual[fim];
            const precisaEspaco = !proximoCaractere || /[\p{L}\p{N}_]/u.test(proximoCaractere);
            const substituto = `@${acolhido.nome}${precisaEspaco ? ' ' : ''}`;
            textarea.setRangeText(substituto, inicio, fim, 'end');
            adicionarMencaoVisual(acolhido);
            textarea.focus();
            ocultarSugestoes();
        };

        const atualizarSugestoes = () => {
            const cursor = textarea.selectionStart;
            const antesCursor = textarea.value.slice(0, cursor);
            const posicaoArroba = antesCursor.lastIndexOf('@');
            if (posicaoArroba < 0 || /[\r\n@]/.test(antesCursor.slice(posicaoArroba + 1))) {
                ocultarSugestoes();
                return;
            }
            const textoAntes = antesCursor.slice(0, posicaoArroba);
            if (textoAntes && /[\p{L}\p{N}_]$/u.test(textoAntes)) {
                ocultarSugestoes();
                return;
            }
            const fragmento = antesCursor.slice(posicaoArroba + 1).trimStart();
            if (!fragmento || fragmento.length > 100) {
                ocultarSugestoes();
                return;
            }
            const buscaNormalizada = normalizar(fragmento);
            opcoesVisiveis = acolhidos.filter((acolhido) => normalizar(acolhido.nome).includes(buscaNormalizada)).slice(0, 8);
            if (!opcoesVisiveis.length) {
                ocultarSugestoes();
                return;
            }
            posicaoMencao = {inicio: posicaoArroba, fim: cursor};
            opcaoAtiva = -1;
            sugestoes.replaceChildren();
            opcoesVisiveis.forEach((acolhido, indice) => {
                const botao = document.createElement('button');
                botao.type = 'button';
                botao.className = 'relatorio-sugestao';
                botao.setAttribute('role', 'option');
                botao.dataset.indice = indice;
                botao.textContent = `${acolhido.nome}${acolhido.status === 'ativo' ? '' : ' (desligado)'}`;
                botao.addEventListener('mousedown', (evento) => {
                    evento.preventDefault();
                    selecionarAcolhido(acolhido);
                });
                sugestoes.appendChild(botao);
            });
            sugestoes.hidden = false;
        };

        const ativarOpcao = (indice) => {
            opcaoAtiva = (indice + opcoesVisiveis.length) % opcoesVisiveis.length;
            sugestoes.querySelectorAll('.relatorio-sugestao').forEach((opcao, index) => {
                opcao.setAttribute('aria-selected', index === opcaoAtiva ? 'true' : 'false');
            });
        };

        textarea.addEventListener('input', atualizarSugestoes);
        textarea.addEventListener('click', atualizarSugestoes);
        textarea.addEventListener('keyup', (evento) => {
            if (!['ArrowDown', 'ArrowUp', 'Enter', 'Escape'].includes(evento.key)) atualizarSugestoes();
        });
        textarea.addEventListener('keydown', (evento) => {
            if (sugestoes.hidden) return;
            if (evento.key === 'ArrowDown') {
                evento.preventDefault();
                ativarOpcao(opcaoAtiva + 1);
            } else if (evento.key === 'ArrowUp') {
                evento.preventDefault();
                ativarOpcao(opcaoAtiva <= 0 ? opcoesVisiveis.length - 1 : opcaoAtiva - 1);
            } else if (evento.key === 'Enter' && opcaoAtiva >= 0) {
                evento.preventDefault();
                selecionarAcolhido(opcoesVisiveis[opcaoAtiva]);
            } else if (evento.key === 'Escape') {
                ocultarSugestoes();
            }
        });
        textarea.addEventListener('blur', () => setTimeout(ocultarSugestoes, 120));

        acolhidos.forEach((acolhido) => {
            if (idsMencoes.querySelector(`[data-mencao-id="${acolhido.id}"]`)) adicionarMencaoVisual(acolhido);
        });
    });
});
</script>

<?php require_once ROOT_DIR . '/src/View/templates/includes/rodapeInterno.inc.php'; ?>

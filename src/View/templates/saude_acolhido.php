<?php
$controleAcesso ??= new \Abdm\Infraestrutura\ControleAcesso($pdo);
$controleAcesso->exigirUsuarioAutenticado();
$controleAcesso->exigirPermissao('saude_acolhido');

[
    'h' => $h,
    'valor' => $valor,
    'hoje' => $hoje,
    'tiposSaude' => $tiposSaude,
    'acolhidos' => $acolhidos,
    'acolhido' => $acolhido,
    'acolhidoFiltro' => $acolhidoFiltro,
    'inicio' => $inicio,
    'fim' => $fim,
    'tipoFiltro' => $tipoFiltro,
    'termo' => $termo,
    'flash' => $flash,
    'formAcolhidoId' => $formAcolhidoId,
    'formTipo' => $formTipo,
    'formData' => $formData,
    'formTitulo' => $formTitulo,
    'formProfissional' => $formProfissional,
    'formDescricao' => $formDescricao,
    'tabelaSaudeDisponivel' => $tabelaSaudeDisponivel,
    'registrosSaude' => $registrosSaude,
    'registro' => $registro,
    'detalhes' => $detalhes,
] = (new \Abdm\Controller\TelasSaudeController($pdo))->registros($controleAcesso);

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
        <div class="card-header bg-primary text-white"><h1 class="h3 mb-0"><i class="bi bi-heart-pulse me-2" aria-hidden="true"></i>Saúde dos acolhidos</h1></div>
        <div class="card-body"><p class="mb-0 text-body-secondary">Linha do tempo do acolhido com consultas, exames, alterações comportamentais e mudanças nas prescrições e nos medicamentos.</p></div>
    </section>

    <?php if (!$tabelaSaudeDisponivel): ?>
        <div class="alert alert-warning">O histórico de saúde não está disponível nesta instalação. Solicite suporte ao administrador.</div>
    <?php else: ?>
        <section class="card border-primary shadow-sm mb-4" aria-labelledby="tituloRegistroSaude">
            <div class="card-header bg-body-tertiary"><h2 class="h5 mb-0" id="tituloRegistroSaude">Registrar consulta, exame ou alteração comportamental</h2></div>
            <div class="card-body">
                <p class="small text-body-secondary">Novas prescrições, alterações efetivas, suspensões, reativações, finalizações e remoções são registradas na linha do tempo.</p>
                <form method="post" action="<?php echo $h(BASE_URL); ?>acoes/controllerSaudeAcolhido.php" class="row g-3">
                    <?php echo \Abdm\Infraestrutura\Sessao::campoTokenCsrf(); ?>
                    <div class="col-md-6 col-lg-4">
                        <label class="form-label" for="acolhidoSaudeNovo">Acolhido</label>
                        <select class="form-select" id="acolhidoSaudeNovo" name="acolhido_id" required>
                            <option value="">Selecione</option>
                            <?php foreach ($acolhidos as $acolhido): ?>
                                <option value="<?php echo (int)$acolhido['id']; ?>" <?php echo $formAcolhidoId === (int)$acolhido['id'] ? 'selected' : ''; ?>><?php echo $h($acolhido['nome']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <label class="form-label" for="tipoSaudeNovo">Tipo de registro</label>
                        <select class="form-select" id="tipoSaudeNovo" name="tipo" required>
                            <?php foreach (['consulta' => 'Consulta médica', 'exame' => 'Exame', 'comportamento' => 'Alteração comportamental'] as $codigo => $rotulo): ?>
                                <option value="<?php echo $h($codigo); ?>" <?php echo $formTipo === $codigo ? 'selected' : ''; ?>><?php echo $h($rotulo); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6 col-lg-2">
                        <label class="form-label" for="dataEventoSaude">Data do evento</label>
                        <input class="form-control" type="date" id="dataEventoSaude" name="data_evento" value="<?php echo $h($formData); ?>" max="<?php echo $h($hoje); ?>" required>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <label class="form-label" for="profissionalSaude">Profissional ou local <span class="text-body-secondary">(opcional)</span></label>
                        <input class="form-control" id="profissionalSaude" name="profissional" maxlength="180" value="<?php echo $h($formProfissional); ?>" placeholder="Médico, laboratório ou local">
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="tituloSaude">Título / especialidade / exame</label>
                        <input class="form-control" id="tituloSaude" name="titulo" maxlength="180" value="<?php echo $h($formTitulo); ?>" required placeholder="Ex.: consulta psiquiátrica, hemograma, alteração do sono">
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="descricaoSaude">Relato e encaminhamentos</label>
                        <textarea class="form-control" id="descricaoSaude" name="descricao" rows="5" maxlength="12000" required placeholder="Registre a consulta, o resultado do exame ou o comportamento observado e as providências adotadas."><?php echo $h($formDescricao); ?></textarea>
                    </div>
                    <div class="col-12 d-flex justify-content-end"><button class="btn btn-primary" type="submit"><i class="bi bi-check2-circle me-1" aria-hidden="true"></i>Salvar registro</button></div>
                </form>
            </div>
        </section>
    <?php endif; ?>

    <section class="card shadow-sm mb-4" aria-labelledby="tituloFiltroSaude">
        <div class="card-header bg-body-tertiary"><h2 class="h5 mb-0" id="tituloFiltroSaude">Consultar histórico</h2></div>
        <div class="card-body">
            <form method="get" id="filtroHistoricoSaude" class="row g-3 align-items-end">
                <div class="col-md-6 col-lg-3"><label class="form-label" for="acolhidoSaudeFiltro">Acolhido</label>
                    <select class="form-select" id="acolhidoSaudeFiltro" name="acolhido"><option value="">Todos os ativos</option>
                        <?php foreach ($acolhidos as $acolhido): ?><option value="<?php echo (int)$acolhido['id']; ?>" <?php echo $acolhidoFiltro === (int)$acolhido['id'] ? 'selected' : ''; ?>><?php echo $h($acolhido['nome']); ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6 col-lg-2"><label class="form-label" for="inicioSaude">De</label><input class="form-control" type="date" id="inicioSaude" name="inicio" value="<?php echo $h($inicio); ?>" max="<?php echo $h($hoje); ?>"></div>
                <div class="col-md-6 col-lg-2"><label class="form-label" for="fimSaude">Até</label><input class="form-control" type="date" id="fimSaude" name="fim" value="<?php echo $h($fim); ?>" max="<?php echo $h($hoje); ?>"></div>
                <div class="col-md-6 col-lg-2"><label class="form-label" for="tipoSaudeFiltro">Tipo</label><select class="form-select" id="tipoSaudeFiltro" name="tipo"><option value="">Todos</option><?php foreach ($tiposSaude as $codigo => $rotulo): ?><option value="<?php echo $h($codigo); ?>" <?php echo $tipoFiltro === $codigo ? 'selected' : ''; ?>><?php echo $h($rotulo); ?></option><?php endforeach; ?></select></div>
                <div class="col-md-8 col-lg-3"><label class="form-label" for="termoSaude">Pesquisar</label><input class="form-control" id="termoSaude" name="termo" maxlength="120" value="<?php echo $h($termo); ?>" placeholder="Nome, profissional ou texto"></div>
                <div class="col-12 d-flex justify-content-end gap-2">
                    <a class="btn btn-outline-secondary" href="<?php echo $h(BASE_URL); ?>views/saude_acolhido.php">Limpar</a>
                    <?php if ($tabelaSaudeDisponivel): ?><button class="btn btn-outline-primary" id="imprimirBuscaSaude" type="button" data-pdf-url="<?php echo $h(BASE_URL); ?>views/gerar_saude_acolhido_pdf.php"><i class="bi bi-printer me-1" aria-hidden="true"></i>Imprimir busca</button><?php endif; ?>
                    <button class="btn btn-primary" type="submit"><i class="bi bi-search me-1" aria-hidden="true"></i>Pesquisar</button>
                </div>
            </form>
        </div>
    </section>

    <section aria-labelledby="tituloHistoricoSaude">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3"><h2 class="h4 mb-0" id="tituloHistoricoSaude">Linha do tempo de saúde</h2><span class="badge text-bg-secondary"><?php echo count($registrosSaude); ?> registro(s)</span></div>
        <?php if (!$tabelaSaudeDisponivel): ?>
            <div class="alert alert-info">O histórico poderá ser consultado depois que a estrutura do banco for instalada.</div>
        <?php elseif (!$registrosSaude): ?>
            <div class="alert alert-info">Nenhum registro corresponde aos filtros informados.</div>
        <?php else: ?>
            <div class="vstack gap-3">
                <?php foreach ($registrosSaude as $registro): ?>
                    <?php
                    $tipoRegistro = (string)$registro['tipo'];
                    $categoriaAutomatica = str_starts_with($tipoRegistro, 'prescricao_') || str_starts_with($tipoRegistro, 'medicamento_');
                    $detalhes = is_string($registro['detalhes_json']) ? json_decode($registro['detalhes_json'], true) : null;
                    if (!is_array($detalhes)) $detalhes = [];
                    $alteracoes = is_array($detalhes['alteracoes'] ?? null) ? $detalhes['alteracoes'] : [];
                    if (!$alteracoes && is_array($detalhes['antes'] ?? null) && is_array($detalhes['atual'] ?? null)) {
                        foreach ($detalhes['atual'] as $campo => $valorAtual) {
                            $valorAnterior = $detalhes['antes'][$campo] ?? '';
                            if ($valorAnterior !== $valorAtual) $alteracoes[$campo] = ['antes' => $valorAnterior, 'depois' => $valorAtual];
                        }
                    }
                    $snapshot = is_array($detalhes['atual'] ?? null) ? $detalhes['atual'] : (is_array($detalhes['antes'] ?? null) ? $detalhes['antes'] : []);
                    ?>
                    <article class="card shadow-sm">
                        <div class="card-header bg-body-tertiary d-flex flex-wrap justify-content-between align-items-center gap-2">
                            <div><span class="badge text-bg-<?php echo $categoriaAutomatica ? 'info' : 'primary'; ?> me-2"><?php echo $h($tiposSaude[$tipoRegistro] ?? 'Registro de saúde'); ?></span><strong><?php echo $h($registro['titulo']); ?></strong></div>
                            <time datetime="<?php echo $h($registro['data_evento']); ?>"><?php echo $h((new DateTimeImmutable($registro['data_evento']))->format('d/m/Y')); ?></time>
                        </div>
                        <div class="card-body">
                            <div class="small text-body-secondary mb-2"><strong><?php echo $h($registro['acolhido_nome']); ?></strong><?php if (!empty($registro['profissional'])): ?> · Profissional/local: <?php echo $h($registro['profissional']); ?><?php endif; ?></div>
                            <div class="text-break"><?php echo nl2br($h($registro['descricao'])); ?></div>
                            <?php if ($alteracoes): ?>
                                <div class="table-responsive mt-3"><table class="table table-sm table-bordered align-middle mb-0"><thead><tr><th>Campo</th><th>Antes</th><th>Depois</th></tr></thead><tbody>
                                    <?php foreach ($alteracoes as $campo => $valores): ?><tr><th><?php echo $h($campo); ?></th><td><?php echo $h($valores['antes'] ?? ''); ?></td><td><?php echo $h($valores['depois'] ?? ''); ?></td></tr><?php endforeach; ?>
                                </tbody></table></div>
                            <?php elseif ($snapshot): ?>
                                <dl class="row small mt-3 mb-0">
                                    <?php foreach ($snapshot as $campo => $valor): ?>
                                        <?php if ($campo !== '_acolhido_id' && $valor !== ''): ?><dt class="col-md-3"><?php echo $h($campo); ?></dt><dd class="col-md-9"><?php echo $h($valor); ?></dd><?php endif; ?>
                                    <?php endforeach; ?>
                                </dl>
                            <?php endif; ?>
                        </div>
                        <div class="card-footer bg-transparent small text-body-secondary">Registrado por <strong><?php echo $h($registro['autor_nome']); ?></strong> em <?php echo $h((new DateTimeImmutable($registro['criado_em']))->format('d/m/Y \à\s H:i')); ?></div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</main>
<script>
const imprimirBuscaSaude = document.getElementById('imprimirBuscaSaude');
const filtroHistoricoSaude = document.getElementById('filtroHistoricoSaude');
if (imprimirBuscaSaude && filtroHistoricoSaude) {
    imprimirBuscaSaude.addEventListener('click', () => {
        const parametros = new URLSearchParams(new FormData(filtroHistoricoSaude));
        const separador = imprimirBuscaSaude.dataset.pdfUrl.includes('?') ? '&' : '?';
        window.abrirPreviaPdf(imprimirBuscaSaude.dataset.pdfUrl + separador + parametros.toString());
    });
}
</script>
<?php require_once ROOT_DIR . '/src/View/templates/includes/rodapeInterno.inc.php'; ?>

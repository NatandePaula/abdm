<?php
$autoloadPath = ROOT_DIR . '/vendor/autoload.php';
if (!is_file($autoloadPath)) {
    http_response_code(500);
    exit('A biblioteca mPDF não está instalada no servidor.');
}
require_once $autoloadPath;
$controleAcesso ??= new \Abdm\Infraestrutura\ControleAcesso($pdo);
$controleAcesso->exigirUsuarioAutenticado();
$controleAcesso->exigirPermissao('saude_acolhido');

[
    'h' => $h,
    'valor' => $valor,
    'tiposSaude' => $tiposSaude,
    'acolhidoId' => $acolhidoId,
    'nomeAcolhido' => $nomeAcolhido,
    'tipoFiltro' => $tipoFiltro,
    'termoBusca' => $termoBusca,
    'registros' => $registros,
    'registro' => $registro,
    'detalhes' => $detalhes,
    'periodo' => $periodo,
    'normalizarSnapshot' => $normalizarSnapshot,
    'campo' => $campo,
    'cabecalhoPath' => $cabecalhoPath,
    'rodapePath' => $rodapePath,
] = (new \Abdm\Controller\TelasSaudeController($pdo))->paraImpressao();

ob_start(); ?>
<style>
body{font-family:dejavusans,sans-serif;color:#111;font-size:9.5pt}
.title{width:100%;border-collapse:collapse;margin-bottom:12px}
.title td{border:1px solid #5f7188;background:#b6c9e6;padding:8px}
.title-main{text-align:center;font-size:14pt;font-weight:bold}
.record{border:1px solid #555;margin:0 0 12px;padding:9px;page-break-inside:avoid}
.record-title{font-size:10pt;font-weight:bold;border-bottom:1px solid #888;padding-bottom:5px;margin-bottom:7px}
.meta{font-size:8.5pt;color:#333;margin:3px 0 7px}
.text{line-height:1.45;white-space:pre-wrap;overflow-wrap:break-word}
.changes{width:100%;border-collapse:collapse;margin-top:8px;font-size:8.5pt}
.changes th,.changes td{border:1px solid #888;padding:5px;text-align:left;vertical-align:top}
.snapshot{width:100%;border-collapse:collapse;margin-top:8px;font-size:8.5pt}
.snapshot th,.snapshot td{border-bottom:1px solid #ccc;padding:4px;text-align:left;vertical-align:top}
.snapshot th{width:28%}
.summary{font-size:8.5pt;text-align:center;margin:0 0 12px}
.empty{text-align:center;padding:18px;border:1px solid #999}
</style>
<table class="title">
    <tr><td class="title-main">HISTÓRICO DE SAÚDE</td><td width="34%"><strong>Período:</strong><br><?php echo $h($periodo); ?></td></tr>
    <tr><td colspan="2"><strong>Acolhido:</strong> <?php echo $h($nomeAcolhido); ?></td></tr>
</table>
<p class="summary"><?php echo count($registros); ?> registro(s)<?php if ($tipoFiltro !== ''): ?> · Tipo: <?php echo $h($tiposSaude[$tipoFiltro]); ?><?php endif; ?><?php if ($termoBusca !== ''): ?> · Busca: “<?php echo $h($termoBusca); ?>”<?php endif; ?></p>
<?php if (!$registros): ?>
    <div class="empty">Não há registros de saúde para os filtros selecionados.</div>
<?php else: ?>
    <?php foreach ($registros as $registro): ?>
        <?php
        $detalhes = is_string($registro['detalhes_json'] ?? null) ? json_decode($registro['detalhes_json'], true) : null;
        if (!is_array($detalhes)) $detalhes = [];
        $alteracoes = is_array($detalhes['alteracoes'] ?? null) ? $detalhes['alteracoes'] : [];
        if (!$alteracoes && is_array($detalhes['antes'] ?? null) && is_array($detalhes['atual'] ?? null)) {
            foreach ($detalhes['atual'] as $campo => $valorAtual) {
                $valorAnterior = $detalhes['antes'][$campo] ?? '';
                if ($valorAnterior !== $valorAtual) $alteracoes[$campo] = ['antes' => $valorAnterior, 'depois' => $valorAtual];
            }
        }
        $snapshot = is_array($detalhes['atual'] ?? null) ? $detalhes['atual'] : (is_array($detalhes['antes'] ?? null) ? $detalhes['antes'] : []);
        $tituloEvento = trim((string)$registro['titulo']);
        $tipoEvento = (string)$registro['tipo'];
        $prefixosTipo = [
            'prescricao_criada' => ['nova prescrição'],
            'prescricao_alterada' => ['prescrição alterada', 'prescrição atualizada'],
            'medicamento_suspenso' => ['medicamento suspenso', 'prescrição suspensa'],
            'medicamento_reativado' => ['medicamento reativado', 'prescrição reativada'],
            'medicamento_finalizado' => ['prescrição finalizada'],
            'prescricao_finalizada_automaticamente' => ['prescrição finalizada automaticamente'],
            'prescricao_removida' => ['prescrição removida'],
        ][$tipoEvento] ?? [];
        $tituloJaIdentificaTipo = false;
        foreach ($prefixosTipo as $prefixoTipo) {
            $posicaoPrefixo = function_exists('mb_stripos') ? mb_stripos($tituloEvento, $prefixoTipo, 0, 'UTF-8') : stripos($tituloEvento, $prefixoTipo);
            if ($posicaoPrefixo === 0) {
                $tituloJaIdentificaTipo = true;
                break;
            }
        }
        $criadoEm = new DateTimeImmutable($registro['criado_em']);
        $dataEvento = (new DateTimeImmutable($registro['data_evento']))->format('d/m/Y');
        $dataLancamento = $criadoEm->format('d/m/Y');
        $descricao = trim((string)$registro['descricao']);
        $descricaoGenerica = [
            'prescricao_criada' => 'Nova prescrição registrada no sistema.',
            'prescricao_alterada' => 'Dados da prescrição alterados.',
        ][$tipoEvento] ?? '';
        if ($descricaoGenerica !== '' && $descricao === $descricaoGenerica) $descricao = '';
        $medicamentoSnapshot = trim((string)($snapshot['Medicamento'] ?? ''));
        $principioAtivoSnapshot = trim((string)($snapshot['Princípio ativo'] ?? ''));
        $principioAtivoRepetido = $medicamentoSnapshot !== '' && $principioAtivoSnapshot !== ''
            && (function_exists('mb_strtolower') ? mb_strtolower($medicamentoSnapshot, 'UTF-8') === mb_strtolower($principioAtivoSnapshot, 'UTF-8') : strtolower($medicamentoSnapshot) === strtolower($principioAtivoSnapshot));
        ?>
        <article class="record">
            <div class="record-title"><?php if (!$tituloJaIdentificaTipo): ?><?php echo $h($tiposSaude[$tipoEvento] ?? 'Registro de saúde'); ?> · <?php endif; ?><?php echo $h($tituloEvento); ?></div>
            <div class="meta">
                <?php if ($dataEvento === $dataLancamento): ?>
                    <?php echo $h($criadoEm->format('d/m/Y H:i')); ?> · <strong>Registrado por:</strong> <?php echo $h($registro['autor_nome']); ?>
                <?php else: ?>
                    <strong>Data do evento:</strong> <?php echo $h($dataEvento); ?> · <strong>Registrado por:</strong> <?php echo $h($registro['autor_nome']); ?> em <?php echo $h($criadoEm->format('d/m/Y H:i')); ?>
                <?php endif; ?>
                <?php if ($acolhidoId === null): ?> · <strong>Acolhido:</strong> <?php echo $h($registro['acolhido_nome']); ?><?php endif; ?>
            </div>
            <?php if (!empty($registro['profissional'])): ?><div class="meta"><strong>Profissional ou local:</strong> <?php echo $h($registro['profissional']); ?></div><?php endif; ?>
            <?php if ($descricao !== ''): ?><div class="text"><?php echo $h($descricao); ?></div><?php endif; ?>
            <?php if ($alteracoes): ?>
                <table class="changes"><thead><tr><th>Campo</th><th>Antes</th><th>Depois</th></tr></thead><tbody>
                    <?php foreach ($alteracoes as $campo => $valores): ?><tr><th><?php echo $h($campo); ?></th><td><?php echo $h($normalizarSnapshot((string)$campo, $valores['antes'] ?? '')); ?></td><td><?php echo $h($normalizarSnapshot((string)$campo, $valores['depois'] ?? '')); ?></td></tr><?php endforeach; ?>
                </tbody></table>
            <?php elseif ($snapshot): ?>
                <table class="snapshot"><tbody>
                    <?php foreach ($snapshot as $campo => $valor): ?>
                        <?php
                        if (in_array($campo, ['_acolhido_id', 'Acolhido', 'Medicamento'], true) || ($campo === 'Princípio ativo' && $principioAtivoRepetido)) continue;
                        if ($campo === 'Status atual' && $tipoEvento === 'prescricao_criada' && $valor === 'Ativa') continue;
                        if ($valor === '') continue;
                        ?>
                        <tr><th><?php echo $h($campo); ?></th><td><?php echo $h($normalizarSnapshot((string)$campo, $valor)); ?></td></tr>
                    <?php endforeach; ?>
                </tbody></table>
            <?php endif; ?>
        </article>
    <?php endforeach; ?>
<?php endif; ?>
<?php
$html = ob_get_clean();
try {
    $mpdf = new \Mpdf\Mpdf(['mode' => 'utf-8', 'format' => 'A4', 'margin_top' => 35, 'margin_bottom' => 35, 'margin_left' => 15, 'margin_right' => 15]);
    $mpdf->imageVars['cabecalhoimg'] = file_get_contents($cabecalhoPath);
    $mpdf->imageVars['rodapeimg'] = file_get_contents($rodapePath);
    $mpdf->SetHTMLHeader('<img src="var:cabecalhoimg" style="width:100%;" />');
    $mpdf->SetHTMLFooter(\Abdm\View\RodapeImpressao::rodapeImpressaoPadrao((new \Abdm\Controller\DadosImpressaoController($pdo))->nomeUsuarioImpressaoAtual()));
    $mpdf->SetTitle('Histórico de saúde - ' . $nomeAcolhido);
    $mpdf->WriteHTML($html);
    $mpdf->Output('historico_saude_acolhido_' . (int)$acolhidoId . '.pdf', 'I');
} catch (\Mpdf\MpdfException $exception) {
    error_log('Falha ao gerar impressão do histórico de saúde. Código: ' . $exception->getCode());
    http_response_code(500);
    exit('Não foi possível preparar a impressão do histórico de saúde.');
}

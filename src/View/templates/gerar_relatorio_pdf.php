<?php
$autoloadPath = ROOT_DIR . '/vendor/autoload.php';
if (!is_file($autoloadPath)) {
    http_response_code(500);
    exit('A biblioteca mPDF não está instalada no servidor.');
}

require_once $autoloadPath;
$controleAcesso ??= new \Abdm\Infraestrutura\ControleAcesso($pdo);
$controleAcesso->exigirPermissao('relatorio_diario');

[
    'h' => $h,
    'termoBusca' => $termoBusca,
    'acolhidoId' => $acolhidoId,
    'acolhidoNome' => $acolhidoNome,
    'relatos' => $relatos,
    'mencoesPorRelato' => $mencoesPorRelato,
    'relato' => $relato,
    'mencao' => $mencao,
    'nomeAutor' => $nomeAutor,
    'relatosPorData' => $relatosPorData,
    'rotuloPeriodo' => $rotuloPeriodo,
] = (new \Abdm\Controller\TelasRelatorioController($pdo))->paraImpressao();

$cabecalhoPath = ROOT_DIR . '/public/assets/imagens/cabecalho.jpg';
$rodapePath = ROOT_DIR . '/public/assets/imagens/rodape.jpg';
if (!is_file($cabecalhoPath) || !is_file($rodapePath)) {
    http_response_code(500);
    exit('As imagens institucionais de impressão não foram encontradas.');
}

ob_start();
?>
<style>
    body { font-family: sans-serif; color: #222; font-size: 10pt; }
    h1 { text-align: center; font-size: 18pt; margin: 0 0 5px; }
    .resumo { text-align: center; font-size: 9pt; margin: 0 0 16px; }
    h2 { font-size: 13pt; border-bottom: 1px solid #333; padding-bottom: 4px; margin: 14px 0 8px; page-break-after: avoid; }
    h3 { font-size: 10pt; text-transform: uppercase; margin: 8px 0 4px; page-break-after: avoid; }
    .ocorrencia { border-bottom: 1px solid #999; padding: 7px 0 9px; }
    .metadados { width: 100%; font-size: 9pt; margin-bottom: 4px; }
    .conteudo { line-height: 1.4; text-align: justify; overflow-wrap: break-word; }
    .mencoes { font-size: 9pt; margin-top: 4px; }
    .vazio { text-align: center; padding: 18px; border: 1px solid #999; }
</style>
<h1>Relatório de Ocorrências</h1>
<p class="resumo"><?php echo $h($rotuloPeriodo); ?> · <?php echo count($relatos); ?> ocorrência(s)<?php if ($termoBusca !== ''): ?> · Termo: “<?php echo $h($termoBusca); ?>”<?php endif; ?><?php if ($acolhidoId !== null): ?> · Acolhido: <?php echo $h($acolhidoNome); ?><?php endif; ?></p>
<?php if (!$relatos): ?>
    <div class="vazio">Nenhuma ocorrência encontrada para os filtros informados.</div>
<?php else: ?>
    <?php foreach ($relatosPorData as $dataGrupo => $turnos): ?>
        <h2>Data de referência: <?php echo $h((new DateTimeImmutable($dataGrupo))->format('d/m/Y')); ?></h2>
        <?php foreach (['dia' => 'Turno diurno', 'noite' => 'Turno noturno'] as $codigoTurno => $nomeTurno): ?>
            <?php if (!empty($turnos[$codigoTurno])): ?>
                <h3><?php echo $h($nomeTurno); ?></h3>
                <?php foreach ($turnos[$codigoTurno] as $relato): ?>
                    <?php $idRelato = (int)$relato['idRelato']; $dataHora = strtotime($relato['criado_em']); ?>
                    <div class="ocorrencia">
                        <table class="metadados">
                            <tr><td><strong>Registro n.º <?php echo $idRelato; ?></strong></td><td align="right">Lançado em <?php echo $h(date('d/m/Y \à\s H:i', $dataHora)); ?></td></tr>
                            <tr><td colspan="2"><strong>Responsável:</strong> <strong><?php echo $h($relato['nome_autor']); ?></strong></td></tr>
                            <?php if (!empty($relato['editado_em'])): ?>
                                <?php $dataEdicao = strtotime($relato['editado_em']); ?>
                                <tr><td colspan="2">Editado pelo autor em <?php echo $h(date('d/m/Y \à\s H:i', $dataEdicao)); ?></td></tr>
                            <?php endif; ?>
                        </table>
                        <div class="conteudo"><?php echo \Abdm\View\TextoRelatorio::formatarConteudoRelatorioHtml((string)$relato['conteudo'], $mencoesPorRelato[$idRelato] ?? [], $termoBusca); ?></div>
                        <?php if (!empty($mencoesPorRelato[$idRelato])): ?>
                            <div class="mencoes"><strong>Acolhidos mencionados:</strong>
                                <?php foreach ($mencoesPorRelato[$idRelato] as $indiceMencao => $mencao): ?><?php echo $indiceMencao > 0 ? ', ' : ''; ?><strong><?php echo $h($mencao); ?></strong><?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        <?php endforeach; ?>
    <?php endforeach; ?>
<?php endif; ?>
<?php
$htmlConteudo = ob_get_clean();

try {
    $mpdf = new \Mpdf\Mpdf([
        'mode' => 'utf-8',
        'format' => 'A4',
        'margin_top' => 35,
        'margin_bottom' => 35,
        'margin_left' => 15,
        'margin_right' => 15,
    ]);
    $mpdf->imageVars['cabecalhoimg'] = file_get_contents($cabecalhoPath);
    $mpdf->imageVars['rodapeimg'] = file_get_contents($rodapePath);
    $mpdf->SetHTMLHeader('<img src="var:cabecalhoimg" style="width:100%;" />');
    $mpdf->SetHTMLFooter(\Abdm\View\RodapeImpressao::rodapeImpressaoPadrao($nomeAutor));
    $mpdf->WriteHTML($htmlConteudo);
    $mpdf->Output('relatorio_ocorrencias_' . date('Ymd_His') . '.pdf', 'I');
} catch (\Mpdf\MpdfException $exception) {
    http_response_code(500);
    error_log('Falha ao gerar impressão do relatório. Código: ' . $exception->getCode());
    exit('Não foi possível preparar a impressão do relatório.');
}

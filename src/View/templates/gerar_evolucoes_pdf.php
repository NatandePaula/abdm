<?php
$autoloadPath = ROOT_DIR . '/vendor/autoload.php';
if (!is_file($autoloadPath)) {
    http_response_code(500);
    exit('A biblioteca mPDF não está instalada no servidor.');
}
require_once $autoloadPath;
$controleAcesso ??= new \Abdm\Infraestrutura\ControleAcesso($pdo);
$controleAcesso->exigirUsuarioAutenticado();

[
    'titulo' => $titulo,
    'evolucoes' => $evolucoes,
    'h' => $h,
    'periodo' => $periodo,
    'cabecalhoPath' => $cabecalhoPath,
    'rodapePath' => $rodapePath,
] = (new \Abdm\Controller\TelasEvolucaoController($pdo))->paraImpressao($controleAcesso);

ob_start(); ?>
<style>
body{font-family:dejavusans,sans-serif;color:#111;font-size:9.5pt}
.title{width:100%;border-collapse:collapse;margin-bottom:12px}
.title td{border:1px solid #5f7188;background:#b6c9e6;padding:8px}
.title-main{text-align:center;font-size:14pt;font-weight:bold}
.record{border:1px solid #555;margin:0 0 12px;padding:9px;page-break-inside:avoid}
.record-title{font-size:10pt;font-weight:bold;border-bottom:1px solid #888;padding-bottom:5px;margin-bottom:7px}
.meta{font-size:8.5pt;color:#333;margin:3px 0 7px}
.professional{text-align:center;margin:7px 0 0;font-size:9pt}
.text{line-height:1.45;white-space:pre-wrap}
.signature{margin:45px auto 0;text-align:center;width:70%;border-top:1px solid #111;padding-top:4px}
.signature-name{font-weight:bold}
.signature-info{font-size:8pt}
</style>
<table class="title">
    <tr><td class="title-main">RELATÓRIO DE EVOLUÇÕES</td><td width="34%"><strong>Período:</strong><br><?php echo $h($periodo); ?></td></tr>
    <tr><td colspan="2"><strong><?php echo $h($titulo); ?></strong></td></tr>
</table>
<?php if (!$evolucoes): ?><p>Não há evoluções registradas para os filtros selecionados.</p><?php endif; ?>
<?php foreach ($evolucoes as $evolucao): ?>
    <article class="record">
        <div class="record-title"><?php echo $h($evolucao['acolhido_nome']); ?></div>
        <div class="meta"><strong>Data e horário:</strong> <?php echo $h((new DateTimeImmutable($evolucao['registrado_em']))->format('d/m/Y H:i')); ?></div>
        <div class="professional"><strong>Profissional:</strong> <strong><?php echo $h($evolucao['profissional_nome']); ?></strong><?php if (!empty($evolucao['atuacao_profissional'])): ?> · <?php echo $h($evolucao['atuacao_profissional']); ?><?php endif; ?><?php if (!empty($evolucao['registro_profissional'])): ?> · Registro: <?php echo $h($evolucao['registro_profissional']); ?><?php endif; ?></div>
        <div class="text"><?php echo $h($evolucao['registro']); ?></div>
        <div class="signature">
            <div class="signature-name"><?php echo $h($evolucao['profissional_nome']); ?></div>
            <div class="signature-info"><?php echo $h($evolucao['atuacao_profissional'] ?? ''); ?><?php if (!empty($evolucao['registro_profissional'])): ?><br>Registro profissional: <?php echo $h($evolucao['registro_profissional']); ?><?php endif; ?></div>
        </div>
    </article>
<?php endforeach; ?>
<?php
$html = ob_get_clean();
try {
    $mpdf = new \Mpdf\Mpdf(['mode' => 'utf-8', 'format' => 'A4', 'margin_top' => 35, 'margin_bottom' => 35, 'margin_left' => 15, 'margin_right' => 15]);
    $mpdf->imageVars['cabecalhoimg'] = file_get_contents($cabecalhoPath);
    $mpdf->imageVars['rodapeimg'] = file_get_contents($rodapePath);
    $mpdf->SetHTMLHeader('<img src="var:cabecalhoimg" style="width:100%;" />');
    $mpdf->SetHTMLFooter(\Abdm\View\RodapeImpressao::rodapeImpressaoPadrao((new \Abdm\Controller\DadosImpressaoController($pdo))->nomeUsuarioImpressaoAtual()));
    $mpdf->WriteHTML($html);
    $mpdf->Output('relatorio_evolucoes.pdf', 'I');
} catch (\Mpdf\MpdfException $exception) {
    error_log('Falha ao gerar impressão de evoluções. Código: ' . $exception->getCode());
    http_response_code(500);
    exit('Não foi possível preparar a impressão das evoluções.');
}

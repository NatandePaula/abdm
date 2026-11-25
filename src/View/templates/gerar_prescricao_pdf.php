<?php
// 1. Carregar dependências
$autoload_path = ROOT_DIR . '/vendor/autoload.php';
if (!file_exists($autoload_path)) {
    http_response_code(500);
    exit('A biblioteca de geração de PDFs não está disponível.');
}
require_once $autoload_path;
$controleAcesso ??= new \Abdm\Infraestrutura\ControleAcesso($pdo);
$controleAcesso->exigirPermissao('prescricoes');

[
    'tipo_impressao' => $tipo_impressao,
    'acolhido' => $acolhido,
    'periodos' => $periodos,
    'prescricoes_ativas' => $prescricoes_ativas,
    'horarios_por_prescricao' => $horarios_por_prescricao,
    'medicacoes_por_horario' => $medicacoes_por_horario,
    'medicacoes_por_intervalo' => $medicacoes_por_intervalo,
    'presc' => $presc,
    'h' => $h,
] = (new \Abdm\Controller\TelasPrescricaoController($pdo))->paraImpressao();

ob_start();
?>
<!-- Estilos para o conteúdo do PDF -->
<style>
    body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; color: #333; font-size: 10pt; }
    .main-title { text-align: center; font-size: 18pt; font-weight: bold; margin-bottom: 10px; }
    .sub-title { text-align: center; font-size: 14pt; font-weight: normal; margin-top: 0; margin-bottom: 30px; }
    .table-plano { width: 100%; border-collapse: collapse; }
    .table-plano th, .table-plano td { border: 1px solid #ccc; padding: 8px; text-align: left; vertical-align: middle; }
    .table-plano th { background-color: #f2f2f2; font-weight: bold; text-align: center; }
    .table-plano td.text-center { text-align: center; }
    .receituario-body { margin-top: 20px; }
    .receita-item { border: 1px solid #999; padding: 15px; margin-bottom: 20px; border-radius: 5px; page-break-inside: avoid; }
    .receita-item h3 { margin-top: 0; border-bottom: 1px solid #ccc; padding-bottom: 5px; font-size: 12pt; }
    .receita-item p, .receita-item ul { margin: 10px 0; }
    .receita-item ul { padding-left: 20px; }
    .receita-item .via-adm { font-size: 9pt; color: #555; }

    /* Estilos para o novo layout do plano de medicação */
    .table-plano td ul.med-list {
        margin: 0;
        padding-left: 15px;
        list-style-type: disc;
    }
    .table-plano td ul.med-list li {
        margin-bottom: 8px;
        padding-left: 5px;
    }
    .table-plano td ul.med-list li:last-child {
        margin-bottom: 0;
    }
    .table-plano span.dose-info {
        font-size: 9pt;
        color: #555;
    }
    .table-plano span.no-med-text {
        color: #999;
        display: block;
        text-align: center;
    }
</style>

<!-- Conteúdo HTML dinâmico -->
<?php if ($tipo_impressao == 'plano'): ?>
    <h1 class="main-title">Plano de Medicação</h1>
    <h2 class="sub-title">Paciente: <?php echo htmlspecialchars($acolhido['nome']); ?></h2>
    <table class="table-plano">
        <thead>
            <tr>
                <th style="width: 18%;">Período</th>
                <th style="width: 14%;">Horário</th>
                <th>Medicações</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!$medicacoes_por_horario): ?>
                <tr><td colspan="3" style="text-align: center;">Nenhuma medicação com horário fixo prevista.</td></tr>
            <?php else: ?>
                <?php $indicePeriodoComMedicacao = 0; ?>
                <?php foreach ($periodos as $periodo): ?>
                    <?php $horariosPeriodo = array_values(array_filter($medicacoes_por_horario, static fn(array $linhaHorario): bool => $linhaHorario['periodo_id'] === (int)$periodo['id'])); ?>
                    <?php if (!$horariosPeriodo) { continue; } ?>
                    <?php $rowspanPeriodo = count($horariosPeriodo); ?>
                    <?php $corFundoPeriodo = $indicePeriodoComMedicacao % 2 === 0 ? '#eaf3ff' : '#ffffff'; $indicePeriodoComMedicacao++; ?>
                    <?php foreach ($horariosPeriodo as $indiceHorario => $linhaHorario): ?>
                        <tr>
                            <?php if ($indiceHorario === 0): ?>
                                <td rowspan="<?php echo $rowspanPeriodo; ?>" class="text-center align-middle" style="background-color:<?php echo $corFundoPeriodo; ?>;"><strong><?php echo htmlspecialchars($periodo['nome'], ENT_QUOTES, 'UTF-8'); ?></strong></td>
                            <?php endif; ?>
                            <td class="text-center" style="background-color:<?php echo $corFundoPeriodo; ?>;"><strong><?php echo htmlspecialchars($linhaHorario['horario'], ENT_QUOTES, 'UTF-8'); ?></strong></td>
                            <td style="background-color:<?php echo $corFundoPeriodo; ?>;">
                                <ul class="med-list">
                                    <?php foreach ($linhaHorario['medicamentos'] as $med): ?>
                                        <li>
                                            <strong><?php echo htmlspecialchars($med['nome'], ENT_QUOTES, 'UTF-8'); ?></strong> (<?php echo htmlspecialchars($med['apresentacao'], ENT_QUOTES, 'UTF-8'); ?>)
                                            <br>
                                            <span class="dose-info">Dose: <?php echo htmlspecialchars($med['dose'], ENT_QUOTES, 'UTF-8'); ?></span>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <?php if (!empty($medicacoes_por_intervalo)): ?>
        <h3 class="section-title" style="margin-top: 30px; font-size: 14pt; page-break-before: auto;">Medicações por Intervalo</h3>
        <table class="table-plano">
            <thead>
                <tr>
                    <th>Medicação</th>
                    <th>Posologia</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($medicacoes_por_intervalo as $presc): ?>
                    <tr>
                        <td>
                            <strong><?php echo htmlspecialchars($presc['nome_comercial']); ?></strong>
                            (<?php echo htmlspecialchars($presc['apresentacao']); ?>)
                        </td>
                        <td>Tomar <?php echo htmlspecialchars(\Abdm\Model\HistoricoSaude::saudeFormatarDose($presc['quantidade_por_dose'], $presc['unidade_dose'])); ?> a cada <strong><?php echo htmlspecialchars($presc['intervalo_horas']); ?> horas</strong>, iniciando às <?php echo date('H:i', strtotime($presc['intervalo_inicio'])); ?>.</td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
<?php elseif ($tipo_impressao == 'receituario'): ?>
    <h1 class="main-title">Receituário Individual</h1>
    <h2 class="sub-title">Paciente: <?php echo htmlspecialchars($acolhido['nome']); ?></h2>
    <div class="receituario-body">
        <?php foreach ($prescricoes_ativas as $presc): ?>
            <div class="receita-item">
                <h3><?php echo htmlspecialchars($presc['nome_comercial'] . ' - ' . $presc['apresentacao']); ?></h3>
                <p><strong>Uso:</strong> <?php echo htmlspecialchars(\Abdm\Model\Prescricao::prescricaoRotuloTipoUso($presc['tipo_uso'] ?? null), ENT_QUOTES, 'UTF-8'); ?></p>
                <p><strong>Posologia:</strong></p>
                <?php if ($presc['tipo_posologia'] === 'intervalo'): ?>
                    <p style="margin-left: 20px;">
                        Tomar <strong><?php echo htmlspecialchars(\Abdm\Model\HistoricoSaude::saudeFormatarDose($presc['quantidade_por_dose'], $presc['unidade_dose'])); ?></strong>
                        a cada <strong><?php echo htmlspecialchars($presc['intervalo_horas']); ?> horas</strong>,
                        iniciando às <strong><?php echo date('H:i', strtotime($presc['intervalo_inicio'])); ?></strong>.
                        <?php if (!empty($presc['instrucao_posologia'])) echo '<br><em>' . htmlspecialchars($presc['instrucao_posologia']) . '</em>'; ?>
                    </p>
                <?php else: ?>
                    <ul>
                        <?php foreach ($horarios_por_prescricao[$presc['id']] ?? [] as $h): ?>
                            <li><strong><?php echo date('H:i', strtotime($h['horario'])); ?>:</strong> Tomar <?php echo htmlspecialchars(\Abdm\Model\HistoricoSaude::saudeFormatarDose($h['quantidade_por_dose'], $h['unidade_dose'])); ?>. <?php echo htmlspecialchars($presc['instrucao_posologia'] ?: ''); ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
                <p class="via-adm"><strong>Via de Administração:</strong> <?php echo htmlspecialchars($presc['via_administracao'] ?: 'Não informado'); ?></p>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php
$html_content = ob_get_clean();

// 4. Gerar o PDF com mPDF
try {
    $mpdf = new \Mpdf\Mpdf([
        'mode' => 'utf-8', 'format' => 'A4',
        'margin_top' => 35, 'margin_bottom' => 35,
        'margin_left' => 15, 'margin_right' => 15,
    ]);

    $path_cabecalho = ROOT_DIR . '/public/assets/imagens/cabecalho.jpg';
    $path_rodape = ROOT_DIR . '/public/assets/imagens/rodape.jpg';

    if (!file_exists($path_cabecalho)) {
        die("<h1>Erro Crítico</h1><p>O arquivo de imagem do cabeçalho não foi encontrado no caminho esperado: <code>" . htmlspecialchars($path_cabecalho) . "</code></p><p>Verifique se o arquivo existe e se o caminho está correto no servidor.</p>");
    }
    if (!file_exists($path_rodape)) {
        die("<h1>Erro Crítico</h1><p>O arquivo de imagem do rodapé (rodape.jpg) não foi encontrado no caminho esperado: <code>" . htmlspecialchars($path_rodape) . "</code></p><p>Verifique se você salvou a imagem como 'rodape.jpg' na pasta 'assets/imagens/'.</p>");
    }

    $data_cabecalho = file_get_contents($path_cabecalho);
    $data_rodape = file_get_contents($path_rodape);

    if ($data_cabecalho === false) {
        die("<h1>Erro Crítico</h1><p>Não foi possível ler o arquivo de imagem do cabeçalho. Verifique as permissões de leitura para o servidor no arquivo: <code>" . htmlspecialchars($path_cabecalho) . "</code></p>");
    }
    if ($data_rodape === false) {
        die("<h1>Erro Crítico</h1><p>Não foi possível ler o arquivo de imagem do rodapé. Verifique as permissões de leitura para o servidor no arquivo: <code>" . htmlspecialchars($path_rodape) . "</code></p>");
    }

    $mpdf->imageVars['cabecalhoimg'] = $data_cabecalho;
    $mpdf->imageVars['rodapeimg'] = $data_rodape;

    $header = '<img src="var:cabecalhoimg" style="width: 100%;" />';
    $mpdf->SetHTMLHeader($header);

    $mpdf->SetHTMLFooter(\Abdm\View\RodapeImpressao::rodapeImpressaoPadrao((new \Abdm\Controller\DadosImpressaoController($pdo))->nomeUsuarioImpressaoAtual()));

    $mpdf->WriteHTML($html_content);

    $mpdf->Output('prescricao_' . str_replace(' ', '_', strtolower($acolhido['nome'])) . '.pdf', 'I');

} catch (\Mpdf\MpdfException $e) {
    error_log('Falha ao gerar o PDF da prescrição.');
    http_response_code(500);
    exit('Não foi possível gerar o PDF.');
}

<?php
$autoloadPath = ROOT_DIR . '/vendor/autoload.php';
if (!is_file($autoloadPath)) {
    http_response_code(500);
    exit('A biblioteca mPDF não está instalada no servidor.');
}
require_once $autoloadPath;
$controleAcesso ??= new \Abdm\Infraestrutura\ControleAcesso($pdo);
$controleAcesso->exigirPermissao('pia');
$h = [\Abdm\Model\Pia::class, 'piaEscaparHtml'];
$telasPia = new \Abdm\Controller\TelasPiaController($pdo);
[
    'configuracaoServico' => $configuracaoServico,
    'visibilidadeCamposPia' => $visibilidadeCamposPia,
    'secoesAtivas' => $secoesAtivas,
] = $telasPia->configuracaoImpressao();
$campoPiaVisivel = static fn(string $chave): bool => !array_key_exists($chave, $visibilidadeCamposPia) || (int)$visibilidadeCamposPia[$chave] === 1;

if (($_GET['branco'] ?? '') === '1') {
    [
        'titulosBranco' => $titulosBranco,
        'camposBranco' => $camposBranco,
        'camposCadastroBranco' => $camposCadastroBranco,
    ] = $telasPia->modeloEmBranco();
    $htmlBranco = '<h1 style="text-align:center">Plano Individual de Atendimento (PIA)</h1><table width="100%"><tr><td><b>Data do acolhimento:</b> ____/____/________</td><td><b>Data de atualização:</b> ____/____/________</td></tr></table>';
    if (($secoesAtivas['identificacao_servico'] ?? false)) {
        $htmlBranco .= '<h2>1. IDENTIFICAÇÃO DO SERVIÇO DE ACOLHIMENTO</h2>';
        if ($campoPiaVisivel('pia_servico_nome')) $htmlBranco .= '<p><b>Nome do serviço de acolhimento institucional:</b> ' . $h($configuracaoServico['nome']) . '</p>';
        if ($campoPiaVisivel('pia_servico_endereco')) $htmlBranco .= '<p><b>Endereço:</b> ' . $h($configuracaoServico['endereco']) . '</p>';
        if ($campoPiaVisivel('pia_servico_telefone') && $configuracaoServico['telefone'] !== '') $htmlBranco .= '<p><b>Telefone:</b> ' . $h($configuracaoServico['telefone']) . '</p>';
    }
    if (($secoesAtivas['acolhido'] ?? false)) {
        $htmlBranco .= '<h2>2. INFORMAÇÕES SOBRE O ACOLHIDO</h2>';
        $basicosBranco = [
            'pia_nome_acolhido' => 'Nome completo', 'data_nascimento' => 'Data de nascimento', 'sexo' => 'Sexo',
            'pia_cpf_acolhido' => 'CPF', 'pia_matricula_acolhido' => 'Matrícula',
            'pia_data_acolhimento' => 'Data de acolhimento', 'pia_data_desligamento' => 'Data de desligamento',
            'pia_motivo_desligamento' => 'Motivo do desligamento', 'pia_origem_acolhimento' => 'Origem do acolhimento',
            'pia_nome_mae' => 'Nome da mãe', 'pia_nome_pai' => 'Nome do pai', 'pia_situacao_acolhido' => 'Situação',
            'pia_plano_assistencial' => 'Plano assistencial ou funerário', 'pia_info_saude' => 'Comorbidades, alergias e restrições',
            'pia_info_nutricional' => 'Informações alimentares e nutricionais', 'pia_motivo_institucionalizacao' => 'Motivo da institucionalização',
            'pia_historico_acolhido' => 'Histórico e observações gerais', 'pia_endereco_origem' => 'Endereço de origem',
        ];
        foreach ($basicosBranco as $chaveBranco => $rotuloBranco) {
            if ($campoPiaVisivel($chaveBranco)) $htmlBranco .= '<p><b>' . htmlspecialchars($rotuloBranco, ENT_QUOTES, 'UTF-8') . ':</b> ______________________________________________</p>';
        }
        foreach ($camposCadastroBranco as $campoCadastroBranco) {
            if ($campoCadastroBranco['chave'] === 'data_nascimento' || in_array((string)$campoCadastroBranco['chave'], \Abdm\Model\PiaCadastro::piaCamposCadastroJaPresentesNoFormularioPrincipal(), true)) continue;
            $htmlBranco .= '<p><b>' . htmlspecialchars($campoCadastroBranco['rotulo'], ENT_QUOTES, 'UTF-8') . ':</b><br>____________________________________________________________________________</p>';
        }
    }
    foreach ($titulosBranco as $secaoBranco => $tituloBranco) {
        if ($secaoBranco === 'acolhido') continue;
        $htmlBranco .= '<h2>' . htmlspecialchars(preg_replace('/^\d+\.\s*/', '', $tituloBranco), ENT_QUOTES, 'UTF-8') . '</h2>';
        foreach ($camposBranco as $campoBranco) {
            if ($campoBranco['secao'] !== $secaoBranco) continue;
            if ($campoBranco['tipo'] === 'documents') {
                $htmlBranco .= '<table width="100%" border="1" cellpadding="5" cellspacing="0"><thead><tr><th>Documento</th><th>Número</th><th>Situação</th></tr></thead><tbody>';
                foreach (['Certidão de nascimento e/ou casamento', 'RG', 'CPF', 'Título eleitoral', 'CTPS', 'Cartão Nacional do SUS', 'Cartão Municipal de Saúde', 'Outros documentos'] as $documentoBranco) {
                    $htmlBranco .= '<tr><td>' . htmlspecialchars($documentoBranco, ENT_QUOTES, 'UTF-8') . '</td><td>&nbsp;</td><td>&nbsp;</td></tr>';
                }
                $htmlBranco .= '</tbody></table>';
                continue;
            }
            $htmlBranco .= '<p><b>' . htmlspecialchars($campoBranco['rotulo'], ENT_QUOTES, 'UTF-8') . ':</b><br>____________________________________________________________________________<br>____________________________________________________________________________</p>';
        }
        if ($secaoBranco === 'plano') $htmlBranco .= '<h3>Assinaturas</h3><table width="100%"><tr><td><br><br>______________________________<br>Responsável técnico</td><td><br><br>______________________________<br>Coordenação</td></tr></table>';
    }
    $pathCabecalhoBranco = ROOT_DIR . '/public/assets/imagens/cabecalho.jpg';
    $pathRodapeBranco = ROOT_DIR . '/public/assets/imagens/rodape.jpg';
    if (!is_file($pathCabecalhoBranco) || !is_file($pathRodapeBranco)) {
        http_response_code(500);
        exit('Não foi possível preparar o cabeçalho e o rodapé da impressão.');
    }
    $mpdfBranco = new \Mpdf\Mpdf(['mode' => 'utf-8', 'format' => 'A4', 'margin_left' => 15, 'margin_right' => 15, 'margin_top' => 35, 'margin_bottom' => 35]);
    $mpdfBranco->imageVars['cabecalhoimg'] = file_get_contents($pathCabecalhoBranco);
    $mpdfBranco->imageVars['rodapeimg'] = file_get_contents($pathRodapeBranco);
    $mpdfBranco->SetHTMLHeader('<img src="var:cabecalhoimg" style="width:100%;" />');
    $mpdfBranco->SetHTMLFooter(\Abdm\View\RodapeImpressao::rodapeImpressaoPadrao((new \Abdm\Controller\DadosImpressaoController($pdo))->nomeUsuarioImpressaoAtual()));
    $mpdfBranco->SetTitle('Modelo em branco - PIA');
    $mpdfBranco->WriteHTML('<style>body{font-family:sans-serif;font-size:10pt}h2{font-size:12pt;border-bottom:1px solid #777;padding-bottom:4px;margin-top:18px}p{margin:8px 0}</style>' . $htmlBranco);
    $mpdfBranco->Output('modelo_pia_em_branco.pdf', 'I');
    exit;
}

[
    'pia' => $pia,
    'dadosCadastro' => $dadosCadastro,
    'assinaturas' => $assinaturas,
    'familiaresCadastro' => $familiaresCadastro,
    'cidsCadastro' => $cidsCadastro,
    'curadorCadastro' => $curadorCadastro,
    'campoPiaVisivel' => $campoPiaVisivel,
    'titulosSecoes' => $titulosSecoes,
    'camposPorSecao' => $camposPorSecao,
    'campo' => $campo,
    'camposCadastro' => $camposCadastro,
    'campoCadastro' => $campoCadastro,
    'renderCampo' => $renderCampo,
    'cabecalhoPath' => $cabecalhoPath,
    'rodapePath' => $rodapePath,
] = $telasPia->paraImpressao($h, $visibilidadeCamposPia);

ob_start();
?>
<style>
    body { font-family: dejavusans, sans-serif; color: #333; font-size: 10pt; }
    .main-title { text-align: center; font-size: 18pt; font-weight: bold; margin: 0 0 5px; }
    .sub-title { text-align: center; font-size: 14pt; font-weight: normal; margin: 0 0 14px; }
    .meta-table { width: 100%; border-collapse: collapse; margin: 0 0 14px; }
    .meta-table th, .meta-table td { border: 1px solid #ddd; padding: 6px; text-align: left; }
    .meta-table th { background: #f2f2f2; }
    .section, .section-content { page-break-inside: auto; break-inside: auto; }
    .section { margin: 0 0 14px; }
    .section-title { font-size: 12pt; font-weight: bold; border-bottom: 1px solid #ccc; padding-bottom: 3px; margin: 20px 0 10px; page-break-after: avoid; }
    .section-content { padding: 0; }
    .section-content > .data-field, .section-content > .data-table { margin-bottom: 8px; }
    .data-field, .data-table { border-collapse: collapse; width: 100%; margin: 0 0 8px; page-break-inside: auto; break-inside: auto; font-size: 10pt; }
    .data-field tbody, .data-table tbody, .data-field tr, .data-table tr,
    .data-field th, .data-field td, .data-table th, .data-table td {
        page-break-inside: auto;
        break-inside: auto;
    }
    .data-field tr, .data-table tr { page-break-before: allow; page-break-after: allow; }
    .data-field-block, .data-field-value, .activity-flow {
        page-break-inside: auto;
        break-inside: auto;
        overflow: visible;
    }
    .data-field-block { margin: 0 0 8px; }
    .data-field-label { font-weight: bold; background: #f2f2f2; border: 1px solid #ddd; padding: 6px; page-break-after: avoid; }
    .data-field-value { border: 1px solid #ddd; padding: 6px; white-space: normal; overflow-wrap: anywhere; }
    .data-table thead { display: table-header-group; }
    .data-field th, .data-field td, .data-table th, .data-table td { border: 1px solid #ddd; padding: 6px; vertical-align: top; }
    .data-field th { width: 30%; text-align: left; background: #f2f2f2; }
    .data-table th { background: #f2f2f2; text-align: left; }
    .center { text-align: center !important; }
    .special-label { font-weight: bold; margin: 5px 0 3px; }
    .field-block { margin: 4px 0 7px; }
    .benefits div, .network-list div { margin: 3px 0; }
    .import-note { color: #555; font-size: 9pt; margin: -5px 0 10px; }
    .signatures-title { text-align: center; font-size: 12pt; font-weight: bold; margin: 24px 0 2px; page-break-after: avoid; }
    .signatures { width: 100%; border-collapse: collapse; margin: 0 auto; page-break-inside: auto; }
    .signatures tr { page-break-inside: avoid; }
    td.signature-person-cell { width: 50%; padding: 8px 6px; text-align: center; vertical-align: top; }
    .signature-block { width: 80mm; max-width: 100%; margin: 0 auto; border-collapse: collapse; table-layout: fixed; }
    .signature-space td { height: 15mm; border: 0; padding: 0; font-size: 1px; line-height: 1px; }
    .signature-rule td { height: 1px; border: 0; border-top: 1px solid #111; padding: 0; font-size: 1px; line-height: 1px; }
    .signature-name { font-weight: bold; padding-top: 5px; }
    .signature-meta { font-size: 8.5pt; }
    .service-info p { margin: 3px 0; }
</style>

<h1 class="main-title">Plano Individual de Atendimento (PIA)</h1>
<h2 class="sub-title"><?php echo $h($pia['acolhido_nome'] ?? ''); ?></h2>
<table class="meta-table"><tbody><tr><th>Data do acolhimento</th><td><?php echo $h(\Abdm\Model\Pia::piaDataExibicao($pia['data_acolhimento'] ?? '')); ?></td><th>Data de atualização</th><td><?php echo $h(\Abdm\Model\Pia::piaDataExibicao($pia['dataAtualizacao'] ?? '')); ?></td></tr></tbody></table>

<?php if (($secoesAtivas['identificacao_servico'] ?? false)): ?><section class="section"><div class="section-title">1. IDENTIFICAÇÃO DO SERVIÇO DE ACOLHIMENTO</div><div class="section-content service-info">
    <?php if ($campoPiaVisivel('pia_servico_nome')): ?><p><strong>Nome do serviço de acolhimento institucional:</strong> <?php echo $h($configuracaoServico['nome']); ?></p><?php endif; ?>
    <?php if ($campoPiaVisivel('pia_servico_endereco')): ?><p><strong>Endereço:</strong> <?php echo $h($configuracaoServico['endereco']); ?></p><?php endif; ?>
    <?php if ($campoPiaVisivel('pia_servico_telefone') && $configuracaoServico['telefone'] !== ''): ?><p><strong>Telefone:</strong> <?php echo $h($configuracaoServico['telefone']); ?></p><?php endif; ?>
</div></section><?php endif; ?>

<?php if (($secoesAtivas['acolhido'] ?? false)): ?><section class="section">
    <div class="section-title">2. INFORMAÇÕES SOBRE O ACOLHIDO</div>
    <div class="section-content">
        <p class="import-note">Dados consultados automaticamente do cadastro atual do acolhido. Para corrigir ou atualizar essas informações, utilize o cadastro; o planejamento do PIA permanece separado.</p>
        <?php $dadosBasicos = [
    'pia_nome_acolhido'=>['Nome',$pia['acolhido_nome']??''], 'data_nascimento'=>['Data de nascimento',\Abdm\Model\Pia::piaDataExibicao($pia['acolhido_nascimento']??'')], 'sexo'=>['Sexo',\Abdm\Model\DadosAcolhido::acolhidoSexoRotulo($pia['sexo']??null)],
    'pia_cpf_acolhido'=>['CPF',\Abdm\Infraestrutura\Criptografia::descriptografar($pia['cpf']??'')], 'pia_matricula_acolhido'=>['Matrícula',$pia['matricula']??''],
    'pia_data_acolhimento'=>['Data do acolhimento',\Abdm\Model\Pia::piaDataExibicao($pia['data_acolhimento']??'')], 'pia_origem_acolhimento'=>['Origem do acolhimento',$pia['origemAcolhimento']??''],
    'pia_situacao_acolhido'=>['Situação',$pia['status']??''], 'pia_data_desligamento'=>['Data de desligamento',\Abdm\Model\Pia::piaDataExibicao($pia['dataDesligamento']??'')],
    'pia_motivo_desligamento'=>['Motivo do desligamento',$pia['motivoDesligamento']??''], 'pia_nome_mae'=>['Nome da mãe',$pia['nome_mae']??''],
    'pia_nome_pai'=>['Nome do pai',$pia['nome_pai']??''],
    'pia_plano_assistencial'=>['Plano assistencial ou funerário',$pia['plano_assistencial_funerario']??''],
    'pia_info_saude'=>['Comorbidades, alergias e restrições',$pia['info_saude']??''], 'pia_info_nutricional'=>['Informações alimentares e nutricionais',$pia['info_nutri']??''],
    'pia_motivo_institucionalizacao'=>['Motivo da institucionalização',$pia['cadastro_motivo']??''], 'pia_historico_acolhido'=>['Histórico e observações gerais',$pia['historico']??''],
    'pia_endereco_origem'=>['Endereço de origem',implode(', ', array_filter([$pia['cadastro_rua']??'', $pia['cadastro_numero']??'', $pia['cadastro_cidade']??'', $pia['cadastro_estado']??'', $pia['cadastro_cep']??'', $pia['endereco_referencia']??'', $pia['endereco_observacao']??'']))],
]; ?>
<?php
$dadosBasicosHtml = '';
$dadosBasicosLinhas = [];
foreach ($dadosBasicos as $chaveBasico => [$rotuloBasico, $valorBasico]) {
    if (!$campoPiaVisivel((string)$chaveBasico) || trim((string)$valorBasico) === '') {
        continue;
    }
    $textoBasico = (string)$valorBasico;
    $quebraEmFluxo = strlen($textoBasico) > 450 || strpos($textoBasico, "\n") !== false || strpos($textoBasico, "\r") !== false;
    if ($quebraEmFluxo) {
        if ($dadosBasicosLinhas) {
            $dadosBasicosHtml .= '<table class="data-field"><tbody>' . implode('', $dadosBasicosLinhas) . '</tbody></table>';
            $dadosBasicosLinhas = [];
        }
        $dadosBasicosHtml .= '<div class="data-field-block"><div class="data-field-label">' . $h($rotuloBasico) . '</div><div class="data-field-value">' . nl2br($h($textoBasico), false) . '</div></div>';
        continue;
    }
    $dadosBasicosLinhas[] = '<tr><th>' . $h($rotuloBasico) . '</th><td>' . nl2br($h($textoBasico), false) . '</td></tr>';
}
if ($dadosBasicosLinhas) {
    $dadosBasicosHtml .= '<table class="data-field"><tbody>' . implode('', $dadosBasicosLinhas) . '</tbody></table>';
}
echo $dadosBasicosHtml;
?>
        <?php foreach ($camposCadastro as $campoCadastro): if ($campoCadastro['chave'] === 'data_nascimento' || in_array((string)$campoCadastro['chave'], \Abdm\Model\PiaCadastro::piaCamposCadastroJaPresentesNoFormularioPrincipal(), true) || ((int)$campoCadastro['ativo'] !== 1 && !array_key_exists((string)$campoCadastro['chave'], $dadosCadastro))) continue; ?><?php echo $renderCampo($campoCadastro); ?><?php endforeach; ?>
    </div>
</section><?php endif; ?>

<?php foreach ($titulosSecoes as $secao => $titulo): ?>
    <?php if ($secao === 'acolhido' || (empty($camposPorSecao[$secao]) && !in_array($secao, ['saude', 'composicao_familiar'], true))) { continue; } ?>
    <section class="section">
        <div class="section-title"><?php echo $h($titulo); ?></div>
        <div class="section-content">
            <?php if ($secao === 'saude'): $camposSaudeCadastro = ['restricao_alimentar'=>'Possui restrição alimentar?','qual_restricao'=>'Qual restrição alimentar?','doencas_cronicas'=>'Doenças crônicas','comorbidades'=>'Comorbidades']; ?><table class="data-field"><tbody><?php foreach ($camposSaudeCadastro as $chaveSaudeCadastro=>$rotuloSaudeCadastro): if(!$campoPiaVisivel($chaveSaudeCadastro)) continue; $valorSaudeCadastro=$dadosCadastro[$chaveSaudeCadastro]??''; if(is_array($valorSaudeCadastro))$valorSaudeCadastro=implode(', ',array_map('strval',$valorSaudeCadastro)); ?><tr><th><?php echo $h($rotuloSaudeCadastro); ?></th><td><?php echo nl2br($h($valorSaudeCadastro!==''?(string)$valorSaudeCadastro:'Não informado'),false); ?></td></tr><?php endforeach; ?></tbody></table><?php endif; ?>
            <?php if ($secao === 'saude' && $campoPiaVisivel('cid10') && $cidsCadastro): ?><table class="data-table"><thead><tr><th colspan="2">DIAGNÓSTICOS CID CADASTRADOS</th></tr><tr><th>Código</th><th>Descrição</th></tr></thead><tbody><?php foreach ($cidsCadastro as $cidCadastro): ?><tr><td><?php echo $h(function_exists('mb_strtoupper') ? mb_strtoupper((string)$cidCadastro['codigo'], 'UTF-8') : strtoupper((string)$cidCadastro['codigo'])); ?></td><td><?php echo $h($cidCadastro['descricao']); ?></td></tr><?php endforeach; ?></tbody></table><?php endif; ?>
            <?php if ($secao === 'composicao_familiar' && $campoPiaVisivel('composicao_familiar') && $curadorCadastro): ?><table class="data-table"><thead><tr><th colspan="2">CURADOR OU RESPONSÁVEL LEGAL</th></tr></thead><tbody><?php foreach (['Nome'=>'nome','CPF'=>'cpf','Data de nascimento'=>'datanNascimento','Grau de parentesco'=>'grauParentesco','Telefone'=>'telefone','Telefone 2'=>'telefone_2'] as $rotuloCurador => $campoCurador): if (empty($curadorCadastro[$campoCurador])) continue; $valorCurador = $campoCurador === 'cpf' ? \Abdm\Infraestrutura\Criptografia::descriptografar($curadorCadastro[$campoCurador]) : ($campoCurador === 'datanNascimento' ? \Abdm\Model\Pia::piaDataExibicao($curadorCadastro[$campoCurador]) : $curadorCadastro[$campoCurador]); ?><tr><th><?php echo $h($rotuloCurador); ?></th><td><?php echo $h($valorCurador); ?></td></tr><?php endforeach; ?><?php $enderecoCuradorTexto = implode(', ', array_filter([$curadorCadastro['rua'] ?? '', $curadorCadastro['numero'] ?? '', $curadorCadastro['cidade'] ?? '', $curadorCadastro['estado'] ?? '', $curadorCadastro['cep'] ?? ''])); ?><?php if ($enderecoCuradorTexto !== ''): ?><tr><th>Endereço</th><td><?php echo $h($enderecoCuradorTexto); ?></td></tr><?php endif; ?></tbody></table><?php endif; ?>
            <?php if ($secao === 'composicao_familiar' && $campoPiaVisivel('composicao_familiar') && $familiaresCadastro): ?><table class="data-table"><thead><tr><th colspan="5">FAMILIARES CADASTRADOS</th></tr><tr><th>Nome</th><th>Parentesco</th><th>Data de nascimento</th><th>Telefone</th><th>Endereço</th></tr></thead><tbody><?php foreach ($familiaresCadastro as $familiarCadastro): $enderecoFamiliarTexto = implode(', ', array_filter([$familiarCadastro['rua'] ?? '', $familiarCadastro['numero'] ?? '', $familiarCadastro['cidade'] ?? '', $familiarCadastro['estado'] ?? '', $familiarCadastro['cep'] ?? ''])); ?><tr><td><?php echo $h($familiarCadastro['nome']); ?></td><td><?php echo $h($familiarCadastro['grauParentesco']); ?></td><td><?php echo $h(\Abdm\Model\Pia::piaDataExibicao($familiarCadastro['dataNascimento'] ?? '')); ?></td><td><?php echo $h($familiarCadastro['telefone']); ?></td><td><?php echo $h($enderecoFamiliarTexto); ?></td></tr><?php endforeach; ?></tbody></table><?php endif; ?>
            <?php foreach ($camposPorSecao[$secao] ?? [] as $campo): ?><?php echo $renderCampo($campo); ?><?php endforeach; ?>
        </div>
    </section>
<?php endforeach; ?>

<p class="evolution-attachment"><strong>As evoluções do acolhido estão anexas a este PIA.</strong></p>

<?php if ($assinaturas): ?>
    <h2 class="signatures-title">Assinaturas</h2>
    <table class="signatures">
        <?php foreach (array_chunk($assinaturas, 2) as $grupoAssinaturas): ?>
            <tr>
                <?php foreach ($grupoAssinaturas as $assinatura): ?>
                    <td class="signature-person-cell">
                        <table class="signature-block" style="width:80mm;border-collapse:collapse;table-layout:fixed;margin:0 auto;"><tbody>
                            <tr class="signature-space"><td style="height:15mm;border:0;padding:0;font-size:1px;line-height:1px;">&nbsp;</td></tr>
                            <tr class="signature-rule"><td style="height:1px;border:0;border-top:1px solid #111;padding:0;font-size:1px;line-height:1px;">&nbsp;</td></tr>
                            <tr><td class="signature-name"><?php echo $h($assinatura['nome_assinante']); ?></td></tr>
                            <?php if (($assinatura['modo'] ?? '') !== 'nome'): ?><tr><td class="signature-meta">
                                <?php if (in_array($assinatura['modo'], ['profissional', 'ambos'], true) && !empty($assinatura['atuacao_profissional'])): ?>
                                    <?php echo $h($assinatura['atuacao_profissional']); ?><?php if (!empty($assinatura['registro_profissional'])): ?><br>Registro profissional: <?php echo $h($assinatura['registro_profissional']); ?><?php endif; ?>
                                <?php endif; ?>
                                <?php if (in_array($assinatura['modo'], ['conselho', 'ambos'], true) && !empty($assinatura['cargo_conselho'])): ?>
                                    <?php if (in_array($assinatura['modo'], ['profissional', 'ambos'], true)): ?><br><?php endif; ?>
                                    <?php echo $h($assinatura['cargo_conselho']); ?>
                                    <?php if (!empty($assinatura['inicio_mandato']) || !empty($assinatura['fim_mandato'])): ?><br>Mandato: <?php echo $h(\Abdm\Model\Pia::piaDataExibicao($assinatura['inicio_mandato'] ?? '')); ?> a <?php echo $h(\Abdm\Model\Pia::piaDataExibicao($assinatura['fim_mandato'] ?? '')); ?><?php endif; ?>
                                <?php endif; ?>
                            </td></tr><?php endif; ?>
                        </tbody></table>
                    </td>
                <?php endforeach; ?>
                <?php if (count($grupoAssinaturas) === 1): ?><td class="signature-person-cell"></td><?php endif; ?>
            </tr>
        <?php endforeach; ?>
    </table>
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
    $mpdf->SetAutoPageBreak(true, 35);
    $mpdf->imageVars['cabecalhoimg'] = file_get_contents($cabecalhoPath);
    $mpdf->imageVars['rodapeimg'] = file_get_contents($rodapePath);
    $mpdf->SetHTMLHeader('<img src="var:cabecalhoimg" style="width:100%;" />');
    $contextoRodapePia = 'PIA · Atualizado em ' . \Abdm\Model\Pia::piaDataExibicao($pia['dataAtualizacao'] ?? '');
    $mpdf->SetHTMLFooter(\Abdm\View\RodapeImpressao::rodapeImpressaoPadrao((new \Abdm\Controller\DadosImpressaoController($pdo))->nomeUsuarioImpressaoAtual(), $contextoRodapePia));
    $mpdf->WriteHTML($htmlConteudo);
    $nomeArquivo = preg_replace('/[^A-Za-z0-9_-]+/', '_', (string)$pia['acolhido_nome']);
    $mpdf->Output('pia_' . trim((string)$nomeArquivo, '_') . '.pdf', 'I');
} catch (\Mpdf\MpdfException $exception) {
    http_response_code(500);
    error_log('Falha ao gerar impressão do PIA. Código: ' . $exception->getCode());
    exit('Não foi possível preparar a impressão do PIA.');
}

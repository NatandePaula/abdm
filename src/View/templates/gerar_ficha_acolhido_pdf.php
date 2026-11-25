<?php
// 1. Carregar dependências
$autoload_path = ROOT_DIR . '/vendor/autoload.php';
if (!file_exists($autoload_path)) {
    http_response_code(500);
    die("<h1>Erro de Configuração do Servidor</h1>" .
        "<p>A biblioteca para gerar PDFs (mPDF) não foi encontrada. O arquivo <code>" . htmlspecialchars($autoload_path) . "</code> não existe.</p>" .
        "<p>Isso geralmente acontece porque as dependências do projeto não foram instaladas com o Composer.</p>" .
        "<p><strong>Como corrigir:</strong></p>" .
        "<ol>" .
        "<li>Abra um terminal (CMD, PowerShell, Git Bash, etc.).</li>" .
        "<li>Navegue até a pasta raiz do seu projeto com o comando: <code>cd c:\\xampp\\htdocs\\abdm</code></li>" .
        "<li>Execute o comando do Composer para instalar as bibliotecas: <code>composer install</code></li>" .
        "</ol>");
}
require_once $autoload_path;
$controleAcesso ??= new \Abdm\Infraestrutura\ControleAcesso($pdo);
$controleAcesso->exigirPermissao('acolhidos');
$controleAcesso->exigirUsuarioAutenticado();

[
    'acolhido' => $acolhido,
    'gruposComplementares' => $gruposComplementares,
    'cids' => $cids,
    'endereco' => $endereco,
    'curador' => $curador,
    'enderecoCurador' => $enderecoCurador,
] = (new \Abdm\Controller\TelasAcolhidoController($pdo))->fichaParaImpressao();

ob_start();
?>
<style>
    body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; color: #333; font-size: 10pt; }
    .main-title { text-align: center; font-size: 18pt; font-weight: bold; margin-bottom: 10px; }
    .sub-title { text-align: center; font-size: 14pt; font-weight: normal; margin-top: 0; margin-bottom: 20px; }
    .section-title { font-size: 12pt; font-weight: bold; border-bottom: 1px solid #ccc; padding-bottom: 3px; margin-top: 20px; margin-bottom: 10px; page-break-after: avoid; }
    .data-table { width: 100%; border-collapse: collapse; font-size: 10pt; margin-bottom: 15px; page-break-inside: auto; break-inside: auto; }
    .data-table tbody, .data-table tr, .data-table th, .data-table td { page-break-inside: auto; break-inside: auto; }
    .data-table tr { page-break-before: allow; page-break-after: allow; }
    .data-table th, .data-table td { border: 1px solid #ddd; padding: 6px; text-align: left; vertical-align: top; }
    .data-table th { background-color: #f2f2f2; font-weight: bold; width: 30%; }
    .data-table td { white-space: pre-wrap; text-align: justify; overflow-wrap: anywhere; }
    .highlight-warning { background-color: #fff3cd; color: #664d03; font-weight: bold; }
</style>

<h1 class="main-title">Ficha Cadastral do Acolhido</h1>
<h2 class="sub-title"><?php echo htmlspecialchars($acolhido['nome']); ?></h2>

<?php if ($acolhido['status'] == 'desligado'): ?>
    <h3 class="section-title">Status do Acolhido</h3>
    <table class="data-table">
        <tbody>
            <tr><th>Status</th><td class="highlight-warning">DESLIGADO</td></tr>
            <tr><th>Data de Desligamento</th><td><?php echo date('d/m/Y', strtotime($acolhido['dataDesligamento'])); ?></td></tr>
            <tr><th>Motivo do Desligamento</th><td><?php echo htmlspecialchars($acolhido['motivoDesligamento']); ?></td></tr>
        </tbody>
    </table>
<?php endif; ?>

<h3 class="section-title">Dados Pessoais</h3>
<table class="data-table">
    <tbody>
        <tr><th>Nome Completo</th><td><?php echo htmlspecialchars($acolhido['nome']); ?></td></tr>
        <tr><th>Data de Nascimento</th><td><?php echo date('d/m/Y', strtotime($acolhido['dataNascimento'])); ?></td></tr>
        <tr><th>Sexo</th><td><?php echo htmlspecialchars(\Abdm\Model\DadosAcolhido::acolhidoSexoRotulo($acolhido['sexo'] ?? null), ENT_QUOTES, 'UTF-8'); ?></td></tr>
        <tr><th>CPF</th><td><?php echo htmlspecialchars(\Abdm\Infraestrutura\Criptografia::descriptografar($acolhido['cpf'])); ?></td></tr>
        <tr><th>Doc. Identificação</th><td><?php echo htmlspecialchars(($acolhido['tipo_documento'] ?? 'Doc') . ': ' . (\Abdm\Model\DadosAcolhido::acolhidoDocumentoDescriptografar($acolhido['documento_identificacao'] ?? '') ?: 'Não informado')); ?></td></tr>
        <tr><th>Nome da Mãe</th><td><?php echo htmlspecialchars($acolhido['nome_mae'] ?? 'Não informada'); ?></td></tr>
        <tr><th>Nome do Pai</th><td><?php echo htmlspecialchars($acolhido['nome_pai'] ?? 'Não informado'); ?></td></tr>
    </tbody>
</table>

<h3 class="section-title">Informações de Acolhimento</h3>
<table class="data-table">
        <tbody>
        <tr><th>Matrícula</th><td><?php echo htmlspecialchars($acolhido['matricula'] ?? 'N/A'); ?></td></tr>
        <tr><th>Data de Acolhimento</th><td><?php echo date('d/m/Y', strtotime($acolhido['dataAcolhimento'])); ?></td></tr>
        <tr><th>Origem do Acolhimento</th><td><?php echo htmlspecialchars($acolhido['origemAcolhimento'] ?? 'Não informada'); ?></td></tr>
        <tr><th>Motivo da Institucionalização</th><td><?php echo htmlspecialchars($acolhido['motivoInstitucionalizacao'] ?? 'Não informado'); ?></td></tr>
        <tr><th>Histórico / Observações</th><td><?php echo htmlspecialchars($acolhido['historico'] ?? 'Nenhum histórico registrado.'); ?></td></tr>
        </tbody>
</table>

<h3 class="section-title">Endereço de Origem</h3>
<table class="data-table">
    <tbody>
        <?php if($endereco): ?>
            <tr><th>Logradouro</th><td><?php echo htmlspecialchars($endereco['rua'] . ', ' . $endereco['numero']); ?></td></tr>
            <tr><th>Cidade / Estado</th><td><?php echo htmlspecialchars($endereco['cidade'] . ' - ' . $endereco['estado']); ?></td></tr>
            <tr><th>CEP</th><td><?php echo htmlspecialchars($endereco['cep']); ?></td></tr>
            <tr><th>Referência</th><td><?php echo htmlspecialchars($acolhido['endereco_referencia'] ?? 'Não informado'); ?></td></tr>
            <tr><th>Observação</th><td><?php echo htmlspecialchars($acolhido['endereco_observacao'] ?? 'Nenhuma.'); ?></td></tr>
        <?php else: ?>
            <tr><td colspan="2">Endereço não informado.</td></tr>
        <?php endif; ?>
    </tbody>
</table>

<h3 class="section-title">Curador / Responsável Legal</h3>
<table class="data-table">
    <tbody>
        <?php if($curador): ?>
            <tr><th>Nome</th><td><?php echo htmlspecialchars($curador['nome']); ?></td></tr>
            <tr><th>Grau de Parentesco</th><td><?php echo htmlspecialchars($curador['grauParentesco']); ?></td></tr>
            <tr><th>CPF</th><td><?php echo htmlspecialchars(\Abdm\Infraestrutura\Criptografia::descriptografar($curador['cpf'])); ?></td></tr>
            <tr><th>Data de Nascimento</th><td><?php echo !empty($curador['datanNascimento']) ? date('d/m/Y', strtotime($curador['datanNascimento'])) : 'Não informado'; ?></td></tr>
            <tr><th>Telefone</th><td><?php echo htmlspecialchars($curador['telefone'] ?? 'Não informado'); ?></td></tr>
            <?php if(!empty($curador['telefone_2'])): ?>
                <tr><th>Telefone 2</th><td><?php echo htmlspecialchars($curador['telefone_2']); ?></td></tr>
            <?php endif; ?>
            <?php if($enderecoCurador): ?>
                <tr><th>Endereço</th><td><?php echo htmlspecialchars($enderecoCurador['rua'] . ', ' . $enderecoCurador['numero'] . ' - ' . $enderecoCurador['cidade'] . '/' . $enderecoCurador['estado']); ?></td></tr>
                <tr><th>CEP</th><td><?php echo htmlspecialchars($enderecoCurador['cep']); ?></td></tr>
            <?php endif; ?>
        <?php else: ?>
            <tr><td colspan="2">Não possui / Não informado.</td></tr>
        <?php endif; ?>
    </tbody>
</table>

<?php if(count($cids) > 0): ?>
<h3 class="section-title">CIDs Relacionados</h3>
<table class="data-table">
    <tbody>
    <?php foreach($cids as $cid): ?>
        <tr>
            <th style="width: 20%;"><?php echo htmlspecialchars(function_exists('mb_strtoupper') ? mb_strtoupper((string)$cid['codigo'], 'UTF-8') : strtoupper((string)$cid['codigo'])); ?></th>
            <td style="width: 80%; text-align: left;"><?php echo htmlspecialchars($cid['descricao']); ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>

<?php foreach ($gruposComplementares as $tituloComplementar => $itensComplementares): ?>
<h3 class="section-title"><?php echo htmlspecialchars($tituloComplementar, ENT_QUOTES, 'UTF-8'); ?></h3>
<table class="data-table"><tbody>
    <?php foreach ($itensComplementares as $rotuloComplementar => $valorComplementar): ?>
        <tr><th><?php echo htmlspecialchars($rotuloComplementar, ENT_QUOTES, 'UTF-8'); ?></th><td><?php echo htmlspecialchars($valorComplementar, ENT_QUOTES, 'UTF-8'); ?></td></tr>
    <?php endforeach; ?>
</tbody></table>
<?php endforeach; ?>

<h3 class="section-title">Informações Clínicas e Benefícios</h3>
<table class="data-table">
    <tbody>
        <tr><th>Cartão SUS</th><td><?php echo htmlspecialchars(\Abdm\Model\DadosAcolhido::acolhidoDocumentoDescriptografar($acolhido['cartao_sus'] ?? '') ?: 'Não possui'); ?></td></tr>
        <tr><th>Cartão Municipal de Saúde</th><td><?php echo htmlspecialchars(\Abdm\Model\DadosAcolhido::acolhidoDocumentoDescriptografar($acolhido['cartao_municipal_saude'] ?? '') ?: 'Não possui'); ?></td></tr>
        <tr><th>Tipo de Benefício</th><td><?php echo htmlspecialchars($acolhido['tipoBeneficio'] ?? 'Não possui'); ?></td></tr>
        <tr><th>Plano Assistencial / Funerário</th><td><?php echo htmlspecialchars($acolhido['plano_assistencial_funerario'] ?? 'Não possui'); ?></td></tr>
        <tr><th>Comorbidades, Alergias, Restrições</th><td><?php echo htmlspecialchars($acolhido['info_saude'] ?? 'Não informado.'); ?></td></tr>
        <tr><th>Informações Nutricionais</th><td><?php echo htmlspecialchars($acolhido['info_nutri'] ?? 'Não informado.'); ?></td></tr>
    </tbody>
</table>

<?php
$html_content = ob_get_clean();

// 4. Gerar o PDF com mPDF
try {
    $mpdf = new \Mpdf\Mpdf([
        'mode' => 'utf-8', 'format' => 'A4',
        'margin_top' => 35, 'margin_bottom' => 35,
        'margin_left' => 15, 'margin_right' => 15,
    ]);

    // Carrega os arquivos locais do cabeçalho e do rodapé da ficha.
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

    $filename = 'ficha_acolhido_' . str_replace(' ', '_', strtolower($acolhido['nome'])) . '.pdf';
    $mpdf->Output($filename, 'I');

} catch (\Mpdf\MpdfException $e) {
    error_log('Falha ao gerar o PDF da ficha do acolhido.');
    http_response_code(500);
    exit('Não foi possível gerar o PDF.');
}
?>

<?php
$documentosForm = is_array($dadosComplementares['documentos'] ?? null) ? $dadosComplementares['documentos'] : [];
$documentosRotulos = [
    'certidao' => 'Certidão de nascimento e/ou casamento',
    'rg' => 'RG',
    'cpf' => 'CPF',
    'titulo_eleitoral' => 'Título eleitoral',
    'ctps' => 'CTPS',
    'sus' => 'Cartão Nacional do SUS',
    'cartao_municipal' => 'Cartão Municipal de Saúde',
    'outros' => 'Outros documentos',
];
$statusDocumentoRotulos = [
    '' => 'Selecione a situação',
    'possui' => 'Possui',
    'nao_possui' => 'Não possui',
    'nao_sabe_informar' => 'Não sabe informar',
    'nao_disponibilizado' => 'Possui, mas não está disponível no momento',
];
?>

<h5 class="mt-4 mb-3 border-bottom pb-2 text-primary">Documentos pessoais</h5>
<p class="text-body-secondary">Informe o número quando disponível e selecione a situação de cada documento.</p>
<div class="table-responsive mb-4">
    <table class="table table-bordered align-middle">
        <thead class="table-light"><tr><th scope="col">Documento</th><th scope="col">Número</th><th scope="col">Situação</th></tr></thead>
        <tbody>
            <?php foreach ($documentosRotulos as $chaveDocumento => $rotuloDocumento): ?>
                <?php
                $linhaDocumento = is_array($documentosForm[$chaveDocumento] ?? null) ? $documentosForm[$chaveDocumento] : [];
                $statusDocumento = (string)($linhaDocumento['situacao'] ?? ($chaveDocumento === 'cpf' && !empty($cpfForm) ? 'possui' : ''));
                $numeroDocumento = $chaveDocumento === 'cpf' ? (string)($cpfForm ?? '') : (string)($linhaDocumento['numero'] ?? '');
                if ($chaveDocumento === 'sus' && $numeroDocumento === '') $numeroDocumento = \Abdm\Model\DadosAcolhido::acolhidoDocumentoDescriptografar($acolhido['cartao_sus'] ?? '');
                if ($chaveDocumento === 'cartao_municipal' && $numeroDocumento === '') $numeroDocumento = (string)($cartaoMunicipalForm ?? '');
                if ($chaveDocumento === 'cartao_municipal' && $statusDocumento === '' && $numeroDocumento !== '') $statusDocumento = 'possui';
                if ($chaveDocumento === 'cartao_municipal' && $statusDocumento === '' && $numeroDocumento === '' && !empty($naoPossuiCartaoMunicipalForm)) $statusDocumento = 'nao_possui';
                $idDocumento = 'documento-' . $chaveDocumento;
                ?>
                <tr>
                    <th scope="row"><?php echo htmlspecialchars($rotuloDocumento, ENT_QUOTES, 'UTF-8'); ?></th>
                    <td>
                        <?php if ($chaveDocumento === 'cpf'): ?>
                            <input class="form-control" id="inputCPF" name="cpf" type="text" inputmode="numeric" autocomplete="off" placeholder="000.000.000-00" maxlength="14" value="<?php echo htmlspecialchars($numeroDocumento, ENT_QUOTES, 'UTF-8'); ?>" required aria-describedby="cpf-feedback">
                            <div class="invalid-feedback" id="cpf-feedback">CPF inválido.</div>
                        <?php else: ?>
                            <input class="form-control" id="<?php echo htmlspecialchars($idDocumento, ENT_QUOTES, 'UTF-8'); ?>" name="dados_acolhido[documentos][<?php echo htmlspecialchars($chaveDocumento, ENT_QUOTES, 'UTF-8'); ?>][numero]" type="text" maxlength="100" value="<?php echo htmlspecialchars($numeroDocumento, ENT_QUOTES, 'UTF-8'); ?>" aria-label="Número: <?php echo htmlspecialchars($rotuloDocumento, ENT_QUOTES, 'UTF-8'); ?>">
                        <?php endif; ?>
                    </td>
                    <td>
                        <select class="form-select" name="dados_acolhido[documentos][<?php echo htmlspecialchars($chaveDocumento, ENT_QUOTES, 'UTF-8'); ?>][situacao]" aria-label="Situação: <?php echo htmlspecialchars($rotuloDocumento, ENT_QUOTES, 'UTF-8'); ?>">
                            <?php foreach ($statusDocumentoRotulos as $valorStatus => $rotuloStatus): ?>
                                <option value="<?php echo htmlspecialchars($valorStatus, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $statusDocumento === $valorStatus ? 'selected' : ''; ?>><?php echo htmlspecialchars($rotuloStatus, ENT_QUOTES, 'UTF-8'); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<h5 class="mt-4 mb-3 border-bottom pb-2 text-primary">Orientação sexual</h5>
<div class="row g-3 mb-4">
    <div class="col-12 col-md-6"><label class="form-label" for="orientacaoSexual">Orientação sexual</label><select class="form-select" id="orientacaoSexual" name="dados_acolhido[orientacao_sexual]"><option value="">Selecione</option><?php foreach (['heterossexual'=>'Heterossexual','homossexual'=>'Homossexual','bissexual'=>'Bissexual','pansexual'=>'Pansexual','assexual'=>'Assexual','outros'=>'Outra','prefere_nao_informar'=>'Prefere não informar'] as $valor => $rotulo): ?><option value="<?php echo htmlspecialchars($valor, ENT_QUOTES, 'UTF-8'); ?>" <?php echo ($dadosComplementares['orientacao_sexual'] ?? '') === $valor ? 'selected' : ''; ?>><?php echo htmlspecialchars($rotulo, ENT_QUOTES, 'UTF-8'); ?></option><?php endforeach; ?></select></div>
    <div class="col-12 col-md-6"><label class="form-label" for="orientacaoSexualOutros">Se marcou “Outra”, especifique</label><input class="form-control" id="orientacaoSexualOutros" name="dados_acolhido[orientacao_sexual_outros]" maxlength="300" value="<?php echo htmlspecialchars((string)($dadosComplementares['orientacao_sexual_outros'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"></div>
</div>

<h5 class="mt-4 mb-3 border-bottom pb-2 text-primary">Benefícios, renda e atividade</h5>
<?php $beneficiosForm = is_array($dadosComplementares['beneficios'] ?? null) ? $dadosComplementares['beneficios'] : []; ?>
<fieldset class="mb-3">
    <legend class="form-label fs-6">Benefícios sociais ou previdenciários</legend>
    <div class="row g-2">
        <?php foreach (['nenhum'=>'Não possui benefício','aposentadoria'=>'Aposentadoria','pensao'=>'Pensão','bpc'=>'BPC/LOAS','programa_social'=>'Programa social ou transferência de renda','outro'=>'Outro benefício'] as $codigoBeneficio => $rotuloBeneficio): ?>
            <div class="col-12 col-md-6"><div class="form-check"><input class="form-check-input" type="checkbox" id="beneficio-<?php echo htmlspecialchars($codigoBeneficio, ENT_QUOTES, 'UTF-8'); ?>" name="dados_acolhido[beneficios][]" value="<?php echo htmlspecialchars($codigoBeneficio, ENT_QUOTES, 'UTF-8'); ?>" <?php echo in_array($codigoBeneficio, $beneficiosForm, true) ? 'checked' : ''; ?>><label class="form-check-label" for="beneficio-<?php echo htmlspecialchars($codigoBeneficio, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($rotuloBeneficio, ENT_QUOTES, 'UTF-8'); ?></label></div></div>
        <?php endforeach; ?>
    </div>
</fieldset>
<div class="row g-3 mb-4">
    <div class="col-12 col-md-6"><label class="form-label" for="atividadeRemunerada">Exerce atividade remunerada?</label><select class="form-select" id="atividadeRemunerada" name="dados_acolhido[atividade_remunerada]"><option value="">Não informado</option><option value="sim" <?php echo ($dadosComplementares['atividade_remunerada'] ?? '') === 'sim' ? 'selected' : ''; ?>>Sim</option><option value="nao" <?php echo ($dadosComplementares['atividade_remunerada'] ?? '') === 'nao' ? 'selected' : ''; ?>>Não</option></select></div>
    <div class="col-12 col-md-6"><label class="form-label" for="atividadeDetalhe">Atividade ou fonte de renda</label><input class="form-control" id="atividadeDetalhe" name="dados_acolhido[beneficio_atividade]" maxlength="5000" value="<?php echo htmlspecialchars((string)($dadosComplementares['beneficio_atividade'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" placeholder="Descreva a atividade, se houver"></div>
    <div class="col-12 col-md-6"><label class="form-label" for="beneficioOutro">Detalhe de outro benefício</label><input class="form-control" id="beneficioOutro" name="dados_acolhido[beneficio_outro]" maxlength="5000" value="<?php echo htmlspecialchars((string)($dadosComplementares['beneficio_outro'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"></div>
    <div class="col-12 col-md-6"><label class="form-label" for="outrasFontesRenda">Outras fontes de renda (por exemplo, doações)</label><input class="form-control" id="outrasFontesRenda" name="dados_acolhido[outras_fontes_renda]" maxlength="5000" value="<?php echo htmlspecialchars((string)($dadosComplementares['outras_fontes_renda'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"></div>
    <div class="col-12 col-md-6"><label class="form-label" for="administraRecursos">Como a renda é administrada?</label><select class="form-select" id="administraRecursos" name="dados_acolhido[administra_recursos]"><option value="">Selecione</option><?php foreach (['sozinho'=>'O acolhido administra sozinho','com_auxilio'=>'O acolhido administra com auxílio','outra_pessoa'=>'Outra pessoa administra'] as $valor => $rotulo): ?><option value="<?php echo htmlspecialchars($valor, ENT_QUOTES, 'UTF-8'); ?>" <?php echo ($dadosComplementares['administra_recursos'] ?? '') === $valor ? 'selected' : ''; ?>><?php echo htmlspecialchars($rotulo, ENT_QUOTES, 'UTF-8'); ?></option><?php endforeach; ?></select></div>
    <div class="col-12 col-md-6"><label class="form-label" for="responsavelRecursos">Quem auxilia ou administra a renda?</label><input class="form-control" id="responsavelRecursos" name="dados_acolhido[responsavel_recursos]" maxlength="1000" value="<?php echo htmlspecialchars((string)($dadosComplementares['responsavel_recursos'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"></div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const nenhum = document.getElementById('beneficio-nenhum');
    const demais = Array.from(document.querySelectorAll('input[name="dados_acolhido[beneficios][]"]')).filter((input) => input !== nenhum);
    if (!nenhum) return;
    nenhum.addEventListener('change', () => {
        if (nenhum.checked) demais.forEach((input) => { input.checked = false; });
    });
    demais.forEach((input) => input.addEventListener('change', () => { if (input.checked) nenhum.checked = false; }));
});
</script>

<?php
$selectSaude = static function (string $chave, string $rotulo, $valorAtual): void {
    ?>
    <div class="col-12 col-md-6"><label class="form-label" for="saude-<?php echo htmlspecialchars($chave, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($rotulo, ENT_QUOTES, 'UTF-8'); ?></label><select class="form-select" id="saude-<?php echo htmlspecialchars($chave, ENT_QUOTES, 'UTF-8'); ?>" name="dados_acolhido[<?php echo htmlspecialchars($chave, ENT_QUOTES, 'UTF-8'); ?>]"><option value="">Selecione</option><option value="sim" <?php echo $valorAtual === 'sim' ? 'selected' : ''; ?>>Sim</option><option value="nao" <?php echo $valorAtual === 'nao' ? 'selected' : ''; ?>>Não</option></select></div>
    <?php
};
$textareaSaude = static function (string $chave, string $rotulo, $valorAtual, int $linhas = 3): void {
    ?>
    <div class="col-12 col-md-6"><label class="form-label" for="saude-<?php echo htmlspecialchars($chave, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($rotulo, ENT_QUOTES, 'UTF-8'); ?></label><textarea class="form-control auto-expand" id="saude-<?php echo htmlspecialchars($chave, ENT_QUOTES, 'UTF-8'); ?>" name="dados_acolhido[<?php echo htmlspecialchars($chave, ENT_QUOTES, 'UTF-8'); ?>]" rows="<?php echo $linhas; ?>"><?php echo htmlspecialchars((string)$valorAtual, ENT_QUOTES, 'UTF-8'); ?></textarea></div>
    <?php
};
?>

<h5 class="mt-4 mb-3 border-bottom pb-2 text-primary">Saúde e acompanhamento</h5>
<div class="row g-3 mb-3">
    <?php $selectSaude('laudo_medico', 'Possui laudo médico?', (string)($dadosComplementares['laudo_medico'] ?? '')); ?>
    <div class="col-12 col-md-6"><label class="form-label" for="saude-cid10">CID-10</label><div class="form-text">Cadastre os códigos CID-10 na lista acima. Eles serão vinculados automaticamente ao registro.</div></div>
    <?php $selectSaude('dst', 'Possui alguma IST?', (string)($dadosComplementares['dst'] ?? '')); ?>
    <?php $textareaSaude('qual_dst', 'Se sim, qual IST?', $dadosComplementares['qual_dst'] ?? '', 2); ?>
    <?php $textareaSaude('especialistas_tratamentos', 'Médicos especialistas com quem já se tratou', $dadosComplementares['especialistas_tratamentos'] ?? '', 4); ?>
    <?php $textareaSaude('especialistas_necessarios', 'Médicos especialistas de que necessita atualmente', $dadosComplementares['especialistas_necessarios'] ?? '', 4); ?>
    <?php $selectSaude('orteses_proteses', 'Necessita de órteses ou próteses?', (string)($dadosComplementares['orteses_proteses'] ?? '')); ?>
    <?php $textareaSaude('qual_orteses_proteses', 'Se sim, quais?', $dadosComplementares['qual_orteses_proteses'] ?? '', 2); ?>
    <?php $selectSaude('cuidados_personalizados', 'Necessita de cuidados personalizados?', (string)($dadosComplementares['cuidados_personalizados'] ?? '')); ?>
    <?php $textareaSaude('qual_cuidados_personalizados', 'Se sim, quais?', $dadosComplementares['qual_cuidados_personalizados'] ?? '', 3); ?>
</div>

<h5 class="mt-4 mb-3 border-bottom pb-2 text-primary">Informações Clínicas e Nutricionais</h5>
<div class="row g-3 mb-4">
    <div class="col-12 col-md-6"><label for="inputInfoSaude" class="form-label fw-bold text-secondary">Outras comorbidades, alergias e restrições</label><textarea class="form-control auto-expand" id="inputInfoSaude" name="info_saude" rows="4"><?php echo htmlspecialchars((string)($acolhido['info_saude'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></textarea></div>
    <div class="col-12 col-md-6"><label for="inputInfoNutri" class="form-label fw-bold text-secondary">Informações alimentares e nutricionais</label><textarea class="form-control auto-expand" id="inputInfoNutri" name="info_nutri" rows="4"><?php echo htmlspecialchars((string)($acolhido['info_nutri'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></textarea></div>
</div>

<?php $envolvimentoAtual = is_array($dadosComplementares['envolvimento_substancias'] ?? null) ? $dadosComplementares['envolvimento_substancias'] : []; ?>
<fieldset class="mb-3">
    <legend class="form-label fs-6">Há indícios de envolvimento do acolhido com:</legend>
    <div class="row g-2">
        <?php foreach (['alcool'=>'Álcool','tabaco'=>'Tabaco','outras_drogas'=>'Outras drogas','outros'=>'Outros'] as $valorSubstancia => $rotuloSubstancia): ?>
            <div class="col-6 col-md-3"><div class="form-check"><input class="form-check-input" type="checkbox" id="substancia-<?php echo substr(sha1($valorSubstancia), 0, 8); ?>" name="dados_acolhido[envolvimento_substancias][]" value="<?php echo htmlspecialchars($valorSubstancia, ENT_QUOTES, 'UTF-8'); ?>" <?php echo in_array($valorSubstancia, $envolvimentoAtual, true) ? 'checked' : ''; ?>><label class="form-check-label" for="substancia-<?php echo substr(sha1($valorSubstancia), 0, 8); ?>"><?php echo htmlspecialchars($rotuloSubstancia, ENT_QUOTES, 'UTF-8'); ?></label></div></div>
        <?php endforeach; ?>
    </div>
</fieldset>
<div class="mb-4"><label class="form-label" for="saude-envolvimento_outros">Se marcou “Outros”, especifique</label><input class="form-control" id="saude-envolvimento_outros" name="dados_acolhido[envolvimento_outros]" maxlength="5000" value="<?php echo htmlspecialchars((string)($dadosComplementares['envolvimento_outros'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"></div>

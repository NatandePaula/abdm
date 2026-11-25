<?php
$perfilForm = $perfilForm ?? [];
$enderecoForm = $enderecoForm ?? [];
$permissoesCatalogo = $permissoesCatalogo ?? \Abdm\Infraestrutura\ControleAcesso::catalogoFuncionalidadesAplicacao();
$permissoesSelecionadas = $permissoesSelecionadas ?? [];
$prefixoCampos = $prefixoCampos ?? 'usuario';
$formIsAdmin = $formIsAdmin ?? false;
$valorCampo = static function (array $dados, string $campo): string {
    $valor = $dados[$campo] ?? '';
    return is_scalar($valor) ? (string)$valor : '';
};
$idCampo = static function (string $campo) use ($prefixoCampos): string {
    return $prefixoCampos . '-' . preg_replace('/[^a-zA-Z0-9_-]/', '-', $campo);
};
$gruposPermissoes = [
    'Acolhidos' => ['acolhidos'],
    'PIA' => ['pia', 'gerenciar_pia_campos'],
    'Evoluções' => ['registrar_evolucao_acolhido', 'consultar_minhas_evolucoes', 'consultar_todas_evolucoes', 'imprimir_evolucao_acolhido', 'imprimir_minhas_evolucoes', 'imprimir_evolucoes_profissional', 'imprimir_evolucoes_todos'],
    'Saúde dos acolhidos' => ['saude_acolhido'],
    'Farmácia' => ['prescricoes', 'dispensacao', 'estoque'],
    'Relatório diário' => ['relatorio_diario'],
    'Sistema' => ['calendario', 'gerenciar_calendario'],
    'Informações' => ['informacoes', 'gerenciar_informacoes'],
];
$permissoesNaoAgrupadas = $permissoesCatalogo;
?>
<h3 class="h6 border-bottom pb-2 mt-2">Dados pessoais e profissionais</h3>
<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label" for="<?php echo htmlspecialchars($idCampo('nome'), ENT_QUOTES, 'UTF-8'); ?>">Nome completo *</label>
        <input class="form-control" id="<?php echo htmlspecialchars($idCampo('nome'), ENT_QUOTES, 'UTF-8'); ?>" name="perfil[nome]" maxlength="45" value="<?php echo htmlspecialchars($valorCampo($perfilForm, 'nome'), ENT_QUOTES, 'UTF-8'); ?>" required>
    </div>
    <div class="col-md-3">
        <label class="form-label" for="<?php echo htmlspecialchars($idCampo('cpf'), ENT_QUOTES, 'UTF-8'); ?>">CPF *</label>
        <input class="form-control" id="<?php echo htmlspecialchars($idCampo('cpf'), ENT_QUOTES, 'UTF-8'); ?>" name="perfil[cpf]" inputmode="numeric" maxlength="14" value="<?php echo htmlspecialchars($valorCampo($perfilForm, 'cpf'), ENT_QUOTES, 'UTF-8'); ?>" data-cpf-field required>
    </div>
    <div class="col-md-3">
        <label class="form-label" for="<?php echo htmlspecialchars($idCampo('dataNascimento'), ENT_QUOTES, 'UTF-8'); ?>">Data de nascimento *</label>
        <input class="form-control" type="date" id="<?php echo htmlspecialchars($idCampo('dataNascimento'), ENT_QUOTES, 'UTF-8'); ?>" name="perfil[dataNascimento]" value="<?php echo htmlspecialchars($valorCampo($perfilForm, 'dataNascimento'), ENT_QUOTES, 'UTF-8'); ?>" required>
    </div>
    <div class="col-md-4">
        <label class="form-label" for="<?php echo htmlspecialchars($idCampo('email'), ENT_QUOTES, 'UTF-8'); ?>">E-mail</label>
        <input class="form-control" type="email" id="<?php echo htmlspecialchars($idCampo('email'), ENT_QUOTES, 'UTF-8'); ?>" name="perfil[email]" maxlength="45" value="<?php echo htmlspecialchars($valorCampo($perfilForm, 'email'), ENT_QUOTES, 'UTF-8'); ?>">
    </div>
    <div class="col-md-4">
        <label class="form-label" for="<?php echo htmlspecialchars($idCampo('telefone'), ENT_QUOTES, 'UTF-8'); ?>">Telefone</label>
        <input class="form-control" type="tel" id="<?php echo htmlspecialchars($idCampo('telefone'), ENT_QUOTES, 'UTF-8'); ?>" name="perfil[telefone]" maxlength="15" value="<?php echo htmlspecialchars($valorCampo($perfilForm, 'telefone'), ENT_QUOTES, 'UTF-8'); ?>">
    </div>
    <div class="col-md-4">
        <label class="form-label" for="<?php echo htmlspecialchars($idCampo('atuacaoProfissional'), ENT_QUOTES, 'UTF-8'); ?>">Atuação profissional</label>
        <input class="form-control" id="<?php echo htmlspecialchars($idCampo('atuacaoProfissional'), ENT_QUOTES, 'UTF-8'); ?>" name="perfil[atuacaoProfissional]" maxlength="120" value="<?php echo htmlspecialchars($valorCampo($perfilForm, 'atuacaoProfissional'), ENT_QUOTES, 'UTF-8'); ?>">
    </div>
    <div class="col-md-3">
        <label class="form-label" for="<?php echo htmlspecialchars($idCampo('registroProfissional'), ENT_QUOTES, 'UTF-8'); ?>">Registro profissional</label>
        <input class="form-control" id="<?php echo htmlspecialchars($idCampo('registroProfissional'), ENT_QUOTES, 'UTF-8'); ?>" name="perfil[registroProfissional]" maxlength="10" value="<?php echo htmlspecialchars($valorCampo($perfilForm, 'registroProfissional'), ENT_QUOTES, 'UTF-8'); ?>">
    </div>
    <div class="col-md-3">
        <label class="form-label" for="<?php echo htmlspecialchars($idCampo('cargoConselho'), ENT_QUOTES, 'UTF-8'); ?>">Cargo no conselho</label>
        <input class="form-control" id="<?php echo htmlspecialchars($idCampo('cargoConselho'), ENT_QUOTES, 'UTF-8'); ?>" name="perfil[cargoConselho]" maxlength="45" value="<?php echo htmlspecialchars($valorCampo($perfilForm, 'cargoConselho'), ENT_QUOTES, 'UTF-8'); ?>">
    </div>
    <div class="col-md-2">
        <label class="form-label" for="<?php echo htmlspecialchars($idCampo('CBO'), ENT_QUOTES, 'UTF-8'); ?>">CBO</label>
        <input class="form-control" id="<?php echo htmlspecialchars($idCampo('CBO'), ENT_QUOTES, 'UTF-8'); ?>" name="perfil[CBO]" maxlength="10" value="<?php echo htmlspecialchars($valorCampo($perfilForm, 'CBO'), ENT_QUOTES, 'UTF-8'); ?>">
    </div>
    <div class="col-md-2">
        <label class="form-label" for="<?php echo htmlspecialchars($idCampo('matricula'), ENT_QUOTES, 'UTF-8'); ?>">Matrícula automática</label>
        <input class="form-control" id="<?php echo htmlspecialchars($idCampo('matricula'), ENT_QUOTES, 'UTF-8'); ?>" value="<?php echo htmlspecialchars($valorCampo($perfilForm, 'matricula'), ENT_QUOTES, 'UTF-8'); ?>" placeholder="Gerada ao salvar" readonly>
        <small class="form-text">F identifica funcionário; T identifica temporário ou outro vínculo.</small>
    </div>
    <div class="col-md-2">
        <label class="form-label" for="<?php echo htmlspecialchars($idCampo('estadoCivil'), ENT_QUOTES, 'UTF-8'); ?>">Estado civil</label>
        <input class="form-control" id="<?php echo htmlspecialchars($idCampo('estadoCivil'), ENT_QUOTES, 'UTF-8'); ?>" name="perfil[estadoCivil]" maxlength="45" value="<?php echo htmlspecialchars($valorCampo($perfilForm, 'estadoCivil'), ENT_QUOTES, 'UTF-8'); ?>">
    </div>
    <div class="col-md-2">
        <label class="form-label" for="<?php echo htmlspecialchars($idCampo('dependentes'), ENT_QUOTES, 'UTF-8'); ?>">Dependentes</label>
        <input class="form-control" type="number" min="0" id="<?php echo htmlspecialchars($idCampo('dependentes'), ENT_QUOTES, 'UTF-8'); ?>" name="perfil[dependentes]" value="<?php echo htmlspecialchars($valorCampo($perfilForm, 'dependentes'), ENT_QUOTES, 'UTF-8'); ?>">
    </div>
    <div class="col-md-2">
        <label class="form-label" for="<?php echo htmlspecialchars($idCampo('IsFuncionario'), ENT_QUOTES, 'UTF-8'); ?>">Vínculo *</label>
        <select class="form-select" id="<?php echo htmlspecialchars($idCampo('IsFuncionario'), ENT_QUOTES, 'UTF-8'); ?>" name="perfil[IsFuncionario]" required>
            <option value="">Selecione</option>
            <option value="1" <?php echo $valorCampo($perfilForm, 'IsFuncionario') === '1' ? 'selected' : ''; ?>>Funcionário</option>
            <option value="0" <?php echo $valorCampo($perfilForm, 'IsFuncionario') === '0' ? 'selected' : ''; ?>>Temporário / outro vínculo</option>
        </select>
    </div>
    <div class="col-12">
        <label class="form-label" for="<?php echo htmlspecialchars($idCampo('comorbidades'), ENT_QUOTES, 'UTF-8'); ?>">Comorbidades</label>
        <textarea class="form-control" id="<?php echo htmlspecialchars($idCampo('comorbidades'), ENT_QUOTES, 'UTF-8'); ?>" name="perfil[comorbidades]" maxlength="200" rows="2"><?php echo htmlspecialchars($valorCampo($perfilForm, 'comorbidades'), ENT_QUOTES, 'UTF-8'); ?></textarea>
    </div>
</div>

<?php
$idGrupoMandato = $idCampo('mandatoConselho');
$idInicioMandato = $idCampo('inicioMandatoConselho');
$idFimMandato = $idCampo('fimMandatoConselho');
$cargoConselhoPreenchido = trim($valorCampo($perfilForm, 'cargoConselho')) !== '';
?>
<div class="row g-3 mt-1" id="<?php echo htmlspecialchars($idGrupoMandato, ENT_QUOTES, 'UTF-8'); ?>"<?php echo $cargoConselhoPreenchido ? '' : ' hidden'; ?>>
    <div class="col-md-6">
        <label class="form-label" for="<?php echo htmlspecialchars($idInicioMandato, ENT_QUOTES, 'UTF-8'); ?>">In&iacute;cio do mandato no conselho</label>
        <input class="form-control" type="date" id="<?php echo htmlspecialchars($idInicioMandato, ENT_QUOTES, 'UTF-8'); ?>" name="perfil[inicioMandatoConselho]" value="<?php echo htmlspecialchars($valorCampo($perfilForm, 'inicioMandatoConselho'), ENT_QUOTES, 'UTF-8'); ?>"<?php echo $cargoConselhoPreenchido ? '' : ' disabled'; ?>>
    </div>
    <div class="col-md-6">
        <label class="form-label" for="<?php echo htmlspecialchars($idFimMandato, ENT_QUOTES, 'UTF-8'); ?>">Fim do mandato no conselho</label>
        <input class="form-control" type="date" id="<?php echo htmlspecialchars($idFimMandato, ENT_QUOTES, 'UTF-8'); ?>" name="perfil[fimMandatoConselho]" value="<?php echo htmlspecialchars($valorCampo($perfilForm, 'fimMandatoConselho'), ENT_QUOTES, 'UTF-8'); ?>"<?php echo $cargoConselhoPreenchido ? '' : ' disabled'; ?>>
    </div>
    <div class="col-12"><small class="form-text">Preencha as duas datas ou deixe ambas vazias.</small></div>
</div>
<script>
(() => {
    const cargo = document.getElementById(<?php echo json_encode($idCampo('cargoConselho')); ?>);
    const grupo = document.getElementById(<?php echo json_encode($idGrupoMandato); ?>);
    if (!cargo || !grupo) return;

    const datasMandato = grupo.querySelectorAll('input[type="date"]');
    const atualizarMandato = () => {
        const temCargo = cargo.value.trim() !== '';
        grupo.hidden = !temCargo;
        datasMandato.forEach((campo) => {
            campo.disabled = !temCargo;
        });
    };
    cargo.addEventListener('input', atualizarMandato);
    cargo.addEventListener('change', atualizarMandato);
    atualizarMandato();
})();
</script>

<h3 class="h6 border-bottom pb-2 mt-4">Endereço</h3>
<div class="row g-3">
    <div class="col-md-2">
        <label class="form-label" for="<?php echo htmlspecialchars($idCampo('cep'), ENT_QUOTES, 'UTF-8'); ?>">CEP *</label>
        <input class="form-control" id="<?php echo htmlspecialchars($idCampo('cep'), ENT_QUOTES, 'UTF-8'); ?>" name="endereco[cep]" inputmode="numeric" maxlength="9" value="<?php echo htmlspecialchars($valorCampo($enderecoForm, 'cep'), ENT_QUOTES, 'UTF-8'); ?>" data-cep-field data-estado-target="<?php echo htmlspecialchars($idCampo('estado'), ENT_QUOTES, 'UTF-8'); ?>" data-cidade-target="<?php echo htmlspecialchars($idCampo('cidade'), ENT_QUOTES, 'UTF-8'); ?>" data-rua-target="<?php echo htmlspecialchars($idCampo('rua'), ENT_QUOTES, 'UTF-8'); ?>" data-cep-status="<?php echo htmlspecialchars($idCampo('cepStatus'), ENT_QUOTES, 'UTF-8'); ?>" required>
    </div>
    <div class="col-md-2">
        <label class="form-label" for="<?php echo htmlspecialchars($idCampo('estado'), ENT_QUOTES, 'UTF-8'); ?>">Estado *</label>
        <input class="form-control" id="<?php echo htmlspecialchars($idCampo('estado'), ENT_QUOTES, 'UTF-8'); ?>" name="endereco[estado]" maxlength="45" value="<?php echo htmlspecialchars($valorCampo($enderecoForm, 'estado'), ENT_QUOTES, 'UTF-8'); ?>" required>
    </div>
    <div class="col-md-4">
        <label class="form-label" for="<?php echo htmlspecialchars($idCampo('cidade'), ENT_QUOTES, 'UTF-8'); ?>">Cidade *</label>
        <input class="form-control" id="<?php echo htmlspecialchars($idCampo('cidade'), ENT_QUOTES, 'UTF-8'); ?>" name="endereco[cidade]" maxlength="45" value="<?php echo htmlspecialchars($valorCampo($enderecoForm, 'cidade'), ENT_QUOTES, 'UTF-8'); ?>" required>
    </div>
    <div class="col-md-3">
        <label class="form-label" for="<?php echo htmlspecialchars($idCampo('rua'), ENT_QUOTES, 'UTF-8'); ?>">Rua / logradouro *</label>
        <input class="form-control" id="<?php echo htmlspecialchars($idCampo('rua'), ENT_QUOTES, 'UTF-8'); ?>" name="endereco[rua]" maxlength="45" value="<?php echo htmlspecialchars($valorCampo($enderecoForm, 'rua'), ENT_QUOTES, 'UTF-8'); ?>" required>
    </div>
    <div class="col-md-1">
        <label class="form-label" for="<?php echo htmlspecialchars($idCampo('numero'), ENT_QUOTES, 'UTF-8'); ?>">Número</label>
        <input class="form-control" type="number" min="0" id="<?php echo htmlspecialchars($idCampo('numero'), ENT_QUOTES, 'UTF-8'); ?>" name="endereco[numero]" value="<?php echo htmlspecialchars($valorCampo($enderecoForm, 'numero'), ENT_QUOTES, 'UTF-8'); ?>">
    </div>
</div>
<small class="form-text d-block" id="<?php echo htmlspecialchars($idCampo('cepStatus'), ENT_QUOTES, 'UTF-8'); ?>"></small>

<h3 class="h6 border-bottom pb-2 mt-4">Permissões de acesso</h3>
<?php if ($formIsAdmin): ?>
    <div class="alert alert-primary mb-0">Esta conta é administradora e tem acesso a todas as funcionalidades. As permissões abaixo não limitam administradores.</div>
<?php else: ?>
    <div class="vstack gap-3">
        <?php foreach ($gruposPermissoes as $nomeGrupo => $codigosGrupo): ?>
            <?php
            $permissoesGrupo = array_intersect_key($permissoesCatalogo, array_fill_keys($codigosGrupo, true));
            foreach (array_keys($permissoesGrupo) as $codigoAgrupado) unset($permissoesNaoAgrupadas[$codigoAgrupado]);
            ?>
            <?php if ($permissoesGrupo): ?>
                <fieldset class="border rounded p-3">
                    <legend class="float-none w-auto px-2 h6 mb-0"><?php echo htmlspecialchars($nomeGrupo, ENT_QUOTES, 'UTF-8'); ?></legend>
                    <div class="row g-2">
                        <?php foreach ($permissoesGrupo as $codigoPermissao => $definicaoPermissao): ?>
                            <?php $idPermissao = $idCampo('permissao-' . $codigoPermissao); ?>
                            <div class="col-md-6">
                                <div class="form-check border rounded p-3 ps-5 h-100">
                                    <input class="form-check-input" type="checkbox" name="permissoes[]" value="<?php echo htmlspecialchars($codigoPermissao, ENT_QUOTES, 'UTF-8'); ?>" id="<?php echo htmlspecialchars($idPermissao, ENT_QUOTES, 'UTF-8'); ?>" <?php echo in_array($codigoPermissao, $permissoesSelecionadas, true) ? 'checked' : ''; ?>>
                                    <label class="form-check-label fw-semibold" for="<?php echo htmlspecialchars($idPermissao, ENT_QUOTES, 'UTF-8'); ?>">
                                        <?php echo htmlspecialchars($definicaoPermissao['nome'], ENT_QUOTES, 'UTF-8'); ?>
                                        <span class="d-block small text-body-secondary fw-normal"><?php echo htmlspecialchars($definicaoPermissao['descricao'], ENT_QUOTES, 'UTF-8'); ?></span>
                                    </label>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </fieldset>
            <?php endif; ?>
        <?php endforeach; ?>
        <?php if ($permissoesNaoAgrupadas): ?>
            <fieldset class="border rounded p-3">
                <legend class="float-none w-auto px-2 h6 mb-0">Outras permissões</legend>
                <div class="row g-2">
                    <?php foreach ($permissoesNaoAgrupadas as $codigoPermissao => $definicaoPermissao): ?>
                        <?php $idPermissao = $idCampo('permissao-' . $codigoPermissao); ?>
                        <div class="col-md-6">
                            <div class="form-check border rounded p-3 ps-5 h-100">
                                <input class="form-check-input" type="checkbox" name="permissoes[]" value="<?php echo htmlspecialchars($codigoPermissao, ENT_QUOTES, 'UTF-8'); ?>" id="<?php echo htmlspecialchars($idPermissao, ENT_QUOTES, 'UTF-8'); ?>" <?php echo in_array($codigoPermissao, $permissoesSelecionadas, true) ? 'checked' : ''; ?>>
                                <label class="form-check-label fw-semibold" for="<?php echo htmlspecialchars($idPermissao, ENT_QUOTES, 'UTF-8'); ?>">
                                    <?php echo htmlspecialchars($definicaoPermissao['nome'], ENT_QUOTES, 'UTF-8'); ?>
                                    <span class="d-block small text-body-secondary fw-normal"><?php echo htmlspecialchars($definicaoPermissao['descricao'], ENT_QUOTES, 'UTF-8'); ?></span>
                                </label>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </fieldset>
        <?php endif; ?>
    </div>
<?php endif; ?>

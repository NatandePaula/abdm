<?php
$controleAcesso ??= new \Abdm\Infraestrutura\ControleAcesso($pdo);
$controleAcesso->exigirPermissao('pia');

[
    'h' => $h,
    'secoesAtivas' => $secoesAtivas,
    'configuracaoServico' => $configuracaoServico,
    'secoes' => $secoes,
    'campoPiaVisivel' => $campoPiaVisivel,
    'chave' => $chave,
    'camposCadastroResumo' => $camposCadastroResumo,
    'campo' => $campo,
    'camposPorSecao' => $camposPorSecao,
    'editandoId' => $editandoId,
    'dados' => $dados,
    'estruturaRegistro' => $estruturaRegistro,
    'assinantesSalvos' => $assinantesSalvos,
    'acolhidoSelecionado' => $acolhidoSelecionado,
    'dataAtualizacaoForm' => $dataAtualizacaoForm,
    'cadastroDados' => $cadastroDados,
    'acolhidoCompleto' => $acolhidoCompleto,
    'assinante' => $assinante,
    'campoCadastro' => $campoCadastro,
    'enderecoCadastro' => $enderecoCadastro,
    'familiaresCadastro' => $familiaresCadastro,
    'cidsCadastro' => $cidsCadastro,
    'curadorCadastro' => $curadorCadastro,
    'usuariosAssinantes' => $usuariosAssinantes,
    'perfisAssinantesJson' => $perfisAssinantesJson,
    'acolhidos' => $acolhidos,
    'acolhidosPorId' => $acolhidosPorId,
    'acolhido' => $acolhido,
    'listaPias' => $listaPias,
    'flash' => $flash,
    'mostrarFormulario' => $mostrarFormulario,
] = (new \Abdm\Controller\TelasPiaController($pdo))->plano();

require_once ROOT_DIR . '/src/View/templates/includes/cabecalhoInterno.inc.php';
?>

<style>
    .pia-section { border: 1px solid var(--bs-border-color); border-radius: .5rem; overflow: visible; margin: 1.25rem 0; padding: 1.25rem; background: var(--bs-body-bg); box-shadow: var(--bs-box-shadow-sm); }
    .pia-section-title { background: var(--bs-tertiary-bg); color: var(--bs-primary); border-bottom: 1px solid var(--bs-border-color); border-radius: .375rem; font-size: 1rem; font-weight: 700; margin: 0 0 1rem; padding: .8rem 1rem; }
    .pia-section-body { padding: .25rem; }
    .pia-form-card { border-radius: .5rem; }
    .pia-grid-table th, .pia-grid-table td { vertical-align: middle; }
    .pia-grid-table input, .pia-grid-table select { min-width: 6rem; }
    .pia-field-wide { width: 100%; }
    .pia-list-name { min-width: 13rem; }
    .pia-assinante-card { width: 100%; max-width: 58rem; margin-inline: auto; border: 1px solid var(--bs-border-color); border-radius: .5rem; padding: 1.25rem; box-shadow: var(--bs-box-shadow-sm); }
    .pia-assinante-card .form-label { display: block; text-align: center; }
    .pia-assinante-card select { text-align: center; }
    [data-signature-summary] { min-height: 2.5rem; border-radius: .375rem; background: var(--bs-tertiary-bg); padding: .65rem .8rem; text-align: center; }
    .pia-service-title { background: #b6c9e6; color: #17263b; border: 1px solid #7589a3; }
    [data-bs-theme="dark"] .pia-service-title { background: #344b6b; color: #fff; }
    .pia-signature-section { max-width: 64rem; margin-inline: auto; }
</style>

<main class="container py-4">
    <?php if (is_array($flash) && !empty($flash['message'])): ?>
        <div class="alert alert-<?php echo $h($flash['type'] ?? 'info'); ?> alert-dismissible fade show" role="alert">
            <?php echo $h($flash['message']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
        </div>
    <?php endif; ?>

    <?php if ($mostrarFormulario): ?>
    <?php $acolhidoForm = $acolhidoCompleto ? array_merge($acolhidoCompleto, ['id' => $acolhidoCompleto['id'] ?? $acolhidoSelecionado, 'nome' => $acolhidoCompleto['nome'] ?? $acolhidoCompleto['acolhido_nome'] ?? '', 'matricula' => $acolhidoCompleto['matricula'] ?? $acolhidoCompleto['acolhido_matricula'] ?? '', 'status' => $acolhidoCompleto['status'] ?? $acolhidoCompleto['acolhido_status'] ?? '', 'dataNascimento' => $acolhidoCompleto['dataNascimento'] ?? $acolhidoCompleto['acolhido_nascimento'] ?? '', 'dataAcolhimento' => $acolhidoCompleto['dataAcolhimento'] ?? $acolhidoCompleto['data_acolhimento'] ?? '']) : ($acolhidosPorId[$acolhidoSelecionado] ?? null); ?>
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div><a class="text-decoration-none" href="<?php echo BASE_URL; ?>views/pia.php"><i class="bi bi-arrow-left me-1" aria-hidden="true"></i>Voltar à lista de PIAs</a><h1 class="h2 mt-2 mb-1"><?php echo $editandoId ? 'Atualizar PIA' : 'Novo Plano Individual de Atendimento'; ?></h1><?php if ($acolhidoForm): ?><p class="text-body-secondary mb-0">Acolhido: <strong><?php echo $h($acolhidoForm['nome']); ?></strong></p><?php endif; ?></div>
        <div class="d-flex flex-wrap gap-2">
            <?php if ($editandoId): ?><a href="<?php echo BASE_URL; ?>views/gerar_pia_pdf.php?id=<?php echo $editandoId; ?>" target="_blank" onclick="return abrirPreviaPdf(this.href);" class="btn btn-outline-secondary"><i class="bi bi-printer me-1" aria-hidden="true"></i>Imprimir PIA</a><?php endif; ?>
            <a href="<?php echo BASE_URL; ?>views/gerar_pia_pdf.php?branco=1" target="_blank" onclick="return abrirPreviaPdf(this.href);" class="btn btn-outline-secondary"><i class="bi bi-file-earmark me-1" aria-hidden="true"></i>Imprimir modelo em branco</a>
            <?php if ($controleAcesso->usuarioPode('gerenciar_pia_campos')): ?><a href="<?php echo BASE_URL; ?>views/gerenciar_pia_campos.php" class="btn btn-outline-secondary"><i class="bi bi-sliders me-1" aria-hidden="true"></i>Gerenciar campos</a><?php endif; ?>
        </div>
    </div>
    <form action="<?php echo BASE_URL; ?>acoes/controllerPia.php" method="post" id="formPia">
        <?= \Abdm\Infraestrutura\Sessao::campoTokenCsrf() ?>
        <input type="hidden" name="acao" value="salvar_pia">
        <input type="hidden" name="pia_id" value="<?= $editandoId ?>">
        <div class="card shadow-lg border-0 pia-form-card mb-4">
            <div class="card-body p-3 p-lg-4">
            <section class="pia-section">
                <div class="row g-0 pia-service-title">
                    <div class="col-md-7 d-flex flex-column justify-content-center align-items-center text-center p-3 border-end">
                        <h2 class="h4 mb-0">PLANO INDIVIDUAL DE ATENDIMENTO</h2>
                        <strong>(PIA)</strong>
                    </div>
                    <div class="col-md-5 p-3">
                        <div class="mb-2">
                            <strong>Data do acolhimento:</strong>
                            <span id="dataAcolhimentoCabecalho"><?= $h(\Abdm\Model\Pia::piaDataExibicao($acolhidoForm['dataAcolhimento'] ?? '')) ?></span>
                        </div>
                        <label class="form-label mb-1" for="dataAtualizacao">Data de atualização</label>
                        <input class="form-control" type="date" id="dataAtualizacao" name="data_atualizacao"
                               value="<?= $h($dataAtualizacaoForm) ?>" max="<?= date('Y-m-d') ?>" required>
                    </div>
                </div>
            </section>
            <?php if (($secoesAtivas['identificacao_servico'] ?? false)): ?><section class="pia-section"><h2 class="pia-section-title">1. IDENTIFICAÇÃO DO SERVIÇO DE ACOLHIMENTO</h2><div class="pia-section-body">
    <?php if ($campoPiaVisivel('pia_servico_nome')): ?><p class="mb-2"><strong>Nome do serviço de acolhimento institucional:</strong> <?php echo $h($configuracaoServico['nome']); ?></p><?php endif; ?>
    <?php if ($campoPiaVisivel('pia_servico_endereco')): ?><p class="mb-2"><strong>Endereço:</strong> <?php echo $h($configuracaoServico['endereco']); ?></p><?php endif; ?>
    <?php if ($campoPiaVisivel('pia_servico_telefone') && $configuracaoServico['telefone'] !== ''): ?><p class="mb-0"><strong>Telefone:</strong> <?php echo $h($configuracaoServico['telefone']); ?></p><?php endif; ?>
</div></section><?php endif; ?>
            <?php if (!$acolhidoForm): ?><section class="pia-section"><h2 class="pia-section-title">Acolhido</h2><div class="pia-section-body"><label class="form-label" for="acolhidoId">Selecione o acolhido</label><select class="form-select" name="acolhido_id" id="acolhidoId" required><option value="">Escolha um acolhido</option><?php foreach ($acolhidos as $acolhido): ?><option value="<?php echo (int)$acolhido['id']; ?>" data-nascimento="<?php echo $h($acolhido['dataNascimento'] ?? ''); ?>" data-acolhimento="<?php echo $h($acolhido['dataAcolhimento'] ?? ''); ?>" <?php echo $acolhidoSelecionado === (int)$acolhido['id'] ? 'selected' : ''; ?>><?php echo $h($acolhido['nome']); ?><?php echo ($acolhido['status'] ?? '') === 'desligado' ? ' (desligado)' : ''; ?></option><?php endforeach; ?></select></div></section><?php else: ?><input type="hidden" name="acolhido_id" value="<?php echo (int)$acolhidoForm['id']; ?>"><?php endif; ?>
            <?php if ($acolhidoForm && ($secoesAtivas['acolhido'] ?? false)): ?>
                <?php
                $idAcolhidoForm = (int)$acolhidoForm['id'];
                $formatarCadastroPia = static function ($valor) use (&$formatarCadastroPia): string {
                    if (is_array($valor)) {
                        $partes = [];
                        foreach ($valor as $chaveValor => $itemValor) {
                            if ($itemValor === '' || $itemValor === null || $itemValor === []) {
                                continue;
                            }
                            $textoItem = $formatarCadastroPia($itemValor);
                            if ($textoItem !== '') {
                                $partes[] = is_string($chaveValor)
                                    ? str_replace('_', ' ', $chaveValor) . ': ' . $textoItem
                                    : $textoItem;
                            }
                        }
                        return implode('; ', $partes);
                    }
                    if (is_bool($valor)) {
                        return $valor ? 'Sim' : 'Não';
                    }
                    return is_scalar($valor) ? trim((string)$valor) : '';
                };
                ?>
                <section class="pia-section">
                    <div class="pia-section-title d-flex flex-wrap align-items-center justify-content-between gap-2"><span>2. INFORMAÇÕES SOBRE O ACOLHIDO</span><?php if($controleAcesso->usuarioPode('acolhidos')): ?><a class="btn btn-sm btn-outline-primary" href="<?php echo BASE_URL; ?>views/editarAcolhido.php?id=<?php echo $idAcolhidoForm; ?>"><i class="bi bi-pencil-square me-1" aria-hidden="true"></i>Editar cadastro</a><?php endif; ?></div>
                    <div class="pia-section-body"><p class="small text-body-secondary mb-3">Informações carregadas automaticamente do cadastro. Para alterá-las, edite a ficha do acolhido.</p>
                        <h3 class="h5 mt-3 mb-3 border-bottom pb-2 text-primary">Dados pessoais e de acolhimento</h3><div class="row g-3 mb-3">
                            <?php foreach ([['pia_nome_acolhido','Nome completo',$acolhidoForm['nome']??''],['data_nascimento','Data de nascimento',\Abdm\Model\Pia::piaDataExibicao($acolhidoForm['dataNascimento']??$acolhidoForm['acolhido_nascimento']??'')],['sexo','Sexo',\Abdm\Model\DadosAcolhido::acolhidoSexoRotulo($acolhidoForm['sexo']??null)],['pia_cpf_acolhido','CPF',\Abdm\Infraestrutura\Criptografia::descriptografar($acolhidoForm['acolhido_cpf']??$acolhidoForm['cpf']??'')],['pia_matricula_acolhido','Matrícula',$acolhidoForm['matricula']??$acolhidoForm['acolhido_matricula']??''],['pia_data_acolhimento','Data de acolhimento',\Abdm\Model\Pia::piaDataExibicao($acolhidoForm['dataAcolhimento']??$acolhidoForm['data_acolhimento']??'')],['pia_data_desligamento','Data de desligamento',\Abdm\Model\Pia::piaDataExibicao($acolhidoForm['cadastro_data_desligamento']??$acolhidoForm['dataDesligamento']??'')],['pia_motivo_desligamento','Motivo do desligamento',$acolhidoForm['cadastro_motivo_desligamento']??$acolhidoForm['motivoDesligamento']??''],['pia_origem_acolhimento','Origem do acolhimento',$acolhidoForm['origemAcolhimento']??$acolhidoForm['cadastro_origem']??''],['pia_nome_mae','Nome da mãe',$acolhidoForm['nome_mae']??$acolhidoForm['cadastro_nome_mae']??''],['pia_nome_pai','Nome do pai',$acolhidoForm['nome_pai']??$acolhidoForm['cadastro_nome_pai']??''],['pia_situacao_acolhido','Situação',$acolhidoForm['status']??$acolhidoForm['acolhido_status']??'']] as [$chaveVisibilidade,$rotuloCadastro,$valorCadastro]): ?>
    <?php if ($campoPiaVisivel($chaveVisibilidade)): ?><div class="col-12 col-md-6"><div class="form-floating"><input class="form-control" value="<?php echo $h($valorCadastro!==''?$valorCadastro:'Não informado'); ?>" placeholder=" " readonly><label><?php echo $h($rotuloCadastro); ?></label></div></div><?php endif; ?>
<?php endforeach; ?>
                            
                        </div>
                        <h3 class="h5 mt-4 mb-3 border-bottom pb-2 text-primary">Informações clínicas e para o acolhimento</h3><div class="row g-3 mb-3">
<?php foreach ([['pia_plano_assistencial','Plano assistencial ou funerário',$acolhidoForm['plano_assistencial_funerario']??''],['pia_info_saude','Comorbidades, alergias e restrições',$acolhidoForm['info_saude']??''],['pia_info_nutricional','Informações alimentares e nutricionais',$acolhidoForm['info_nutri']??''],['pia_motivo_institucionalizacao','Motivo da institucionalização',$acolhidoForm['cadastro_motivo']??$acolhidoForm['motivoInstitucionalizacao']??''],['pia_historico_acolhido','Histórico e observações gerais',$acolhidoForm['historico']??'']] as [$chaveVisibilidade,$rotuloResumo,$valorResumo]): ?>
    <?php if($campoPiaVisivel($chaveVisibilidade)): ?><div class="col-12 col-md-6"><div class="form-floating"><textarea class="form-control" placeholder=" " readonly style="height:6rem"><?php echo $h($valorResumo); ?></textarea><label><?php echo $h($rotuloResumo); ?></label></div></div><?php endif; ?>
<?php endforeach; ?></div><?php if(is_array($enderecoCadastro) && $campoPiaVisivel('pia_endereco_origem')): ?><h3 class="h5 mt-4 mb-3 border-bottom pb-2 text-primary">Endereço de origem</h3><div class="row g-3 mb-3"><?php foreach(['cep'=>'CEP','rua'=>'Rua/Logradouro','numero'=>'Número','cidade'=>'Cidade','estado'=>'Estado'] as $chaveEndereco=>$rotuloEndereco): ?><div class="col-12 col-md-6"><div class="form-floating"><input class="form-control" value="<?php echo $h($enderecoCadastro[$chaveEndereco]??''); ?>" placeholder=" " readonly><label><?php echo $h($rotuloEndereco); ?></label></div></div><?php endforeach; ?><div class="col-12 col-md-6"><div class="form-floating"><input class="form-control" value="<?php echo $h($acolhidoForm['endereco_referencia']??''); ?>" placeholder=" " readonly><label>Referência</label></div></div><div class="col-12 col-md-6"><div class="form-floating"><textarea class="form-control" placeholder=" " readonly style="height:3.6rem"><?php echo $h($acolhidoForm['endereco_observacao']??''); ?></textarea><label>Observação do endereço</label></div></div></div><?php endif; ?>
                        <?php $camposExtrasCadastro=array_values(array_filter($camposCadastroResumo,static fn(array $campo):bool=>!in_array((string)$campo['chave'],['data_nascimento'],true))); ?>
                        <?php if($camposExtrasCadastro): ?><h3 class="h5 mt-4 mb-3 border-bottom pb-2 text-primary">Outras informações do cadastro</h3><div class="row g-3 mb-3"><?php foreach($camposExtrasCadastro as $campoCadastro): $valorExtra=$cadastroDados[$campoCadastro['chave']]??''; if(is_array($valorExtra))$valorExtra=$formatarCadastroPia($valorExtra); if($valorExtra==='')$valorExtra='Não informado'; ?><div class="col-12 <?php echo $campoCadastro['tipo']==='textarea'?'':'col-md-6'; ?>"><div class="form-floating"><?php if($campoCadastro['tipo']==='textarea'): ?><textarea class="form-control" placeholder=" " readonly style="height:6rem"><?php echo $h($valorExtra); ?></textarea><?php else: ?><input class="form-control" value="<?php echo $h($valorExtra); ?>" placeholder=" " readonly><?php endif; ?><label><?php echo $h($campoCadastro['rotulo']); ?></label></div></div><?php endforeach; ?></div><?php endif; ?>
                    </div>
                </section>
            <?php endif; ?><?php foreach ($secoes as $chaveSecao => $tituloSecao): ?>
                <?php
                if ($chaveSecao === 'acolhido') { continue; }
                $camposSecao = $camposPorSecao[$chaveSecao] ?? [];
                $camposCadastroSecao = [];
                if (!$camposSecao && !$camposCadastroSecao && (!$acolhidoForm || !in_array($chaveSecao, ['saude', 'composicao_familiar'], true))) { continue; }
                ?>
                <section class="pia-section">
                    <h2 class="pia-section-title"><?php echo $h($tituloSecao); ?></h2>
                    <div class="pia-section-body">
                        <?php if ($acolhidoForm && $chaveSecao === 'saude'): ?>
                            <?php foreach (['restricao_alimentar'=>'Possui restrição alimentar?','qual_restricao'=>'Qual restrição alimentar?','doencas_cronicas'=>'Doenças crônicas','comorbidades'=>'Comorbidades'] as $chaveSaudeCadastro => $rotuloSaudeCadastro): if(!$campoPiaVisivel($chaveSaudeCadastro)) continue; $valorSaudeCadastro=$cadastroDados[$chaveSaudeCadastro]??''; if(is_array($valorSaudeCadastro))$valorSaudeCadastro=$formatarCadastroPia($valorSaudeCadastro); ?>
                                <div class="row g-3 mb-3"><div class="col-12 <?php echo in_array($chaveSaudeCadastro,['doencas_cronicas','comorbidades'],true)?'':'col-md-6'; ?>"><div class="form-floating"><?php if(in_array($chaveSaudeCadastro,['doencas_cronicas','comorbidades'],true)): ?><textarea class="form-control" placeholder=" " readonly style="height:6rem"><?php echo $h($valorSaudeCadastro!==''?$valorSaudeCadastro:'Não informado'); ?></textarea><?php else: ?><input class="form-control" value="<?php echo $h($valorSaudeCadastro!==''?$valorSaudeCadastro:'Não informado'); ?>" placeholder=" " readonly><?php endif; ?><label><?php echo $h($rotuloSaudeCadastro); ?></label></div></div></div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        <?php if ($acolhidoForm && $chaveSecao === 'saude' && in_array('cid10', array_column($camposSecao, 'chave'), true)): ?><div class="table-responsive mb-3"><table class="table table-sm table-bordered align-middle"><thead><tr><th colspan="2">Diagnósticos CID cadastrados</th></tr><tr><th>Código</th><th>Descrição</th></tr></thead><tbody><?php if ($cidsCadastro): foreach ($cidsCadastro as $cidCadastro): ?><tr><td><?php echo $h(function_exists('mb_strtoupper') ? mb_strtoupper((string)$cidCadastro['codigo'], 'UTF-8') : strtoupper((string)$cidCadastro['codigo'])); ?></td><td><?php echo $h($cidCadastro['descricao']); ?></td></tr><?php endforeach; else: ?><tr><td colspan="2" class="text-body-secondary">Nenhum CID cadastrado.</td></tr><?php endif; ?></tbody></table></div><?php endif; ?>
                        <?php if ($acolhidoForm && $chaveSecao === 'composicao_familiar' && in_array('composicao_familiar', array_column($camposSecao, 'chave'), true)): ?>
                            <?php if ($curadorCadastro): ?><div class="border rounded p-3 mb-3"><h3 class="h6">Curador ou responsável legal</h3><p class="mb-1"><strong>Nome:</strong> <?php echo $h($curadorCadastro['nome']); ?> · <strong>CPF:</strong> <?php echo $h(\Abdm\Infraestrutura\Criptografia::descriptografar($curadorCadastro['cpf'] ?? '')); ?> · <strong>Parentesco:</strong> <?php echo $h($curadorCadastro['grauParentesco']); ?></p><p class="mb-1"><strong>Telefone:</strong> <?php echo $h($curadorCadastro['telefone']); ?><?php if ($curadorCadastro['telefone_2']): ?> · <strong>Telefone 2:</strong> <?php echo $h($curadorCadastro['telefone_2']); ?><?php endif; ?></p><?php $enderecoCuradorCadastro = implode(', ', array_filter([$curadorCadastro['rua'] ?? '', $curadorCadastro['numero'] ?? '', $curadorCadastro['cidade'] ?? '', $curadorCadastro['estado'] ?? '', $curadorCadastro['cep'] ?? ''])); ?><?php if ($enderecoCuradorCadastro !== ''): ?><p class="mb-0"><strong>Endereço:</strong> <?php echo $h($enderecoCuradorCadastro); ?></p><?php endif; ?></div><?php endif; ?>
                            <h5 class="mt-4 mb-3 border-bottom pb-2 text-primary">Familiares Registrados</h5>
                            <?php if ($familiaresCadastro): ?>
    <?php foreach ($familiaresCadastro as $indiceFamiliar => $familiarCadastro): ?>
        <div class="border p-3 mb-3 rounded shadow-sm"><h3 class="h6 mb-3 text-secondary">Familiar #<?php echo $indiceFamiliar + 1; ?></h3>
            <div class="row g-3 mb-3"><div class="col-12 col-md-6"><div class="form-floating"><input class="form-control" value="<?php echo $h($familiarCadastro['nome'] ?? ''); ?>" readonly><label>Nome</label></div></div><div class="col-12 col-md-6"><div class="form-floating"><input class="form-control" value="<?php echo $h($familiarCadastro['grauParentesco'] ?? ''); ?>" readonly><label>Grau de parentesco</label></div></div></div>
            <div class="row g-3 mb-3"><div class="col-12 col-md-6"><div class="form-floating"><input class="form-control" value="<?php echo $h(\Abdm\Model\Pia::piaDataExibicao($familiarCadastro['dataNascimento'] ?? '')); ?>" readonly><label>Data de nascimento</label></div></div><div class="col-12 col-md-6"><div class="form-floating"><input class="form-control" value="<?php echo $h($familiarCadastro['telefone'] ?? 'Não informado'); ?>" readonly><label>Telefone</label></div></div></div>
            <h4 class="h6 mt-3 mb-3 border-bottom pb-1 text-muted">Endereço do familiar</h4><div class="row g-3"><div class="col-12 col-md-3"><div class="form-floating"><input class="form-control" value="<?php echo $h($familiarCadastro['cep'] ?? 'N/A'); ?>" readonly><label>CEP</label></div></div><div class="col-12 col-md-6"><div class="form-floating"><input class="form-control" value="<?php echo $h($familiarCadastro['rua'] ?? 'N/A'); ?>" readonly><label>Rua/Logradouro</label></div></div><div class="col-12 col-md-3"><div class="form-floating"><input class="form-control" value="<?php echo $h($familiarCadastro['numero'] ?? 'N/A'); ?>" readonly><label>Número</label></div></div><div class="col-12 col-md-6"><div class="form-floating"><input class="form-control" value="<?php echo $h($familiarCadastro['cidade'] ?? 'N/A'); ?>" readonly><label>Cidade</label></div></div><div class="col-12 col-md-6"><div class="form-floating"><input class="form-control" value="<?php echo $h($familiarCadastro['estado'] ?? 'N/A'); ?>" readonly><label>Estado</label></div></div></div>
        </div>
    <?php endforeach; ?>
<?php else: ?><p class="text-muted fst-italic">Nenhum familiar informado.</p><?php endif; ?>
                        <?php endif; ?>
                        <?php foreach ($camposCadastroSecao as $campoCadastro): $valorCadastro = $cadastroDados[$campoCadastro['chave']] ?? ''; $textoCadastro = $campoCadastro['tipo'] === 'date' ? \Abdm\Model\Pia::piaDataExibicao((string)$valorCadastro) : $formatarCadastroPia($valorCadastro); ?>
                            <div class="row g-2 mb-2"><div class="col-12"><div class="border rounded p-2"><strong><?php echo $h($campoCadastro['rotulo']); ?>:</strong> <?php echo nl2br($h($textoCadastro !== '' ? $textoCadastro : 'Não informado')); ?></div></div></div>
                        <?php endforeach; ?>
                        <?php foreach ($camposSecao as $campo): ?>
                            <?php
                            $chave = (string)$campo['chave'];
                            if ($acolhidoForm && $chaveSecao === 'composicao_familiar' && $chave === 'composicao_familiar') continue;
                            if ($acolhidoForm && $chaveSecao === 'saude' && $chave === 'cid10') continue;
                            $rotulo = $chave === 'dst' ? 'Possui alguma IST?' : ($chave === 'qual_dst' ? 'Qual IST?' : (string)$campo['rotulo']);
                            $tipo = (string)$campo['tipo'];
                            $somenteLeituraCadastro = array_key_exists($chave, $cadastroDados);
                            $valorCampo = $somenteLeituraCadastro
                                ? $cadastroDados[$chave]
                                : (array_key_exists($chave, $dados) ? $dados[$chave]
                                : \Abdm\Model\Pia::piaValorInicialCadastroCampo($chave, is_array($acolhidoForm) ? $acolhidoForm : [], $cadastroDados, $familiaresCadastro, $cidsCadastro));
                            if ($chave === 'cid10' && is_string($valorCampo)) {
                                $valorCampo = strtoupper($valorCampo);
                            }
                            $opcoes = \Abdm\Model\Pia::piaOpcoesCampo($campo);
                            if (in_array($tipo, ['select', 'multi'], true) && isset($estruturaRegistro[$chave])) {
                                $opcoes = array_values(array_unique(array_merge($opcoes, \Abdm\Model\Pia::piaOpcoesCampo($estruturaRegistro[$chave]))));
                            }
                            ?>
                            <?php if (in_array($tipo, ['text', 'textarea', 'date', 'select'], true)): ?>
                                <div class="row g-3 mb-3">
                                    <div class="col-12 <?php echo $tipo === 'textarea' ? '' : 'col-lg-6'; ?>">
                                        <label class="form-label" for="campo-<?php echo $h($chave); ?>"><?php echo $h($rotulo); ?></label>
                                        <?php if ($tipo === 'textarea'): ?>
                                            <textarea class="form-control" id="campo-<?php echo $h($chave); ?>" <?php echo $somenteLeituraCadastro ? 'disabled' : 'name="dados[' . $h($chave) . ']"'; ?> rows="3"><?php echo $h(is_string($valorCampo) ? $valorCampo : ''); ?></textarea>
                                        <?php elseif ($tipo === 'select'): ?>
                                            <select class="form-select" id="campo-<?php echo $h($chave); ?>" <?php echo $somenteLeituraCadastro ? 'disabled' : 'name="dados[' . $h($chave) . ']"'; ?>>
                                                <option value="">Selecione</option>
                                                <?php foreach ($opcoes as $opcao): ?><option value="<?php echo $h($opcao); ?>" <?php echo $valorCampo === $opcao ? 'selected' : ''; ?>><?php echo $h(ucfirst($opcao)); ?></option><?php endforeach; ?>
                                            </select>
                                        <?php else: ?>
                                            <input class="form-control" id="campo-<?php echo $h($chave); ?>" <?php echo $somenteLeituraCadastro ? 'readonly' : 'name="dados[' . $h($chave) . ']"'; ?> type="<?php echo $tipo === 'date' ? 'date' : 'text'; ?>" value="<?php echo $h(is_string($valorCampo) ? $valorCampo : ''); ?>">
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php elseif ($tipo === 'multi'): ?>
                                <?php $selecionados = is_array($valorCampo) ? $valorCampo : []; ?>
                                <fieldset class="mb-3">
                                    <legend class="form-label fs-6"><?php echo $h($rotulo); ?></legend>
                                    <div class="d-flex flex-wrap gap-3">
                                        <?php foreach ($opcoes as $indiceOpcao => $opcao): $idOpcao = $chave . '-' . $indiceOpcao; ?>
                                            <div class="form-check"><input class="form-check-input" type="checkbox" <?php echo $somenteLeituraCadastro ? 'disabled' : 'name="dados[' . $h($chave) . '][]"'; ?> value="<?php echo $h($opcao); ?>" id="<?php echo $h($idOpcao); ?>" <?php echo in_array($opcao, $selecionados, true) ? 'checked' : ''; ?>><label class="form-check-label" for="<?php echo $h($idOpcao); ?>"><?php echo $h(ucfirst($opcao)); ?></label></div>
                                        <?php endforeach; ?>
                                    </div>
                                </fieldset>
                            <?php elseif ($tipo === 'documents'): ?>
    <?php
    $linhasDocumento = ['certidao'=>'Certidão de nascimento e/ou casamento','rg'=>'RG','cpf'=>'CPF','titulo_eleitoral'=>'Título eleitoral','ctps'=>'CTPS','sus'=>'Cartão Nacional do SUS','cartao_municipal'=>'Cartão Municipal de Saúde','outros'=>'Outros documentos'];
    $docs = is_array($cadastroDados['documentos'] ?? null) ? $cadastroDados['documentos'] : [];
    $rotulosSituacaoDocumento = ['possui'=>'Possui','nao_possui'=>'Não possui','nao_sabe_informar'=>'Não sabe informar','nao_disponibilizado'=>'Possui, mas não está disponível no momento'];
    ?>
    <div class="table-responsive mb-4"><table class="table table-bordered align-middle pia-grid-table"><thead class="table-light"><tr><th>Documento</th><th>Número</th><th>Situação</th></tr></thead><tbody>
        <?php foreach ($linhasDocumento as $itemDocumento=>$nomeDocumento): $linhaDocumento=is_array($docs[$itemDocumento]??null)?$docs[$itemDocumento]:[]; $situacaoDocumento=(string)($linhaDocumento['situacao']??''); $numeroDocumento=(string)($linhaDocumento['numero']??''); if($itemDocumento==='cpf' && $numeroDocumento==='') $numeroDocumento=\Abdm\Infraestrutura\Criptografia::descriptografar($acolhidoForm['acolhido_cpf']??$acolhidoForm['cpf']??''); ?>
            <tr><th scope="row"><?php echo $h($nomeDocumento); ?></th><td><?php echo $h($situacaoDocumento==='possui' && $numeroDocumento!==''?$numeroDocumento:'—'); ?></td><td><?php echo $h($rotulosSituacaoDocumento[$situacaoDocumento]??'Não informado'); ?></td></tr>
        <?php endforeach; ?>
    </tbody></table></div><?php elseif ($tipo === 'benefits'): ?>
                                <?php $beneficiosMarcados = is_array($valorCampo) ? $valorCampo : []; $opcoesBeneficio = ['nenhum' => 'Não recebe nenhum tipo de benefício', 'aposentadoria' => 'Aposentadoria', 'pensao' => 'Pensão', 'bpc' => 'Benefício de Prestação Continuada – BPC/LOAS', 'programa_social' => 'Programa social ou transferência de renda', 'atividade' => 'Exerce atividade laborativa remunerada', 'outro' => 'Outro']; ?>
                                <fieldset class="mb-3">
                                    <legend class="form-label fs-6"><?php echo $h($rotulo); ?></legend>
                                    <div class="row g-2">
                                        <?php foreach ($opcoesBeneficio as $codigoBeneficio => $nomeBeneficio): ?>
                                            <div class="col-12 col-md-6"><div class="form-check"><input class="form-check-input" type="checkbox" <?php echo $somenteLeituraCadastro ? 'disabled' : 'name="dados[' . $h($chave) . '][]"'; ?> id="<?php echo $h($chave . '-' . $codigoBeneficio); ?>" value="<?php echo $h($codigoBeneficio); ?>" <?php echo in_array($codigoBeneficio, $beneficiosMarcados, true) ? 'checked' : ''; ?>><label class="form-check-label" for="<?php echo $h($chave . '-' . $codigoBeneficio); ?>"><?php echo $h($nomeBeneficio); ?></label></div></div>
                                        <?php endforeach; ?>
                                    </div>
                                </fieldset>
                            <?php elseif ($tipo === 'family'): ?>
                                <?php $familia = is_array($valorCampo) ? $valorCampo : []; $totalLinhasFamilia = max(5, count($familia)); ?>
                                <div class="table-responsive mb-2">
                                    <table class="table table-bordered table-sm pia-grid-table mb-0">
                                        <thead class="table-light"><tr><th>Nome</th><th>Parentesco</th><th>Ocupação</th><th>Data de nascimento</th><th>Sexo</th><th aria-label="Ações"></th></tr></thead>
                                        <tbody id="linhasFamilia" data-counter="<?php echo $totalLinhasFamilia; ?>">
                                            <?php for ($i = 0; $i < $totalLinhasFamilia; $i++): $linha = $familia[$i] ?? []; ?>
                                                <tr><td><input class="form-control form-control-sm" name="dados[<?php echo $h($chave); ?>][<?php echo $i; ?>][nome]" value="<?php echo $h($linha['nome'] ?? ''); ?>"></td><td><input class="form-control form-control-sm" name="dados[<?php echo $h($chave); ?>][<?php echo $i; ?>][parentesco]" value="<?php echo $h($linha['parentesco'] ?? ''); ?>"></td><td><input class="form-control form-control-sm" name="dados[<?php echo $h($chave); ?>][<?php echo $i; ?>][ocupacao]" value="<?php echo $h($linha['ocupacao'] ?? ''); ?>"></td><td><input class="form-control form-control-sm" type="date" name="dados[<?php echo $h($chave); ?>][<?php echo $i; ?>][data_nascimento]" value="<?php echo $h($linha['data_nascimento'] ?? ''); ?>"></td><td><input class="form-control form-control-sm" name="dados[<?php echo $h($chave); ?>][<?php echo $i; ?>][sexo]" value="<?php echo $h($linha['sexo'] ?? ''); ?>"></td><td><button type="button" class="btn btn-sm btn-outline-danger" data-remove-row aria-label="Remover familiar"><i class="bi bi-trash" aria-hidden="true"></i></button></td></tr>
                                            <?php endfor; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <button class="btn btn-sm btn-outline-primary mb-3" type="button" data-add-row="linhasFamilia" data-template="modeloLinhaFamilia"><i class="bi bi-plus-circle me-1" aria-hidden="true"></i>Adicionar familiar</button>
                                <template id="modeloLinhaFamilia"><tr><td><input class="form-control form-control-sm" name="dados[<?php echo $h($chave); ?>][__INDEX__][nome]"></td><td><input class="form-control form-control-sm" name="dados[<?php echo $h($chave); ?>][__INDEX__][parentesco]"></td><td><input class="form-control form-control-sm" name="dados[<?php echo $h($chave); ?>][__INDEX__][ocupacao]"></td><td><input class="form-control form-control-sm" type="date" name="dados[<?php echo $h($chave); ?>][__INDEX__][data_nascimento]"></td><td><input class="form-control form-control-sm" name="dados[<?php echo $h($chave); ?>][__INDEX__][sexo]"></td><td><button type="button" class="btn btn-sm btn-outline-danger" data-remove-row aria-label="Remover familiar"><i class="bi bi-trash" aria-hidden="true"></i></button></td></tr></template>
                            <?php elseif ($tipo === 'network'): ?>
                                <?php $redeMarcada = is_array($valorCampo) ? $valorCampo : []; $opcoesRede = ['conselho_tutelar' => 'Conselho Tutelar', 'cras' => 'Centro de Referência de Assistência Social – CRAS', 'creas' => 'Centro de Referência Especializado de Assistência Social – CREAS', 'pse' => 'Serviço da Proteção Social Especial – PSE – Alta Complexidade', 'transferencia_renda' => 'Programas de Transferência de Renda', 'protecao_basica' => 'Proteção Social Básica (SEMASDH)', 'ministerio_publico' => 'Ministério Público', 'apoio_sociofamiliar' => 'Outros serviços de apoio sociofamiliar', 'vara_justica' => 'Vara da Justiça', 'delegacias' => 'Delegacias comuns e especializadas', 'entidades_religiosas' => 'Entidades religiosas', 'saude' => 'Serviços de saúde', 'educacao' => 'Centros de educação', 'cultura' => 'Cultura']; ?>
                                <fieldset class="mb-3"><legend class="form-label fs-6"><?php echo $h($rotulo); ?></legend><div class="row g-2">
                                    <?php foreach ($opcoesRede as $codigoRede => $nomeRede): ?><div class="col-12 col-lg-6"><div class="form-check"><input class="form-check-input" type="checkbox" name="dados[<?php echo $h($chave); ?>][]" id="<?php echo $h($chave . '-' . $codigoRede); ?>" value="<?php echo $h($codigoRede); ?>" <?php echo in_array($codigoRede, $redeMarcada, true) ? 'checked' : ''; ?>><label class="form-check-label" for="<?php echo $h($chave . '-' . $codigoRede); ?>"><?php echo $h($nomeRede); ?></label></div></div><?php endforeach; ?>
                                </div></fieldset>
                            <?php elseif ($tipo === 'activities'): ?>
                                <?php $atividades = is_array($valorCampo) ? $valorCampo : []; $temas = ['convivencia' => 'Convivência familiar e comunitária', 'desenvolvimento' => 'Desenvolvimento do usuário', 'documentacao' => 'Documentação', 'esporte_cultura' => 'Esporte/cultura/lazer', 'geracao_renda' => 'Geração de renda', 'mobilizacao_rede' => 'Mobilização da rede', 'transferencia_renda' => 'Programa de transferência de renda', 'vinculos' => 'Relações de vínculos no serviço', 'saude' => 'Saúde', 'servicos' => 'Serviços/programas/projetos']; ?>
                                <div class="table-responsive mb-3"><table class="table table-bordered table-sm pia-grid-table mb-0"><thead class="table-light"><tr><th colspan="2" class="text-center">PLANO DE ATIVIDADES</th></tr></thead><tbody>
                                    <?php foreach ($temas as $tema => $nomeTema): ?><tr><th scope="row" class="w-25"><?php echo $h($nomeTema); ?></th><td><textarea class="form-control form-control-sm" rows="2" name="dados[<?php echo $h($chave); ?>][<?php echo $h($tema); ?>]"><?php echo $h($atividades[$tema] ?? ''); ?></textarea></td></tr><?php endforeach; ?>
                                </tbody></table></div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endforeach; ?>

            <section class="pia-section pia-signature-section">
                <h2 class="pia-section-title">Responsáveis pelas assinaturas</h2>
                <div class="pia-section-body">
                    <p class="text-body-secondary">Somente usuários com conta ativa podem ser incluídos. Não é necessário ter permissão de acesso ao PIA. Escolha somente o nome, a atuação profissional, o cargo no conselho com o mandato, ou as duas identificações.</p>
                    <div id="listaAssinantes" class="d-grid gap-3" data-counter="<?php echo max(1, count($assinantesSalvos)); ?>">
                        <?php $linhasAssinantes = $assinantesSalvos ?: [['usuario_id' => 0, 'modo' => 'profissional']]; ?>
                        <?php foreach ($linhasAssinantes as $indiceAssinante => $assinante): ?>
                            <?php $idSelecionado = (int)($assinante['usuario_id'] ?? 0); ?>
                            <div class="pia-assinante-card" data-assinante-row>
                                <div class="row g-3 align-items-end">
                                    <div class="col-12 col-lg-6">
                                        <label class="form-label" for="assinante-<?php echo $indiceAssinante; ?>">Pessoa responsável</label>
                                        <select class="form-select" name="assinantes[<?php echo $indiceAssinante; ?>][usuario_id]" id="assinante-<?php echo $indiceAssinante; ?>" required>
                                            <option value="">Selecione um usuário ativo</option>
                                            <?php foreach ($usuariosAssinantes as $usuarioAssinante): ?>
                                                <option value="<?php echo (int)$usuarioAssinante['idUsuario']; ?>" data-atuacao="<?php echo $h($usuarioAssinante['atuacaoProfissional'] ?? ''); ?>" data-registro="<?php echo $h($usuarioAssinante['registroProfissional'] ?? ''); ?>" data-cargo="<?php echo $h($usuarioAssinante['cargoConselho'] ?? ''); ?>" data-inicio="<?php echo $h($usuarioAssinante['inicioMandatoConselho'] ?? ''); ?>" data-fim="<?php echo $h($usuarioAssinante['fimMandatoConselho'] ?? ''); ?>" <?php echo $idSelecionado === (int)$usuarioAssinante['idUsuario'] ? 'selected' : ''; ?>><?php echo $h($usuarioAssinante['nome']); ?><?php echo empty($usuarioAssinante['idLogin']) ? ' (sem acesso ao sistema)' : ((int)$usuarioAssinante['ativo'] !== 1 ? ' (acesso desativado)' : ''); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-12 col-lg-5">
                                        <label class="form-label" for="modoAssinante-<?php echo $indiceAssinante; ?>">Identificação que será impressa</label>
                                        <select class="form-select" name="assinantes[<?php echo $indiceAssinante; ?>][modo]" id="modoAssinante-<?php echo $indiceAssinante; ?>" required>
                                            <option value="nome" <?php echo ($assinante['modo'] ?? '') === 'nome' ? 'selected' : ''; ?>>Somente nome</option>
                                            <option value="profissional" <?php echo ($assinante['modo'] ?? '') === 'profissional' ? 'selected' : ''; ?>>Atuação profissional</option>
                                            <option value="conselho" <?php echo ($assinante['modo'] ?? '') === 'conselho' ? 'selected' : ''; ?>>Cargo no conselho e mandato</option>
                                            <option value="ambos" <?php echo ($assinante['modo'] ?? '') === 'ambos' ? 'selected' : ''; ?>>Atuação e cargo no conselho</option>
                                        </select>
                                    </div>
                                    <div class="col-12 col-lg-1 d-grid"><button class="btn btn-outline-danger" type="button" data-remove-signer aria-label="Remover assinante"><i class="bi bi-trash" aria-hidden="true"></i></button></div>
                                    <?php $perfilAssinante = null; foreach ($usuariosAssinantes as $candidatoAssinante) { if ((int)$candidatoAssinante['idUsuario'] === $idSelecionado) { $perfilAssinante = $candidatoAssinante; break; } } ?>
                                    <div class="col-12 small text-body-secondary" data-signature-summary>
                                        <?php if ($perfilAssinante): ?>
                                            <?php if (!empty($perfilAssinante['atuacaoProfissional'])): ?>Atuação: <?php echo $h($perfilAssinante['atuacaoProfissional']); ?><?php echo !empty($perfilAssinante['registroProfissional']) ? ' · Registro: ' . $h($perfilAssinante['registroProfissional']) : ''; ?>. <?php endif; ?>
                                            <?php if (!empty($perfilAssinante['cargoConselho'])): ?>Conselho: <?php echo $h($perfilAssinante['cargoConselho']); ?><?php if (!empty($perfilAssinante['inicioMandatoConselho']) || !empty($perfilAssinante['fimMandatoConselho'])): ?> · Mandato: <?php echo $h(\Abdm\Model\Pia::piaDataExibicao($perfilAssinante['inicioMandatoConselho'] ?? '')); ?> a <?php echo $h(\Abdm\Model\Pia::piaDataExibicao($perfilAssinante['fimMandatoConselho'] ?? '')); ?><?php endif; ?><?php endif; ?>
                                        <?php elseif (!empty($assinante['nome']) && (int)($assinante['ativo'] ?? 0) !== 1): ?><?php echo $h($assinante['nome']); ?> não está ativo. Selecione um usuário ativo para substituí-lo.<?php else: ?>Os dados profissionais e o mandato serão exibidos conforme o perfil da pessoa selecionada.<?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-primary mt-3" id="adicionarAssinante"><i class="bi bi-person-plus me-1" aria-hidden="true"></i>Adicionar responsável</button>
                </div>
            </section>

            <div class="d-flex flex-wrap justify-content-between gap-2 mb-5">
                <a href="<?php echo BASE_URL; ?>views/pia.php" class="btn btn-outline-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1" aria-hidden="true"></i><?php echo $editandoId ? 'Salvar alterações' : 'Cadastrar PIA'; ?></button>
            </div>
            </div></div>
        </form>
    <?php else: ?>
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
            <div>
                <h1 class="h2 mb-1">Plano Individual de Atendimento (PIA)</h1>
                <p class="text-body-secondary mb-0">PIA individual de todos os acolhidos. Os dados cadastrais são carregados automaticamente e o planejamento é preenchido nesta página.</p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <?php if ($controleAcesso->usuarioPode('gerenciar_pia_campos')): ?><a href="<?php echo BASE_URL; ?>views/gerenciar_pia_campos.php" class="btn btn-outline-secondary"><i class="bi bi-sliders me-1" aria-hidden="true"></i>Gerenciar campos</a><?php endif; ?>
                <a href="<?php echo BASE_URL; ?>views/gerar_pia_pdf.php?branco=1" target="_blank" onclick="return abrirPreviaPdf(this.href);" class="btn btn-outline-secondary"><i class="bi bi-file-earmark me-1" aria-hidden="true"></i>Imprimir modelo em branco</a>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header"><h2 class="h5 mb-0">PIA por acolhido</h2></div>
            <?php if (!$listaPias): ?>
                <div class="card-body text-center py-5"><i class="bi bi-journal-text fs-1 text-body-secondary" aria-hidden="true"></i><p class="mt-3 mb-0">Ainda não há PIAs cadastrados.</p></div>
            <?php else: ?>
                <div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead><tr><th class="pia-list-name">Acolhido</th><th>Data de atualização</th><th>Responsáveis pelas assinaturas</th><th class="text-end">Ações</th></tr></thead><tbody>
                    <?php foreach ($listaPias as $piaLinha): ?>
                        <tr><td><strong><?php echo $h($piaLinha['acolhido_nome']); ?></strong><?php if (($piaLinha['acolhido_status'] ?? '') === 'desligado'): ?><span class="badge text-bg-secondary ms-2">Desligado</span><?php endif; ?><br><span class="badge <?php echo empty(\Abdm\Model\Pia::piaDadosJsonParaLista($piaLinha['dados_json'] ?? null)) ? 'text-bg-warning' : 'text-bg-success'; ?>"><?php echo empty(\Abdm\Model\Pia::piaDadosJsonParaLista($piaLinha['dados_json'] ?? null)) ? 'Pendente de preenchimento' : 'Em preenchimento'; ?></span></td>
                            <td><?php echo $h(\Abdm\Model\Pia::piaDataExibicao($piaLinha['dataAtualizacao'] ?? '')); ?></td>
                            <td><?php echo $h($piaLinha['nomes_assinantes'] ?: 'Nenhum responsável informado'); ?></td>
                            <td><div class="d-flex justify-content-end gap-2"><a class="btn btn-sm btn-outline-primary" href="<?php echo BASE_URL; ?>views/pia.php?editar=<?php echo (int)$piaLinha['idPIA']; ?>" title="Editar PIA" aria-label="Editar PIA de <?php echo $h($piaLinha['acolhido_nome']); ?>"><i class="bi bi-pencil-square" aria-hidden="true"></i></a><a class="btn btn-sm btn-outline-secondary" href="<?php echo BASE_URL; ?>views/gerar_pia_pdf.php?id=<?php echo (int)$piaLinha['idPIA']; ?>" target="_blank" onclick="return abrirPreviaPdf(this.href);" title="Imprimir PIA" aria-label="Imprimir PIA de <?php echo $h($piaLinha['acolhido_nome']); ?>"><i class="bi bi-printer" aria-hidden="true"></i></a></div></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody></table></div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</main>

<?php if ($mostrarFormulario): ?>
    <script type="application/json" id="perfisAssinantes"><?php echo $perfisAssinantesJson; ?></script>
    <template id="modeloAssinante"><div class="pia-assinante-card" data-assinante-row><div class="row g-3 align-items-end"><div class="col-12 col-lg-6"><label class="form-label" for="assinante-__INDEX__">Pessoa responsável</label><select class="form-select" name="assinantes[__INDEX__][usuario_id]" id="assinante-__INDEX__" required><option value="">Selecione um usuário ativo</option><?php foreach ($usuariosAssinantes as $usuarioAssinante): ?><option value="<?php echo (int)$usuarioAssinante['idUsuario']; ?>"><?php echo $h($usuarioAssinante['nome']); ?><?php echo empty($usuarioAssinante['idLogin']) ? ' (sem acesso ao sistema)' : ((int)$usuarioAssinante['ativo'] !== 1 ? ' (acesso desativado)' : ''); ?></option><?php endforeach; ?></select></div><div class="col-12 col-lg-5"><label class="form-label" for="modoAssinante-__INDEX__">Identificação que será impressa</label><select class="form-select" name="assinantes[__INDEX__][modo]" id="modoAssinante-__INDEX__" required><option value="profissional">Atuação profissional</option><option value="nome">Somente nome</option><option value="conselho">Cargo no conselho e mandato</option><option value="ambos">Atuação e cargo no conselho</option></select></div><div class="col-12 col-lg-1 d-grid"><button class="btn btn-outline-danger" type="button" data-remove-signer aria-label="Remover assinante"><i class="bi bi-trash" aria-hidden="true"></i></button></div><div class="col-12 small text-body-secondary" data-signature-summary>Os dados profissionais e o mandato serão exibidos conforme o perfil da pessoa selecionada.</div></div></div></template>
    <script>
        (() => {
            const dataAcolhido = document.getElementById('acolhidoId');
            dataAcolhido?.addEventListener('change', () => {
                const option = dataAcolhido.selectedOptions[0];
                const dataBr = value => value && /^\d{4}-\d{2}-\d{2}$/.test(value) ? value.split('-').reverse().join('/') : '';
                const dataCabecalho = document.getElementById('dataAcolhimentoCabecalho');
                if (dataCabecalho) dataCabecalho.textContent = dataBr(option?.dataset.acolhimento || '');
                const nascimento = document.getElementById('campo-data_nascimento');
                if (nascimento && !nascimento.value) nascimento.value = option?.dataset.nascimento || '';
            });
            document.querySelectorAll('[data-add-row]').forEach(button => {
                button.addEventListener('click', () => {
                    const body = document.getElementById(button.dataset.addRow);
                    const model = document.getElementById(button.dataset.template);
                    if (!body || !model) return;
                    const index = Number(body.dataset.counter || 0);
                    body.dataset.counter = String(index + 1);
                    body.insertAdjacentHTML('beforeend', model.innerHTML.replaceAll('__INDEX__', String(index)));
                });
            });
            document.addEventListener('click', event => {
                const removeRow = event.target.closest('[data-remove-row]');
                if (removeRow) removeRow.closest('tr')?.remove();
                const removeSigner = event.target.closest('[data-remove-signer]');
                if (removeSigner) removeSigner.closest('[data-assinante-row]')?.remove();
            });
            const assinantes = document.getElementById('listaAssinantes');
            const modeloAssinante = document.getElementById('modeloAssinante');
            const perfis = JSON.parse(document.getElementById('perfisAssinantes')?.textContent || '{}');
            const atualizarOpcoesModo = row => {
                const pessoa = row.querySelector('select[name$="[usuario_id]"]')?.value || '';
                const modoSelect = row.querySelector('select[name$="[modo]"]');
                if (!pessoa || !modoSelect) return;
                const perfil = perfis[pessoa] || {};
                const temAtuacao = Boolean((perfil.atuacao || '').trim());
                const temCargo = Boolean((perfil.cargo || '').trim());
                modoSelect.querySelectorAll('option').forEach(option => {
                    option.disabled = option.value === 'profissional' ? !temAtuacao
                        : option.value === 'conselho' ? !temCargo
                        : option.value === 'ambos' ? !(temAtuacao && temCargo) : false;
                });
                if (modoSelect.selectedOptions[0]?.disabled) {
                    modoSelect.value = temAtuacao ? 'profissional' : (temCargo ? 'conselho' : 'nome');
                }
            };
            const atualizarResumo = row => {
                const pessoa = row.querySelector('select[name$="[usuario_id]"]')?.value || '';
                const modo = row.querySelector('select[name$="[modo]"]')?.value || '';
                const perfil = perfis[pessoa];
                const resumo = row.querySelector('[data-signature-summary]');
                if (!perfil || !resumo) return;
                if (modo === 'nome') {
                    resumo.textContent = 'A assinatura será identificada somente pelo nome cadastrado.';
                    return;
                }
                const linhas = [];
                if (['profissional', 'ambos'].includes(modo) && perfil.atuacao) {
                    linhas.push('Atuação: ' + perfil.atuacao + (perfil.registro ? ' · Registro: ' + perfil.registro : ''));
                }
                if (['conselho', 'ambos'].includes(modo) && perfil.cargo) {
                    const dataBr = value => value && /^\d{4}-\d{2}-\d{2}$/.test(value) ? value.split('-').reverse().join('/') : '';
                    const inicio = dataBr(perfil.inicio || '');
                    const fim = dataBr(perfil.fim || '');
                    linhas.push('Conselho: ' + perfil.cargo + (inicio || fim ? ' · Mandato: ' + inicio + ' a ' + fim : ''));
                }
                resumo.textContent = linhas.join('. ') || 'O perfil não possui os dados selecionados. Atualize o cadastro do usuário antes de imprimir.';
            };
            const vincularResumo = row => {
                row.querySelector('select[name$="[usuario_id]"]')?.addEventListener('change', () => {
                    atualizarOpcoesModo(row);
                    atualizarResumo(row);
                });
                row.querySelector('select[name$="[modo]"]')?.addEventListener('change', () => atualizarResumo(row));
                atualizarOpcoesModo(row);
            };
            document.querySelectorAll('[data-assinante-row]').forEach(vincularResumo);
            document.getElementById('adicionarAssinante')?.addEventListener('click', () => {
                if (!assinantes || !modeloAssinante) return;
                const index = Number(assinantes.dataset.counter || 0);
                assinantes.dataset.counter = String(index + 1);
                assinantes.insertAdjacentHTML('beforeend', modeloAssinante.innerHTML.replaceAll('__INDEX__', String(index)));
                if (assinantes.lastElementChild) vincularResumo(assinantes.lastElementChild);
            });
            document.getElementById('formPia')?.addEventListener('submit', event => {
                if (!assinantes || assinantes.querySelector('[data-assinante-row]')) return;
                event.preventDefault();
                alert('Adicione pelo menos um responsável pelas assinaturas do PIA.');
            });
        })();
    </script>
<?php endif; ?>
<?php require_once ROOT_DIR . '/src/View/templates/includes/rodapeInterno.inc.php'; ?>



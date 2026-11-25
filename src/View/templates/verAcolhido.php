<?php 
$controleAcesso ??= new \Abdm\Infraestrutura\ControleAcesso($pdo);
$controleAcesso->exigirPermissao('acolhidos');
require_once ROOT_DIR.'/src/View/templates/includes/cabecalhoInterno.inc.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("<div class='container my-5'><div class='alert alert-danger'>ID do acolhido não informado.</div></div>");
}

[
    'idAcolhido' => $idAcolhido,
    'acolhido' => $acolhido,
    'gruposComplementares' => $gruposComplementares,
    'cids' => $cids,
    'endereco' => $endereco,
    'familiares' => $familiares,
    'curador' => $curador,
    'enderecoCurador' => $enderecoCurador,
] = (new \Abdm\Controller\TelasAcolhidoController($pdo))->exibir();

?>

<div class="container my-5 d-print-none">
    <div class="row justify-content-center">
        <div class="col-lg-12">
            <div class="card shadow-lg border-0 rounded-lg">
                <div class="card-header bg-primary text-white d-flex flex-column align-items-stretch gap-2">
                    <h3 class="h5 fw-normal mb-0 d-flex flex-wrap align-items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-person-vcard-fill me-2 flex-shrink-0" viewBox="0 0 16 16">
                            <path d="M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2-2H2a2 2 0 0 1-2-2V4Zm9 1.5a.5.5 0 0 0 .5.5h4a.5.5 0 0 0 0-1h-4a.5.5 0 0 0-.5.5ZM9 8a.5.5 0 0 0 .5.5h4a.5.5 0 0 0 0-1h-4A.5.5 0 0 0 9 8Zm1 2.5a.5.5 0 0 0 .5.5h3a.5.5 0 0 0 0-1h-3a.5.5 0 0 0-.5.5ZM5 8c0 .54-.42 1-1 1s-1-.46-1-1 .42-1 1-1 1 .46 1 1ZM1 12a1 1 0 0 0 1 1h7.5a.5.5 0 0 0 0-1H2a1 1 0 0 0-1 1Z"/>
                        </svg>
                        <span class="text-nowrap">Ficha do Acolhido:</span>
                        <span class="text-nowrap ms-2"><?php echo htmlspecialchars($acolhido['nome']); ?></span>
                    </h3>
                    <div class="d-flex flex-wrap align-items-center gap-2">
                        <a href="<?php echo BASE_URL; ?>views/acolhidos.php" class="btn btn-light me-2" title="Voltar para a lista">
                            <i class="bi bi-arrow-left-circle me-1"></i> Voltar
                        </a>
                        <a href="<?php echo BASE_URL; ?>views/editarAcolhido.php?id=<?php echo $idAcolhido; ?>" class="btn btn-light fw-bold me-2" title="Editar este acolhido">
                            <i class="bi bi-pencil-square me-1"></i> Editar
                        </a>
                        <?php if ($controleAcesso->usuarioPode('registrar_evolucao_acolhido') || $controleAcesso->usuarioPode('consultar_minhas_evolucoes') || $controleAcesso->usuarioPode('consultar_todas_evolucoes') || $controleAcesso->usuarioPode('imprimir_evolucao_acolhido') || $controleAcesso->usuarioPode('imprimir_minhas_evolucoes') || $controleAcesso->usuarioPode('imprimir_evolucoes_profissional') || $controleAcesso->usuarioPode('imprimir_evolucoes_todos')): ?>
                            <a href="<?php echo BASE_URL; ?>views/evolucoes.php?acolhido=<?php echo $idAcolhido; ?>" class="btn btn-light fw-bold me-2" title="Registrar ou consultar a evolução deste acolhido">
                                <i class="bi bi-journal-medical me-1" aria-hidden="true"></i> Evolução
                            </a>
                        <?php endif; ?>
                        <?php if ($controleAcesso->usuarioPode('pia')): ?>
                            <a href="<?php echo BASE_URL; ?>views/pia.php?novo=1&amp;acolhido=<?php echo $idAcolhido; ?>" class="btn btn-light fw-bold me-2" title="Criar um PIA para este acolhido">
                                <i class="bi bi-journal-text me-1" aria-hidden="true"></i> PIA
                            </a>
                        <?php endif; ?>
                        <?php if ($controleAcesso->usuarioPode('prescricoes')): ?>
                            <a href="<?php echo BASE_URL; ?>views/prescricoes.php?id_acolhido=<?php echo $idAcolhido; ?>" class="btn btn-success fw-bold me-2" title="Ver prescrições">
                                <i class="bi bi-file-medical-fill me-1"></i> Prescrições
                            </a>
                        <?php endif; ?>
                        <button type="button" class="btn btn-danger fw-bold me-2" data-bs-toggle="modal" data-bs-target="#desligarModal" title="Desligar acolhido">
                            <i class="bi bi-person-x-fill me-1"></i> Desligar
                        </button>
                        <a href="<?php echo BASE_URL; ?>views/gerar_ficha_acolhido_pdf.php?id=<?php echo $idAcolhido; ?>" target="_blank" onclick="return abrirPreviaPdf(this.href);" class="btn btn-secondary" title="Imprimir Ficha">
                            <i class="bi bi-printer-fill me-1"></i> Imprimir
                        </a>
                    </div>
                </div>
                <div class="card-body p-4">
                    <?php if ($acolhido['status'] == 'desligado'): ?>
                        <h5 class="mt-2 mb-4 border-bottom pb-2 text-danger">Status do Acolhido</h5>
                        <div class="alert alert-warning" role="alert">
                            <h4 class="alert-heading">Acolhido Desligado</h4>
                            <p>Este acolhido foi desligado em <strong><?php echo date('d/m/Y', strtotime($acolhido['dataDesligamento'])); ?></strong>.</p>
                            <hr>
                            <p class="mb-0"><strong>Motivo:</strong> <?php echo htmlspecialchars($acolhido['motivoDesligamento']); ?></p>
                        </div>
                    <?php endif; ?>

                    <!-- Dados Pessoais -->
                    <h5 class="mt-2 mb-4 border-bottom pb-2 text-primary">Dados Pessoais</h5>
                    <div class="row mb-3">
                        <div class="col-md-6 mb-3"><div class="form-floating"><input type="text" class="form-control" value="<?php echo htmlspecialchars($acolhido['nome']); ?>" readonly><label>Nome Completo</label></div></div>
                        <div class="col-md-3 mb-3"><div class="form-floating"><input type="text" class="form-control" value="<?php echo date('d/m/Y', strtotime($acolhido['dataNascimento'])); ?>" readonly><label>Data de Nascimento</label></div></div>
                        <div class="col-md-3 mb-3"><div class="form-floating"><input type="text" class="form-control" value="<?php echo htmlspecialchars(\Abdm\Model\DadosAcolhido::acolhidoSexoRotulo($acolhido['sexo'] ?? null), ENT_QUOTES, 'UTF-8'); ?>" readonly><label>Sexo</label></div></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4 mb-3"><div class="form-floating"><input type="text" class="form-control" value="<?php echo htmlspecialchars(\Abdm\Infraestrutura\Criptografia::descriptografar($acolhido['cpf'])); ?>" readonly><label>CPF</label></div></div>
                        <div class="col-md-3 mb-3"><div class="form-floating"><input type="text" class="form-control" value="<?php echo htmlspecialchars($acolhido['tipo_documento'] ?? 'Não informado'); ?>" readonly><label>Tipo Doc. Ident.</label></div></div>
                        <div class="col-md-5 mb-3"><div class="form-floating"><input type="text" class="form-control" value="<?php echo htmlspecialchars(\Abdm\Model\DadosAcolhido::acolhidoDocumentoDescriptografar($acolhido['documento_identificacao'] ?? '') ?: 'Não informado'); ?>" readonly><label>Nº Documento</label></div></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4 mb-3"><div class="form-floating"><input type="text" class="form-control" value="<?php echo htmlspecialchars(\Abdm\Model\DadosAcolhido::acolhidoDocumentoDescriptografar($acolhido['cartao_sus'] ?? '') ?: 'Não possui'); ?>" readonly><label>Cartão SUS</label></div></div>
                        <div class="col-md-4 mb-3"><div class="form-floating"><input type="text" class="form-control" value="<?php echo htmlspecialchars(\Abdm\Model\DadosAcolhido::acolhidoDocumentoDescriptografar($acolhido['cartao_municipal_saude'] ?? '') ?: 'Não possui'); ?>" readonly><label>Cartão Municipal de Saúde</label></div></div>
                        <div class="col-md-4 mb-3"><div class="form-floating"><input type="text" class="form-control" value="<?php echo htmlspecialchars($acolhido['tipoBeneficio'] ?? 'Não possui'); ?>" readonly><label>Tipo de Benefício</label></div></div>
                    </div>
                    <div class="mb-3"><div class="form-floating"><textarea class="form-control auto-expand" readonly style="min-height: 80px;"><?php echo htmlspecialchars($acolhido['plano_assistencial_funerario'] ?? 'Não possui'); ?></textarea><label>Plano Assistencial / Funerário</label></div></div>
                    <div class="row mb-3">
                        <div class="col-md-4 mb-3"><div class="form-floating"><input type="text" class="form-control" value="<?php echo date('d/m/Y', strtotime($acolhido['dataAcolhimento'])); ?>" readonly><label>Data de Acolhimento</label></div></div>
                        <div class="col-md-4 mb-3"><div class="form-floating"><input type="text" class="form-control" value="<?php echo htmlspecialchars($acolhido['origemAcolhimento'] ?? 'Não informada'); ?>" readonly><label>Origem do Acolhimento</label></div></div>
                        <div class="col-md-4 mb-3"><div class="form-floating"><input type="text" class="form-control" value="<?php echo htmlspecialchars($acolhido['matricula'] ?? 'N/A'); ?>" readonly><label>Matrícula</label></div></div>
                    </div>

                    <?php if ($gruposComplementares): ?>
                        <section class="border rounded-3 p-3 p-lg-4 mb-4 bg-body-tertiary">
                            <h5 class="mb-3 border-bottom pb-2 text-primary">Documentos, renda e informações de saúde</h5>
                            <?php foreach ($gruposComplementares as $tituloComplementar => $itensComplementares): ?>
                                <h6 class="mt-3 mb-2 text-secondary"><?php echo htmlspecialchars($tituloComplementar, ENT_QUOTES, 'UTF-8'); ?></h6>
                                <div class="table-responsive"><table class="table table-sm table-bordered bg-body align-middle mb-3"><tbody>
                                    <?php foreach ($itensComplementares as $rotuloComplementar => $valorComplementar): ?>
                                        <tr><th scope="row" class="w-25"><?php echo htmlspecialchars($rotuloComplementar, ENT_QUOTES, 'UTF-8'); ?></th><td><?php echo nl2br(htmlspecialchars($valorComplementar, ENT_QUOTES, 'UTF-8')); ?></td></tr>
                                    <?php endforeach; ?>
                                </tbody></table></div>
                            <?php endforeach; ?>
                        </section>
                    <?php endif; ?>

                    <!-- Filiação -->
                    <h5 class="mt-4 mb-4 border-bottom pb-2 text-primary">Filiação</h5>
                    <div class="row mb-3">
                        <div class="col-md-6 mb-3"><div class="form-floating"><input type="text" class="form-control" value="<?php echo htmlspecialchars($acolhido['nome_mae'] ?? 'Não informada'); ?>" readonly><label>Nome da Mãe</label></div></div>
                        <div class="col-md-6 mb-3"><div class="form-floating"><input type="text" class="form-control" value="<?php echo htmlspecialchars($acolhido['nome_pai'] ?? 'Não informado'); ?>" readonly><label>Nome do Pai</label></div></div>
                    </div>

                    <!-- Endereço -->
                    <h5 class="mt-4 mb-4 border-bottom pb-2 text-primary">Endereço de Origem</h5>
                    <?php if($endereco): ?>
                        <div class="row mb-3">
                            <div class="col-md-3 mb-3"><div class="form-floating"><input type="text" class="form-control" value="<?php echo htmlspecialchars($endereco['cep']); ?>" readonly><label>CEP</label></div></div>
                            <div class="col-md-6 mb-3"><div class="form-floating"><input type="text" class="form-control" value="<?php echo htmlspecialchars($endereco['rua']); ?>" readonly><label>Rua/Logradouro</label></div></div>
                            <div class="col-md-3 mb-3"><div class="form-floating"><input type="text" class="form-control" value="<?php echo htmlspecialchars($endereco['numero']); ?>" readonly><label>Número</label></div></div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6 mb-3"><div class="form-floating"><input type="text" class="form-control" value="<?php echo htmlspecialchars($endereco['cidade']); ?>" readonly><label>Cidade</label></div></div>
                            <div class="col-md-6 mb-3"><div class="form-floating"><input type="text" class="form-control" value="<?php echo htmlspecialchars($endereco['estado']); ?>" readonly><label>Estado</label></div></div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6"><div class="form-floating"><input type="text" class="form-control" value="<?php echo htmlspecialchars($acolhido['endereco_referencia'] ?? 'Não informado'); ?>" readonly><label>Referência</label></div></div>
                            <div class="col-md-6"><div class="form-floating"><textarea class="form-control auto-expand" readonly style="min-height: 58px;"><?php echo htmlspecialchars($acolhido['endereco_observacao'] ?? 'Nenhuma.'); ?></textarea><label>Observação do Endereço</label></div></div>
                        </div>
                    <?php else: ?>
                        <p class="text-muted fst-italic">Endereço não informado.</p>
                    <?php endif; ?>

                    <!-- Curador -->
                    <h5 class="mt-4 mb-3 border-bottom pb-2 text-primary">Curador / Responsável Legal</h5>
                    <?php if($curador): ?>
                        <div class="row mb-3">
                            <div class="col-md-6 mb-3"><div class="form-floating"><input type="text" class="form-control" value="<?php echo htmlspecialchars($curador['nome']); ?>" readonly><label>Nome</label></div></div>
                            <div class="col-md-6 mb-3"><div class="form-floating"><input type="text" class="form-control" value="<?php echo htmlspecialchars(\Abdm\Infraestrutura\Criptografia::descriptografar($curador['cpf'])); ?>" readonly><label>CPF</label></div></div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6 mb-3"><div class="form-floating"><input type="text" class="form-control" value="<?php echo !empty($curador['datanNascimento']) ? date('d/m/Y', strtotime($curador['datanNascimento'])) : 'Não informado'; ?>" readonly><label>Data de Nascimento</label></div></div>
                            <div class="col-md-6 mb-3"><div class="form-floating"><input type="text" class="form-control" value="<?php echo htmlspecialchars($curador['grauParentesco']); ?>" readonly><label>Grau de Parentesco</label></div></div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6 mb-3"><div class="form-floating"><input type="text" class="form-control" value="<?php echo htmlspecialchars($curador['telefone'] ?? 'Não informado'); ?>" readonly><label>Telefone</label></div></div>
                            <div class="col-md-6 mb-3"><div class="form-floating"><input type="text" class="form-control" value="<?php echo htmlspecialchars($curador['telefone_2'] ?? 'Não informado'); ?>" readonly><label>Telefone 2</label></div></div>
                        </div>
                        <?php if($enderecoCurador): ?>
                            <h6 class="mt-2 mb-3 text-secondary">Endereço do Curador</h6>
                            <div class="row mb-3">
                                <div class="col-md-3 mb-3"><div class="form-floating"><input type="text" class="form-control" value="<?php echo htmlspecialchars($enderecoCurador['cep'] ?? 'N/A'); ?>" readonly><label>CEP</label></div></div>
                                <div class="col-md-6 mb-3"><div class="form-floating"><input type="text" class="form-control" value="<?php echo htmlspecialchars($enderecoCurador['rua'] ?? 'N/A'); ?>" readonly><label>Rua/Logradouro</label></div></div>
                                <div class="col-md-3 mb-3"><div class="form-floating"><input type="text" class="form-control" value="<?php echo htmlspecialchars($enderecoCurador['numero'] ?? 'N/A'); ?>" readonly><label>Número</label></div></div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3"><div class="form-floating"><input type="text" class="form-control" value="<?php echo htmlspecialchars($enderecoCurador['cidade'] ?? 'N/A'); ?>" readonly><label>Cidade</label></div></div>
                                <div class="col-md-6 mb-3"><div class="form-floating"><input type="text" class="form-control" value="<?php echo htmlspecialchars($enderecoCurador['estado'] ?? 'N/A'); ?>" readonly><label>Estado</label></div></div>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <p class="text-muted fst-italic">Não possui / Não informado.</p>
                    <?php endif; ?>

                    <!-- CIDs -->
                    <h5 class="mt-4 mb-3 border-bottom pb-2 text-primary">CIDs Relacionados</h5>
                    <?php if(count($cids) > 0): ?>
                        <?php foreach($cids as $cid): ?>
                            <div class="input-group mb-3">
                                <div class="form-floating" style="width: 20%;"><input type="text" class="form-control" value="<?php echo htmlspecialchars(function_exists('mb_strtoupper') ? mb_strtoupper((string)$cid['codigo'], 'UTF-8') : strtoupper((string)$cid['codigo'])); ?>" readonly><label>Código CID</label></div>
                                <div class="form-floating" style="width: 80%;"><input type="text" class="form-control" value="<?php echo htmlspecialchars($cid['descricao']); ?>" readonly><label>Descrição</label></div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted fst-italic">Nenhum CID informado.</p>
                    <?php endif; ?>

                    <!-- Informações Clínicas e Nutricionais -->
                    <h5 class="mt-4 mb-3 border-bottom pb-2 text-primary">Informações Clínicas e Nutricionais</h5>
                    <div class="row mb-3">
                        <div class="col-md-6 mb-3"><div class="form-floating"><textarea class="form-control auto-expand" readonly style="min-height: 120px;"><?php echo htmlspecialchars($acolhido['info_saude'] ?? 'Não informado.'); ?></textarea><label>Outras Comorbidades, Alergias e Restrições</label></div></div>
                        <div class="col-md-6 mb-3"><div class="form-floating"><textarea class="form-control auto-expand" readonly style="min-height: 120px;"><?php echo htmlspecialchars($acolhido['info_nutri'] ?? 'Não informado.'); ?></textarea><label>Informações Alimentares e Nutricionais</label></div></div>
                    </div>
                    <div class="mb-3"><div class="form-floating"><textarea class="form-control auto-expand" readonly style="min-height: 100px;"><?php echo htmlspecialchars($acolhido['motivoInstitucionalizacao'] ?? 'Não informado'); ?></textarea><label>Motivo da Institucionalização</label></div></div>
                    <div class="mb-3"><div class="form-floating"><textarea class="form-control auto-expand" readonly style="min-height: 120px;"><?php echo htmlspecialchars($acolhido['historico'] ?? 'Nenhum histórico registrado.'); ?></textarea><label>Histórico / Observações Gerais</label></div></div>

                    <!-- Familiares -->
                    <h5 class="mt-4 mb-3 border-bottom pb-2 text-primary">Familiares Registrados</h5>
                    <?php if(count($familiares) > 0): ?>
                        <?php foreach($familiares as $index => $fam): ?>
                            <div class="border p-3 mb-3 rounded shadow-sm">
                                <h6 class="mb-3 text-secondary">Familiar #<?php echo $index + 1; ?></h6>
                                <div class="row mb-3">
                                    <div class="col-md-6 mb-3"><div class="form-floating"><input type="text" class="form-control" value="<?php echo htmlspecialchars($fam['nome']); ?>" readonly><label>Nome</label></div></div>
                                    <div class="col-md-6 mb-3"><div class="form-floating"><input type="text" class="form-control" value="<?php echo htmlspecialchars($fam['grauParentesco']); ?>" readonly><label>Grau Parentesco</label></div></div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-6 mb-3"><div class="form-floating"><input type="text" class="form-control" value="<?php echo !empty($fam['dataNascimento']) ? date('d/m/Y', strtotime($fam['dataNascimento'])) : 'Não informado'; ?>" readonly><label>Data de Nascimento</label></div></div>
                                    <div class="col-md-6 mb-3"><div class="form-floating"><input type="text" class="form-control" value="<?php echo htmlspecialchars($fam['telefone'] ?? 'Não informado'); ?>" readonly><label>Telefone</label></div></div>
                                </div>
                                <h6 class="mt-3 mb-3 border-bottom pb-1 text-muted">Endereço do Familiar</h6>
                                <div class="row mb-3">
                                    <div class="col-md-3 mb-3"><div class="form-floating"><input type="text" class="form-control" value="<?php echo htmlspecialchars($fam['cep'] ?? 'N/A'); ?>" readonly><label>CEP</label></div></div>
                                    <div class="col-md-6 mb-3"><div class="form-floating"><input type="text" class="form-control" value="<?php echo htmlspecialchars($fam['rua'] ?? 'N/A'); ?>" readonly><label>Rua/Logradouro</label></div></div>
                                    <div class="col-md-3 mb-3"><div class="form-floating"><input type="text" class="form-control" value="<?php echo htmlspecialchars($fam['numero'] ?? 'N/A'); ?>" readonly><label>Número</label></div></div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3"><div class="form-floating"><input type="text" class="form-control" value="<?php echo htmlspecialchars($fam['cidade'] ?? 'N/A'); ?>" readonly><label>Cidade</label></div></div>
                                    <div class="col-md-6 mb-3"><div class="form-floating"><input type="text" class="form-control" value="<?php echo htmlspecialchars($fam['estado'] ?? 'N/A'); ?>" readonly><label>Estado</label></div></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted fst-italic">Nenhum familiar informado.</p>
                    <?php endif; ?>
                </div> <!-- Fim card-body -->

                <div class="card-footer text-center py-3">
                    <div class="text-muted small">Ficha gerada em <?php echo date('d/m/Y H:i:s'); ?></div>
                </div>
            </div> <!-- Fim card -->
        </div>
    </div>
</div>

<!-- Modal de Desligamento -->
<div class="modal fade" id="desligarModal" tabindex="-1" aria-labelledby="desligarModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="desligarModalLabel">Desligar Acolhido</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="<?php echo BASE_URL; ?>acoes/controllerDesligarAcolhido.php" method="POST">
        <?php echo \Abdm\Infraestrutura\Sessao::campoTokenCsrf(); ?>
        <div class="modal-body">
            <input type="hidden" name="acao" value="desligar">
            <input type="hidden" name="id_acolhido" value="<?php echo $idAcolhido; ?>">
            <p>Você está prestes a desligar <strong><?php echo htmlspecialchars($acolhido['nome']); ?></strong> do sistema.</p>
            <div class="mb-3">
                <label for="dataDesligamento" class="form-label">Data do Desligamento</label>
                <input type="date" class="form-control" id="dataDesligamento" name="data_desligamento" value="<?php echo date('Y-m-d'); ?>" required>
            </div>
            <div class="mb-3">
                <label for="motivoDesligamento" class="form-label">Motivo do Desligamento</label>
                <textarea class="form-control" id="motivoDesligamento" name="motivo_desligamento" rows="4" required></textarea>
            </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-danger">Confirmar Desligamento</button>
        </div>
      </form>
    </div>
  </div>
</div>

<style>
    .form-control[readonly].auto-expand { resize: none; overflow-y: hidden; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    function ajustarAlturaAreaTexto(el) {
        el.style.height = 'auto';
        el.style.height = (el.scrollHeight + 2) + 'px';
    }
    document.querySelectorAll('.form-control[readonly].auto-expand').forEach(textarea => {
        ajustarAlturaAreaTexto(textarea);
    });
});
</script>

<?php
require_once ROOT_DIR.'/src/View/templates/includes/rodapeInterno.inc.php';
?>

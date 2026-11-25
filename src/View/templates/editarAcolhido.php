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
    'cpfBancoNaoRecuperavel' => $cpfBancoNaoRecuperavel,
    'cids' => $cids,
    'endereco' => $endereco,
    'familiares' => $familiares,
    'curador' => $curador,
    'enderecoCurador' => $enderecoCurador,
    'temDadosFormularioAnterior' => $temDadosFormularioAnterior,
    'curadorNaoPossuiForm' => $curadorNaoPossuiForm,
    'curadorCpfForm' => $curadorCpfForm,
    'maeNaoInformadoForm' => $maeNaoInformadoForm,
    'paiNaoInformadoForm' => $paiNaoInformadoForm,
    'familiarNaoPossuiForm' => $familiarNaoPossuiForm,
] = (new \Abdm\Controller\TelasAcolhidoController($pdo))->editar();

?>

<div class="container my-5">
    <?php if ($cpfBancoNaoRecuperavel): ?>
        <div class="alert alert-warning" role="alert">
            O CPF salvo neste registro antigo não pôde ser recuperado. Informe novamente o CPF correto antes de salvar as alterações.
        </div>
    <?php endif; ?>
    <div class="row justify-content-center">
        <div class="col-lg-12">
            <div class="card shadow-lg border-0 rounded-lg">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h3 class="text-center font-weight-light my-3 mb-0">
                        <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="currentColor" class="bi bi-pencil-square me-2" viewBox="0 0 16 16">
                          <path d="M15.502 1.94a.5.5 0 0 1 0 .706L14.459 3.69l-2-2L13.502.646a.5.5 0 0 1 .707 0l1.293 1.293zm-1.75 2.456-2-2L4.939 9.21a.5.5 0 0 0-.121.196l-.805 2.414a.25.25 0 0 0 .316.316l2.414-.805a.5.5 0 0 0 .196-.12l6.813-6.814z"/>
                          <path fill-rule="evenodd" d="M1 13.5A1.5 1.5 0 0 0 2.5 15h11a1.5 1.5 0 0 0 1.5-1.5v-6a.5.5 0 0 0-1 0v6a.5.5 0 0 1-.5.5h-11a.5.5 0 0 1-.5-.5v-11a.5.5 0 0 1 .5-.5H9a.5.5 0 0 0 0-1H2.5A1.5 1.5 0 0 0 1 2.5v11z"/>
                        </svg>
                        Editar Cadastro de Acolhido
                    </h3>
                    <a href="<?php echo BASE_URL; ?>views/verAcolhido.php?id=<?php echo $idAcolhido; ?>" class="btn btn-light text-primary fw-bold">Voltar</a>
                </div>
                <div class="card-body">
                    <?php if ($temDadosFormularioAnterior): ?>
                        <div class="alert alert-info" role="status">Os dados digitados foram preservados. Corrija ou complete as informações pendentes e salve novamente.</div>
                    <?php endif; ?>
                    <form action="<?php echo BASE_URL; ?>acoes/controllerAtualizarAcolhido.php" method="POST" id="form-cadastro" autocomplete="off">
                        <?php echo \Abdm\Infraestrutura\Sessao::campoTokenCsrf(); ?>
                        <input type="hidden" name="id_acolhido" value="<?php echo $idAcolhido; ?>">
                        
                        <h5 class="mt-4 mb-3 border-bottom pb-2 text-primary">Dados Pessoais</h5>
                        
                        <div class="row mb-3">
                            <div class="col-md-4 mb-3">
                                <div class="form-floating">
                                    <input class="form-control" id="inputNomeCompleto" name="nome" type="text" placeholder="Nome Completo" value="<?php echo htmlspecialchars($acolhido['nome'] ?? ''); ?>" required />
                                    <label for="inputNomeCompleto">Nome Completo</label>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="form-floating">
                                    <input class="form-control" id="inputDataNasc" name="data_nascimento" type="date" value="<?php echo htmlspecialchars($acolhido['dataNascimento'] ?? ''); ?>" required />
                                    <label for="inputDataNasc">Data de Nascimento</label>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="form-floating">
                                    <select class="form-select" id="inputSexo" name="sexo">
                                        <option value="">Selecione</option>
                                        <?php foreach (\Abdm\Model\DadosAcolhido::acolhidoOpcoesSexo() as $valorSexo => $rotuloSexo): ?>
                                            <option value="<?php echo htmlspecialchars($valorSexo, ENT_QUOTES, 'UTF-8'); ?>" <?php echo ($acolhido['sexo'] ?? '') === $valorSexo ? 'selected' : ''; ?>><?php echo htmlspecialchars($rotuloSexo, ENT_QUOTES, 'UTF-8'); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <label for="inputSexo">Sexo</label>
                                </div>
                            </div>
                        </div>

                                                <?php require ROOT_DIR . '/src/View/templates/includes/acolhido_dados_pessoais_form.inc.php'; ?>

<div class="mb-3">
                            <label for="planoAssistencial">Plano Assistencial ou Funerário (Descreva)</label>
                            <textarea class="form-control auto-expand" id="planoAssistencial" name="plano_assistencial_funerario" rows="2"><?php echo htmlspecialchars($acolhido['plano_assistencial_funerario'] ?? ''); ?></textarea>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6 mb-3">
                                <div class="form-floating">
                                    <input class="form-control" id="inputDataAcolhimento" name="data_acolhimento" type="date" value="<?php echo htmlspecialchars($acolhido['dataAcolhimento'] ?? ''); ?>" required />
                                    <label for="inputDataAcolhimento">Data de Acolhimento</label>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="form-floating">
                                    <input class="form-control" id="inputOrigemAcolhimento" name="origem_acolhimento" type="text" placeholder="Origem" value="<?php echo htmlspecialchars($acolhido['origemAcolhimento'] ?? ''); ?>" />
                                    <label for="inputOrigemAcolhimento">Origem do Acolhimento</label>
                                </div>
                            </div>
                        </div>
                        
                        <h5 class="mt-4 mb-3 border-bottom pb-2 text-primary">Filiação</h5>
                        <div class="row mb-3">
                            <div class="col-md-6 mb-3">
                                <div class="form-floating">
                                    <input class="form-control" id="inputNomeMae" name="nome_mae" type="text" placeholder="Nome da Mãe" value="<?php echo htmlspecialchars($acolhido['nome_mae'] ?? $acolhido['nomeMae'] ?? ''); ?>" required />
                                    <label for="inputNomeMae">Nome da Mãe</label>
                                </div>
                                <div class="form-check mt-2">
                                    <input class="form-check-input" type="checkbox" value="1" id="maeNaoInformado" name="mae_nao_informado" <?php echo $maeNaoInformadoForm ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="maeNaoInformado">Nome da Mãe não informado</label>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="form-floating">
                                    <input class="form-control" id="inputNomePai" name="nome_pai" type="text" placeholder="Nome do Pai" value="<?php echo htmlspecialchars($acolhido['nome_pai'] ?? $acolhido['nomePai'] ?? ''); ?>" required />
                                    <label for="inputNomePai">Nome do Pai</label>
                                </div>
                                <div class="form-check mt-2">
                                    <input class="form-check-input" type="checkbox" value="1" id="paiNaoInformado" name="pai_nao_informado" <?php echo $paiNaoInformadoForm ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="paiNaoInformado">Nome do Pai não informado</label>
                                </div>
                            </div>
                        </div>

                        <h5 class="mt-4 mb-3 border-bottom pb-2 text-primary">Endereço de Origem</h5>
                        <div class="row mb-3">
                            <div class="col-md-3 mb-3">
                                <div class="form-floating">
                                    <input class="form-control <?php echo $endereco ? 'is-valid' : ''; ?>" id="inputCep" name="endereco_cep" type="text" placeholder="CEP" maxlength="9" value="<?php echo htmlspecialchars($endereco['cep'] ?? ''); ?>" required />
                                    <label for="inputCep">CEP</label>
                                </div>
                                <div class="invalid-feedback" id="cep-feedback">CEP inválido ou não encontrado.</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="form-floating">
                                    <input class="form-control" id="inputRua" name="endereco_rua" type="text" placeholder="Rua/Logradouro" value="<?php echo htmlspecialchars($endereco['rua'] ?? ''); ?>" required />
                                    <label for="inputRua">Rua/Logradouro</label>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="form-floating">
                                    <input class="form-control" id="inputNumero" name="endereco_numero" type="number" placeholder="Número" value="<?php echo htmlspecialchars($endereco['numero'] ?? ''); ?>" required />
                                    <label for="inputNumero">Número</label>
                                </div>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6 mb-3">
                                <div class="form-floating">
                                    <input class="form-control" id="inputCidade" name="endereco_cidade" type="text" placeholder="Cidade" value="<?php echo htmlspecialchars($endereco['cidade'] ?? ''); ?>" required />
                                    <label for="inputCidade">Cidade</label>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="form-floating">
                                    <input class="form-control" id="inputEstado" name="endereco_estado" type="text" placeholder="Estado" value="<?php echo htmlspecialchars($endereco['estado'] ?? ''); ?>" required />
                                    <label for="inputEstado">Estado</label>
                                </div>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6 mb-3">
                                <div class="form-floating">
                                    <input class="form-control" id="inputReferencia" name="endereco_referencia" type="text" placeholder="Referência" maxlength="150" value="<?php echo htmlspecialchars($acolhido['endereco_referencia'] ?? ''); ?>" />
                                    <label for="inputReferencia">Referência</label>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="form-floating">
                                    <textarea class="form-control" id="inputEnderecoObs" name="endereco_observacao" placeholder="Observação"><?php echo htmlspecialchars($acolhido['endereco_observacao'] ?? ''); ?></textarea>
                                    <label for="inputEnderecoObs">Observação <span class="text-muted small">(opcional)</span></label>
                                </div>
                            </div>
                        </div>

                        <h5 class="mt-5 mb-3 border-bottom pb-2 text-primary">Curador / Responsável Legal</h5>
                        <div class="form-check mt-2 mb-4">
                            <input class="form-check-input" type="checkbox" value="1" id="curadorNaoPossui" name="curador_nao_possui" <?php echo $curadorNaoPossuiForm ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="curadorNaoPossui">Não possui Curador / Responsável Legal</label>
                        </div>
                        
                        <div id="curador-container">
                            <div class="row mb-3">
                                <div class="col-md-6 mb-3">
                                    <div class="form-floating">
                                        <input class="form-control curador-input" id="inputNomeCurador" name="curador_nome" type="text" placeholder="Nome do Curador" value="<?php echo htmlspecialchars($curador['nome'] ?? ''); ?>" required data-required="true"/>
                                        <label for="inputNomeCurador">Nome do Curador</label>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="form-floating">
                                        <input class="form-control curador-input <?php echo $curador && !empty($curador['cpf']) ? 'is-valid' : ''; ?>" id="inputCpfCurador" name="curador_cpf" type="text" placeholder="CPF do Curador" maxlength="14" value="<?php echo htmlspecialchars($curadorCpfForm, ENT_QUOTES, 'UTF-8'); ?>" />
                                        <label for="inputCpfCurador">CPF do Curador</label>
                                    </div>
                                    <div class="invalid-feedback" id="curador-cpf-feedback">CPF inválido.</div>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-3 mb-3">
                                    <div class="form-floating">
                                        <input class="form-control curador-input curador-cep <?php echo $enderecoCurador ? 'is-valid' : ''; ?>" id="inputCepCurador" name="curador_endereco_cep" type="text" placeholder="CEP Curador" maxlength="9" value="<?php echo htmlspecialchars($enderecoCurador['cep'] ?? ''); ?>"/>
                                        <label for="inputCepCurador">CEP Curador</label>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="form-floating">
                                        <input class="form-control curador-input curador-rua" id="inputRuaCurador" name="curador_endereco_rua" type="text" placeholder="Rua Curador" value="<?php echo htmlspecialchars($enderecoCurador['rua'] ?? ''); ?>" />
                                        <label for="inputRuaCurador">Rua/Logradouro</label>
                                    </div>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <div class="form-floating">
                                        <input class="form-control curador-input" id="inputNumeroCurador" name="curador_endereco_numero" type="number" placeholder="Número Curador" value="<?php echo htmlspecialchars($enderecoCurador['numero'] ?? ''); ?>"/>
                                        <label for="inputNumeroCurador">Número</label>
                                    </div>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6 mb-3">
                                    <div class="form-floating">
                                        <input class="form-control curador-input curador-cidade" id="inputCidadeCurador" name="curador_endereco_cidade" type="text" placeholder="Cidade Curador" value="<?php echo htmlspecialchars($enderecoCurador['cidade'] ?? ''); ?>" />
                                        <label for="inputCidadeCurador">Cidade</label>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="form-floating">
                                        <input class="form-control curador-input curador-estado" id="inputEstadoCurador" name="curador_endereco_estado" type="text" placeholder="Estado Curador" value="<?php echo htmlspecialchars($enderecoCurador['estado'] ?? ''); ?>" />
                                        <label for="inputEstadoCurador">Estado</label>
                                    </div>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6 mb-3">
                                    <div class="form-floating">
                                        <input class="form-control curador-input" id="inputDataNascCurador" name="curador_dataNascimento" type="date" value="<?php echo htmlspecialchars($curador['datanNascimento'] ?? ''); ?>"/>
                                        <label for="inputDataNascCurador">Data de Nascimento</label>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="form-floating">
                                        <input class="form-control curador-input" id="inputGrauParentescoCurador" name="curador_grauParentesco" type="text" placeholder="Grau de Parentesco" required data-required="true" value="<?php echo htmlspecialchars($curador['grauParentesco'] ?? ''); ?>"/>
                                        <label for="inputGrauParentescoCurador">Grau de Parentesco</label>
                                    </div>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6 mb-3">
                                    <div class="form-floating">
                                        <input class="form-control curador-input" id="inputTelefoneCurador" name="curador_telefone" type="tel" placeholder="Telefone" value="<?php echo htmlspecialchars($curador['telefone'] ?? ''); ?>" />
                                        <label for="inputTelefoneCurador">Telefone</label>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="form-floating">
                                        <input class="form-control curador-input" id="inputTelefoneCurador2" name="curador_telefone_2" type="tel" placeholder="Telefone Alternativo" value="<?php echo htmlspecialchars($curador['telefone_2'] ?? ''); ?>" />
                                        <label for="inputTelefoneCurador2">Telefone Alternativo (Opcional)</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <h5 class="mt-5 mb-3 border-bottom pb-2 text-primary">CID-10 — diagnósticos identificados</h5>
                        <div id="cid-container">
                            <div class="text-info mb-3" id="cid-loading-status">Carregando base de dados CID...</div>
                        </div>
                        <button type="button" class="btn btn-outline-info w-100 py-2 mt-3 mb-4" id="add-cid-btn">Adicionar CID</button>

                        <?php require ROOT_DIR . '/src/View/templates/includes/acolhido_saude_form.inc.php'; ?>

                        <section class="card border-0 shadow-sm mb-4"><div class="card-header bg-body-tertiary"><h2 class="h5 mb-0">Informações para o Acolhimento</h2></div><div class="card-body">
                        <div class="mb-3">
                            <label for="motivoInstitucionalizacao">Motivo da Institucionalização</label>
                            <textarea class="form-control auto-expand" id="motivoInstitucionalizacao" name="motivo_institucionalizacao" rows="3" required><?php echo htmlspecialchars($acolhido['motivoInstitucionalizacao'] ?? ''); ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="historicoAcolhido">Histórico/Observações Gerais</label>
                            <textarea class="form-control auto-expand" id="historicoAcolhido" name="historico" rows="4"><?php echo htmlspecialchars($acolhido['historico'] ?? ''); ?></textarea>
                        </div>

                        </div></section>

                        <h5 class="mt-5 mb-3 border-bottom pb-2 text-primary">Familiares</h5>
                        <div class="form-check mt-2 mb-4">
                            <input class="form-check-input" type="checkbox" value="1" id="familiarNaoPossui" name="familiar_nao_possui" <?php echo $familiarNaoPossuiForm ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="familiarNaoPossui">Não possui familiares / Não informar</label>
                        </div>
                        <div id="familiares-container"></div>
                        <button type="button" class="btn btn-outline-success w-100 py-2 mt-3 mb-4" id="add-familiar-btn">Adicionar Familiar</button>

                        
                        <div class="mt-5 mb-0">
                            <button type="submit" class="btn btn-primary btn-block w-100 py-3 fs-5 fw-bold shadow">
                                Salvar Alterações do Acolhido
                            </button>
                        </div>
                    </form>
                </div>
                <div class="card-footer text-center py-3"><div class="text-muted small">Revise as informações antes de salvar as alterações do acolhido.</div></div>
            </div>
        </div>
    </div>
</div>

<script src="<?php echo BASE_URL; ?>assets/js/cep-autocomplete.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        let familiarCount = 0;
        let cidCount = 0;
        const familiarContainer = document.getElementById('familiares-container');
        const cidContainer = document.getElementById('cid-container');
        const addFamiliarBtn = document.getElementById('add-familiar-btn');
        const addCidBtn = document.getElementById('add-cid-btn');
        const cidLoadingStatus = document.getElementById('cid-loading-status');
        
        // ==========================================
        // INJEÇÃO DOS DADOS DO BANCO
        // ==========================================
        const prefilledCids = <?php echo json_encode($cids, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_INVALID_UTF8_SUBSTITUTE) ?: '[]'; ?>;
        const prefilledFamiliares = <?php echo json_encode($familiares, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_INVALID_UTF8_SUBSTITUTE) ?: '[]'; ?>;

        // CID - CSV Loading
        let cidDatabase = null;
        fetch('<?php echo BASE_URL; ?>assets/CID-10-CATEGORIAS.CSV')
            .then(res => res.arrayBuffer())
            .then(buffer => {
                const decoder = new TextDecoder('iso-8859-1');
                const csvText = decoder.decode(buffer);
                const lines = csvText.split('\n');
                cidDatabase = [];
                for(let i = 1; i < lines.length; i++) {
                    const cols = lines[i].split(';');
                    if(cols.length >= 3) cidDatabase.push({ codigo: cols[0].trim().toUpperCase(), descricao: cols[2].trim() });
                }
                cidLoadingStatus.textContent = 'Base CID (CSV) carregada com sucesso.';
                cidLoadingStatus.classList.replace('text-info', 'text-success');
            })
            .catch(err => {
                cidLoadingStatus.textContent = 'Erro ao carregar o arquivo CID-10-CATEGORIAS.CSV.';
                cidLoadingStatus.classList.replace('text-info', 'text-danger');
            });

        async function fetchCidDescription(cidCode) {
            const cidBuscaLimpo = cidCode.toUpperCase().substring(0, 3).replace('.', ''); 
            if (!cidDatabase) return 'Aguarde, carregando...';
            const cidEncontrado = cidDatabase.find(c => c.codigo === cidBuscaLimpo);
            return cidEncontrado ? cidEncontrado.descricao : `Categoria CID '${cidBuscaLimpo}' não encontrada.`;
        }

        // Utilitários CPF / CEP
        function formatarCPF(input) {
            let v = input.value.replace(/\D/g, ''); 
            v = v.replace(/(\d{3})(\d)/, '$1.$2');
            v = v.replace(/(\d{3})(\d)/, '$1.$2');
            v = v.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
            input.value = v;
        }
        // Criação de Blocos Dinâmicos
        function criarBlocoCid(cidData = null) {
            cidCount++;
            const blockId = `cid-${cidCount}`;
            const div = document.createElement('div');
            div.className = 'input-group mb-3 cid-block';
            div.id = blockId;
            div.innerHTML = `
                <div class="form-floating flex-grow-1">
                    <input class="form-control cid-code text-uppercase" name="cid[${cidCount}][codigo]" type="text" placeholder="CID" maxlength="6" required />
                    <label>Código CID</label>
                </div>
                <div class="form-floating flex-grow-1 ms-2">
                    <input class="form-control cid-desc" name="cid[${cidCount}][descricao]" type="text" placeholder="Descrição" readonly />
                    <label>Descrição</label>
                </div>
                <button type="button" class="btn btn-outline-danger remove-cid-btn ms-2" data-target="${blockId}">Remover</button>
            `;
            cidContainer.appendChild(div);

            const codeIn = div.querySelector('.cid-code');
            const descIn = div.querySelector('.cid-desc');
            codeIn.addEventListener('input', function() {
                this.value = this.value.toUpperCase();
            });
            
            if(cidData) {
                setTimeout(() => {
                    codeIn.value = String(cidData.codigo || '').toUpperCase();
                    descIn.value = cidData.descricao || '';
                    codeIn.classList.add('is-valid');
                }, 50);
            }
            
            codeIn.addEventListener('blur', async function() {
                if(this.value.length >= 3) descIn.value = await fetchCidDescription(this.value);
            });
        }

        function criarBlocoFamiliar(famData = null) {
            familiarCount++;
            const index = familiarCount;
            const blockId = `familiar-${index}`;
            const div = document.createElement('div');
            div.className = 'border p-3 mb-3 rounded shadow-sm familiar-block';
            div.id = blockId;
            div.innerHTML = `
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="mb-0 text-secondary">Familiar #${index}</h6>
                    <button type="button" class="btn btn-sm btn-outline-danger remove-familiar-btn" data-target="${blockId}">Remover</button>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3"><div class="form-floating"><input class="form-control fam-nome" name="familiar[${index}][nome]" type="text" required /><label>Nome</label></div></div>
                    <div class="col-md-6 mb-3"><div class="form-floating"><input class="form-control fam-grau" name="familiar[${index}][grauParentesco]" type="text" required /><label>Grau Parentesco</label></div></div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6 mb-3"><div class="form-floating"><input class="form-control fam-data" id="inputFamiliarData${index}" name="familiar[${index}][dataNascimento]" type="date" /><label for="inputFamiliarData${index}">Data de nascimento</label></div></div>
                    <div class="col-md-6 mb-3"><div class="form-floating"><input class="form-control fam-tel" id="inputFamiliarTelefone${index}" name="familiar[${index}][telefone]" type="tel" placeholder="Telefone" /><label for="inputFamiliarTelefone${index}">Telefone</label></div></div>
                </div>
                <h6 class="mt-3 mb-3 border-bottom pb-1 text-muted">Endereço</h6>
                <div class="row mb-3">
                    <div class="col-md-3 mb-3"><div class="form-floating"><input class="form-control fam-cep" id="inputFamiliarCep${index}" name="familiar[${index}][endereco_cep]" type="text" maxlength="9" /><label>CEP</label></div></div>
                    <div class="col-md-6 mb-3"><div class="form-floating"><input class="form-control fam-rua" id="inputFamiliarRua${index}" name="familiar[${index}][endereco_rua]" type="text" /><label>Rua</label></div></div>
                    <div class="col-md-3 mb-3"><div class="form-floating"><input class="form-control fam-num" name="familiar[${index}][endereco_numero]" type="number" /><label>Número</label></div></div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6 mb-3"><div class="form-floating"><input class="form-control fam-cid" id="inputFamiliarCidade${index}" name="familiar[${index}][endereco_cidade]" type="text" /><label>Cidade</label></div></div>
                    <div class="col-md-6 mb-3"><div class="form-floating"><input class="form-control fam-est" id="inputFamiliarEstado${index}" name="familiar[${index}][endereco_estado]" type="text" /><label>Estado</label></div></div>
                </div>
            `;
            familiarContainer.appendChild(div);
            
            if(famData) {
                setTimeout(() => {
                    div.querySelector('.fam-nome').value = famData.nome || '';
                    div.querySelector('.fam-grau').value = famData.grauParentesco || '';
                    div.querySelector('.fam-data').value = famData.dataNascimento || '';
                    div.querySelector('.fam-tel').value = famData.telefone || '';
                    div.querySelector('.fam-cep').value = famData.endereco_cep || '';
                    div.querySelector('.fam-rua').value = famData.endereco_rua || '';
                    div.querySelector('.fam-num').value = famData.endereco_numero || '';
                    div.querySelector('.fam-cid').value = famData.endereco_cidade || '';
                    div.querySelector('.fam-est').value = famData.endereco_estado || '';
                }, 50);
            }
            setupCepAutocomplete(`inputFamiliarCep${index}`, `inputFamiliarRua${index}`, `inputFamiliarCidade${index}`, `inputFamiliarEstado${index}`);
        }

        addCidBtn.addEventListener('click', () => criarBlocoCid());
        addFamiliarBtn.addEventListener('click', () => criarBlocoFamiliar());
        document.body.addEventListener('click', function(e) {
            if(e.target.classList.contains('remove-cid-btn') || e.target.classList.contains('remove-familiar-btn')) {
                document.getElementById(e.target.getAttribute('data-target')).remove();
            }
        });

        // Checkboxes Lógica
        function alternarObrigatoriedadeCampo(input, checkbox) {
            if(!input) return;
            input.disabled = checkbox.checked;
            if (checkbox.checked) {
                input.value = ''; input.removeAttribute('required');
            } else if(input.hasAttribute('data-required')) {
                input.setAttribute('required', '');
            }
        }

        ['maeNaoInformado','paiNaoInformado','curadorNaoPossui'].forEach(id => {
            const chk = document.getElementById(id);
            if(chk) {
                chk.addEventListener('change', function() {
                    if(this.id === 'curadorNaoPossui') {
                        document.getElementById('curador-container').style.display = this.checked ? 'none' : 'block';
                        document.querySelectorAll('.curador-input').forEach(inp => alternarObrigatoriedadeCampo(inp, this));
                    } else {
                        const inp = this.parentElement.previousElementSibling.querySelector('input');
                        alternarObrigatoriedadeCampo(inp, this);
                    }
                });
            }
        });

        const chkFamiliar = document.getElementById('familiarNaoPossui');
        chkFamiliar.addEventListener('change', function() {
            document.getElementById('familiares-container').style.display = this.checked ? 'none' : 'block';
            addFamiliarBtn.style.display = this.checked ? 'none' : 'block';
            if(this.checked) familiarContainer.innerHTML = '';
        });

        // Auto-Run pre-fills
        document.querySelectorAll('input[required]').forEach(el => el.setAttribute('data-required', 'true'));
        if(prefilledCids.length > 0) prefilledCids.forEach(cid => criarBlocoCid(cid)); else criarBlocoCid();
        if(prefilledFamiliares.length > 0) {
            prefilledFamiliares.forEach(fam => criarBlocoFamiliar(fam));
            chkFamiliar.checked = false;
        } else if (<?php echo $temDadosFormularioAnterior ? 'true' : 'false'; ?>) {
            chkFamiliar.checked = <?php echo $familiarNaoPossuiForm ? 'true' : 'false'; ?>;
            if (!chkFamiliar.checked) criarBlocoFamiliar();
        } else chkFamiliar.checked = true;
        
        ['curadorNaoPossui','maeNaoInformado','paiNaoInformado','familiarNaoPossui'].forEach(id => {
            if(document.getElementById(id)) document.getElementById(id).dispatchEvent(new Event('change'));
        });

        document.getElementById('form-cadastro').addEventListener('submit', function() {
            this.querySelectorAll('.cid-code').forEach(input => {
                input.value = input.value.toUpperCase();
            });
        });
        
        // Configura Inputs de Saúde (SUS e Municipal)
        const setupHealthCheck = (idChk, idInp) => {
            const chk = document.getElementById(idChk); const inp = document.getElementById(idInp);
            if(chk && inp) { chk.addEventListener('change', () => alternarObrigatoriedadeCampo(inp, chk)); alternarObrigatoriedadeCampo(inp, chk); }
        };
        setupHealthCheck('naoPossuiCartaoMunicipal', 'inputCartaoMunicipal');
        
        const cpfInputs = [document.getElementById('inputCPF'), document.getElementById('inputCpfCurador')];
        cpfInputs.forEach(inp => { if(inp) inp.addEventListener('input', () => formatarCPF(inp)); });
        setupCepAutocomplete('inputCep', 'inputRua', 'inputCidade', 'inputEstado');
        setupCepAutocomplete('inputCepCurador', 'inputRuaCurador', 'inputCidadeCurador', 'inputEstadoCurador');
    });
</script>

<?php require_once ROOT_DIR.'/src/View/templates/includes/rodapeInterno.inc.php'; ?>

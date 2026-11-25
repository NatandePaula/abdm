<?php 
$controleAcesso ??= new \Abdm\Infraestrutura\ControleAcesso($pdo);
$controleAcesso->exigirPermissao('acolhidos');
$dadosComplementares = [];
$acolhido = [];
$cpfForm = '';
$cartaoMunicipalForm = '';
$naoPossuiCartaoMunicipalForm = false;
require_once ROOT_DIR.'/src/View/templates/includes/cabecalhoInterno.inc.php';
?>
<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-12">
            <div class="card shadow-lg border-0 rounded-lg">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h3 class="text-center font-weight-light my-3 mb-0">
                        <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="currentColor" class="bi bi-person-plus-fill me-2" viewBox="0 0 16 16">
                            <path d="M1 14s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1zm5-7a3 3 0 1 0 0-6 3 3 0 0 0 0 6"/>
                            <path fill-rule="evenodd" d="M13.5 5a.5.5 0 0 1 .5.5V7h1.5a.5.5 0 0 1 0 1H14v1.5a.5.5 0 0 1-1 0V8h-1.5a.5.5 0 0 1 0-1H13V5.5a.5.5 0 0 1 .5-.5"/>
                        </svg>
                        Cadastro de Acolhido
                    </h3>
                    <a href="<?php echo BASE_URL; ?>views/acolhidos.php" class="btn btn-light text-primary fw-bold">Voltar</a>
                </div>
                <div class="card-body">
                    <!-- Formulário de Cadastro -->
                    <form action="<?php echo BASE_URL; ?>acoes/controllerCadastrarAcolhido.php" method="POST" id="form-cadastro" autocomplete="off">
                        <?php echo \Abdm\Infraestrutura\Sessao::campoTokenCsrf(); ?>
                        
                        <!-- 1. INFORMAÇÕES PESSOAIS -->
                        <h5 class="mt-4 mb-3 border-bottom pb-2 text-primary">Dados Pessoais</h5>
                        
                        <div class="row mb-3">
                            <div class="col-md-4 mb-3">
                                <div class="form-floating">
                                    <input class="form-control" id="inputNomeCompleto" name="nome" type="text" placeholder="Nome Completo" required />
                                    <label for="inputNomeCompleto">Nome Completo</label>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="form-floating">
                                    <input class="form-control" id="inputDataNasc" name="data_nascimento" type="date" required />
                                    <label for="inputDataNasc">Data de Nascimento</label>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="form-floating">
                                    <select class="form-select" id="inputSexo" name="sexo">
                                        <option value="">Selecione</option>
                                        <?php foreach (\Abdm\Model\DadosAcolhido::acolhidoOpcoesSexo() as $valorSexo => $rotuloSexo): ?>
                                            <option value="<?php echo htmlspecialchars($valorSexo, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($rotuloSexo, ENT_QUOTES, 'UTF-8'); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <label for="inputSexo">Sexo</label>
                                </div>
                            </div>
                        </div>

                        <?php require ROOT_DIR . '/src/View/templates/includes/acolhido_dados_pessoais_form.inc.php'; ?>

                        <div class="mb-3">
                            <label for="planoAssistencial">Plano Assistencial ou Funerário (Descreva)</label>
                            <textarea class="form-control auto-expand" id="planoAssistencial" name="plano_assistencial_funerario" rows="2"></textarea>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6 mb-3">
                                <div class="form-floating">
                                    <input class="form-control" id="inputDataAcolhimento" name="data_acolhimento" type="date" value="<?php echo date('Y-m-d'); ?>" required />
                                    <label for="inputDataAcolhimento">Data de Acolhimento</label>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="form-floating">
                                    <input class="form-control" id="inputOrigemAcolhimento" name="origem_acolhimento" type="text" placeholder="Ex: Rua, Hospital, Família" />
                                    <label for="inputOrigemAcolhimento">Origem do Acolhimento</label>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Filiação -->
                        <h5 class="mt-4 mb-3 border-bottom pb-2 text-primary">Filiação</h5>
                        <div class="row mb-3">
                            <div class="col-md-6 mb-3">
                                <div class="form-floating">
                                    <input class="form-control" id="inputNomeMae" name="nome_mae" type="text" placeholder="Nome da Mãe" required />
                                    <label for="inputNomeMae">Nome da Mãe</label>
                                </div>
                                <div class="form-check mt-2">
                                    <input class="form-check-input" type="checkbox" value="1" id="maeNaoInformado" name="mae_nao_informado">
                                    <label class="form-check-label" for="maeNaoInformado">Nome da Mãe não informado / Não possui</label>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="form-floating">
                                    <input class="form-control" id="inputNomePai" name="nome_pai" type="text" placeholder="Nome do Pai" required />
                                    <label for="inputNomePai">Nome do Pai</label>
                                </div>
                                <div class="form-check mt-2">
                                    <input class="form-check-input" type="checkbox" value="1" id="paiNaoInformado" name="pai_nao_informado">
                                    <label class="form-check-label" for="paiNaoInformado">Nome do Pai não informado / Não possui</label>
                                </div>
                            </div>
                        </div>

                        <!-- 2. ENDEREÇO -->
                        <h5 class="mt-4 mb-3 border-bottom pb-2 text-primary">Endereço de Origem</h5>
                        <div class="row mb-3">
                            <div class="col-md-3 mb-3">
                                <div class="form-floating">
                                    <input class="form-control" id="inputCep" name="endereco_cep" type="text" placeholder="CEP" maxlength="9" required />
                                    <label for="inputCep">CEP</label>
                                </div>
                                <div class="invalid-feedback" id="cep-feedback">CEP inválido ou não encontrado.</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="form-floating">
                                    <input class="form-control" id="inputRua" name="endereco_rua" type="text" placeholder="Rua/Logradouro" required />
                                    <label for="inputRua">Rua/Logradouro</label>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="form-floating">
                                    <input class="form-control" id="inputNumero" name="endereco_numero" type="number" placeholder="Número" required />
                                    <label for="inputNumero">Número</label>
                                </div>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6 mb-3">
                                <div class="form-floating">
                                    <input class="form-control" id="inputCidade" name="endereco_cidade" type="text" placeholder="Cidade" required />
                                    <label for="inputCidade">Cidade</label>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="form-floating">
                                    <input class="form-control" id="inputEstado" name="endereco_estado" type="text" placeholder="Estado" required />
                                    <label for="inputEstado">Estado</label>
                                </div>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6 mb-3">
                                <div class="form-floating">
                                    <input class="form-control" id="inputReferencia" name="endereco_referencia" type="text" placeholder="Referência" maxlength="150" />
                                    <label for="inputReferencia">Referência</label>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="form-floating">
                                    <textarea class="form-control" id="inputEnderecoObs" name="endereco_observacao" placeholder="Observação" style="height: 58px;"></textarea>
                                    <label for="inputEnderecoObs">Observação <span class="text-muted small">(opcional)</span></label>
                                </div>
                            </div>
                        </div>

                        <!-- 3. CURADOR -->
                        <h5 class="mt-5 mb-3 border-bottom pb-2 text-primary">Curador / Responsável Legal</h5>
                        <div class="form-check mt-2 mb-4">
                            <input class="form-check-input" type="checkbox" value="1" id="curadorNaoPossui" name="curador_nao_possui">
                            <label class="form-check-label" for="curadorNaoPossui">Não possui Curador / Responsável Legal</label>
                        </div>
                        
                        <div id="curador-container">
                            <div class="row mb-3">
                                <div class="col-md-6 mb-3">
                                    <div class="form-floating">
                                        <input class="form-control curador-input" id="inputNomeCurador" name="curador_nome" type="text" placeholder="Nome do Curador" required data-required="true"/>
                                        <label for="inputNomeCurador">Nome do Curador</label>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="form-floating">
                                        <input class="form-control curador-input" id="inputCpfCurador" name="curador_cpf" type="text" placeholder="CPF do Curador" maxlength="14" />
                                        <label for="inputCpfCurador">CPF do Curador</label>
                                    </div>
                                    <div class="invalid-feedback" id="curador-cpf-feedback">CPF inválido.</div>
                                </div>
                            </div>
                            <!-- Endereço Curador -->
                            <div class="row mb-3">
                                <div class="col-md-3 mb-3">
                                    <div class="form-floating">
                                        <input class="form-control curador-input curador-cep" id="inputCepCurador" name="curador_endereco_cep" type="text" placeholder="CEP Curador" maxlength="9" />
                                        <label for="inputCepCurador">CEP Curador</label>
                                    </div>
                                    <div class="invalid-feedback" id="curador-cep-feedback">CEP inválido.</div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="form-floating">
                                        <input class="form-control curador-input curador-rua" id="inputRuaCurador" name="curador_endereco_rua" type="text" placeholder="Rua Curador" />
                                        <label for="inputRuaCurador">Rua/Logradouro</label>
                                    </div>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <div class="form-floating">
                                        <input class="form-control curador-input" id="inputNumeroCurador" name="curador_endereco_numero" type="number" placeholder="Número Curador" />
                                        <label for="inputNumeroCurador">Número</label>
                                    </div>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6 mb-3">
                                    <div class="form-floating">
                                        <input class="form-control curador-input curador-cidade" id="inputCidadeCurador" name="curador_endereco_cidade" type="text" placeholder="Cidade Curador" />
                                        <label for="inputCidadeCurador">Cidade</label>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="form-floating">
                                        <input class="form-control curador-input curador-estado" id="inputEstadoCurador" name="curador_endereco_estado" type="text" placeholder="Estado Curador" />
                                        <label for="inputEstadoCurador">Estado</label>
                                    </div>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6 mb-3">
                                    <div class="form-floating">
                                        <input class="form-control curador-input" id="inputDataNascCurador" name="curador_dataNascimento" type="date" />
                                        <label for="inputDataNascCurador">Data de Nascimento</label>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="form-floating">
                                        <input class="form-control curador-input" id="inputGrauParentescoCurador" name="curador_grauParentesco" type="text" placeholder="Grau de Parentesco" required data-required="true"/>
                                        <label for="inputGrauParentescoCurador">Grau de Parentesco</label>
                                    </div>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6 mb-3">
                                    <div class="form-floating">
                                        <input class="form-control curador-input" id="inputTelefoneCurador" name="curador_telefone" type="tel" placeholder="Telefone" />
                                        <label for="inputTelefoneCurador">Telefone</label>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="form-floating">
                                        <input class="form-control curador-input" id="inputTelefoneCurador2" name="curador_telefone_2" type="tel" placeholder="Telefone Alternativo" />
                                        <label for="inputTelefoneCurador2">Telefone Alternativo (Opcional)</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        

                        <!-- CID -->
                        <h5 class="mt-5 mb-3 border-bottom pb-2 text-primary">CID-10 — diagnósticos identificados</h5>
                        <div id="cid-container">
                            <div class="text-info mb-3" id="cid-loading-status">Carregando base de dados CID...</div>
                        </div>
                        <button type="button" class="btn btn-outline-info w-100 py-2 mt-3 mb-4" id="add-cid-btn">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-file-medical me-2" viewBox="0 0 16 16">
                                <path d="M8 10a.5.5 0 0 0 .5-.5V8h2a.5.5 0 0 0 0-1h-2V5.5a.5.5 0 0 0-1 0V7h-2a.5.5 0 0 0 0 1h2v1.5a.5.5 0 0 0 .5.5z"/>
                                <path d="M4 0a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2H4zm0 1h8a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1z"/>
                            </svg>
                            Adicionar CID
                        </button>

                        <?php require ROOT_DIR . '/src/View/templates/includes/acolhido_saude_form.inc.php'; ?>

                        <!-- NOVAS CAIXAS -->
                        <section class="card border-0 shadow-sm mb-4"><div class="card-header bg-body-tertiary"><h2 class="h5 mb-0">Informações para o Acolhimento</h2></div><div class="card-body">
    <div class="mb-3"><label for="motivoInstitucionalizacao">Motivo da Institucionalização</label><textarea class="form-control auto-expand" id="motivoInstitucionalizacao" name="motivo_institucionalizacao" rows="3" required placeholder="Descreva o motivo principal do acolhimento."></textarea></div>
    <div class="mb-3"><label for="historicoAcolhido">Histórico/Observações Gerais</label><textarea class="form-control auto-expand" id="historicoAcolhido" name="historico" rows="4" placeholder="Detalhes adicionais sobre o acolhido, seu estado atual e necessidades."></textarea></div>
</div></section>
<!-- 5. FAMILIARES -->
                        <h5 class="mt-5 mb-3 border-bottom pb-2 text-primary">Familiares</h5>
                        <div class="form-check mt-2 mb-4">
                            <input class="form-check-input" type="checkbox" value="1" id="familiarNaoPossui" name="familiar_nao_possui">
                            <label class="form-check-label" for="familiarNaoPossui">Não possui familiares / Não informar</label>
                        </div>
                        <div id="familiares-container"></div>
                        <button type="button" class="btn btn-outline-success w-100 py-2 mt-3 mb-4" id="add-familiar-btn">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-person-lines-fill me-2" viewBox="0 0 16 16">
                                <path d="M6 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6m-5 6s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1zM11 5a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5zm2.5 1h-1a.5.5 0 0 0-.5.5v1a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5zm-4 4a.5.5 0 0 1 .5-.5h2a.5.5 0 0 1 0 1H9a.5.5 0 0 1-.5-.5zm-2 0a.5.5 0 0 1 .5-.5h2a.5.5 0 0 1 0 1H6a.5.5 0 0 1-.5-.5z"/>
                            </svg>
                            Adicionar Familiar
                        </button>

                        <div class="mt-5 mb-0">
                            <button type="submit" class="btn btn-primary btn-block w-100 py-3 fs-5 fw-bold shadow">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-floppy-fill me-2" viewBox="0 0 16 16">
                                    <path d="M0 1.5A1.5 1.5 0 0 1 1.5 0H3v5.5A1.5 1.5 0 0 0 4.5 7h7A1.5 1.5 0 0 0 13 5.5V0h.086a1.5 1.5 0 0 1 1.06.44l1.415 1.414A1.5 1.5 0 0 1 16 2.914V14.5a1.5 1.5 0 0 1-1.5 1.5H14v-5.5A1.5 1.5 0 0 0 12.5 9h-9A1.5 1.5 0 0 0 2 10.5V16h-.5A1.5 1.5 0 0 1 0 14.5z"/>
                                    <path d="M3 16h10v-5.5a.5.5 0 0 0-.5-.5h-9a.5.5 0 0 0-.5.5zm9-16H4v5.5a.5.5 0 0 0 .5.5h7a.5.5 0 0 0 .5-.5zM9 1h2v4H9z"/>
                                </svg>
                                Salvar Cadastro do Acolhido
                            </button>
                        </div>
                    </form>
                </div>
                <div class="card-footer text-center py-3">
                    <div class="text-muted small">Certifique-se de que todos os campos obrigatórios foram preenchidos.</div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="<?php echo BASE_URL; ?>assets/js/cep-autocomplete.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Inicialização de variáveis
        let familiarCount = 0;
        let cidCount = 0;
        const form = document.getElementById('form-cadastro');
        const familiarContainer = document.getElementById('familiares-container');
        const cidContainer = document.getElementById('cid-container');
        const addFamiliarBtn = document.getElementById('add-familiar-btn');
        const addCidBtn = document.getElementById('add-cid-btn');
        const cidLoadingStatus = document.getElementById('cid-loading-status');
        
        // ==========================================
        // Lógica do CID (busca em arquivo CSV local)
        // ==========================================
        let cidDatabase = null;
        
        fetch('<?php echo BASE_URL; ?>assets/CID-10-CATEGORIAS.CSV')
            .then(res => res.arrayBuffer())
            .then(buffer => {
                const decoder = new TextDecoder('iso-8859-1');
                const csvText = decoder.decode(buffer);
                
                const lines = csvText.split('\n');
                cidDatabase = [];
                
                // Converte o CSV para um array de objetos
                for(let i = 1; i < lines.length; i++) {
                    if(lines[i].trim() === '') continue;
                    const cols = lines[i].split(';');
                    if(cols.length >= 3) {
                        cidDatabase.push({
                            codigo: cols[0].trim().toUpperCase(),
                            descricao: cols[2].trim()
                        });
                    }
                }
                
                cidLoadingStatus.textContent = 'Base CID (CSV) carregada com sucesso.';
                cidLoadingStatus.classList.replace('text-info', 'text-success');
            })
            .catch(err => {
                console.error('Erro ao carregar CSV:', err);
                cidLoadingStatus.textContent = 'Erro ao carregar o arquivo CID-10-CATEGORIAS.CSV.';
                cidLoadingStatus.classList.replace('text-info', 'text-danger');
            });

        async function fetchCidDescription(cidCode) {
            const upperCid = cidCode.toUpperCase().trim();
            
            // O CSV usa categorias de 3 caracteres (Ex: A00), então normalizamos a busca
            const cidBuscaLimpo = upperCid.substring(0, 3).replace('.', ''); 
            
            if (!cidDatabase) return 'Aguarde, base de dados carregando...';
            
            const cidEncontrado = cidDatabase.find(c => c.codigo === cidBuscaLimpo);
            
            return cidEncontrado ? cidEncontrado.descricao : `Categoria CID '${cidBuscaLimpo}' não encontrada.`;
        }

        // ==========================================
        // Funções de Validação (CPF e CEP)
        // ==========================================
        function validarCPF(cpf) {
            cpf = cpf.replace(/[^\d]/g, "");
            if (cpf.length !== 11 || /^(\d)\1{10}$/.test(cpf)) return false; 
            let sum = 0, remainder;
            for (let i = 1; i <= 9; i++) sum += parseInt(cpf.substring(i - 1, i)) * (11 - i);
            remainder = (sum * 10) % 11;
            if ((remainder === 10) || (remainder === 11)) remainder = 0;
            if (remainder !== parseInt(cpf.substring(9, 10))) return false;
            sum = 0;
            for (let i = 1; i <= 10; i++) sum += parseInt(cpf.substring(i - 1, i)) * (12 - i);
            remainder = (sum * 10) % 11;
            if ((remainder === 10) || (remainder === 11)) remainder = 0;
            if (remainder !== parseInt(cpf.substring(10, 11))) return false;
            return true;
        }

        function formatarCPF(input) {
            let v = input.value.replace(/\D/g, ''); 
            v = v.replace(/(\d{3})(\d)/, '$1.$2');
            v = v.replace(/(\d{3})(\d)/, '$1.$2');
            v = v.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
            input.value = v;
        }

        function tratarValidacaoCpf(input, feedback) {
            formatarCPF(input);
            const valid = validarCPF(input.value);
            if (input.value.length === 14 && !valid) {
                input.classList.add('is-invalid');
                input.classList.remove('is-valid');
                if(feedback) feedback.textContent = "CPF inválido.";
            } else if (input.value.length === 14 && valid) {
                input.classList.remove('is-invalid');
                input.classList.add('is-valid');
            } else {
                input.classList.remove('is-invalid', 'is-valid');
            }
        }

        // ==========================================
        // Funções para adicionar blocos dinâmicos (CID e Familiar)
        // ==========================================
        function criarBlocoCid() {
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
            
            codeIn.addEventListener('blur', async function() {
                if(this.value.length >= 3) {
                    descIn.value = 'Buscando...';
                    descIn.value = await fetchCidDescription(this.value);
                } else {
                    descIn.value = '';
                }
            });
        }

        function criarBlocoFamiliar() {
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
                    <div class="col-md-6 mb-3">
                        <div class="form-floating">
                            <input class="form-control" name="familiar[${index}][nome]" type="text" placeholder="Nome" required />
                            <label>Nome</label>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="form-floating">
                            <input class="form-control" name="familiar[${index}][grauParentesco]" type="text" placeholder="Grau" required />
                            <label>Grau Parentesco</label>
                        </div>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6 mb-3">
                        <div class="form-floating">
                            <input class="form-control" name="familiar[${index}][dataNascimento]" type="date" />
                            <label>Data Nasc.</label>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="form-floating">
                            <input class="form-control" name="familiar[${index}][telefone]" type="tel" placeholder="Telefone" />
                            <label>Telefone</label>
                        </div>
                    </div>
                </div>
                <h6 class="mt-3 mb-3 border-bottom pb-1 text-muted">Endereço</h6>
                <div class="row mb-3">
                    <div class="col-md-3 mb-3">
                        <div class="form-floating">
                            <input class="form-control" id="inputFamiliarCep${index}" name="familiar[${index}][endereco_cep]" type="text" placeholder="CEP" maxlength="9" />
                            <label>CEP</label>
                        </div>
                        <div class="invalid-feedback" id="familiar-cep-feedback-${index}">CEP inválido.</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="form-floating">
                            <input class="form-control" id="inputFamiliarRua${index}" name="familiar[${index}][endereco_rua]" type="text" placeholder="Rua" />
                            <label>Rua</label>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="form-floating">
                            <input class="form-control" name="familiar[${index}][endereco_numero]" type="number" placeholder="Nº" />
                            <label>Número</label>
                        </div>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6 mb-3">
                        <div class="form-floating">
                            <input class="form-control" id="inputFamiliarCidade${index}" name="familiar[${index}][endereco_cidade]" type="text" placeholder="Cidade" />
                            <label>Cidade</label>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="form-floating">
                            <input class="form-control" id="inputFamiliarEstado${index}" name="familiar[${index}][endereco_estado]" type="text" placeholder="Estado" />
                            <label>Estado</label>
                        </div>
                    </div>
                </div>
            `;
            familiarContainer.appendChild(div);
            setupCepAutocomplete(`inputFamiliarCep${index}`, `inputFamiliarRua${index}`, `inputFamiliarCidade${index}`, `inputFamiliarEstado${index}`, `familiar-cep-feedback-${index}`);
        }

        // ==========================================
        // Event Listeners (BOTÕES)
        // ==========================================
        // Importante: Definir antes de chamar as funções que dependem deles
        addCidBtn.addEventListener('click', criarBlocoCid);
        addFamiliarBtn.addEventListener('click', criarBlocoFamiliar);

        // Delegação de eventos para remoção
        document.body.addEventListener('click', function(e) {
            if(e.target.classList.contains('remove-cid-btn')) {
                e.target.closest('.cid-block').remove();
            }
            if(e.target.classList.contains('remove-familiar-btn')) {
                e.target.closest('.familiar-block').remove();
            }
        });

        // ==========================================
        // Lógica para habilitar/desabilitar campos com checkboxes
        // ==========================================
        function alternarObrigatoriedadeCampo(input, checkbox) {
            if(!input) return;
            const isChecked = checkbox.checked;
            const originalRequired = input.hasAttribute('data-required');

            // Lógica: Se marcado (checked), então NÃO TEM -> Desabilita.
            // Se desmarcado (unchecked), ENTÃO TEM -> Habilita.
            input.disabled = isChecked;

            if (isChecked) {
                // Desabilitado (Não possui)
                input.value = '';
                input.removeAttribute('required');
                input.classList.remove('is-invalid', 'is-valid');
            } else {
                // Habilitado (Possui)
                if(originalRequired) input.setAttribute('required', '');
                input.classList.remove('is-valid'); // Limpa estado visual ao reativar
                
                // Adiciona validação visual ao digitar (apenas uma vez)
                if(!input.hasAttribute('data-val-listener')) {
                    input.setAttribute('data-val-listener', 'true');
                    input.addEventListener('input', function() {
                        if(this.value.trim().length > 0) this.classList.add('is-valid');
                        else this.classList.remove('is-valid');
                    });
                }
            }
        }

        // Configuração Inicial dos Checkboxes e Inputs
        const chkCurador = document.getElementById('curadorNaoPossui');
        const boxCurador = document.getElementById('curador-container');
        const chkFamiliar = document.getElementById('familiarNaoPossui');
        const boxFamiliar = document.getElementById('familiares-container');
        
        // Checkbox Curador
        chkCurador.addEventListener('change', function() {
            boxCurador.style.display = this.checked ? 'none' : 'block';
            const inputs = boxCurador.querySelectorAll('input, select');
            inputs.forEach(inp => {
                inp.disabled = this.checked;
                if(this.checked) {
                    inp.value = '';
                    inp.removeAttribute('required');
                } else if(inp.hasAttribute('data-required')) {
                    inp.setAttribute('required', '');
                }
            });
        });

        // Checkbox Familiar
        chkFamiliar.addEventListener('change', function() {
            boxFamiliar.style.display = this.checked ? 'none' : 'block';
            addFamiliarBtn.style.display = this.checked ? 'none' : 'block';
            if(this.checked) {
                boxFamiliar.innerHTML = '';
                familiarCount = 0;
            } else if(familiarCount === 0) {
                criarBlocoFamiliar();
            }
        });

        // Checkboxes Saúde
        const setupHealthCheck = (idChk, idInp) => {
            const chk = document.getElementById(idChk);
            const inp = document.getElementById(idInp);
            if (!chk || !inp) return;
            chk.addEventListener('change', () => alternarObrigatoriedadeCampo(inp, chk));
            alternarObrigatoriedadeCampo(inp, chk); // Estado inicial
        };

        // ==========================================
        // Configuração inicial da página
        // ==========================================
        document.querySelectorAll('input[required], select[required]').forEach(el => el.setAttribute('data-required', 'true'));
        
        setupHealthCheck('naoPossuiCartaoMunicipal', 'inputCartaoMunicipal');
        
        const nomeMae = document.getElementById('inputNomeMae');
        const chkMae = document.getElementById('maeNaoInformado');
        if(nomeMae && chkMae) {
            chkMae.addEventListener('change', () => alternarObrigatoriedadeCampo(nomeMae, chkMae));
            alternarObrigatoriedadeCampo(nomeMae, chkMae);
        }

        const nomePai = document.getElementById('inputNomePai');
        const chkPai = document.getElementById('paiNaoInformado');
        if(nomePai && chkPai) {
            chkPai.addEventListener('change', () => alternarObrigatoriedadeCampo(nomePai, chkPai));
            alternarObrigatoriedadeCampo(nomePai, chkPai);
        }

        const cpf1 = document.getElementById('inputCPF');
        const cpf2 = document.getElementById('inputCpfCurador');
        if(cpf1) { cpf1.addEventListener('input', ()=>formatarCPF(cpf1)); cpf1.addEventListener('blur', ()=>tratarValidacaoCpf(cpf1, document.getElementById('cpf-feedback'))); }
        if(cpf2) { cpf2.addEventListener('input', ()=>formatarCPF(cpf2)); cpf2.addEventListener('blur', ()=>tratarValidacaoCpf(cpf2, document.getElementById('curador-cpf-feedback'))); }
        
        setupCepAutocomplete('inputCep', 'inputRua', 'inputCidade', 'inputEstado', 'cep-feedback');
        setupCepAutocomplete('inputCepCurador', 'inputRuaCurador', 'inputCidadeCurador', 'inputEstadoCurador', 'curador-cep-feedback');

        document.getElementById('form-cadastro').addEventListener('submit', function() {
            this.querySelectorAll('.cid-code').forEach(input => {
                input.value = input.value.toUpperCase();
            });
        });

        chkCurador.dispatchEvent(new Event('change'));
        chkFamiliar.dispatchEvent(new Event('change'));
        
        criarBlocoCid(); // Adiciona o primeiro CID
    });
</script>

<?php
require_once ROOT_DIR.'/src/View/templates/includes/rodapeInterno.inc.php';
?>

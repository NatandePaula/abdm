<?php

namespace Abdm\Controller;

use Abdm\Infraestrutura\ControleAcesso;
use Abdm\Infraestrutura\Criptografia;
use Abdm\Model\DadosAcolhido;
use Abdm\Model\DadosFormulario;
use Abdm\Infraestrutura\Sessao;

use PDO;
use Exception;
use InvalidArgumentException;

final class CadastrarAcolhidoController
{
    private readonly ControleAcesso $controleAcesso;

    public function __construct(private readonly PDO $pdo)
    {
        $this->controleAcesso = new ControleAcesso($pdo);
    }

    public function executar(): void
    {
        $pdo = $this->pdo;
        $this->controleAcesso->exigirPermissao('acolhidos');

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            Sessao::exigirTokenCsrfValido();
            try {
                $cepAcolhido = DadosAcolhido::acolhidoCepParaBanco($_POST['endereco_cep'] ?? null, 'o CEP do endereço de origem');
                // Inicia a transação para garantir a integridade dos dados
                $pdo->beginTransaction();

                $stmtEnd = (new \Abdm\Model\PersistenciaAcolhidos($pdo))->prepararInsercaoEndereco();
                $stmtEnd->execute([
                    $cepAcolhido,
                    DadosFormulario::limparDados($_POST['endereco_rua']),
                    DadosFormulario::limparDados($_POST['endereco_numero']),
                    DadosFormulario::limparDados($_POST['endereco_cidade']),
                    DadosFormulario::limparDados($_POST['endereco_estado'])
                ]);
                $idEnderecoAcolhido = $pdo->lastInsertId();

                $curadorId = null;
                $curadorNaoPossui = isset($_POST['curador_nao_possui']) ? 1 : 0;

                if (!$curadorNaoPossui) {
                    $stmtEnd->execute([
                        DadosAcolhido::acolhidoCepParaBanco($_POST['curador_endereco_cep'] ?? null, 'o CEP do curador', false),
                        DadosFormulario::limparDados($_POST['curador_endereco_rua'] ?? null) ?? '',
                        DadosFormulario::limparDados($_POST['curador_endereco_numero']),
                        DadosFormulario::limparDados($_POST['curador_endereco_cidade'] ?? null) ?? '',
                        DadosFormulario::limparDados($_POST['curador_endereco_estado'] ?? null) ?? ''
                    ]);
                    $idEnderecoCurador = $pdo->lastInsertId();

                    $stmtCurador = (new \Abdm\Model\PersistenciaAcolhidos($pdo))->prepararInsercaoCurador();
                    $stmtCurador->execute([
                        DadosFormulario::limparDados($_POST['curador_nome']),
                        Criptografia::criptografar($_POST['curador_cpf']), // CPF Criptografado
                        DadosFormulario::limparDados($_POST['curador_dataNascimento']),
                        DadosFormulario::limparDados($_POST['curador_grauParentesco']),
                        DadosFormulario::limparDados($_POST['curador_telefone']),
                        $idEnderecoCurador
                    ]);
                    $curadorId = $pdo->lastInsertId();
                }

                $dadosComplementaresJson = DadosAcolhido::acolhidoDadosComplementaresFormularioParaJson();
                $dadosComplementares = DadosAcolhido::acolhidoDadosComplementaresJsonParaLista($dadosComplementaresJson);
                $sexoAcolhido = DadosAcolhido::acolhidoSexoParaBanco($_POST['sexo'] ?? null);
                $maeNaoInformado    = isset($_POST['mae_nao_informado']) ? 1 : 0;
                $paiNaoInformado    = isset($_POST['pai_nao_informado']) ? 1 : 0;
                $familiarNaoPossui  = isset($_POST['familiar_nao_possui']) ? 1 : 0;
                [$tipoDocumentoLegado, $numeroDocumentoLegado] = DadosAcolhido::acolhidoDocumentoLegado($dadosComplementares);
                $documentoSus = $dadosComplementares['documentos']['sus'] ?? [];
                $cartaoSus = ($documentoSus['situacao'] ?? '') === 'possui' ? DadosAcolhido::acolhidoDocumentoCriptografar($documentoSus['numero'] ?? '') : null;
                $documentoMunicipal = $dadosComplementares['documentos']['cartao_municipal'] ?? [];
                $cartaoMunicipal = ($documentoMunicipal['situacao'] ?? '') === 'possui' ? DadosAcolhido::acolhidoDocumentoCriptografar($documentoMunicipal['numero'] ?? '') : null;
                $numeroDocumentoLegado = DadosAcolhido::acolhidoDocumentoCriptografar($numeroDocumentoLegado);

                $matricula = date('Ymd') . rand(100, 999);


                $stmtAcolhido = (new \Abdm\Model\PersistenciaAcolhidos($pdo))->prepararInsercaoAcolhido();
                $stmtAcolhido->execute([
                    DadosFormulario::limparDados($_POST['nome']),
                    Criptografia::criptografar($_POST['cpf']),
                    DadosFormulario::limparDados($_POST['data_nascimento']),
                    $sexoAcolhido,
                    DadosAcolhido::acolhidoResumoBeneficios($dadosComplementares),
                    DadosFormulario::limparDados($_POST['historico']),
                    DadosFormulario::limparDados($_POST['motivo_institucionalizacao']),
                    DadosFormulario::limparDados($_POST['data_acolhimento']),
                    DadosFormulario::limparDados($_POST['origem_acolhimento']),
                    $matricula,
                    $curadorId,
                    $idEnderecoAcolhido,
                    $maeNaoInformado ? null : DadosFormulario::limparDados($_POST['nome_mae'] ?? null),
                    $paiNaoInformado ? null : DadosFormulario::limparDados($_POST['nome_pai'] ?? null),
                    $tipoDocumentoLegado,
                    $numeroDocumentoLegado,
                    DadosFormulario::limparDados($cartaoMunicipal),
                    $cartaoSus,
                    DadosFormulario::limparDados($_POST['plano_assistencial_funerario'] ?? null),
                    DadosFormulario::limparDados($_POST['info_saude'] ?? null),
                    DadosFormulario::limparDados($_POST['info_nutri'] ?? null),
                    DadosFormulario::limparDados($_POST['endereco_referencia'] ?? null),
                    DadosFormulario::limparDados($_POST['endereco_observacao'] ?? null),
                    $dadosComplementaresJson
                ]);

                $acolhidoId = $pdo->lastInsertId();

                // Todo acolhido inicia com um registro de PIA, mesmo antes do planejamento ser preenchido.
                $stmtPiaInicial = (new \Abdm\Model\PersistenciaAcolhidos($pdo))->prepararInsercaoPia();
                $stmtPiaInicial->execute([(int)$acolhidoId]);

                if (isset($_POST['cid']) && is_array($_POST['cid'])) {
                    $stmtCid = (new \Abdm\Model\PersistenciaAcolhidos($pdo))->prepararInsercaoCidDoAcolhido();

                    foreach ($_POST['cid'] as $cid) {
                        if (!is_array($cid)) {
                            continue;
                        }
                        $codigoCid = is_string($cid['codigo'] ?? null) ? strtoupper(trim($cid['codigo'])) : '';
                        if ($codigoCid !== '') {
                            $stmtCid->execute([
                                $codigoCid,
                                is_string($cid['descricao'] ?? null) ? DadosFormulario::limparDados($cid['descricao']) : null,
                                $acolhidoId
                            ]);
                        }
                    }
                }

                if (!$familiarNaoPossui && isset($_POST['familiar']) && is_array($_POST['familiar'])) {

                    $stmtFam = (new \Abdm\Model\PersistenciaAcolhidos($pdo))->prepararInsercaoFamiliar();

                    foreach ($_POST['familiar'] as $fam) {
                        if (!empty($fam['nome'])) {
                            $stmtEnd->execute([
                                DadosAcolhido::acolhidoCepParaBanco($fam['endereco_cep'] ?? null, 'o CEP do familiar', false),
                                DadosFormulario::limparDados($fam['endereco_rua'] ?? null) ?? '',
                                DadosFormulario::limparDados($fam['endereco_numero']),
                                DadosFormulario::limparDados($fam['endereco_cidade'] ?? null) ?? '',
                                DadosFormulario::limparDados($fam['endereco_estado'] ?? null) ?? ''
                            ]);
                            $idEnderecoFam = $pdo->lastInsertId();

                            $stmtFam->execute([
                                DadosFormulario::limparDados($fam['nome']),
                                DadosFormulario::limparDados($fam['grauParentesco']),
                                DadosFormulario::limparDados($fam['dataNascimento']),
                                DadosFormulario::limparDados($fam['telefone']),
                                $acolhidoId,
                                $idEnderecoFam
                            ]);
                        }
                    }
                }

                $pdo->commit();

                $_SESSION['flash_message'] = [
                    'type' => 'success',
                    'message' => 'Acolhido cadastrado com sucesso!'
                ];
                header("Location: ../views/acolhidos.php");
                exit();

            } catch (Exception $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                error_log('Falha ao cadastrar o acolhido. Código: ' . $e->getCode());
                $_SESSION['flash_message'] = [
                    'type' => 'danger',
                    'message' => $e instanceof InvalidArgumentException ? $e->getMessage() : 'Não foi possível salvar o cadastro.'
                ];
                header("Location: ../views/cadastrarAcolhido.php");
                exit;
            }
        } else {
            header("Location: ../views/cadastro_acolhido.php");
            exit();
        }
    }
}

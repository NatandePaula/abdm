<?php

namespace Abdm\Controller;

use Abdm\Infraestrutura\ControleAcesso;
use Abdm\Infraestrutura\Criptografia;
use Abdm\Model\DadosAcolhido;
use Abdm\Model\DadosFormulario;
use Abdm\Infraestrutura\Sessao;

use PDO;
use Throwable;
use Exception;
use InvalidArgumentException;

final class AtualizarAcolhidoController
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

        if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id_acolhido'])) {
            Sessao::exigirTokenCsrfValido();
            try {
                $idAcolhido = (int)$_POST['id_acolhido'];
                $cepAcolhido = DadosAcolhido::acolhidoCepParaBanco($_POST['endereco_cep'] ?? null, 'o CEP do endereço de origem');
                $pdo->beginTransaction();

                // Busca referências antigas (Endereço e Curador)
                $stmt = (new \Abdm\Model\PersistenciaAcolhidos($pdo))->prepararConsultaAcolhidoComEnderecoIdEnderecoPorId();
                $stmt->execute([$idAcolhido]);
                $atual = $stmt->fetch();

                if (!$atual) {
                    throw new Exception("Acolhido não encontrado para atualização.");
                }

                if ($atual['Endereco_idEndereco']) {
                    $stmtEnd = (new \Abdm\Model\PersistenciaAcolhidos($pdo))->prepararAtualizacaoEnderecoDeCep();
                    $stmtEnd->execute([
                        $cepAcolhido, DadosFormulario::limparDados($_POST['endereco_rua']),
                        DadosFormulario::limparDados($_POST['endereco_numero']), DadosFormulario::limparDados($_POST['endereco_cidade']),
                        DadosFormulario::limparDados($_POST['endereco_estado']), $atual['Endereco_idEndereco']
                    ]);
                }

                $curadorNaoPossui = isset($_POST['curador_nao_possui']) ? 1 : 0;
                $curadorId = $atual['curador_id'];

                if (!$curadorNaoPossui) {
                    if ($curadorId) {
                        $stmtCur = (new \Abdm\Model\PersistenciaAcolhidos($pdo))->prepararAtualizacaoCuradorDeNome();
                        $stmtCur->execute([
                            DadosFormulario::limparDados($_POST['curador_nome']), Criptografia::criptografar($_POST['curador_cpf']),
                            DadosFormulario::limparDados($_POST['curador_dataNascimento']), DadosFormulario::limparDados($_POST['curador_grauParentesco']),
                            DadosFormulario::limparDados($_POST['curador_telefone']), DadosFormulario::limparDados($_POST['curador_telefone_2']), $curadorId
                        ]);

                        $stmtCurEndId = (new \Abdm\Model\PersistenciaAcolhidos($pdo))->prepararConsultaCuradorComIdEnderecoPorId();
                        $stmtCurEndId->execute([$curadorId]);
                        $idEndCurador = $stmtCurEndId->fetchColumn();

                        if ($idEndCurador) {
                            $stmtEnd = (new \Abdm\Model\PersistenciaAcolhidos($pdo))->prepararAtualizacaoEnderecoDeCep();
                            $stmtEnd->execute([
                                DadosAcolhido::acolhidoCepParaBanco($_POST['curador_endereco_cep'] ?? null, 'o CEP do curador', false), DadosFormulario::limparDados($_POST['curador_endereco_rua'] ?? null) ?? '',
                                DadosFormulario::limparDados($_POST['curador_endereco_numero'] ?? null), DadosFormulario::limparDados($_POST['curador_endereco_cidade'] ?? null) ?? '',
                                DadosFormulario::limparDados($_POST['curador_endereco_estado'] ?? null) ?? '', $idEndCurador
                            ]);
                        }
                    } else {
                        $stmtNewEnd = (new \Abdm\Model\PersistenciaAcolhidos($pdo))->prepararInsercaoEndereco();
                        $stmtNewEnd->execute([
                            DadosAcolhido::acolhidoCepParaBanco($_POST['curador_endereco_cep'] ?? null, 'o CEP do curador', false), DadosFormulario::limparDados($_POST['curador_endereco_rua'] ?? null) ?? '',
                            DadosFormulario::limparDados($_POST['curador_endereco_numero'] ?? null), DadosFormulario::limparDados($_POST['curador_endereco_cidade'] ?? null) ?? '',
                            DadosFormulario::limparDados($_POST['curador_endereco_estado'] ?? null) ?? ''
                        ]);
                        $newIdEnd = $pdo->lastInsertId();

                        $stmtNewCur = (new \Abdm\Model\PersistenciaAcolhidos($pdo))->prepararInsercaoCuradorComSegundoTelefone();
                        $stmtNewCur->execute([
                            DadosFormulario::limparDados($_POST['curador_nome']), Criptografia::criptografar($_POST['curador_cpf']),
                            DadosFormulario::limparDados($_POST['curador_dataNascimento']), DadosFormulario::limparDados($_POST['curador_grauParentesco']),
                            DadosFormulario::limparDados($_POST['curador_telefone']), DadosFormulario::limparDados($_POST['curador_telefone_2']),
                            $newIdEnd
                        ]);
                        $curadorId = $pdo->lastInsertId();
                    }
                } else {
                    $curadorId = null; // Se "não possui" estiver marcado, o ID do curador é nulo.
                }

                $dadosComplementaresBase = DadosAcolhido::acolhidoDadosComplementaresParaEdicao($atual);
                $dadosComplementaresBaseJson = json_encode($dadosComplementaresBase, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
                $dadosComplementaresJson = DadosAcolhido::acolhidoDadosComplementaresFormularioParaJson($dadosComplementaresBaseJson === false ? null : $dadosComplementaresBaseJson);
                $dadosComplementares = DadosAcolhido::acolhidoDadosComplementaresJsonParaLista($dadosComplementaresJson);
                $sexoAcolhido = DadosAcolhido::acolhidoSexoParaBanco($_POST['sexo'] ?? null);
                [$tipoDocumentoLegado, $numeroDocumentoLegado] = DadosAcolhido::acolhidoDocumentoLegado($dadosComplementares);
                $documentoSus = $dadosComplementares['documentos']['sus'] ?? [];
                $cartaoSus = ($documentoSus['situacao'] ?? '') === 'possui' ? DadosAcolhido::acolhidoDocumentoCriptografar($documentoSus['numero'] ?? '') : null;
                $documentoMunicipal = $dadosComplementares['documentos']['cartao_municipal'] ?? [];
                $cartaoMunicipal = ($documentoMunicipal['situacao'] ?? '') === 'possui' ? DadosAcolhido::acolhidoDocumentoCriptografar($documentoMunicipal['numero'] ?? '') : null;
                $numeroDocumentoLegado = DadosAcolhido::acolhidoDocumentoCriptografar($numeroDocumentoLegado);

                $stmtAcolhido = (new \Abdm\Model\PersistenciaAcolhidos($pdo))->prepararAtualizacaoAcolhidoDeNome();
                $stmtAcolhido->execute([
                    DadosFormulario::limparDados($_POST['nome'] ?? null), Criptografia::criptografar($_POST['cpf'] ?? null), DadosFormulario::limparDados($_POST['data_nascimento'] ?? null),
                    $sexoAcolhido,
                    DadosAcolhido::acolhidoResumoBeneficios($dadosComplementares), DadosFormulario::limparDados($_POST['historico'] ?? null), DadosFormulario::limparDados($_POST['motivo_institucionalizacao'] ?? null),
                    DadosFormulario::limparDados($_POST['data_acolhimento'] ?? null), DadosFormulario::limparDados($_POST['origem_acolhimento'] ?? null), $curadorId,
                    isset($_POST['mae_nao_informado']) ? null : DadosFormulario::limparDados($_POST['nome_mae'] ?? null),
                    isset($_POST['pai_nao_informado']) ? null : DadosFormulario::limparDados($_POST['nome_pai'] ?? null),
                    $tipoDocumentoLegado, $numeroDocumentoLegado,
                    DadosFormulario::limparDados($cartaoMunicipal),
                    $cartaoSus, DadosFormulario::limparDados($_POST['plano_assistencial_funerario'] ?? null),
                    DadosFormulario::limparDados($_POST['info_saude'] ?? null), DadosFormulario::limparDados($_POST['info_nutri'] ?? null),
                    DadosFormulario::limparDados($_POST['endereco_referencia'] ?? null), DadosFormulario::limparDados($_POST['endereco_observacao'] ?? null), $dadosComplementaresJson,
                    $idAcolhido
                ]);

                (new \Abdm\Model\PersistenciaAcolhidos($pdo))->prepararExclusaoCidDoAcolhido()->execute([$idAcolhido]);
                if (isset($_POST['cid']) && is_array($_POST['cid'])) {
                    $stmtCid = (new \Abdm\Model\PersistenciaAcolhidos($pdo))->prepararInsercaoCidDoAcolhido();
                    foreach ($_POST['cid'] as $cid) {
                        if (!is_array($cid)) {
                            continue;
                        }
                        $codigoCid = is_string($cid['codigo'] ?? null) ? strtoupper(trim($cid['codigo'])) : '';
                        if ($codigoCid !== '') {
                            $descricaoCid = is_string($cid['descricao'] ?? null) ? DadosFormulario::limparDados($cid['descricao']) : null;
                            $stmtCid->execute([$codigoCid, $descricaoCid, $idAcolhido]);
                        }
                    }
                }

                (new \Abdm\Model\PersistenciaAcolhidos($pdo))->prepararExclusaoFamiliar()->execute([$idAcolhido]);
                if (!isset($_POST['familiar_nao_possui']) && isset($_POST['familiar']) && is_array($_POST['familiar'])) {
                    $stmtNewEnd = (new \Abdm\Model\PersistenciaAcolhidos($pdo))->prepararInsercaoEndereco();
                    $stmtFam = (new \Abdm\Model\PersistenciaAcolhidos($pdo))->prepararInsercaoFamiliar();
                    foreach ($_POST['familiar'] as $fam) {
                        if (!empty($fam['nome'] ?? null)) {
                            $stmtNewEnd->execute([DadosAcolhido::acolhidoCepParaBanco($fam['endereco_cep'] ?? null, 'o CEP do familiar', false), DadosFormulario::limparDados($fam['endereco_rua'] ?? null) ?? '', DadosFormulario::limparDados($fam['endereco_numero'] ?? null), DadosFormulario::limparDados($fam['endereco_cidade'] ?? null) ?? '', DadosFormulario::limparDados($fam['endereco_estado'] ?? null) ?? '']);
                            $stmtFam->execute([DadosFormulario::limparDados($fam['nome']), DadosFormulario::limparDados($fam['grauParentesco'] ?? null), DadosFormulario::limparDados($fam['dataNascimento'] ?? null), DadosFormulario::limparDados($fam['telefone'] ?? null), $idAcolhido, $pdo->lastInsertId()]);
                        }
                    }
                }

                $stmtPiaAtualizar = (new \Abdm\Model\PersistenciaAcolhidos($pdo))->prepararConsultaPiaComIdPIAPorAcolhidoIdUnica();
                $stmtPiaAtualizar->execute([$idAcolhido]);
                $piaExistenteId = (int)$stmtPiaAtualizar->fetchColumn();
                if ($piaExistenteId > 0) {
                    (new \Abdm\Model\PersistenciaAcolhidos($pdo))->prepararAtualizacaoPiaDeDataAtualizacao()->execute([(int)($_SESSION['id_login'] ?? 0), $piaExistenteId]);
                } else {
                    (new \Abdm\Model\PersistenciaAcolhidos($pdo))->prepararInsercaoPia()->execute([$idAcolhido]);
                }
                $pdo->commit();
                if (isset($_SESSION['acolhido_edicao_pendente'][$idAcolhido])) {
                    unset($_SESSION['acolhido_edicao_pendente'][$idAcolhido]);
                }
                $_SESSION['flash_message'] = [
                    'type' => 'success',
                    'message' => 'Ficha do acolhido atualizada com sucesso!'
                ];
                header("Location: ../views/verAcolhido.php?id=$idAcolhido");
                exit();

            } catch (Exception $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                error_log('Falha ao atualizar o cadastro do acolhido. Código: ' . $e->getCode());
                if ($idAcolhido > 0) {
                    $dadosFormulario = $_POST;
                    unset($dadosFormulario['csrf_token'], $dadosFormulario['id_acolhido']);
                    if (is_array($dadosFormulario['dados_acolhido']['documentos'] ?? null)) {
                        try {
                            foreach ($dadosFormulario['dados_acolhido']['documentos'] as &$documentoPendente) {
                                if (is_array($documentoPendente) && isset($documentoPendente['numero'])) {
                                    $documentoPendente['numero'] = DadosAcolhido::acolhidoDocumentoCriptografar($documentoPendente['numero']) ?? '';
                                }
                            }
                            unset($documentoPendente);
                        } catch (Throwable $erroCriptografia) {
                            foreach ($dadosFormulario['dados_acolhido']['documentos'] as &$documentoPendente) {
                                if (is_array($documentoPendente)) {
                                    $documentoPendente['numero'] = '';
                                }
                            }
                            unset($documentoPendente);
                        }
                    }
                    $_SESSION['acolhido_edicao_pendente'][$idAcolhido] = [
                        'dados' => $dadosFormulario,
                    ];
                }
                $_SESSION['flash_message'] = ['type' => 'danger', 'message' => $e instanceof InvalidArgumentException ? $e->getMessage() : 'Não foi possível atualizar o cadastro.'];
                header("Location: ../views/editarAcolhido.php?id=" . $idAcolhido);
                exit;
            }
        } else {
            header("Location: ../views/acolhidos.php");
            exit();
        }
    }
}

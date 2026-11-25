<?php

namespace Abdm\Controller;

use PDO;

final class TelasAcolhidoController
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function exibir(): array
    {
        $pdo = $this->pdo;
        $idAcolhido = (int)$_GET['id'];

        $acolhido = (new \Abdm\Model\Acolhidos($pdo))->ficha($idAcolhido);

        if (!$acolhido) {
            die("<div class='container my-5'><div class='alert alert-danger'>Acolhido não encontrado.</div></div>");
        }

        $dadosComplementares = \Abdm\Model\DadosAcolhido::acolhidoDadosComplementaresParaEdicao($acolhido);
        $gruposComplementares = \Abdm\Model\DadosAcolhido::acolhidoDadosComplementaresGrupos($dadosComplementares);

        $cids = (new \Abdm\Model\Acolhidos($pdo))->cids($idAcolhido);

        $endereco = null;
        if (!empty($acolhido['Endereco_idEndereco'])) {
            $endereco = (new \Abdm\Model\Acolhidos($pdo))->endereco($acolhido['Endereco_idEndereco']);
        }

        $familiares = (new \Abdm\Model\Acolhidos($pdo))->familiares($idAcolhido);

        $curador = null;
        $enderecoCurador = null;
        if (!empty($acolhido['curador_id'])) {
            $curador = (new \Abdm\Model\Acolhidos($pdo))->curador($acolhido['curador_id']);

            if($curador && !empty($curador['idEndereco'])) {
                $enderecoCurador = (new \Abdm\Model\Acolhidos($pdo))->endereco($curador['idEndereco']);
            }
        }

        return [
            'idAcolhido' => $idAcolhido ?? null,
            'acolhido' => $acolhido ?? null,
            'gruposComplementares' => $gruposComplementares ?? null,
            'cids' => $cids ?? null,
            'endereco' => $endereco ?? null,
            'familiares' => $familiares ?? null,
            'curador' => $curador ?? null,
            'enderecoCurador' => $enderecoCurador ?? null,
        ];
    }

    public function editar(): array
    {
        $pdo = $this->pdo;
        $idAcolhido = (int)$_GET['id'];


        $acolhido = (new \Abdm\Model\Acolhidos($pdo))->ficha($idAcolhido);
        if (!$acolhido) die("<div class='container my-5 alert alert-danger'>Acolhido não encontrado.</div>");
        $dadosComplementares = \Abdm\Model\DadosAcolhido::acolhidoDadosComplementaresParaEdicao($acolhido);
        $cpfArmazenado = (string)($acolhido['cpf'] ?? '');
        $cpfForm = (string)\Abdm\Infraestrutura\Criptografia::descriptografar($cpfArmazenado);
        $cpfDigitosForm = preg_replace('/\D+/', '', $cpfForm) ?? '';
        $cpfBancoNaoRecuperavel = $cpfArmazenado !== '' && strlen($cpfDigitosForm) !== 11;
        if ($cpfBancoNaoRecuperavel) {
            $cpfForm = '';
        }
        $cartaoMunicipalForm = \Abdm\Model\DadosAcolhido::acolhidoDocumentoDescriptografar($acolhido['cartao_municipal_saude'] ?? '');
        $naoPossuiCartaoMunicipalForm = $cartaoMunicipalForm === '';

        $cids = (new \Abdm\Model\Acolhidos($pdo))->cids($idAcolhido);

        $endereco = null;
        if (!empty($acolhido['Endereco_idEndereco'])) {
            $endereco = (new \Abdm\Model\Acolhidos($pdo))->endereco($acolhido['Endereco_idEndereco']);
        }

        $familiares = (new \Abdm\Model\Acolhidos($pdo))->familiaresParaEdicao($idAcolhido);

        $curador = null; $enderecoCurador = null;
        if (!empty($acolhido['curador_id'])) {
            $curador = (new \Abdm\Model\Acolhidos($pdo))->curador($acolhido['curador_id']);

            if($curador && !empty($curador['idEndereco'])) {
                $enderecoCurador = (new \Abdm\Model\Acolhidos($pdo))->endereco($curador['idEndereco']);
            }
        }

        $pendenciaEdicao = $_SESSION['acolhido_edicao_pendente'][$idAcolhido] ?? null;
        $dadosFormularioAnterior = [];
        if (is_array($pendenciaEdicao)
            && isset($pendenciaEdicao['dados'])
            && is_array($pendenciaEdicao['dados'])) {
            $dadosFormularioAnterior = $pendenciaEdicao['dados'];
        } elseif (isset($_SESSION['acolhido_edicao_pendente'][$idAcolhido])) {
            unset($_SESSION['acolhido_edicao_pendente'][$idAcolhido]);
        }
        $temDadosFormularioAnterior = $dadosFormularioAnterior !== [];
        $valorPostAnterior = static function (string $campo, $padrao = '') use ($dadosFormularioAnterior) {
            if (!array_key_exists($campo, $dadosFormularioAnterior)) {
                return $padrao;
            }
            $valor = $dadosFormularioAnterior[$campo];
            return is_scalar($valor) ? (string)$valor : $padrao;
        };
        $aplicarCamposPostAnterior = static function (array $destino, array $mapeamento) use ($dadosFormularioAnterior): array {
            foreach ($mapeamento as $campoPost => $campoDestino) {
                if (array_key_exists($campoPost, $dadosFormularioAnterior) && is_scalar($dadosFormularioAnterior[$campoPost])) {
                    $destino[$campoDestino] = (string)$dadosFormularioAnterior[$campoPost];
                }
            }
            return $destino;
        };

        $acolhido = $aplicarCamposPostAnterior($acolhido, [
            'nome' => 'nome',
            'data_nascimento' => 'dataNascimento',
            'sexo' => 'sexo',
            'data_acolhimento' => 'dataAcolhimento',
            'origem_acolhimento' => 'origemAcolhimento',
            'nome_mae' => 'nome_mae',
            'nome_pai' => 'nome_pai',
            'plano_assistencial_funerario' => 'plano_assistencial_funerario',
            'endereco_referencia' => 'endereco_referencia',
            'endereco_observacao' => 'endereco_observacao',
            'info_saude' => 'info_saude',
            'info_nutri' => 'info_nutri',
            'motivo_institucionalizacao' => 'motivoInstitucionalizacao',
            'historico' => 'historico',
        ]);
        $cpfForm = $valorPostAnterior('cpf', $cpfForm);

        $endereco = $aplicarCamposPostAnterior(is_array($endereco) ? $endereco : [], [
            'endereco_cep' => 'cep',
            'endereco_rua' => 'rua',
            'endereco_numero' => 'numero',
            'endereco_cidade' => 'cidade',
            'endereco_estado' => 'estado',
        ]);

        $curadorNaoPossuiForm = $temDadosFormularioAnterior
            ? isset($dadosFormularioAnterior['curador_nao_possui'])
            : empty($curador);
        $curadorCpfForm = (string)\Abdm\Infraestrutura\Criptografia::descriptografar($curador['cpf'] ?? '');
        if ($temDadosFormularioAnterior) {
            if ($curadorNaoPossuiForm) {
                $curador = [];
                $enderecoCurador = [];
                $curadorCpfForm = '';
            } else {
                $curador = $aplicarCamposPostAnterior(is_array($curador) ? $curador : [], [
                    'curador_nome' => 'nome',
                    'curador_dataNascimento' => 'datanNascimento',
                    'curador_grauParentesco' => 'grauParentesco',
                    'curador_telefone' => 'telefone',
                    'curador_telefone_2' => 'telefone_2',
                ]);
                $enderecoCurador = $aplicarCamposPostAnterior(is_array($enderecoCurador) ? $enderecoCurador : [], [
                    'curador_endereco_cep' => 'cep',
                    'curador_endereco_rua' => 'rua',
                    'curador_endereco_numero' => 'numero',
                    'curador_endereco_cidade' => 'cidade',
                    'curador_endereco_estado' => 'estado',
                ]);
                $curadorCpfForm = $valorPostAnterior('curador_cpf');
            }
        }
        $maeNaoInformadoForm = $temDadosFormularioAnterior
            ? isset($dadosFormularioAnterior['mae_nao_informado'])
            : empty($acolhido['nome_mae']) && empty($acolhido['nomeMae']);
        $paiNaoInformadoForm = $temDadosFormularioAnterior
            ? isset($dadosFormularioAnterior['pai_nao_informado'])
            : empty($acolhido['nome_pai']) && empty($acolhido['nomePai']);
        $familiarNaoPossuiForm = $temDadosFormularioAnterior
            ? isset($dadosFormularioAnterior['familiar_nao_possui'])
            : count($familiares) === 0;

        if ($temDadosFormularioAnterior) {
            $dadosComplementares = is_array($dadosFormularioAnterior['dados_acolhido'] ?? null)
                ? $dadosFormularioAnterior['dados_acolhido']
                : [];
            $documentosPendentes = is_array($dadosComplementares['documentos'] ?? null)
                ? $dadosComplementares['documentos']
                : [];
            foreach ($documentosPendentes as &$documentoPendente) {
                if (is_array($documentoPendente) && isset($documentoPendente['numero'])) {
                    $documentoPendente['numero'] = \Abdm\Model\DadosAcolhido::acolhidoDocumentoDescriptografar($documentoPendente['numero']);
                }
            }
            unset($documentoPendente);
            $dadosComplementares['documentos'] = $documentosPendentes;
            $susPendente = $dadosComplementares['documentos']['sus']['numero'] ?? '';
            $municipalPendente = $dadosComplementares['documentos']['cartao_municipal']['numero'] ?? '';
            $acolhido['cartao_sus'] = \Abdm\Model\DadosAcolhido::acolhidoDocumentoDescriptografar($susPendente !== '' ? $susPendente : ($acolhido['cartao_sus'] ?? ''));
            $cartaoMunicipalForm = \Abdm\Model\DadosAcolhido::acolhidoDocumentoDescriptografar($municipalPendente !== '' ? $municipalPendente : ($acolhido['cartao_municipal_saude'] ?? ''));
            $cidsPostados = $dadosFormularioAnterior['cid'] ?? [];
            $cids = is_array($cidsPostados) ? array_values(array_filter($cidsPostados, 'is_array')) : [];
            foreach ($cids as &$cidPostado) {
                $cidPostado['codigo'] = is_scalar($cidPostado['codigo'] ?? null)
                    ? strtoupper(trim((string)$cidPostado['codigo']))
                    : '';
                $cidPostado['descricao'] = is_scalar($cidPostado['descricao'] ?? null)
                    ? (string)$cidPostado['descricao']
                    : '';
            }
            unset($cidPostado);

            $familiaresPostados = $dadosFormularioAnterior['familiar'] ?? [];
            $familiares = [];
            if (is_array($familiaresPostados)) {
                $camposFamiliar = ['nome', 'grauParentesco', 'dataNascimento', 'telefone', 'endereco_cep', 'endereco_rua', 'endereco_numero', 'endereco_cidade', 'endereco_estado'];
                foreach ($familiaresPostados as $familiarPostado) {
                    if (!is_array($familiarPostado)) continue;
                    $familiarLimpo = [];
                    foreach ($camposFamiliar as $campoFamiliar) {
                        $familiarLimpo[$campoFamiliar] = is_scalar($familiarPostado[$campoFamiliar] ?? null)
                            ? (string)$familiarPostado[$campoFamiliar]
                            : '';
                    }
                    $familiares[] = $familiarLimpo;
                }
            }

            $documentoMunicipalPostado = $dadosComplementares['documentos']['cartao_municipal'] ?? null;
            if (is_array($documentoMunicipalPostado)) {
                $cartaoMunicipalForm = is_scalar($documentoMunicipalPostado['numero'] ?? null)
                    ? (string)$documentoMunicipalPostado['numero']
                    : '';
                $naoPossuiCartaoMunicipalForm = ($documentoMunicipalPostado['situacao'] ?? '') === 'nao_possui';
            }
            $documentoSusPostado = $dadosComplementares['documentos']['sus'] ?? null;
            if (is_array($documentoSusPostado)) {
                $acolhido['cartao_sus'] = is_scalar($documentoSusPostado['numero'] ?? null)
                    ? (string)$documentoSusPostado['numero']
                    : '';
            }
        }

        return [
            'idAcolhido' => $idAcolhido ?? null,
            'acolhido' => $acolhido ?? null,
            'cpfBancoNaoRecuperavel' => $cpfBancoNaoRecuperavel ?? null,
            'cids' => $cids ?? null,
            'endereco' => $endereco ?? null,
            'familiares' => $familiares ?? null,
            'curador' => $curador ?? null,
            'enderecoCurador' => $enderecoCurador ?? null,
            'temDadosFormularioAnterior' => $temDadosFormularioAnterior ?? null,
            'curadorNaoPossuiForm' => $curadorNaoPossuiForm ?? null,
            'curadorCpfForm' => $curadorCpfForm ?? null,
            'maeNaoInformadoForm' => $maeNaoInformadoForm ?? null,
            'paiNaoInformadoForm' => $paiNaoInformadoForm ?? null,
            'familiarNaoPossuiForm' => $familiarNaoPossuiForm ?? null,
        ];
    }

    public function listarAtivos(): array
    {
        $pdo = $this->pdo;
        $acolhidos = (new \Abdm\Model\Acolhidos($pdo))->ativos();

        return [
            'acolhidos' => $acolhidos ?? null,
        ];
    }

    public function listarDesligados(): array
    {
        $pdo = $this->pdo;
        $acolhidos = (new \Abdm\Model\Acolhidos($pdo))->desligados();

        return [
            'acolhidos' => $acolhidos ?? null,
        ];
    }

    public function fichaParaImpressao(): array
    {
        $pdo = $this->pdo;
        // 2. Validação e busca de dados
        if (!isset($_GET['id']) || empty($_GET['id'])) {
            die("Parâmetro 'id' do acolhido não informado.");
        }
        $idAcolhido = (int)$_GET['id'];

        $acolhido = (new \Abdm\Model\Acolhidos($pdo))->ficha($idAcolhido);

        if (!$acolhido) {
            die("Acolhido não encontrado.");
        }

        $dadosComplementares = \Abdm\Model\DadosAcolhido::acolhidoDadosComplementaresParaEdicao($acolhido);
        $gruposComplementares = \Abdm\Model\DadosAcolhido::acolhidoDadosComplementaresGrupos($dadosComplementares);

        $cids = (new \Abdm\Model\Acolhidos($pdo))->cids($idAcolhido);

        $endereco = null;
        if (!empty($acolhido['Endereco_idEndereco'])) {
            $endereco = (new \Abdm\Model\Acolhidos($pdo))->endereco($acolhido['Endereco_idEndereco']);
        }

        $familiares = (new \Abdm\Model\Acolhidos($pdo))->familiares($idAcolhido);

        $curador = null;
        $enderecoCurador = null;
        if (!empty($acolhido['curador_id'])) {
            $curador = (new \Abdm\Model\Acolhidos($pdo))->curador($acolhido['curador_id']);

            if($curador && !empty($curador['idEndereco'])) {
                $enderecoCurador = (new \Abdm\Model\Acolhidos($pdo))->endereco($curador['idEndereco']);
            }
        }

        // 3. Montar o HTML do conteúdo para o PDF

        return [
            'acolhido' => $acolhido ?? null,
            'gruposComplementares' => $gruposComplementares ?? null,
            'cids' => $cids ?? null,
            'endereco' => $endereco ?? null,
            'curador' => $curador ?? null,
            'enderecoCurador' => $enderecoCurador ?? null,
        ];
    }
}

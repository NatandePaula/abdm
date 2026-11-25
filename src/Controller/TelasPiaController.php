<?php

namespace Abdm\Controller;

use PDO;

final class TelasPiaController
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    private function secoesAtivas(\Abdm\Model\Pia $pia): array
    {
        $ativas = [];
        foreach ($pia->piaSecoesConfiguradas() as $secao) {
            $ativas[(string)$secao['chave']] = (int)($secao['ativo'] ?? 1) === 1;
        }
        return $ativas;
    }

    public function configuracaoImpressao(): array
    {
        $pia = new \Abdm\Model\Pia($this->pdo);
        return [
            'configuracaoServico' => $pia->piaConfiguracaoServico(),
            'visibilidadeCamposPia' => $pia->piaVisibilidadeCampos(),
            'secoesAtivas' => $this->secoesAtivas($pia),
        ];
    }

    public function modeloEmBranco(): array
    {
        $consultas = new \Abdm\Model\ConsultasPia($this->pdo);
        $chavesExclusivas = ['cartao_sus', 'documentos', 'beneficios', 'composicao_familiar', 'cid10', 'info_saude', 'info_nutri', 'restricao_alimentar', 'qual_restricao', 'doencas_cronicas', 'comorbidades', 'motivo_acolhimento', 'historico'];
        return [
            'titulosBranco' => (new \Abdm\Model\Pia($this->pdo))->piaSecoes(),
            'camposBranco' => $consultas->camposDoModelo(),
            'camposCadastroBranco' => array_values(array_filter(
                $consultas->camposCadastraisDoModelo(),
                static fn (array $campo): bool => !in_array((string)$campo['chave'], $chavesExclusivas, true)
                    && !str_starts_with((string)$campo['chave'], 'pia_')
            )),
        ];
    }

    public function plano(): array
    {
        $pdo = $this->pdo;
        $h = [\Abdm\Model\Pia::class, 'piaEscaparHtml'];
        $servicoPia = new \Abdm\Model\Pia($pdo);
        $configuracaoServico = $servicoPia->piaConfiguracaoServico();
        $secoes = $servicoPia->piaSecoes();
        $camposAtivos = (new \Abdm\Model\ConsultasPia($pdo))->camposAtivos();
        $visibilidadeCamposPia = $servicoPia->piaVisibilidadeCampos();
        $campoPiaVisivel = static fn(string $chave): bool => !array_key_exists($chave, $visibilidadeCamposPia) || (int)$visibilidadeCamposPia[$chave] === 1;
        $chavesExclusivasCadastro = ['cartao_sus', 'documentos', 'info_saude', 'info_nutri', 'restricao_alimentar', 'qual_restricao', 'doencas_cronicas', 'comorbidades', 'motivo_acolhimento', 'historico'];
        $camposCadastroResumo = array_values(array_filter(
            (new \Abdm\Model\ConsultasPia($pdo))->camposCadastraisAtivos(),
            static fn(array $campo): bool => !in_array((string)$campo['chave'], $chavesExclusivasCadastro, true) && !str_starts_with((string)$campo['chave'], 'pia_')
        ));
        $camposCadastroPorSecao = [];
        foreach ($camposCadastroResumo as $campoCadastroResumo) {
            $camposCadastroPorSecao[(string)$campoCadastroResumo['secao']][] = $campoCadastroResumo;
        }
        $camposPlanejamento = array_values(array_filter($camposAtivos, static fn(array $campo): bool => ($campo['origem'] ?? 'cadastro') === 'planejamento' && !in_array((string)$campo['chave'], ['restricao_alimentar', 'qual_restricao', 'doencas_cronicas', 'comorbidades', 'motivo_acolhimento', 'historico'], true)));
        $camposPorSecao = [];
        foreach ($camposPlanejamento as $campo) { $camposPorSecao[$campo['secao']][] = $campo; }
        $editandoId = filter_input(INPUT_GET, 'editar', FILTER_VALIDATE_INT) ?: 0;
        $novo = ($_GET['novo'] ?? '') === '1';
        $registro = null;
        $dados = [];
        $estruturaRegistro = [];
        $assinantesSalvos = [];
        $acolhidoSelecionado = (int)($_GET['acolhido'] ?? 0);
        $dataAtualizacaoForm = date('Y-m-d');
        $cadastroDados = [];
        $cadastroFormRetentativa = [];
        $enderecoFormRetentativa = [];
        $acolhidoCompleto = null;

        // Links antigos para "novo PIA" passam a abrir o PIA já existente daquele acolhido.
        if ($novo && $acolhidoSelecionado > 0) {
            $stmtPiaExistente = (new \Abdm\Model\ConsultasPia($pdo))->idMaisRecente($acolhidoSelecionado);
            $piaExistenteId = (int)$stmtPiaExistente;
            if ($piaExistenteId > 0) {
                $editandoId = $piaExistenteId;
                $novo = false;
            }
        }

        if ($editandoId > 0) {
            $registro = (new \Abdm\Model\ConsultasPia($pdo))->fichaDeAcolhidoAtivo($editandoId);
            if (!$registro) {
                http_response_code(404);
                exit('PIA não encontrado.');
            }
            $acolhidoSelecionado = (int)$registro['acolhido_id'];
            $dados = \Abdm\Model\Pia::piaDadosJsonParaLista($registro['dados_json'] ?? null);
            $acolhidoCompleto = $registro;
            $cadastroDados = \Abdm\Model\DadosAcolhido::acolhidoDadosComplementaresParaEdicao(array_replace($registro, ['cpf' => $registro['acolhido_cpf'] ?? '']));
            foreach (\Abdm\Model\Pia::piaDadosJsonParaLista($registro['estrutura_json'] ?? null) as $campoSalvo) {
                if (is_array($campoSalvo) && is_string($campoSalvo['chave'] ?? null)) {
                    $estruturaRegistro[$campoSalvo['chave']] = $campoSalvo;
                }
            }
            $dataAtualizacaoForm = $registro['dataAtualizacao'] ?: date('Y-m-d');
            $stmtAssinantes = (new \Abdm\Model\ConsultasPia($pdo))->assinantesAtuais($editandoId);
            foreach ($stmtAssinantes as $assinante) {
                $assinantesSalvos[] = ['usuario_id' => (int)$assinante['usuario_idUsuario'], 'modo' => $assinante['modo'], 'nome' => $assinante['nome'], 'ativo' => (int)($assinante['ativo'] ?? 0)];
            }
        } elseif ($novo && $acolhidoSelecionado > 0) {
            $acolhidoBase = (new \Abdm\Model\Acolhidos($pdo))->fichaAtiva($acolhidoSelecionado);
            if (!$acolhidoBase) {
                $acolhidoSelecionado = 0;
            } else {
                $dados['data_nascimento'] = $acolhidoBase['dataNascimento'] ?? '';
                $acolhidoCompleto = $acolhidoBase;
                $cadastroDados = array_replace_recursive(\Abdm\Model\Pia::piaDadosJsonParaLista($acolhidoBase['dados_cadastrais_json'] ?? null), \Abdm\Model\DadosAcolhido::acolhidoDadosComplementaresParaEdicao($acolhidoBase));
            }
        }

        $retentativa = $_SESSION['pia_form_retentativa'] ?? null;
        unset($_SESSION['pia_form_retentativa']);
        if (is_array($retentativa) && (($editandoId > 0 && (int)($retentativa['pia_id'] ?? 0) === $editandoId)
            || ($editandoId === 0 && (int)($retentativa['acolhido_id'] ?? 0) === $acolhidoSelecionado))) {
            $dados = is_array($retentativa['dados'] ?? null) ? $retentativa['dados'] : $dados;
            $cadastroFormRetentativa = is_array($retentativa['cadastro'] ?? null) ? $retentativa['cadastro'] : [];
            $enderecoFormRetentativa = is_array($retentativa['endereco'] ?? null) ? $retentativa['endereco'] : [];
            if (is_array($retentativa['dados_cadastrais'] ?? null)) $cadastroDados = array_replace_recursive($cadastroDados, $retentativa['dados_cadastrais']);
            $assinantesSalvos = is_array($retentativa['assinantes'] ?? null) ? $retentativa['assinantes'] : $assinantesSalvos;
            $dataAtualizacaoForm = is_string($retentativa['data_atualizacao'] ?? null) ? $retentativa['data_atualizacao'] : $dataAtualizacaoForm;
        }

        foreach ($camposCadastroResumo as $campoCadastro) {
            $chaveCadastro = (string)$campoCadastro['chave'];
            if (!array_key_exists($chaveCadastro, $cadastroDados) && array_key_exists($chaveCadastro, $dados)) {
                $cadastroDados[$chaveCadastro] = $dados[$chaveCadastro];
            }
        }
        $enderecoCadastro = null;
        $familiaresCadastro = [];
        $cidsCadastro = [];
        $curadorCadastro = null;
        if (!empty($acolhidoCompleto['Endereco_idEndereco'])) {
            $enderecoCadastro = (new \Abdm\Model\ConsultasPia($pdo))->enderecoCadastro((int)$acolhidoCompleto['Endereco_idEndereco']) ?: null;
        }
        if ($acolhidoSelecionado > 0) {
            $familiaresCadastro = (new \Abdm\Model\ConsultasPia($pdo))->familiaresCadastro($acolhidoSelecionado);
            $cidsCadastro = (new \Abdm\Model\ConsultasPia($pdo))->cidsCadastro($acolhidoSelecionado);
            if (!empty($acolhidoCompleto['curador_id'])) {
                $curadorCadastro = (new \Abdm\Model\ConsultasPia($pdo))->curadorCadastro((int)$acolhidoCompleto['curador_id']) ?: null;
            }
        }

        $stmtUsuarios = (new \Abdm\Model\ConsultasPia($pdo))->assinantesDisponiveis();
        $usuariosAssinantes = $stmtUsuarios;
        $perfisAssinantesJson = json_encode(array_reduce($usuariosAssinantes, static function (array $perfis, array $usuario): array {
            $perfis[(string)$usuario['idUsuario']] = [
                'atuacao' => trim((string)($usuario['atuacaoProfissional'] ?? '')),
                'registro' => trim((string)($usuario['registroProfissional'] ?? '')),
                'cargo' => trim((string)($usuario['cargoConselho'] ?? '')),
                'inicio' => (string)($usuario['inicioMandatoConselho'] ?? ''),
                'fim' => (string)($usuario['fimMandatoConselho'] ?? ''),
            ];
            return $perfis;
        }, []), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
        if ($perfisAssinantesJson === false) {
            $perfisAssinantesJson = '{}';
        }

        $acolhidos = (new \Abdm\Model\Acolhidos($pdo))->selecaoParaPia();
        $acolhidosPorId = [];
        foreach ($acolhidos as $acolhido) {
            $acolhidosPorId[(int)$acolhido['id']] = $acolhido;
        }

        $listaPias = [];
        if (!$novo && !$editandoId) {
            // Corrige cadastros antigos/importados que ainda não receberam o registro inicial de PIA.
            (new \Abdm\Model\ConsultasPia($pdo))->inicializarFaltantes();
            $listaPias = (new \Abdm\Model\ConsultasPia($pdo))->planosDosAtivos();
        }

        $flash = $_SESSION['flash_message'] ?? null;
        unset($_SESSION['flash_message']);
        $mostrarFormulario = $novo || $editandoId > 0;
        $usarEstilosImpressao = true;

        return [
            'h' => $h ?? null,
            'secoesAtivas' => $this->secoesAtivas($servicoPia),
            'configuracaoServico' => $configuracaoServico ?? null,
            'secoes' => $secoes ?? null,
            'campoPiaVisivel' => $campoPiaVisivel ?? null,
            'chave' => $chave ?? null,
            'camposCadastroResumo' => $camposCadastroResumo ?? null,
            'campo' => $campo ?? null,
            'camposPorSecao' => $camposPorSecao ?? null,
            'editandoId' => $editandoId ?? null,
            'dados' => $dados ?? null,
            'estruturaRegistro' => $estruturaRegistro ?? null,
            'assinantesSalvos' => $assinantesSalvos ?? null,
            'acolhidoSelecionado' => $acolhidoSelecionado ?? null,
            'dataAtualizacaoForm' => $dataAtualizacaoForm ?? null,
            'cadastroDados' => $cadastroDados ?? null,
            'acolhidoCompleto' => $acolhidoCompleto ?? null,
            'assinante' => $assinante ?? null,
            'campoCadastro' => $campoCadastro ?? null,
            'enderecoCadastro' => $enderecoCadastro ?? null,
            'familiaresCadastro' => $familiaresCadastro ?? null,
            'cidsCadastro' => $cidsCadastro ?? null,
            'curadorCadastro' => $curadorCadastro ?? null,
            'usuariosAssinantes' => $usuariosAssinantes ?? null,
            'perfisAssinantesJson' => $perfisAssinantesJson ?? null,
            'acolhidos' => $acolhidos ?? null,
            'acolhidosPorId' => $acolhidosPorId ?? null,
            'acolhido' => $acolhido ?? null,
            'listaPias' => $listaPias ?? null,
            'flash' => $flash ?? null,
            'mostrarFormulario' => $mostrarFormulario ?? null,
        ];
    }

    public function campos(): array
    {
        $pdo = $this->pdo;
        $servicoPia = new \Abdm\Model\Pia($pdo);
        $configuracaoServico = $servicoPia->piaConfiguracaoServico();
        $servicoConfiguracaoDisponivel = $servicoPia->piaServicoConfiguracaoDisponivel();
        $secoes = $servicoPia->piaSecoes();
        $secoesGerenciaveis = $servicoPia->piaSecoesConfiguradas();
        $ordemSecao = [];
        foreach ($secoesGerenciaveis as $secaoGerenciavel) $ordemSecao[(string)$secaoGerenciavel['chave']] = (int)$secaoGerenciavel['ordem'];
        $campos = (new \Abdm\Model\ConsultasPia($pdo))->camposGerenciaveis();
        $camposPorSecao = [];
        foreach ($campos as $campo) {
            $camposPorSecao[$campo['secao']][] = $campo;
        }
        $flash = $_SESSION['flash_message'] ?? null;
        unset($_SESSION['flash_message']);
        $nomesTipos = [
            'text' => 'Texto curto', 'textarea' => 'Texto longo', 'date' => 'Data', 'select' => 'Seleção única', 'multi' => 'Múltipla seleção',
            'documents' => 'Tabela de documentos', 'benefits' => 'Renda e benefícios', 'family' => 'Composição familiar',
            'network' => 'Rede socioassistencial', 'activities' => 'Plano de atividades',
        ];

        return [
            'titulosSecoes' => $servicoPia->piaSecoes(true),
            'configuracaoServico' => $configuracaoServico ?? null,
            'servicoConfiguracaoDisponivel' => $servicoConfiguracaoDisponivel ?? null,
            'secoes' => $secoes ?? null,
            'secoesGerenciaveis' => $secoesGerenciaveis ?? null,
            'ordemSecao' => $ordemSecao ?? null,
            'camposPorSecao' => $camposPorSecao ?? null,
            'campo' => $campo ?? null,
            'flash' => $flash ?? null,
            'nomesTipos' => $nomesTipos ?? null,
        ];
    }

    public function paraImpressao(callable $h, array $visibilidadeCamposPia): array
    {
        $pdo = $this->pdo;
        $servicoPia = new \Abdm\Model\Pia($pdo);
        $idPia = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if (!$idPia || $idPia < 1) {
            http_response_code(400);
            exit('PIA não informado.');
        }
        $pia = (new \Abdm\Model\ConsultasPia($pdo))->fichaParaImpressao((int)$idPia);
        if (!$pia || empty($pia['dados_json'])) {
            http_response_code(404);
            exit('PIA não encontrado.');
        }
        $dados = \Abdm\Model\Pia::piaDadosJsonParaLista($pia['dados_json']);
        $dadosCadastro = \Abdm\Model\DadosAcolhido::acolhidoDadosComplementaresParaEdicao($pia);
        $estrutura = \Abdm\Model\Pia::piaDadosJsonParaLista($pia['estrutura_json'] ?? null);
        $assinaturas = (new \Abdm\Model\ConsultasPia($pdo))->assinaturasParaImpressao((int)$idPia);
        $familiaresCadastro = (new \Abdm\Model\ConsultasPia($pdo))->familiaresParaImpressao((int)$pia['acolhido_id']);
        $cidsCadastro = (new \Abdm\Model\ConsultasPia($pdo))->cidsParaImpressao((int)$pia['acolhido_id']);
        $curadorCadastro = null;
        if (!empty($pia['curador_id'])) {
            $curadorCadastro = (new \Abdm\Model\ConsultasPia($pdo))->curadorParaImpressao((int)$pia['curador_id']) ?: null;
        }
        $camposPlanejamentoAtivos = (new \Abdm\Model\ConsultasPia($pdo))->camposDePlanejamento();
        $campoPiaVisivel = static fn(string $chave): bool => !array_key_exists($chave, $visibilidadeCamposPia) || (int)$visibilidadeCamposPia[$chave] === 1;
        $chavesEstrutura = [];
        foreach ($estrutura as $campoEstrutura) {
            if (is_array($campoEstrutura) && is_string($campoEstrutura['chave'] ?? null)) {
                $chavesEstrutura[$campoEstrutura['chave']] = true;
            }
        }
        foreach ($camposPlanejamentoAtivos as $campoPlanejamento) {
            if (!isset($chavesEstrutura[(string)$campoPlanejamento['chave']])) {
                $estrutura[] = $campoPlanejamento;
            }
        }
        $titulosSecoes = $servicoPia->piaSecoes();
        $camposPorSecao = [];
        foreach ($estrutura as $campo) {
            if (!is_array($campo) || !isset($campo['chave'], $campo['secao'], $campo['tipo']) || $campo['tipo'] === 'evolution') {
                continue;
            }
            if (array_key_exists((string)$campo['chave'], $visibilidadeCamposPia) && (int)$visibilidadeCamposPia[(string)$campo['chave']] !== 1) continue;
            $origemCampo = (string)($campo['origem'] ?? (in_array((string)$campo['secao'], ['procedimentos', 'plano'], true) ? 'planejamento' : 'cadastro'));
            if ($origemCampo === 'planejamento' && !in_array((string)$campo['chave'], ['restricao_alimentar', 'qual_restricao', 'doencas_cronicas', 'comorbidades', 'motivo_acolhimento', 'historico'], true)) {
                $campo['origem'] = 'planejamento';
                $camposPorSecao[(string)$campo['secao']][] = $campo;
            }
        }
        $chavesCadastroRepetidas = ['cartao_sus', 'documentos', 'info_saude', 'info_nutri', 'restricao_alimentar', 'qual_restricao', 'doencas_cronicas', 'comorbidades', 'motivo_acolhimento', 'historico'];
        $camposCadastro = array_values(array_filter(
            (new \Abdm\Model\ConsultasPia($pdo))->cadastroParaImpressao(),
            static fn(array $campo): bool => !in_array((string)$campo['chave'], $chavesCadastroRepetidas, true) && !str_starts_with((string)$campo['chave'], 'pia_')
        ));
        foreach ($camposCadastro as $campoCadastro) {
            if (!array_key_exists((string)$campoCadastro['chave'], $dadosCadastro) && array_key_exists((string)$campoCadastro['chave'], $dados)) {
                $dadosCadastro[(string)$campoCadastro['chave']] = $dados[(string)$campoCadastro['chave']];
            }
        }
        foreach ($camposPorSecao as &$camposDaSecao) {
            usort($camposDaSecao, static fn(array $a, array $b): int => ((int)($a['ordem'] ?? 0)) <=> ((int)($b['ordem'] ?? 0)));
        }
        unset($camposDaSecao);

        $renderCampo = static function (array $campo) use ($dados, $dadosCadastro, $pia, $familiaresCadastro, $cidsCadastro, $h): string {
            $chave = (string)$campo['chave'];
            $tipo = (string)$campo['tipo'];
            $rotulo = $chave === 'dst' ? 'Possui alguma IST?' : ($chave === 'qual_dst' ? 'Qual IST?' : (string)($campo['rotulo'] ?? ''));
            $valor = array_key_exists($chave, $dadosCadastro)
                ? $dadosCadastro[$chave]
                : ((($campo['origem'] ?? '') === 'cadastro') ? ''
                : (array_key_exists($chave, $dados) ? $dados[$chave] : \Abdm\Model\Pia::piaValorInicialCadastroCampo($chave, $pia, $dadosCadastro, $familiaresCadastro, $cidsCadastro)));
            if ($chave === 'cid10' && is_string($valor)) {
                $valor = strtoupper($valor);
            }
            $opcoes = isset($campo['opcoes']) && $campo['opcoes'] !== '' ? array_filter(array_map('trim', explode('|', (string)$campo['opcoes']))) : [];
            $celula = static fn($texto): string => nl2br($h($texto), false);
            if (in_array($tipo, ['text', 'date', 'textarea', 'select'], true)) {
                if ($tipo === 'date') {
                    $texto = \Abdm\Model\Pia::piaDataExibicao($valor);
                } elseif ($tipo === 'select') {
                    $texto = (string)$valor;
                } else {
                    $texto = is_string($valor) ? $valor : '';
                }
                if ($tipo === 'textarea' || strlen($texto) > 450 || strpos($texto, "\n") !== false || strpos($texto, "\r") !== false) {
                    return '<div class="data-field-block"><div class="data-field-label">' . $h($rotulo) . '</div><div class="data-field-value">' . $celula($texto) . '</div></div>';
                }
                return '<table class="data-field"><tr><th>' . $h($rotulo) . '</th><td>' . $celula($texto) . '</td></tr></table>';
            }
            if ($tipo === 'multi') {
                $marcadas = is_array($valor) ? $valor : [];
                $itens = [];
                foreach ($opcoes as $opcao) {
                    $itens[] = '<span class="check-item">(' . (in_array($opcao, $marcadas, true) ? 'X' : ' ') . ') ' . $h($opcao) . '</span>';
                }
                return '<div class="field-block"><strong>' . $h($rotulo) . ':</strong> ' . implode(' &nbsp; ', $itens) . '</div>';
            }
            if ($tipo === 'documents') {
                $nomes = ['certidao' => 'Certidão de nascimento e/ou casamento', 'rg' => 'RG', 'cpf' => 'CPF', 'titulo_eleitoral' => 'Título eleitoral', 'ctps' => 'CTPS', 'sus' => 'Cartão Nacional do SUS', 'cartao_municipal' => 'Cartão Municipal de Saúde', 'outros' => 'Outros documentos'];
                $titulosStatus = ['possui'=>'Possui','nao_possui'=>'Não possui','nao_sabe_informar'=>'Não sabe informar','nao_disponibilizado'=>'Possui, mas não está disponível no momento'];
                $html = '<table class="data-table"><thead><tr><th>Documento</th><th>Número</th><th>Situação</th></tr></thead><tbody>';
                foreach ($nomes as $item => $nome) {
                    $linha = is_array($valor[$item] ?? null) ? $valor[$item] : [];
                    $situacao = (string)($linha['situacao'] ?? '');
                    $numero = \Abdm\Model\DadosAcolhido::acolhidoDocumentoDescriptografar($linha['numero'] ?? '');
                    if ($item === 'cpf' && $numero === '') $numero = \Abdm\Infraestrutura\Criptografia::descriptografar($pia['cpf'] ?? '');
                    $html .= '<tr><td>' . $h($nome) . '</td><td>' . $h($situacao === 'possui' ? $numero : '—') . '</td><td>' . $h($titulosStatus[$situacao] ?? 'Não informado') . '</td></tr>';
                }
                return '<div class="special-label">' . $h($rotulo) . '</div>' . $html . '</tbody></table>';
            }
            if ($tipo === 'benefits') {
                $nomes = ['nenhum' => 'Não recebe nenhum tipo de benefício', 'aposentadoria' => 'Aposentadoria', 'pensao' => 'Pensão', 'bpc' => 'Benefício de Prestação Continuada – BPC/LOAS', 'programa_social' => 'Programa social ou transferência de renda', 'atividade' => 'Exerce atividade laborativa remunerada', 'outro' => 'Outro'];
                $marcadas = is_array($valor) ? $valor : [];
                $itens = [];
                foreach ($nomes as $codigo => $nome) {
                    $itens[] = '<div>(' . (in_array($codigo, $marcadas, true) ? 'X' : ' ') . ') ' . $h($nome) . '</div>';
                }
                return '<div class="special-label">' . $h($rotulo) . '</div><div class="benefits">' . implode('', $itens) . '</div>';
            }
            if ($tipo === 'family') {
                $familia = is_array($valor) ? $valor : [];
                $html = '<table class="data-table"><thead><tr><th>Nome</th><th>Parentesco</th><th>Ocupação</th><th>DN</th><th>Sexo</th></tr></thead><tbody>';
                foreach ($familia as $linha) {
                    if (!is_array($linha)) { continue; }
                    $html .= '<tr><td>' . $h($linha['nome'] ?? '') . '</td><td>' . $h($linha['parentesco'] ?? '') . '</td><td>' . $h($linha['ocupacao'] ?? '') . '</td><td>' . $h(\Abdm\Model\Pia::piaDataExibicao($linha['data_nascimento'] ?? '')) . '</td><td>' . $h($linha['sexo'] ?? '') . '</td></tr>';
                }
                if (!$familia) { $html .= '<tr><td colspan="5">&nbsp;</td></tr>'; }
                return '<div class="special-label">' . $h($rotulo) . '</div>' . $html . '</tbody></table>';
            }
            if ($tipo === 'network') {
                $nomes = ['conselho_tutelar' => 'Conselho Tutelar', 'cras' => 'Centro de Referência de Assistência Social – CRAS', 'creas' => 'Centro de Referência Especializado de Assistência Social – CREAS', 'pse' => 'Serviço da Proteção Social Especial – PSE – Alta Complexidade', 'transferencia_renda' => 'Programas de Transferência de Renda', 'protecao_basica' => 'Projetos, programas e serviços da Proteção Social Básica (SEMASDH)', 'ministerio_publico' => 'Ministério Público', 'apoio_sociofamiliar' => 'Outros serviços de apoio sociofamiliar', 'vara_justica' => 'Vara da Justiça', 'delegacias' => 'Delegacias comuns e especializadas', 'entidades_religiosas' => 'Entidades Religiosas', 'saude' => 'Serviços de Saúde', 'educacao' => 'Centros de educação', 'cultura' => 'Cultura'];
                $marcadas = is_array($valor) ? $valor : [];
                $itens = [];
                foreach ($nomes as $codigo => $nome) { $itens[] = '<div>(' . (in_array($codigo, $marcadas, true) ? 'X' : ' ') . ') ' . $h($nome) . ';</div>'; }
                return '<div class="special-label">' . $h($rotulo) . '</div><div class="network-list">' . implode('', $itens) . '</div>';
            }
            if ($tipo === 'activities') {
                $atividades = is_array($valor) ? $valor : [];
                $temas = ['convivencia' => 'Convivência familiar e comunitária', 'desenvolvimento' => 'Desenvolvimento do usuário', 'documentacao' => 'Documentação', 'esporte_cultura' => 'Esporte/cultura/lazer', 'geracao_renda' => 'Geração de renda', 'mobilizacao_rede' => 'Mobilização da rede', 'transferencia_renda' => 'Programa de transferência de renda', 'vinculos' => 'Relações de vínculos no serviço', 'saude' => 'Saúde', 'servicos' => 'Serviços/programas/projetos'];
                $html = '<div class="activity-flow"><div class="special-label">PLANO DE ATIVIDADES</div>';
                foreach ($temas as $tema => $nome) {
                    $textoAtividade = is_string($atividades[$tema] ?? null) ? $atividades[$tema] : '';
                    $valorAtividade = trim($textoAtividade) === '' ? '&nbsp;' : $celula($textoAtividade);
                    $html .= '<div class="data-field-block"><div class="data-field-label">' . $h($nome) . '</div><div class="data-field-value">' . $valorAtividade . '</div></div>';
                }
                return $html . '</div>';
            }
            return '';
        };

        $cabecalhoPath = ROOT_DIR . '/public/assets/imagens/cabecalho.jpg';
        $rodapePath = ROOT_DIR . '/public/assets/imagens/rodape.jpg';
        if (!is_file($cabecalhoPath) || !is_file($rodapePath)) {
            error_log('Os arquivos de cabeçalho ou rodapé para a impressão do PIA não foram encontrados.');
            http_response_code(500);
            exit('Não foi possível preparar o cabeçalho e o rodapé da impressão.');
        }

        return [
            'pia' => $pia ?? null,
            'dadosCadastro' => $dadosCadastro ?? null,
            'assinaturas' => $assinaturas ?? null,
            'familiaresCadastro' => $familiaresCadastro ?? null,
            'cidsCadastro' => $cidsCadastro ?? null,
            'curadorCadastro' => $curadorCadastro ?? null,
            'campoPiaVisivel' => $campoPiaVisivel ?? null,
            'titulosSecoes' => $titulosSecoes ?? null,
            'camposPorSecao' => $camposPorSecao ?? null,
            'campo' => $campo ?? null,
            'camposCadastro' => $camposCadastro ?? null,
            'campoCadastro' => $campoCadastro ?? null,
            'renderCampo' => $renderCampo ?? null,
            'cabecalhoPath' => $cabecalhoPath ?? null,
            'rodapePath' => $rodapePath ?? null,
        ];
    }
}

<?php

namespace Abdm\Controller;

use Abdm\Infraestrutura\ControleAcesso;
use Abdm\Model\DadosAcolhido;
use Abdm\Model\Pia;
use Abdm\Infraestrutura\Sessao;

use PDO;
use Throwable;
use InvalidArgumentException;

final class PiaController
{
    private readonly ControleAcesso $controleAcesso;

    public function __construct(private readonly PDO $pdo)
    {
        $this->controleAcesso = new ControleAcesso($pdo);
    }

    public function executar(): void
    {
        $pdo = $this->pdo;
        $servicoPia = new Pia($pdo);
        $this->controleAcesso->exigirUsuarioAutenticado();

        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            http_response_code(405);
            exit('Método não permitido.');
        }
        Sessao::exigirTokenCsrfValido();

        $acao = is_string($_POST['acao'] ?? null) ? $_POST['acao'] : '';
        $urlPia = BASE_URL . 'views/pia.php';
        $urlCampos = BASE_URL . 'views/gerenciar_pia_campos.php';
        $loginAtual = (int)($_SESSION['id_login'] ?? 0);

        $redirecionarCampo = static function () use ($urlCampos): void {
            header('Location: ' . $urlCampos);
            exit;
        };

        $definirFlash = static function (string $tipo, string $mensagem): void {
            $_SESSION['flash_message'] = ['type' => $tipo, 'message' => $mensagem];
        };

        $salvarFormularioRetentativa = static function (string $mensagem) use ($definirFlash, $urlPia): void {
            $_SESSION['pia_form_retentativa'] = [
                'pia_id' => (int)($_POST['pia_id'] ?? 0),
                'acolhido_id' => (int)($_POST['acolhido_id'] ?? 0),
                'data_atualizacao' => is_string($_POST['data_atualizacao'] ?? null) ? $_POST['data_atualizacao'] : date('Y-m-d'),
                'dados' => is_array($_POST['dados'] ?? null) ? $_POST['dados'] : [],
                'cadastro' => is_array($_POST['cadastro'] ?? null) ? $_POST['cadastro'] : [],
                'endereco' => is_array($_POST['endereco'] ?? null) ? $_POST['endereco'] : [],
                'dados_cadastrais' => is_array($_POST['dados_cadastrais'] ?? null) ? $_POST['dados_cadastrais'] : [],
                'assinantes' => is_array($_POST['assinantes'] ?? null) ? $_POST['assinantes'] : [],
            ];
            $definirFlash('warning', $mensagem);
            $id = (int)($_POST['pia_id'] ?? 0);
            $acolhido = (int)($_POST['acolhido_id'] ?? 0);
            $destino = $id > 0 ? ($urlPia . '?editar=' . $id) : ($urlPia . '?novo=1&acolhido=' . $acolhido);
            header('Location: ' . $destino);
            exit;
        };

        try {
            if ($acao === 'salvar_pia') {
                $this->controleAcesso->exigirPermissao('pia');
                $idPia = filter_var($_POST['pia_id'] ?? 0, FILTER_VALIDATE_INT);
                $idAcolhido = filter_var($_POST['acolhido_id'] ?? 0, FILTER_VALIDATE_INT);
                if ($idPia === false || $idPia < 0 || $idAcolhido === false || $idAcolhido < 1) {
                    throw new InvalidArgumentException('Selecione um acolhido válido.');
                }
                $stmtAcolhido = (new \Abdm\Model\PersistenciaPia($pdo))->prepararConsultaAcolhidoComIdPorId();
                $stmtAcolhido->execute([$idAcolhido]);
                if (!$stmtAcolhido->fetchColumn()) {
                    throw new InvalidArgumentException('O acolhido selecionado não foi encontrado.');
                }

                // O registro de PIA já é criado junto ao acolhido; gravações iniciadas por links antigos o atualizam.
                if ((int)$idPia === 0) {
                    $stmtPiaDoAcolhido = (new \Abdm\Model\PersistenciaPia($pdo))->prepararConsultaPiaComIdPIAPorAcolhidoIdUnica();
                    $stmtPiaDoAcolhido->execute([(int)$idAcolhido]);
                    $piaDoAcolhido = (int)$stmtPiaDoAcolhido->fetchColumn();
                    if ($piaDoAcolhido > 0) {
                        $idPia = $piaDoAcolhido;
                        $_POST['pia_id'] = $idPia;
                    }
                }

                $dataAtualizacao = $_POST['data_atualizacao'] ?? date('Y-m-d');
                if (!is_string($dataAtualizacao) || !Pia::piaDataValida($dataAtualizacao) || $dataAtualizacao > date('Y-m-d')) {
                    throw new InvalidArgumentException('Informe uma data de atualização válida até hoje.');
                }

                $stmtCampos = (new \Abdm\Model\PersistenciaPia($pdo))->consultarCampoPiaComIdCampoPorAtivo();
                $camposAtivos = $stmtCampos->fetchAll(PDO::FETCH_ASSOC);
                $dadosRecebidos = is_array($_POST['dados'] ?? null) ? $_POST['dados'] : [];
                $assinantesPost = is_array($_POST['assinantes'] ?? null) ? $_POST['assinantes'] : [];

                $dadosAnteriores = [];
                $estruturaAnterior = [];
                $assinantesAnteriores = [];
                if ($idPia > 0) {
                    $stmtExistente = (new \Abdm\Model\PersistenciaPia($pdo))->prepararConsultaPiaComAcolhidoIdPorIdPIA();
                    $stmtExistente->execute([$idPia]);
                    $piaExistente = $stmtExistente->fetch(PDO::FETCH_ASSOC);
                    if (!$piaExistente) {
                        throw new InvalidArgumentException('O PIA que você tentou editar não foi encontrado.');
                    }
                    $idAcolhido = (int)$piaExistente['acolhido_id'];
                    $dadosAnteriores = Pia::piaDadosJsonParaLista($piaExistente['dados_json'] ?? null);
                    foreach (Pia::piaDadosJsonParaLista($piaExistente['estrutura_json'] ?? null) as $campoAnterior) {
                        if (is_array($campoAnterior) && is_string($campoAnterior['chave'] ?? null)) {
                            $estruturaAnterior[$campoAnterior['chave']] = $campoAnterior;
                        }
                    }
                    $stmtAssinantesAntigos = (new \Abdm\Model\PersistenciaPia($pdo))->prepararConsultaAssinaturaPiaComUsuarioIdUsuarioPorPiaIdPIA();
                    $stmtAssinantesAntigos->execute([$idPia]);
                    $assinantesAnteriores = array_map('intval', $stmtAssinantesAntigos->fetchAll(PDO::FETCH_COLUMN));
                }

                $dados = $dadosAnteriores;
                $estrutura = $estruturaAnterior;
                foreach ($camposAtivos as $campo) {
                    $chave = (string)$campo['chave'];
                    $rotuloCampo = $chave === 'dst' ? 'Possui alguma IST?' : ($chave === 'qual_dst' ? 'Qual IST?' : (string)$campo['rotulo']);
                    $definicaoAnterior = $estrutura[$chave] ?? [];
                    $opcoes = Pia::piaOpcoesCampo($campo);
                    if (in_array((string)$campo['tipo'], ['select', 'multi'], true) && is_array($definicaoAnterior)) {
                        $opcoes = array_values(array_unique(array_merge($opcoes, Pia::piaOpcoesCampo($definicaoAnterior))));
                    }
                    $estrutura[$chave] = [
                        'chave' => $chave,
                        'secao' => (string)$campo['secao'],
                        'origem' => 'planejamento',
                        'rotulo' => $rotuloCampo,
                        'tipo' => (string)$campo['tipo'],
                        'opcoes' => in_array((string)$campo['tipo'], ['select', 'multi'], true) ? implode('|', $opcoes) : (string)($campo['opcoes'] ?? ''),
                        'ordem' => (int)$campo['ordem'],
                    ];
                    if (!array_key_exists($chave, $dadosRecebidos)) {
                        if (in_array((string)$campo['tipo'], ['multi', 'benefits', 'network', 'family'], true)) {
                            $dados[$chave] = [];
                        }
                        continue;
                    }
                    $valor = $dadosRecebidos[$chave];
                    $tipo = (string)$campo['tipo'];
                    if (in_array($tipo, ['text', 'textarea', 'date', 'select'], true)) {
                        if (!is_string($valor)) {
                            throw new InvalidArgumentException('Há um campo com valor inválido: ' . $campo['rotulo'] . '.');
                        }
                        $valor = trim($valor);
                        if (Pia::piaTextoTamanho($valor) > 20000) {
                            throw new InvalidArgumentException('O campo “' . $campo['rotulo'] . '” excede o tamanho permitido.');
                        }
                        if ($tipo === 'date' && $valor !== '' && !Pia::piaDataValida($valor)) {
                            throw new InvalidArgumentException('Informe uma data válida no campo “' . $campo['rotulo'] . '”.');
                        }
                        if ($tipo === 'select' && $valor !== '' && !in_array($valor, $opcoes, true)) {
                            throw new InvalidArgumentException('A opção escolhida para “' . $campo['rotulo'] . '” não é válida.');
                        }
                        $dados[$chave] = $valor;
                    } elseif ($tipo === 'multi') {
                        if (!is_array($valor)) {
                            throw new InvalidArgumentException('Há uma seleção inválida no campo “' . $campo['rotulo'] . '”.');
                        }
                        $selecionados = [];
                        foreach ($valor as $opcao) {
                            if (!is_string($opcao) || !in_array($opcao, $opcoes, true)) {
                                throw new InvalidArgumentException('Uma opção escolhida para “' . $campo['rotulo'] . '” não é válida.');
                            }
                            $selecionados[] = $opcao;
                        }
                        $dados[$chave] = array_values(array_unique($selecionados));
                    } elseif ($tipo === 'documents') {
                        $documentos = [];
                        $itensDocumento = ['certidao', 'rg', 'cpf', 'titulo_eleitoral', 'ctps', 'sus', 'cartao_municipal', 'outros'];
                        if (!is_array($valor)) {
                            throw new InvalidArgumentException('A situação documental está inválida.');
                        }
                        foreach ($itensDocumento as $item) {
                            $linha = $valor[$item] ?? [];
                            if (!is_array($linha)) {
                                continue;
                            }
                            $situacao = is_string($linha['situacao'] ?? null) ? $linha['situacao'] : '';
                            $numero = is_string($linha['numero'] ?? null) ? trim($linha['numero']) : '';
                            if ($situacao !== '' && !in_array($situacao, ['possui', 'nao_possui', 'nao_sabe_informar', 'nao_disponibilizado'], true)) {
                                throw new InvalidArgumentException('Há uma situação documental inválida.');
                            }
                            $documentos[$item] = [
                                'situacao' => $situacao,
                                'numero' => $situacao === 'possui'
                                    ? (DadosAcolhido::acolhidoDocumentoCriptografar(Pia::piaTextoRecortar($numero, 100)) ?? '')
                                    : '',
                            ];
                        }
                        $dados[$chave] = $documentos;
                    } elseif ($tipo === 'benefits') {
                        $permitidos = ['nenhum', 'aposentadoria', 'pensao', 'bpc', 'programa_social', 'atividade', 'outro'];
                        if (!is_array($valor)) {
                            throw new InvalidArgumentException('A seleção de renda e benefícios está inválida.');
                        }
                        $dados[$chave] = array_values(array_unique(array_filter($valor, static fn($item): bool => is_string($item) && in_array($item, $permitidos, true))));
                    } elseif ($tipo === 'family') {
                        if (!is_array($valor)) {
                            throw new InvalidArgumentException('Há linhas inválidas na tabela “' . $campo['rotulo'] . '”.');
                        }
                        $linhas = [];
                        foreach (array_slice($valor, 0, 100) as $linha) {
                            if (!is_array($linha)) {
                                continue;
                            }
                            $normalizada = [];
                            $chavesPermitidas = ['nome', 'parentesco', 'ocupacao', 'data_nascimento', 'sexo'];
                            foreach ($linha as $nome => $conteudo) {
                                if (!is_string($nome) || !in_array($nome, $chavesPermitidas, true) || !is_string($conteudo)) {
                                    continue;
                                }
                                $normalizada[$nome] = Pia::piaTextoRecortar(trim($conteudo), 20000);
                            }
                            if (!empty($normalizada['data_nascimento']) && !Pia::piaDataValida($normalizada['data_nascimento'])) {
                                throw new InvalidArgumentException('Informe uma data de nascimento válida na composição familiar.');
                            }
                            if (array_filter($normalizada, static fn(string $texto): bool => $texto !== '')) {
                                $linhas[] = $normalizada;
                            }
                        }
                        $dados[$chave] = $linhas;
                    } elseif ($tipo === 'network') {
                        if (!is_array($valor)) {
                            throw new InvalidArgumentException('A seleção da rede socioassistencial está inválida.');
                        }
                        $permitidos = ['conselho_tutelar', 'cras', 'creas', 'pse', 'transferencia_renda', 'protecao_basica', 'ministerio_publico', 'apoio_sociofamiliar', 'vara_justica', 'delegacias', 'entidades_religiosas', 'saude', 'educacao', 'cultura'];
                        $dados[$chave] = array_values(array_unique(array_filter($valor, static fn($item): bool => is_string($item) && in_array($item, $permitidos, true))));
                    } elseif ($tipo === 'activities') {
                        if (!is_array($valor)) {
                            throw new InvalidArgumentException('O plano de atividades está inválido.');
                        }
                        $permitidos = ['convivencia', 'desenvolvimento', 'documentacao', 'esporte_cultura', 'geracao_renda', 'mobilizacao_rede', 'transferencia_renda', 'vinculos', 'saude', 'servicos'];
                        $atividades = [];
                        foreach ($permitidos as $item) {
                            $texto = $valor[$item] ?? '';
                            $atividades[$item] = is_string($texto) ? Pia::piaTextoRecortar(trim($texto), 20000) : '';
                        }
                        $dados[$chave] = $atividades;
                    }
                }

                $assinantes = [];
                foreach (array_slice($assinantesPost, 0, 20) as $assinante) {
                    if (!is_array($assinante)) {
                        continue;
                    }
                    $idUsuario = filter_var($assinante['usuario_id'] ?? null, FILTER_VALIDATE_INT);
                    $modo = is_string($assinante['modo'] ?? null) ? $assinante['modo'] : '';
                    if (!$idUsuario || !in_array($modo, ['nome', 'profissional', 'conselho', 'ambos'], true)) {
                        continue;
                    }
                    if (isset($assinantes[$idUsuario])) {
                        throw new InvalidArgumentException('Cada responsável pelas assinaturas deve ser selecionado apenas uma vez.');
                    }
                    $stmtUsuario = (new \Abdm\Model\PersistenciaPia($pdo))->prepararConsultaPerfilPorIdUsuario();
                    $stmtUsuario->execute([(int)$idUsuario]);
                    $usuario = $stmtUsuario->fetch(PDO::FETCH_ASSOC);
                    if (!$usuario) {
                        throw new InvalidArgumentException('Um dos responsáveis selecionados não está disponível.');
                    }
                    $atuacao = trim((string)($usuario['atuacaoProfissional'] ?? ''));
                    $cargo = trim((string)($usuario['cargoConselho'] ?? ''));
                    if (($modo === 'profissional' || $modo === 'ambos') && $atuacao === '') {
                        throw new InvalidArgumentException('Cadastre a atuação profissional de ' . $usuario['nome'] . ' antes de selecioná-la para assinatura.');
                    }
                    if (($modo === 'conselho' || $modo === 'ambos') && $cargo === '') {
                        throw new InvalidArgumentException('Cadastre o cargo no conselho de ' . $usuario['nome'] . ' antes de selecioná-lo para assinatura.');
                    }
                    $assinantes[(int)$idUsuario] = [
                        'id' => (int)$idUsuario,
                        'modo' => $modo,
                        'nome' => (string)$usuario['nome'],
                        'atuacao' => $atuacao,
                        'registro' => trim((string)($usuario['registroProfissional'] ?? '')),
                        'cargo' => $cargo,
                        'inicio' => $usuario['inicioMandatoConselho'] ?: null,
                        'fim' => $usuario['fimMandatoConselho'] ?: null,
                    ];
                }
                if (!$assinantes) {
                    throw new InvalidArgumentException('Adicione pelo menos um responsável pelas assinaturas do PIA.');
                }

                foreach ($estrutura as &$definicao) {
                    if (isset($definicao['ordem'])) {
                        $definicao['ordem'] = (int)$definicao['ordem'];
                    }
                }
                unset($definicao);
                $jsonDados = json_encode($dados, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
                $jsonEstrutura = json_encode(array_values($estrutura), JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
                if ($jsonDados === false || $jsonEstrutura === false) {
                    throw new InvalidArgumentException('Não foi possível preparar as informações do PIA. Revise o conteúdo e tente novamente.');
                }

                $pdo->beginTransaction();
                if ($idPia > 0) {
                    $stmtSalvar = (new \Abdm\Model\PersistenciaPia($pdo))->prepararAtualizacaoPiaDeDataAtualizacao();
                    $stmtSalvar->execute([$dataAtualizacao, $jsonDados, $jsonEstrutura, $loginAtual, (int)$idPia, (int)$idAcolhido]);
                    $idPiaSalvo = (int)$idPia;
                    (new \Abdm\Model\PersistenciaPia($pdo))->prepararExclusaoAssinaturaPia()->execute([$idPiaSalvo]);
                } else {
                    $stmtSalvar = (new \Abdm\Model\PersistenciaPia($pdo))->prepararInsercaoPia();
                    $stmtSalvar->execute([(int)$idAcolhido, $dataAtualizacao, $jsonDados, $jsonEstrutura, $loginAtual, $loginAtual]);
                    $idPiaSalvo = (int)$pdo->lastInsertId();
                }
                $stmtAssinatura = (new \Abdm\Model\PersistenciaPia($pdo))->prepararInsercaoAssinaturaPia();
                $ordemAssinante = 0;
                foreach ($assinantes as $assinante) {
                    $stmtAssinatura->execute([
                        $idPiaSalvo,
                        $assinante['id'],
                        $assinante['modo'],
                        $assinante['nome'],
                        $assinante['atuacao'] ?: null,
                        $assinante['registro'] ?: null,
                        $assinante['cargo'] ?: null,
                        $assinante['inicio'],
                        $assinante['fim'],
                        $ordemAssinante++,
                    ]);
                }
                $pdo->commit();
                unset($_SESSION['pia_form_retentativa']);
                $definirFlash('success', $idPia > 0 ? 'PIA atualizado com sucesso.' : 'PIA cadastrado com sucesso.');
                header('Location: ' . $urlPia . '?editar=' . $idPiaSalvo);
                exit;
            }

            if ($acao === 'salvar_configuracao_servico') {
                $this->controleAcesso->exigirPermissao('gerenciar_pia_campos');
                $nomeServico = is_string($_POST['nome'] ?? null) ? trim($_POST['nome']) : '';
                $enderecoServico = is_string($_POST['endereco'] ?? null) ? trim($_POST['endereco']) : '';
                $telefoneServico = is_string($_POST['telefone'] ?? null) ? trim($_POST['telefone']) : '';
                if ($nomeServico === '' || Pia::piaTextoTamanho($nomeServico) > 180) {
                    throw new InvalidArgumentException('Informe um nome de serviço com até 180 caracteres.');
                }
                if ($enderecoServico === '' || Pia::piaTextoTamanho($enderecoServico) > 255) {
                    throw new InvalidArgumentException('Informe um endereço com até 255 caracteres.');
                }
                if (Pia::piaTextoTamanho($telefoneServico) > 60) {
                    throw new InvalidArgumentException('O telefone deve ter no máximo 60 caracteres.');
                }
                if (!$servicoPia->piaServicoConfiguracaoDisponivel()) {
                    throw new InvalidArgumentException('As informações do serviço de acolhimento não estão disponíveis para edição nesta instalação.');
                }
                $stmtSalvarServico = (new \Abdm\Model\PersistenciaPia($pdo))->prepararInsercaoPiaServicoConfiguracao();
                $stmtSalvarServico->execute([
                    $nomeServico,
                    $enderecoServico,
                    $telefoneServico !== '' ? $telefoneServico : null,
                    $loginAtual > 0 ? $loginAtual : null,
                ]);
                $definirFlash('success', 'Informações do serviço de acolhimento atualizadas.');
                $redirecionarCampo();
            }

            if ($acao === 'criar_secao') {
                $this->controleAcesso->exigirPermissao('gerenciar_pia_campos');
                $titulo = is_string($_POST['titulo'] ?? null) ? trim($_POST['titulo']) : '';
                if ($titulo === '' || Pia::piaTextoTamanho($titulo) > 180) {
                    throw new InvalidArgumentException('Informe um título de seção com até 180 caracteres.');
                }
                $stmtOrdem = (new \Abdm\Model\PersistenciaPia($pdo))->consultarSecaoPia();
                $ordem = (int)$stmtOrdem->fetchColumn();
                $chave = 'personalizada_' . bin2hex(random_bytes(8));
                $stmtCriarSecao = (new \Abdm\Model\PersistenciaPia($pdo))->prepararInsercaoSecaoPia();
                $stmtCriarSecao->execute([$chave, $titulo, $ordem, $loginAtual]);
                $definirFlash('success', 'Seção personalizada criada. Agora você pode adicionar campos a ela.');
                $redirecionarCampo();
            }

            if ($acao === 'editar_secao') {
                $this->controleAcesso->exigirPermissao('gerenciar_pia_campos');
                $chave = is_string($_POST['chave'] ?? null) ? $_POST['chave'] : '';
                $titulo = is_string($_POST['titulo'] ?? null) ? trim($_POST['titulo']) : '';
                if ($chave === '' || $titulo === '' || Pia::piaTextoTamanho($titulo) > 180) throw new InvalidArgumentException('Informe um título válido para a seção.');
                $stmtSecao = (new \Abdm\Model\PersistenciaPia($pdo))->prepararConsultaSecaoPiaComFixaPorChave();
                $stmtSecao->execute([$chave]);
                $configuracaoSecao = $stmtSecao->fetch(PDO::FETCH_ASSOC);
                if (!$configuracaoSecao || (int)$configuracaoSecao['fixa'] === 1 || (int)$configuracaoSecao['ordem'] <= 50) throw new InvalidArgumentException('Somente seções personalizadas podem ter o título alterado.');
                (new \Abdm\Model\PersistenciaPia($pdo))->prepararAtualizacaoSecaoPiaDeTitulo()->execute([$titulo, $chave]);
                $definirFlash('success', 'Título da seção atualizado.');
                $redirecionarCampo();
            }

            if ($acao === 'alternar_secao') {
                $this->controleAcesso->exigirPermissao('gerenciar_pia_campos');
                $chave = is_string($_POST['chave'] ?? null) ? $_POST['chave'] : '';
                $stmtSecao = (new \Abdm\Model\PersistenciaPia($pdo))->prepararConsultaSecaoPiaComAtivoPorChave();
                $stmtSecao->execute([$chave]);
                $ativo = $stmtSecao->fetchColumn();
                if ($ativo === false) throw new InvalidArgumentException('A seção selecionada não existe.');
                (new \Abdm\Model\PersistenciaPia($pdo))->prepararAtualizacaoSecaoPiaDeAtivo()->execute([(int)$ativo === 1 ? 0 : 1, $chave]);
                $definirFlash('success', (int)$ativo === 1 ? 'Seção ocultada nos próximos PIAs.' : 'Seção reexibida nos próximos PIAs.');
                $redirecionarCampo();
            }

            if ($acao === 'excluir_secao') {
                $this->controleAcesso->exigirPermissao('gerenciar_pia_campos');
                $chave = is_string($_POST['chave'] ?? null) ? $_POST['chave'] : '';
                $pdo->beginTransaction();
                $stmtSecao = (new \Abdm\Model\PersistenciaPia($pdo))->prepararConsultaSecaoPiaComFixaPorChaveComBloqueio();
                $stmtSecao->execute([$chave]);
                $configuracaoSecao = $stmtSecao->fetch(PDO::FETCH_ASSOC);
                if (!$configuracaoSecao || (int)$configuracaoSecao['fixa'] === 1 || (int)$configuracaoSecao['ordem'] <= 50) throw new InvalidArgumentException('Seções 1 a 5 do modelo não podem ser excluídas.');
                (new \Abdm\Model\PersistenciaPia($pdo))->prepararExclusaoCampoPiaPorSecao()->execute([$chave]);
                (new \Abdm\Model\PersistenciaPia($pdo))->prepararExclusaoSecaoPia()->execute([$chave]);
                $pdo->commit();
                $definirFlash('success', 'Seção e campos associados excluídos permanentemente. PIAs já salvos permanecem preservados.');
                $redirecionarCampo();
            }

            if ($acao === 'criar_campo') {
                $this->controleAcesso->exigirPermissao('gerenciar_pia_campos');
                $secoes = $servicoPia->piaSecoes();
                $secao = is_string($_POST['secao'] ?? null) ? $_POST['secao'] : '';
                $origem = is_string($_POST['origem'] ?? null) ? $_POST['origem'] : '';
                $rotulo = is_string($_POST['rotulo'] ?? null) ? trim($_POST['rotulo']) : '';
                $tipo = is_string($_POST['tipo'] ?? null) ? $_POST['tipo'] : '';
                $opcoes = is_string($_POST['opcoes'] ?? null) ? trim($_POST['opcoes']) : '';
                if (!isset($secoes[$secao]) || !in_array($origem, ['cadastro', 'planejamento'], true) || $rotulo === '' || Pia::piaTextoTamanho($rotulo) > 180) {
                    throw new InvalidArgumentException('Informe a seção e o título do novo campo.');
                }
                $configuracaoSecoes = [];
                foreach ($servicoPia->piaSecoesConfiguradas() as $itemSecao) $configuracaoSecoes[(string)$itemSecao['chave']] = $itemSecao;
                if ((int)($configuracaoSecoes[$secao]['ordem'] ?? 0) < 50) {
                    throw new InvalidArgumentException('Novos campos só podem ser criados a partir da seção 5.');
                }
                // A seção 2 espelha o cadastro; as demais seções são preenchidas no próprio PIA.
                $origem = $secao === 'acolhido' ? 'cadastro' : 'planejamento';
                if (!in_array($tipo, ['text', 'textarea', 'date', 'select', 'multi'], true)) {
                    throw new InvalidArgumentException('Selecione um tipo de campo válido.');
                }
                $opcoesFinal = null;
                if (in_array($tipo, ['select', 'multi'], true)) {
                    $listaOpcoes = array_values(array_unique(array_filter(array_map('trim', preg_split('/\r\n|\r|\n|\|/', $opcoes) ?: []), static fn(string $opcao): bool => $opcao !== '')));
                    if (count($listaOpcoes) < 2 || count($listaOpcoes) > 40) {
                        throw new InvalidArgumentException('Informe de 2 a 40 opções para esse campo, uma por linha.');
                    }
                    foreach ($listaOpcoes as $opcao) {
                        if (Pia::piaTextoTamanho($opcao) > 120) {
                            throw new InvalidArgumentException('Cada opção deve ter no máximo 120 caracteres.');
                        }
                    }
                    $opcoesFinal = implode('|', $listaOpcoes);
                }
                $stmtOrdem = (new \Abdm\Model\PersistenciaPia($pdo))->prepararConsultaCampoPiaPorSecao();
                $stmtOrdem->execute([$secao]);
                $chave = 'personalizado_' . bin2hex(random_bytes(8));
                $stmtCriar = (new \Abdm\Model\PersistenciaPia($pdo))->prepararInsercaoCampoPia();
                $stmtCriar->execute([$chave, $secao, $origem, $rotulo, $tipo, $opcoesFinal, (int)$stmtOrdem->fetchColumn(), $loginAtual]);
                $definirFlash('success', $origem === 'cadastro' ? 'Campo adicionado ao cadastro do acolhido; ele também será exibido no PIA.' : 'Campo adicionado ao planejamento do PIA.');
                $redirecionarCampo();
            }

            if ($acao === 'editar_campo') {
                $this->controleAcesso->exigirPermissao('gerenciar_pia_campos');
                $idCampo = filter_var($_POST['id_campo'] ?? null, FILTER_VALIDATE_INT);
                $rotulo = is_string($_POST['rotulo'] ?? null) ? trim($_POST['rotulo']) : '';
                $opcoes = is_string($_POST['opcoes'] ?? null) ? trim($_POST['opcoes']) : '';
                if (!$idCampo || $rotulo === '' || Pia::piaTextoTamanho($rotulo) > 180) {
                    throw new InvalidArgumentException('Informe um título válido para o campo.');
                }
                $stmtTipo = (new \Abdm\Model\PersistenciaPia($pdo))->prepararConsultaCampoPiaComTipoPorIdCampo();
                $stmtTipo->execute([(int)$idCampo]);
                $campoExistente = $stmtTipo->fetch(PDO::FETCH_ASSOC);
                if (!$campoExistente) {
                    throw new InvalidArgumentException('O campo selecionado não existe.');
                }
                $ordemSecaoCampo = 999;
                foreach ($servicoPia->piaSecoesConfiguradas() as $secaoConfiguradaCampo) {
                    if ((string)$secaoConfiguradaCampo['chave'] === (string)$campoExistente['secao']) {
                        $ordemSecaoCampo = (int)$secaoConfiguradaCampo['ordem'];
                        break;
                    }
                }
                if ($ordemSecaoCampo <= 50 && empty($campoExistente['criado_por_login'])) {
                    throw new InvalidArgumentException('Os campos originais das seções 1 a 5 podem ser ocultados, mas não reconfigurados.');
                }
                $tipo = $campoExistente['tipo'];
                $opcoesFinal = null;
                if (in_array($tipo, ['select', 'multi'], true)) {
                    $listaOpcoes = array_values(array_unique(array_filter(array_map('trim', preg_split('/\r\n|\r|\n|\|/', $opcoes) ?: []), static fn(string $opcao): bool => $opcao !== '')));
                    if (count($listaOpcoes) < 2 || count($listaOpcoes) > 40) {
                        throw new InvalidArgumentException('Informe de 2 a 40 opções para esse campo, uma por linha.');
                    }
                    $opcoesFinal = implode('|', $listaOpcoes);
                }
                $stmtAtualizar = (new \Abdm\Model\PersistenciaPia($pdo))->prepararAtualizacaoCampoPiaDeRotulo();
                $stmtAtualizar->execute([$rotulo, $opcoesFinal, (int)$idCampo]);
                $definirFlash('success', 'Campo atualizado. PIAs já salvos mantêm os títulos e opções registrados na versão deles.');
                $redirecionarCampo();
            }

            if ($acao === 'alternar_campo') {
                $this->controleAcesso->exigirPermissao('gerenciar_pia_campos');
                $idCampo = filter_var($_POST['id_campo'] ?? null, FILTER_VALIDATE_INT);
                if (!$idCampo) {
                    throw new InvalidArgumentException('Campo inválido.');
                }
                $stmtStatus = (new \Abdm\Model\PersistenciaPia($pdo))->prepararConsultaCampoPiaComAtivoPorIdCampo();
                $stmtStatus->execute([(int)$idCampo]);
                $campoStatus = $stmtStatus->fetch(PDO::FETCH_ASSOC);
                if (!$campoStatus) {
                    throw new InvalidArgumentException('O campo selecionado não existe.');
                }
                $atual = $campoStatus['ativo'];        $stmtAlternar = (new \Abdm\Model\PersistenciaPia($pdo))->prepararAtualizacaoCampoPiaDeAtivo();
                $stmtAlternar->execute([(int)$atual === 1 ? 0 : 1, (int)$idCampo]);
                $definirFlash('success', (int)$atual === 1 ? 'Campo ocultado dos próximos PIAs.' : 'Campo reexibido no formulário.');
                $redirecionarCampo();
            }

            if ($acao === 'excluir_campo') {
                $this->controleAcesso->exigirPermissao('gerenciar_pia_campos');
                $idCampo = filter_var($_POST['id_campo'] ?? null, FILTER_VALIDATE_INT);
                if (!$idCampo) throw new InvalidArgumentException('Campo inválido.');
                $stmtCampo = (new \Abdm\Model\PersistenciaPia($pdo))->prepararConsultaCampoPiaComSecaoPorIdCampo();
                $stmtCampo->execute([(int)$idCampo]);
                $campoExcluir = $stmtCampo->fetch(PDO::FETCH_ASSOC);
                if (!$campoExcluir) throw new InvalidArgumentException('O campo selecionado não existe.');
                $secaoCampo = (string)$campoExcluir['secao'];
                $secoesConfiguradas = [];
                foreach ($servicoPia->piaSecoesConfiguradas() as $secao) $secoesConfiguradas[(string)$secao['chave']] = $secao;
                $secaoConfigurada = $secoesConfiguradas[$secaoCampo] ?? null;
                if ($secaoConfigurada && ((int)$secaoConfigurada['ordem'] < 50 || ((int)$secaoConfigurada['ordem'] === 50 && empty($campoExcluir['criado_por_login'])))) {
                    throw new InvalidArgumentException('Campos das seções 1 a 5 podem ser ocultados, mas não excluídos.');
                }
                (new \Abdm\Model\PersistenciaPia($pdo))->prepararExclusaoCampoPiaPorId()->execute([(int)$idCampo]);
                $definirFlash('success', 'Campo excluído permanentemente. Os PIAs já salvos permanecem preservados.');
                $redirecionarCampo();
            }

            throw new InvalidArgumentException('Ação não reconhecida.');
        } catch (InvalidArgumentException $exception) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            if ($acao === 'salvar_pia') {
                $salvarFormularioRetentativa($exception->getMessage());
            }
            $definirFlash('warning', $exception->getMessage());
            if (in_array($acao, ['salvar_configuracao_servico', 'criar_campo', 'editar_campo', 'alternar_campo', 'excluir_campo', 'criar_secao', 'editar_secao', 'alternar_secao', 'excluir_secao'], true)) {
                $redirecionarCampo();
            }
            header('Location: ' . $urlPia);
            exit;
        } catch (Throwable $exception) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log('Falha ao salvar PIA. Código: ' . $exception->getCode());
            if ($acao === 'salvar_pia') {
                $salvarFormularioRetentativa('Não foi possível salvar o PIA. Os dados digitados foram preservados; tente novamente.');
            }
            $definirFlash('danger', 'Não foi possível concluir a operação solicitada.');
            if (in_array($acao, ['salvar_configuracao_servico', 'criar_campo', 'editar_campo', 'alternar_campo', 'excluir_campo', 'criar_secao', 'editar_secao', 'alternar_secao', 'excluir_secao'], true)) {
                $redirecionarCampo();
            }
            header('Location: ' . $urlPia);
            exit;
        }
    }
}

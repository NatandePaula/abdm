<?php

namespace Abdm\Controller;

use Abdm\Infraestrutura\ControleAcesso;
use Abdm\Model\RelatosDiarios;
use Abdm\Infraestrutura\Sessao;

use DateTimeImmutable;
use DateTimeZone;
use PDO;
use Throwable;
use InvalidArgumentException;

final class RelatorioController
{
    private readonly ControleAcesso $controleAcesso;

    public function __construct(private readonly PDO $pdo)
    {
        $this->controleAcesso = new ControleAcesso($pdo);
    }

    private static function redirecionarRelatorio(string $data, string $fragment): void
    {
        $url = BASE_URL . 'views/relatorio.php?' . http_build_query([
            'escopo' => 'dia',
            'data' => $data,
        ]) . '#' . rawurlencode($fragment);
        header('Location: ' . $url);
        exit;
    }

    private static function recuperarFormularioRelatorio($conteudo, $periodo, $dataRelato, $acolhidosPost): void
    {
        $_SESSION['relatorio_form_retentativa'] = [
            'periodo' => is_string($periodo) && in_array($periodo, ['dia', 'noite'], true) ? $periodo : 'dia',
            'data' => is_string($dataRelato) ? substr($dataRelato, 0, 10) : date('Y-m-d'),
            'conteudo' => is_string($conteudo) ? substr($conteudo, 0, 30000) : '',
            'acolhidos' => is_array($acolhidosPost)
                ? array_values(array_filter($acolhidosPost, static fn($id): bool => is_string($id) && ctype_digit($id)))
                : [],
        ];
    }

    private static function recuperarEdicaoRelato(int $idRelato, $conteudo, $periodo, $acolhidosPost): void
    {
        if ($idRelato < 1) {
            return;
        }
        if (!isset($_SESSION['relatorio_edicao_retentativa']) || !is_array($_SESSION['relatorio_edicao_retentativa'])) {
            $_SESSION['relatorio_edicao_retentativa'] = [];
        }
        $_SESSION['relatorio_edicao_retentativa'][$idRelato] = [
            'periodo' => is_string($periodo) && in_array($periodo, ['dia', 'noite'], true) ? $periodo : 'dia',
            'conteudo' => is_string($conteudo) ? substr($conteudo, 0, 30000) : '',
            'acolhidos' => is_array($acolhidosPost)
                ? array_values(array_filter($acolhidosPost, static fn($id): bool => is_string($id) && ctype_digit($id)))
                : [],
        ];
    }

    private static function validarConteudoRelato($conteudo): string
    {
        if (!is_string($conteudo) || preg_match('//u', $conteudo) !== 1) {
            throw new InvalidArgumentException('O texto do registro está inválido.');
        }
        $conteudo = trim($conteudo);
        $tamanhoConteudo = function_exists('mb_strlen') ? mb_strlen($conteudo, 'UTF-8') : strlen($conteudo);
        if ($tamanhoConteudo < 1 || $tamanhoConteudo > 10000) {
            throw new InvalidArgumentException('O registro deve ter entre 1 e 10.000 caracteres.');
        }
        return $conteudo;
    }

    private static function validarPeriodoRelato($periodo): string
    {
        if (!is_string($periodo) || !in_array($periodo, ['dia', 'noite'], true)) {
            throw new InvalidArgumentException('Selecione o turno do registro.');
        }
        return $periodo;
    }

    private static function validarDataReferenciaRelato($data): string
    {
        if (!is_string($data)) {
            throw new InvalidArgumentException('Informe uma data válida para a ocorrência.');
        }

        $dataObj = DateTimeImmutable::createFromFormat('!Y-m-d', $data);
        $erros = DateTimeImmutable::getLastErrors();
        if (!$dataObj || $dataObj->format('Y-m-d') !== $data
            || (is_array($erros) && ($erros['warning_count'] !== 0 || $erros['error_count'] !== 0))) {
            throw new InvalidArgumentException('Informe uma data válida para a ocorrência.');
        }
        if ($data > date('Y-m-d')) {
            throw new InvalidArgumentException('A data da ocorrência não pode ser futura.');
        }

        return $data;
    }

    private static function resolverMencoesRelato(PDO $pdo, string $conteudo, $acolhidosPost, array $mencoesExistentes = []): array
    {
        if (!is_array($acolhidosPost)) {
            throw new InvalidArgumentException('A lista de menções está inválida.');
        }

        $acolhidoIdsPostados = [];
        foreach ($acolhidosPost as $acolhidoPost) {
            if (!is_string($acolhidoPost) || !ctype_digit($acolhidoPost)) {
                throw new InvalidArgumentException('Uma menção selecionada é inválida.');
            }
            $acolhidoId = filter_var($acolhidoPost, FILTER_VALIDATE_INT);
            if ($acolhidoId === false || $acolhidoId < 1) {
                throw new InvalidArgumentException('Uma menção selecionada é inválida.');
            }
            $acolhidoIdsPostados[] = (int)$acolhidoId;
        }
        $acolhidoIdsPostados = array_values(array_unique($acolhidoIdsPostados));

        $mencoesExistentes = array_values(array_unique(array_filter(
            array_map('intval', $mencoesExistentes),
            static fn(int $id): bool => $id > 0
        )));
        $todosAcolhidos = (new \Abdm\Model\ConsultasRelatorio($pdo))->acolhidosParaMencoes($mencoesExistentes);
        $acolhidosPorNome = [];
        $idsEncontrados = [];
        foreach ($todosAcolhidos as $acolhido) {
            $id = (int)$acolhido['id'];
            $nome = trim((string)$acolhido['nome']);
            $idsEncontrados[$id] = true;
            $chaveNome = function_exists('mb_strtolower') ? mb_strtolower($nome, 'UTF-8') : strtolower($nome);
            $acolhidosPorNome[$chaveNome]['nome'] = $nome;
            $acolhidosPorNome[$chaveNome]['ids'][] = $id;
        }

        foreach ($acolhidoIdsPostados as $id) {
            if (!isset($idsEncontrados[$id])) {
                throw new InvalidArgumentException('Um acolhido selecionado não foi encontrado.');
            }
        }

        // O ID do autocomplete resolve nomes homônimos; menções únicas também
        // podem ser reconhecidas quando digitadas diretamente no texto.
        $acolhidoIdsMencionados = [];
        foreach ($acolhidosPorNome as $grupo) {
            $nome = $grupo['nome'];
            if ($nome === '' || preg_match('/(?<![\p{L}\p{N}_])@' . preg_quote($nome, '/') . '(?![\p{L}\p{N}_])/iu', $conteudo) !== 1) {
                continue;
            }

            $idsDoNome = array_values(array_unique($grupo['ids']));
            $selecionadosDoNome = array_values(array_intersect($acolhidoIdsPostados, $idsDoNome));
            if (count($idsDoNome) > 1 && !$selecionadosDoNome) {
                throw new InvalidArgumentException('O nome @' . $nome . ' pertence a mais de um acolhido. Selecione o correto nas sugestões.');
            }
            foreach ($selecionadosDoNome ?: $idsDoNome as $id) {
                $acolhidoIdsMencionados[] = (int)$id;
            }
        }
        return array_values(array_unique($acolhidoIdsMencionados));
    }

    private static function salvarMencoesRelato(PDO $pdo, int $idRelato, array $acolhidoIds): void
    {
        $stmtExcluir = (new \Abdm\Model\PersistenciaRelatorios($pdo))->prepararExclusaoMencao();
        $stmtExcluir->execute([$idRelato]);
        if (!$acolhidoIds) {
            return;
        }
        $stmtMencao = (new \Abdm\Model\PersistenciaRelatorios($pdo))->prepararInsercaoMencao();
        foreach ($acolhidoIds as $acolhidoId) {
            $stmtMencao->execute([$idRelato, $acolhidoId]);
        }
    }

    public function executar(): void
    {
        $pdo = $this->pdo;
        $this->controleAcesso->exigirPermissao('relatorio_diario');

        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            header('Location: ' . BASE_URL . 'views/relatorio.php');
            exit;
        }
        Sessao::exigirTokenCsrfValido();

        $acao = $_POST['acao'] ?? '';
        $periodo = $_POST['periodo'] ?? '';
        $dataRelato = $_POST['data_relato'] ?? date('Y-m-d');
        $conteudo = $_POST['conteudo'] ?? '';
        $acolhidosPost = $_POST['acolhidos'] ?? [];
        $idRelatoPost = isset($_POST['relato_id']) && is_string($_POST['relato_id']) && ctype_digit($_POST['relato_id'])
            ? (int)$_POST['relato_id']
            : 0;
        $idLoginAtual = (int)($_SESSION['id_login'] ?? 0);
        $dataRedirect = date('Y-m-d');
        $fragmentRedirect = 'novoRelato';

        try {
            if (!is_string($acao) || !in_array($acao, ['criar_relato', 'editar_relato'], true)) {
                throw new InvalidArgumentException('Ação de registro inválida.');
            }

            if ($acao === 'editar_relato') {
                if ($idRelatoPost < 1) {
                    throw new InvalidArgumentException('O registro selecionado é inválido.');
                }
                if (!(new RelatosDiarios($pdo))->edicaoRelatoDisponivel()) {
                    throw new InvalidArgumentException('A edição ainda não está habilitada porque a atualização do banco de dados está pendente.');
                }
                $fragmentRedirect = 'relato-' . $idRelatoPost;
                $pdo->beginTransaction();
                $stmtRelato = (new \Abdm\Model\PersistenciaRelatorios($pdo))->prepararConsultaRelatoComIdRelatoPorIdRelatoComBloqueio();
                $stmtRelato->execute([$idRelatoPost]);
                $relatoAtual = $stmtRelato->fetch(PDO::FETCH_ASSOC);
                if (!$relatoAtual) {
                    throw new InvalidArgumentException('A ocorrência selecionada não foi encontrada.');
                }
                $dataRedirect = (string)$relatoAtual['data_relato'];
                if ((int)$relatoAtual['autorLogin_idLogin'] !== $idLoginAtual) {
                    throw new InvalidArgumentException('Somente o responsável pelo registro pode editá-lo.');
                }
                $timezoneAplicacao = new DateTimeZone(date_default_timezone_get());
                $dataLancamento = DateTimeImmutable::createFromFormat('!Y-m-d H:i:s', (string)$relatoAtual['criado_em'], $timezoneAplicacao);
                $agora = new DateTimeImmutable('now', $timezoneAplicacao);
                $segundosDesdeLancamento = $dataLancamento instanceof DateTimeImmutable
                    ? (float)$agora->format('U.u') - (float)$dataLancamento->format('U.u')
                    : -1;
                if ($segundosDesdeLancamento < 0 || $segundosDesdeLancamento > 86400) {
                    throw new InvalidArgumentException('O prazo de 24 horas para editar esta ocorrência terminou.');
                }

                $periodoValidado = self::validarPeriodoRelato($periodo);
                $conteudoValidado = self::validarConteudoRelato($conteudo);
                $stmtMencoesExistentes = (new \Abdm\Model\PersistenciaRelatorios($pdo))->prepararConsultaMencaoComAcolhidoIdPorIdRelato();
                $stmtMencoesExistentes->execute([$idRelatoPost]);
                $mencoesExistentes = array_map('intval', $stmtMencoesExistentes->fetchAll(PDO::FETCH_COLUMN));
                $acolhidoIdsMencionados = self::resolverMencoesRelato($pdo, $conteudoValidado, $acolhidosPost, $mencoesExistentes);

                $stmtAtualizar = (new \Abdm\Model\PersistenciaRelatorios($pdo))->prepararAtualizacaoRelatoDePeriodo();
                $stmtAtualizar->execute([$periodoValidado, $conteudoValidado, date('Y-m-d H:i:s'), $idLoginAtual, $idRelatoPost, $idLoginAtual]);
                self::salvarMencoesRelato($pdo, $idRelatoPost, $acolhidoIdsMencionados);
                $pdo->commit();
                unset($_SESSION['relatorio_edicao_retentativa'][$idRelatoPost]);
                if (empty($_SESSION['relatorio_edicao_retentativa'])) {
                    unset($_SESSION['relatorio_edicao_retentativa']);
                }
                $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'Ocorrência atualizada. A data original de lançamento foi mantida.'];
            } else {
                $fragmentRedirect = 'novoRelato';
                $dataRelatoValidada = self::validarDataReferenciaRelato($dataRelato);
                $dataRedirect = $dataRelatoValidada;
                $periodoValidado = self::validarPeriodoRelato($periodo);
                $conteudoValidado = self::validarConteudoRelato($conteudo);
                $acolhidoIdsMencionados = self::resolverMencoesRelato($pdo, $conteudoValidado, $acolhidosPost);

                $pdo->beginTransaction();
                $stmt = (new \Abdm\Model\PersistenciaRelatorios($pdo))->prepararInsercaoRelato();
                $stmt->execute([
                    $dataRelatoValidada,
                    $periodoValidado,
                    $conteudoValidado,
                    $idLoginAtual,
                    date('Y-m-d H:i:s'),
                ]);
                $idRelatoPost = (int)$pdo->lastInsertId();
                $fragmentRedirect = 'relato-' . $idRelatoPost;
                self::salvarMencoesRelato($pdo, $idRelatoPost, $acolhidoIdsMencionados);
                $pdo->commit();
                unset($_SESSION['relatorio_form_retentativa']);
                $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'Ocorrência registrada no relatório.'];
            }
        } catch (InvalidArgumentException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            if ($acao === 'editar_relato') {
                self::recuperarEdicaoRelato($idRelatoPost, $conteudo, $periodo, $acolhidosPost);
            } else {
                self::recuperarFormularioRelatorio($conteudo, $periodo, $dataRelato, $acolhidosPost);
            }
            $_SESSION['flash_message'] = ['type' => 'danger', 'message' => $e->getMessage()];
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log('Falha na operação do relatório diário. Código: ' . $e->getCode());
            if ($acao === 'editar_relato') {
                self::recuperarEdicaoRelato($idRelatoPost, $conteudo, $periodo, $acolhidosPost);
            } else {
                self::recuperarFormularioRelatorio($conteudo, $periodo, $dataRelato, $acolhidosPost);
            }
            $_SESSION['flash_message'] = ['type' => 'danger', 'message' => $acao === 'editar_relato'
                ? 'Não foi possível editar a ocorrência. Tente novamente.'
                : 'Não foi possível registrar a ocorrência. Tente novamente.'];
        }

        if ($acao === 'editar_relato' && $idRelatoPost > 0 && $dataRedirect === date('Y-m-d')) {
            $stmtData = (new \Abdm\Model\PersistenciaRelatorios($pdo))->prepararConsultaRelatoComDataRelatoPorIdRelato();
            $stmtData->execute([$idRelatoPost]);
            $dataOriginal = $stmtData->fetchColumn();
            if (is_string($dataOriginal) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $dataOriginal)) {
                $dataRedirect = $dataOriginal;
            }
        }
        self::redirecionarRelatorio($dataRedirect, $fragmentRedirect);
    }
}

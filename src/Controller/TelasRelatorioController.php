<?php

namespace Abdm\Controller;

use PDO;
use DateTimeImmutable;
use DateTime;

final class TelasRelatorioController
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function diario(): array
    {
        $pdo = $this->pdo;
        $h = static fn($valor): string => htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');
        $dataHoje = date('Y-m-d');
        $escopo = $_GET['escopo'] ?? 'dia';
        if (!is_string($escopo) || !in_array($escopo, ['dia', 'periodo', 'todos'], true)) {
            $escopo = 'dia';
        }

        $dataInicial = $dataHoje;
        $dataFinal = $dataHoje;
        if ($escopo === 'dia') {
            $dataInformada = $_GET['data'] ?? $dataHoje;
            if (\Abdm\View\TextoRelatorio::relatorioDataFiltroValida($dataInformada, $dataHoje)) {
                $dataInicial = $dataInformada;
                $dataFinal = $dataInformada;
            } else {
                $_SESSION['flash_message'] = ['type' => 'warning', 'message' => 'Selecione uma data válida até hoje.'];
            }
        } elseif ($escopo === 'periodo') {
            $inicioInformado = $_GET['inicio'] ?? $dataHoje;
            $fimInformado = $_GET['fim'] ?? $dataHoje;
            if (\Abdm\View\TextoRelatorio::relatorioDataFiltroValida($inicioInformado, $dataHoje)
                && \Abdm\View\TextoRelatorio::relatorioDataFiltroValida($fimInformado, $dataHoje)
                && $inicioInformado <= $fimInformado
            ) {
                $dataInicial = $inicioInformado;
                $dataFinal = $fimInformado;
            } else {
                $escopo = 'dia';
                $_SESSION['flash_message'] = ['type' => 'warning', 'message' => 'Informe um intervalo válido, em ordem cronológica, até hoje.'];
            }
        }

        $acolhidos = (new \Abdm\Model\Acolhidos($pdo))->ativosParaRelatorio();
        $acolhidosPorId = [];
        foreach ($acolhidos as $acolhido) {
            $acolhidosPorId[(int)$acolhido['id']] = $acolhido;
        }

        $filtroAcolhidoId = null;
        $filtroAcolhidoPost = $_GET['acolhido'] ?? '';
        if (is_string($filtroAcolhidoPost) && $filtroAcolhidoPost !== '') {
            $idValidado = filter_var($filtroAcolhidoPost, FILTER_VALIDATE_INT);
            if ($idValidado !== false && $idValidado > 0 && isset($acolhidosPorId[(int)$idValidado])) {
                $filtroAcolhidoId = (int)$idValidado;
            } else {
                $_SESSION['flash_message'] = ['type' => 'warning', 'message' => 'O acolhido selecionado não foi encontrado.'];
            }
        }

        $termoBusca = $_GET['termo'] ?? '';
        if (!is_string($termoBusca)) {
            $termoBusca = '';
        }
        $termoBusca = trim($termoBusca);
        if (preg_match('//u', $termoBusca) !== 1) {
            $termoBusca = '';
            $_SESSION['flash_message'] = ['type' => 'warning', 'message' => 'O termo de pesquisa contém caracteres inválidos.'];
        }
        if (function_exists('mb_substr')) {
            $termoBusca = mb_substr($termoBusca, 0, 120, 'UTF-8');
        } else {
            $termoBusca = substr($termoBusca, 0, 120);
        }

        $suporteEdicaoRelatos = (new \Abdm\Model\RelatosDiarios($pdo))->edicaoRelatoDisponivel();
        $relatos = (new \Abdm\Model\ConsultasRelatorio($pdo))->consultar($escopo, $dataInicial, $dataFinal, $termoBusca, $filtroAcolhidoId, $suporteEdicaoRelatos);

        $mencoesPorRelato = [];
        if ($relatos) {
            $idsRelatos = array_map(static fn(array $relato): int => (int)$relato['idRelato'], $relatos);
            $stmtMencoes = (new \Abdm\Model\ConsultasRelatorio($pdo))->mencoesComIdentificador($idsRelatos);
            foreach ($stmtMencoes as $mencao) {
                $mencoesPorRelato[(int)$mencao['idRelato']][] = $mencao;
            }
        }

        $stmtAutorAtual = (new \Abdm\Model\ConsultasRelatorio($pdo))->nomeAutor((int)$_SESSION['id_login']);
        $nomeAutorAtual = (string)($stmtAutorAtual ?: ($_SESSION['username'] ?? ''));

        $formRetentativa = $_SESSION['relatorio_form_retentativa'] ?? null;
        unset($_SESSION['relatorio_form_retentativa']);
        $conteudoFormulario = is_array($formRetentativa) && is_string($formRetentativa['conteudo'] ?? null)
            ? $formRetentativa['conteudo']
            : '';
        $periodoFormulario = is_array($formRetentativa) && in_array($formRetentativa['periodo'] ?? '', ['dia', 'noite'], true)
            ? $formRetentativa['periodo']
            : 'dia';
        $dataFormulario = is_array($formRetentativa) && $dataFiltroValida($formRetentativa['data'] ?? null)
            ? $formRetentativa['data']
            : $dataHoje;
        $formCadastroAberto = is_array($formRetentativa);
        $acolhidosMarcados = is_array($formRetentativa) && is_array($formRetentativa['acolhidos'] ?? null)
            ? array_values(array_filter(array_map('intval', $formRetentativa['acolhidos']), static fn(int $id): bool => isset($acolhidosPorId[$id])))
            : [];
        $edicoesRetentativa = $_SESSION['relatorio_edicao_retentativa'] ?? [];
        unset($_SESSION['relatorio_edicao_retentativa']);
        if (!is_array($edicoesRetentativa)) {
            $edicoesRetentativa = [];
        }

        $relatosPorData = [];
        foreach ($relatos as $relato) {
            $relatosPorData[$relato['data_relato']][$relato['periodo']][] = $relato;
        }
        $rotuloPeriodo = $escopo === 'dia'
            ? 'Data: ' . (new DateTimeImmutable($dataInicial))->format('d/m/Y')
            : ($escopo === 'periodo'
                ? 'Período: ' . (new DateTimeImmutable($dataInicial))->format('d/m/Y') . ' a ' . (new DateTimeImmutable($dataFinal))->format('d/m/Y')
                : 'Todo o período');

        $dataBaseNavegacao = $escopo === 'dia' ? $dataInicial : $dataHoje;
        $parametrosDiaAnterior = [
            'escopo' => 'dia',
            'data' => (new DateTimeImmutable($dataBaseNavegacao))->modify('-1 day')->format('Y-m-d'),
        ];
        if ($termoBusca !== '') {
            $parametrosDiaAnterior['termo'] = $termoBusca;
        }
        if ($filtroAcolhidoId !== null) {
            $parametrosDiaAnterior['acolhido'] = (string)$filtroAcolhidoId;
        }
        $urlDiaAnterior = BASE_URL . 'views/relatorio.php?' . http_build_query($parametrosDiaAnterior);
        $mostrarProximoDia = $escopo === 'dia' && $dataInicial < $dataHoje;
        $urlProximoDia = '';
        if ($mostrarProximoDia) {
            $parametrosProximoDia = [
                'escopo' => 'dia',
                'data' => (new DateTimeImmutable($dataInicial))->modify('+1 day')->format('Y-m-d'),
            ];
            if ($termoBusca !== '') {
                $parametrosProximoDia['termo'] = $termoBusca;
            }
            if ($filtroAcolhidoId !== null) {
                $parametrosProximoDia['acolhido'] = (string)$filtroAcolhidoId;
            }
            $urlProximoDia = BASE_URL . 'views/relatorio.php?' . http_build_query($parametrosProximoDia);
        }
        $urlHoje = BASE_URL . 'views/relatorio.php?' . http_build_query(array_filter([
            'escopo' => 'dia',
            'data' => $dataHoje,
            'termo' => $termoBusca !== '' ? $termoBusca : null,
            'acolhido' => $filtroAcolhidoId !== null ? (string)$filtroAcolhidoId : null,
        ], static fn($valor): bool => $valor !== null));

        $dadosAcolhidosJson = json_encode(array_map(static function (array $acolhido): array {
            return [
                'id' => (int)$acolhido['id'],
                'nome' => (string)$acolhido['nome'],
                'status' => (string)$acolhido['status'],
            ];
        }, $acolhidos), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
        if ($dadosAcolhidosJson === false) {
            $dadosAcolhidosJson = '[]';
        }

        $usarEstilosImpressao = true;

        return [
            'h' => $h ?? null,
            'dataHoje' => $dataHoje ?? null,
            'escopo' => $escopo ?? null,
            'dataInicial' => $dataInicial ?? null,
            'dataFinal' => $dataFinal ?? null,
            'acolhidos' => $acolhidos ?? null,
            'acolhidosPorId' => $acolhidosPorId ?? null,
            'acolhido' => $acolhido ?? null,
            'filtroAcolhidoId' => $filtroAcolhidoId ?? null,
            'termoBusca' => $termoBusca ?? null,
            'suporteEdicaoRelatos' => $suporteEdicaoRelatos ?? null,
            'relatos' => $relatos ?? null,
            'mencoesPorRelato' => $mencoesPorRelato ?? null,
            'relato' => $relato ?? null,
            'mencao' => $mencao ?? null,
            'nomeAutorAtual' => $nomeAutorAtual ?? null,
            'conteudoFormulario' => $conteudoFormulario ?? null,
            'periodoFormulario' => $periodoFormulario ?? null,
            'dataFormulario' => $dataFormulario ?? null,
            'formCadastroAberto' => $formCadastroAberto ?? null,
            'acolhidosMarcados' => $acolhidosMarcados ?? null,
            'id' => $id ?? null,
            'edicoesRetentativa' => $edicoesRetentativa ?? null,
            'relatosPorData' => $relatosPorData ?? null,
            'rotuloPeriodo' => $rotuloPeriodo ?? null,
            'urlDiaAnterior' => $urlDiaAnterior ?? null,
            'mostrarProximoDia' => $mostrarProximoDia ?? null,
            'urlProximoDia' => $urlProximoDia ?? null,
            'urlHoje' => $urlHoje ?? null,
            'dadosAcolhidosJson' => $dadosAcolhidosJson ?? null,
        ];
    }

    public function paraImpressao(): array
    {
        $pdo = $this->pdo;
        $h = static fn($valor): string => htmlspecialchars((string)$valor, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $dataHoje = date('Y-m-d');
        $escopo = $_GET['escopo'] ?? 'dia';
        if (!is_string($escopo) || !in_array($escopo, ['dia', 'periodo', 'todos'], true)) {
            $escopo = 'dia';
        }

        $dataInicial = $dataHoje;
        $dataFinal = $dataHoje;
        if ($escopo === 'dia') {
            $data = $_GET['data'] ?? $dataHoje;
            if (\Abdm\View\TextoRelatorio::relatorioDataFiltroValida($data, $dataHoje)) {
                $dataInicial = $dataFinal = $data;
            }
        } elseif ($escopo === 'periodo') {
            $inicio = $_GET['inicio'] ?? $dataHoje;
            $fim = $_GET['fim'] ?? $dataHoje;
            if (\Abdm\View\TextoRelatorio::relatorioDataFiltroValida($inicio, $dataHoje)
                && \Abdm\View\TextoRelatorio::relatorioDataFiltroValida($fim, $dataHoje)
                && $inicio <= $fim
            ) {
                $dataInicial = $inicio;
                $dataFinal = $fim;
            } else {
                $escopo = 'dia';
            }
        }

        $termoBusca = $_GET['termo'] ?? '';
        $termoBusca = is_string($termoBusca) && preg_match('//u', $termoBusca) === 1 ? trim($termoBusca) : '';
        $termoBusca = function_exists('mb_substr') ? mb_substr($termoBusca, 0, 120, 'UTF-8') : substr($termoBusca, 0, 120);

        $acolhidoId = null;
        $acolhidoNome = '';
        $filtroAcolhido = $_GET['acolhido'] ?? '';
        if (is_string($filtroAcolhido) && $filtroAcolhido !== '' && filter_var($filtroAcolhido, FILTER_VALIDATE_INT) !== false) {
            $acolhido = (new \Abdm\Model\Acolhidos($pdo))->identificacaoAtivo((int)$filtroAcolhido);
            if ($acolhido) {
                $acolhidoId = (int)$acolhido['id'];
                $acolhidoNome = (string)$acolhido['nome'];
            }
        }

        $suporteEdicao = (new \Abdm\Model\RelatosDiarios($pdo))->edicaoRelatoDisponivel();
        $relatos = (new \Abdm\Model\ConsultasRelatorio($pdo))->consultar($escopo, $dataInicial, $dataFinal, $termoBusca, $acolhidoId, $suporteEdicao);

        $mencoesPorRelato = [];
        if ($relatos) {
            $ids = array_map(static fn(array $relato): int => (int)$relato['idRelato'], $relatos);
            $stmtMencoes = (new \Abdm\Model\ConsultasRelatorio($pdo))->mencoes($ids);
            foreach ($stmtMencoes as $mencao) {
                $mencoesPorRelato[(int)$mencao['idRelato']][] = (string)$mencao['nome'];
            }
        }

        $nomeAutor = (new DadosImpressaoController($pdo))->nomeUsuarioImpressaoAtual();

        $relatosPorData = [];
        foreach ($relatos as $relato) {
            $relatosPorData[$relato['data_relato']][$relato['periodo']][] = $relato;
        }
        $rotuloPeriodo = $escopo === 'dia'
            ? 'Data: ' . (new DateTimeImmutable($dataInicial))->format('d/m/Y')
            : ($escopo === 'periodo'
                ? 'Período: ' . (new DateTimeImmutable($dataInicial))->format('d/m/Y') . ' a ' . (new DateTimeImmutable($dataFinal))->format('d/m/Y')
                : 'Todo o período');

        return [
            'h' => $h ?? null,
            'termoBusca' => $termoBusca ?? null,
            'acolhidoId' => $acolhidoId ?? null,
            'acolhidoNome' => $acolhidoNome ?? null,
            'relatos' => $relatos ?? null,
            'mencoesPorRelato' => $mencoesPorRelato ?? null,
            'relato' => $relato ?? null,
            'mencao' => $mencao ?? null,
            'nomeAutor' => $nomeAutor ?? null,
            'relatosPorData' => $relatosPorData ?? null,
            'rotuloPeriodo' => $rotuloPeriodo ?? null,
        ];
    }
}

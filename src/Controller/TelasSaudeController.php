<?php

namespace Abdm\Controller;

use PDO;
use DateTimeImmutable;
use DateTime;

final class TelasSaudeController
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function registros(\Abdm\Infraestrutura\ControleAcesso $controleAcesso): array
    {
        $pdo = $this->pdo;
        $h = static fn($valor): string => htmlspecialchars((string)$valor, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $hoje = date('Y-m-d');
        $dataValida = static function ($valor) use ($hoje): bool {
            if (!is_string($valor)) return false;
            $data = DateTimeImmutable::createFromFormat('!Y-m-d', $valor);
            $erros = DateTimeImmutable::getLastErrors();
            return $data !== false && $data->format('Y-m-d') === $valor
                && (!is_array($erros) || ($erros['warning_count'] === 0 && $erros['error_count'] === 0))
                && $valor <= $hoje;
        };
        $tiposSaude = [
            'consulta' => 'Consulta médica',
            'exame' => 'Exame',
            'comportamento' => 'Alteração comportamental',
            'prescricao_criada' => 'Nova prescrição',
            'prescricao_alterada' => 'Prescrição alterada',
            'medicamento_suspenso' => 'Medicamento suspenso',
            'medicamento_reativado' => 'Medicamento reativado',
            'medicamento_finalizado' => 'Prescrição finalizada',
            'prescricao_finalizada_automaticamente' => 'Prescrição finalizada automaticamente',
            'prescricao_removida' => 'Prescrição removida',
        ];
        $acolhidos = (new \Abdm\Model\Acolhidos($pdo))->selecaoAtivos();
        $acolhidosPorId = [];
        foreach ($acolhidos as $acolhido) $acolhidosPorId[(int)$acolhido['id']] = $acolhido;

        $acolhidoEntrada = $_GET['acolhido'] ?? '';
        $acolhidoFiltro = filter_var($acolhidoEntrada, FILTER_VALIDATE_INT);
        $acolhidoFiltro = $acolhidoFiltro && isset($acolhidosPorId[(int)$acolhidoFiltro]) ? (int)$acolhidoFiltro : null;
        $acolhidoFiltroIndisponivel = $acolhidoEntrada !== '' && $acolhidoFiltro === null;
        $inicio = is_string($_GET['inicio'] ?? null) ? $_GET['inicio'] : '';
        $fim = is_string($_GET['fim'] ?? null) ? $_GET['fim'] : '';
        $tipoFiltro = is_string($_GET['tipo'] ?? null) && isset($tiposSaude[$_GET['tipo']]) ? $_GET['tipo'] : '';
        $termo = is_string($_GET['termo'] ?? null) ? trim($_GET['termo']) : '';
        if (function_exists('mb_substr')) $termo = mb_substr($termo, 0, 120, 'UTF-8');
        else $termo = substr($termo, 0, 120);

        $flash = $_SESSION['flash_message'] ?? null;
        unset($_SESSION['flash_message']);
        if ($acolhidoFiltroIndisponivel && $flash === null) {
            $flash = ['type' => 'warning', 'message' => 'Selecione um acolhido ativo para consultar o histórico.'];
        }
        if (($inicio !== '' && !$dataValida($inicio)) || ($fim !== '' && !$dataValida($fim))) {
            $inicio = $dataValida($inicio) ? $inicio : '';
            $fim = $dataValida($fim) ? $fim : '';
            $flash = ['type' => 'warning', 'message' => 'O período informado é inválido.'];
        }
        if ($inicio !== '' && $fim !== '' && $inicio > $fim) {
            $inicio = '';
            $fim = '';
            $flash = ['type' => 'warning', 'message' => 'A data inicial deve ser anterior à data final.'];
        }

        $formRetentativa = $_SESSION['saude_form_retentativa'] ?? [];
        unset($_SESSION['saude_form_retentativa']);
        if (!is_array($formRetentativa)) $formRetentativa = [];
        $formAcolhidoId = filter_var($formRetentativa['acolhido_id'] ?? null, FILTER_VALIDATE_INT);
        $formAcolhidoId = $formAcolhidoId && isset($acolhidosPorId[(int)$formAcolhidoId]) ? (int)$formAcolhidoId : ($acolhidoFiltro ?? 0);
        $formTipo = is_string($formRetentativa['tipo'] ?? null) && in_array($formRetentativa['tipo'], ['consulta', 'exame', 'comportamento'], true)
            ? $formRetentativa['tipo'] : 'consulta';
        $formData = $dataValida($formRetentativa['data_evento'] ?? null) ? $formRetentativa['data_evento'] : $hoje;
        $formTitulo = is_string($formRetentativa['titulo'] ?? null) ? $formRetentativa['titulo'] : '';
        $formProfissional = is_string($formRetentativa['profissional'] ?? null) ? $formRetentativa['profissional'] : '';
        $formDescricao = is_string($formRetentativa['descricao'] ?? null) ? $formRetentativa['descricao'] : '';

        $tabelaSaudeDisponivel = (new \Abdm\Model\HistoricoSaude($pdo))->saudeRegistrosTabelaDisponivel();
        $registrosSaude = [];
        if ($tabelaSaudeDisponivel && !$acolhidoFiltroIndisponivel) {
            (new \Abdm\Model\HistoricoSaude($pdo))->saudeFinalizarPrescricoesExpiradas();
            $registrosSaude = (new \Abdm\Model\RegistrosSaude($pdo))->consultar($acolhidoFiltro, $inicio, $fim, $tipoFiltro, $termo);
            $registrosSaude = array_values(array_filter($registrosSaude, static function (array $registro): bool {
                if (($registro['tipo'] ?? '') !== 'prescricao_alterada') {
                    return true;
                }
                $detalhes = is_string($registro['detalhes_json'] ?? null) ? json_decode($registro['detalhes_json'], true) : null;
                if (!is_array($detalhes) || !empty($detalhes['alteracoes'])) {
                    return true;
                }
                return isset($detalhes['antes'], $detalhes['atual']) && $detalhes['antes'] !== $detalhes['atual'];
            }));
        }

        return [
            'h' => $h ?? null,
            'valor' => $valor ?? null,
            'hoje' => $hoje ?? null,
            'tiposSaude' => $tiposSaude ?? null,
            'acolhidos' => $acolhidos ?? null,
            'acolhido' => $acolhido ?? null,
            'acolhidoFiltro' => $acolhidoFiltro ?? null,
            'inicio' => $inicio ?? null,
            'fim' => $fim ?? null,
            'tipoFiltro' => $tipoFiltro ?? null,
            'termo' => $termo ?? null,
            'flash' => $flash ?? null,
            'formAcolhidoId' => $formAcolhidoId ?? null,
            'formTipo' => $formTipo ?? null,
            'formData' => $formData ?? null,
            'formTitulo' => $formTitulo ?? null,
            'formProfissional' => $formProfissional ?? null,
            'formDescricao' => $formDescricao ?? null,
            'tabelaSaudeDisponivel' => $tabelaSaudeDisponivel ?? null,
            'registrosSaude' => $registrosSaude ?? null,
            'registro' => $registro ?? null,
            'detalhes' => $detalhes ?? null,
        ];
    }

    public function paraImpressao(): array
    {
        $pdo = $this->pdo;
        if (!(new \Abdm\Model\HistoricoSaude($pdo))->saudeRegistrosTabelaDisponivel()) {
            http_response_code(503);
            exit('O histórico de saúde ainda não está disponível no banco de dados.');
        }
        (new \Abdm\Model\HistoricoSaude($pdo))->saudeFinalizarPrescricoesExpiradas();

        $h = static fn($valor): string => htmlspecialchars((string)$valor, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $hoje = date('Y-m-d');
        $validarData = static function ($valor) use ($hoje): bool {
            if (!is_string($valor)) return false;
            $data = DateTimeImmutable::createFromFormat('!Y-m-d', $valor);
            $erros = DateTimeImmutable::getLastErrors();
            return $data !== false && $data->format('Y-m-d') === $valor
                && (!is_array($erros) || ($erros['warning_count'] === 0 && $erros['error_count'] === 0))
                && $valor <= $hoje;
        };

        $tiposSaude = [
            'consulta' => 'Consulta médica',
            'exame' => 'Exame',
            'comportamento' => 'Alteração comportamental',
            'prescricao_criada' => 'Nova prescrição',
            'prescricao_alterada' => 'Prescrição alterada',
            'medicamento_suspenso' => 'Medicamento suspenso',
            'medicamento_reativado' => 'Medicamento reativado',
            'medicamento_finalizado' => 'Prescrição finalizada',
            'prescricao_finalizada_automaticamente' => 'Prescrição finalizada automaticamente',
            'prescricao_removida' => 'Prescrição removida',
        ];
        $acolhidoEntrada = $_GET['acolhido'] ?? '';
        $acolhidoId = null;
        $nomeAcolhido = 'Todos os acolhidos ativos';
        if ($acolhidoEntrada !== '') {
            $acolhidoId = is_string($acolhidoEntrada) ? filter_var($acolhidoEntrada, FILTER_VALIDATE_INT) : false;
            if (!$acolhidoId || $acolhidoId < 1) {
                http_response_code(400);
                exit('O acolhido informado é inválido.');
            }
            $nomeAcolhido = (new \Abdm\Model\Acolhidos($pdo))->nomeAtivo($acolhidoId);
            if (!is_string($nomeAcolhido) || $nomeAcolhido === '') {
                http_response_code(404);
                exit('Acolhido não encontrado.');
            }
        }

        $inicio = $_GET['inicio'] ?? '';
        $fim = $_GET['fim'] ?? '';
        if (($inicio !== '' && !$validarData($inicio)) || ($fim !== '' && !$validarData($fim)) || ($inicio !== '' && $fim !== '' && $inicio > $fim)) {
            http_response_code(400);
            exit('O período informado é inválido.');
        }
        $tipoFiltro = $_GET['tipo'] ?? '';
        if (!is_string($tipoFiltro) || ($tipoFiltro !== '' && !isset($tiposSaude[$tipoFiltro]))) {
            http_response_code(400);
            exit('O tipo de registro informado é inválido.');
        }
        $termoBusca = $_GET['termo'] ?? '';
        $termoBusca = is_string($termoBusca) ? trim($termoBusca) : '';
        $termoBusca = function_exists('mb_substr') ? mb_substr($termoBusca, 0, 120, 'UTF-8') : substr($termoBusca, 0, 120);

        $registros = (new \Abdm\Model\RegistrosSaude($pdo))->consultar($acolhidoId, $inicio, $fim, $tipoFiltro, $termoBusca);
        $registros = array_values(array_filter($registros, static function (array $registro): bool {
            if (($registro['tipo'] ?? '') !== 'prescricao_alterada') {
                return true;
            }
            $detalhes = is_string($registro['detalhes_json'] ?? null) ? json_decode($registro['detalhes_json'], true) : null;
            if (!is_array($detalhes) || !empty($detalhes['alteracoes'])) {
                return true;
            }
            return isset($detalhes['antes'], $detalhes['atual']) && $detalhes['antes'] !== $detalhes['atual'];
        }));

        $periodo = 'Todo o histórico';
        if ($inicio !== '' && $fim !== '') {
            $periodo = $inicio === $fim
                ? (new DateTimeImmutable($inicio))->format('d/m/Y')
                : (new DateTimeImmutable($inicio))->format('d/m/Y') . ' a ' . (new DateTimeImmutable($fim))->format('d/m/Y');
        } elseif ($inicio !== '') {
            $periodo = 'A partir de ' . (new DateTimeImmutable($inicio))->format('d/m/Y');
        } elseif ($fim !== '') {
            $periodo = 'Até ' . (new DateTimeImmutable($fim))->format('d/m/Y');
        }
        $normalizarSnapshot = static function (string $campo, $valor): string {
            $texto = (string)$valor;
            if ($campo !== 'Posologia') {
                return $texto;
            }
            return preg_replace_callback(
                '/(\d+(?:[.,]\d+)?)\s+(comprimidos?|cápsulas?|capsulas?|ml|gotas?|unidades?)\b/ui',
                static function (array $partes): string {
                    $quantidade = (float)str_replace(',', '.', $partes[1]);
                    $numero = rtrim(rtrim(number_format($quantidade, 2, '.', ''), '0'), '.');
                    $unidade = function_exists('mb_strtolower') ? mb_strtolower($partes[2], 'UTF-8') : strtolower($partes[2]);
                    $base = rtrim($unidade, 's');
                    if (str_starts_with($unidade, 'cápsula')) $base = 'cápsula';
                    if (str_starts_with($unidade, 'capsula')) $base = 'capsula';
                    return $numero . ' ' . ($base === 'ml' ? 'ml' : ($quantidade === 1.0 ? $base : $base . 's'));
                },
                $texto
            ) ?? $texto;
        };
        $cabecalhoPath = ROOT_DIR . '/public/assets/imagens/cabecalho.jpg';
        $rodapePath = ROOT_DIR . '/public/assets/imagens/rodape.jpg';
        if (!is_file($cabecalhoPath) || !is_file($rodapePath)) {
            http_response_code(500);
            exit('Não foi possível preparar o cabeçalho e o rodapé da impressão.');
        }

        return [
            'h' => $h ?? null,
            'valor' => $valor ?? null,
            'tiposSaude' => $tiposSaude ?? null,
            'acolhidoId' => $acolhidoId ?? null,
            'nomeAcolhido' => $nomeAcolhido ?? null,
            'tipoFiltro' => $tipoFiltro ?? null,
            'termoBusca' => $termoBusca ?? null,
            'registros' => $registros ?? null,
            'registro' => $registro ?? null,
            'detalhes' => $detalhes ?? null,
            'periodo' => $periodo ?? null,
            'normalizarSnapshot' => $normalizarSnapshot ?? null,
            'campo' => $campo ?? null,
            'cabecalhoPath' => $cabecalhoPath ?? null,
            'rodapePath' => $rodapePath ?? null,
        ];
    }
}

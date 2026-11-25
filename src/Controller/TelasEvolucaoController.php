<?php

namespace Abdm\Controller;

use PDO;
use DateTimeImmutable;
use DateTime;

final class TelasEvolucaoController
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

        $acolhidos = (new \Abdm\Model\Acolhidos($pdo))->selecaoAtivos();
        $podeRegistrarEvolucao = $controleAcesso->usuarioPode('registrar_evolucao_acolhido');
        $podeConsultarMinhasEvolucoes = $controleAcesso->usuarioPode('consultar_minhas_evolucoes');
        $podeConsultarTodasEvolucoes = $controleAcesso->usuarioPode('consultar_todas_evolucoes');
        $podeConsultarEvolucoes = $podeConsultarMinhasEvolucoes || $podeConsultarTodasEvolucoes;
        $profissionais = (new \Abdm\Model\Usuarios($pdo))->profissionais();
        $acolhidosPorId = [];
        foreach ($acolhidos as $acolhido) $acolhidosPorId[(int)$acolhido['id']] = $acolhido;
        $acolhidoGet = $_GET['acolhido'] ?? '';
        $idValidado = is_string($acolhidoGet) ? filter_var($acolhidoGet, FILTER_VALIDATE_INT) : false;
        $acolhidoId = $idValidado && isset($acolhidosPorId[(int)$idValidado]) ? (int)$idValidado : null;

        $inicio = is_string($_GET['inicio'] ?? null) ? $_GET['inicio'] : '';
        $fim = is_string($_GET['fim'] ?? null) ? $_GET['fim'] : '';
        $termo = is_string($_GET['termo'] ?? null) ? trim($_GET['termo']) : '';
        $termo = function_exists('mb_substr') ? mb_substr($termo, 0, 120, 'UTF-8') : substr($termo, 0, 120);
        $flash = $_SESSION['flash_message'] ?? null;
        unset($_SESSION['flash_message']);
        if ($acolhidoGet !== '' && $acolhidoId === null) {
            $flash = ['type' => 'warning', 'message' => 'O acolhido selecionado não foi encontrado.'];
        }
        if (($inicio !== '' && !$dataValida($inicio)) || ($fim !== '' && !$dataValida($fim))) {
            $inicio = $dataValida($inicio) ? $inicio : '';
            $fim = $dataValida($fim) ? $fim : '';
            $flash = ['type' => 'warning', 'message' => 'Informe um período válido.'];
        }
        if ($inicio !== '' && $fim !== '' && $inicio > $fim) {
            $inicio = '';
            $fim = '';
            $flash = ['type' => 'warning', 'message' => 'A data inicial deve ser anterior à data final.'];
        }

        $form = $_SESSION['evolucao_form_retentativa'] ?? [];
        unset($_SESSION['evolucao_form_retentativa']);
        if (!is_array($form)) $form = [];
        $evolucoes = [];
        if ($acolhidoId !== null && $podeConsultarEvolucoes) {
            $evolucoes = (new \Abdm\Model\Evolucoes($pdo))->consultar($acolhidoId, $podeConsultarTodasEvolucoes ? null : (int)$_SESSION['id_login'], $inicio, $fim, $termo);
        }
        $acolhidoSelecionado = $acolhidoId !== null ? $acolhidosPorId[$acolhidoId] : null;

        return [
            'h' => $h ?? null,
            'hoje' => $hoje ?? null,
            'acolhidos' => $acolhidos ?? null,
            'podeRegistrarEvolucao' => $podeRegistrarEvolucao ?? null,
            'podeConsultarTodasEvolucoes' => $podeConsultarTodasEvolucoes ?? null,
            'podeConsultarEvolucoes' => $podeConsultarEvolucoes ?? null,
            'profissionais' => $profissionais ?? null,
            'acolhido' => $acolhido ?? null,
            'acolhidoId' => $acolhidoId ?? null,
            'inicio' => $inicio ?? null,
            'fim' => $fim ?? null,
            'termo' => $termo ?? null,
            'flash' => $flash ?? null,
            'form' => $form ?? null,
            'evolucoes' => $evolucoes ?? null,
            'acolhidoSelecionado' => $acolhidoSelecionado ?? null,
        ];
    }

    public function paraImpressao(\Abdm\Infraestrutura\ControleAcesso $controleAcesso): array
    {
        $pdo = $this->pdo;
        $modos = [
            'meus' => ['permissao' => 'imprimir_minhas_evolucoes', 'titulo' => 'Evoluções registradas por mim'],
            'acolhido' => ['permissao' => 'imprimir_evolucao_acolhido', 'titulo' => 'Evoluções do acolhido'],
            'profissional' => ['permissao' => 'imprimir_evolucoes_profissional', 'titulo' => 'Evoluções por profissional'],
            'todos' => ['permissao' => 'imprimir_evolucoes_todos', 'titulo' => 'Evoluções de todos os profissionais'],
        ];
        $modo = $_GET['modo'] ?? '';
        if (!is_string($modo) || !isset($modos[$modo])) {
            http_response_code(400);
            exit('Selecione um modelo de impressão válido.');
        }
        $controleAcesso->exigirPermissao($modos[$modo]['permissao']);

        $hoje = date('Y-m-d');
        $validarData = static function ($valor) use ($hoje): bool {
            if (!is_string($valor)) return false;
            $data = DateTimeImmutable::createFromFormat('!Y-m-d', $valor);
            $erros = DateTimeImmutable::getLastErrors();
            return $data !== false && $data->format('Y-m-d') === $valor
                && (!is_array($erros) || ($erros['warning_count'] === 0 && $erros['error_count'] === 0))
                && $valor <= $hoje;
        };
        $escopo = $_GET['escopo'] ?? 'dia';
        if (!is_string($escopo) || !in_array($escopo, ['dia', 'periodo', 'todos'], true)) {
            http_response_code(400);
            exit('Selecione um período válido.');
        }
        $dataInicial = $hoje;
        $dataFinal = $hoje;
        if ($escopo === 'dia') {
            $data = $_GET['data'] ?? $hoje;
            if (!$validarData($data)) { http_response_code(400); exit('Informe uma data válida.'); }
            $dataInicial = $data;
            $dataFinal = $data;
        } elseif ($escopo === 'periodo') {
            $inicio = $_GET['inicio'] ?? '';
            $fim = $_GET['fim'] ?? '';
            if (!$validarData($inicio) || !$validarData($fim) || $inicio > $fim) {
                http_response_code(400);
                exit('Informe um intervalo válido.');
            }
            $dataInicial = $inicio;
            $dataFinal = $fim;
        }

        $acolhidoId = null;
        $profissionalId = null;
        $titulo = $modos[$modo]['titulo'];
        if ($modo === 'acolhido') {
            $acolhidoId = filter_var($_GET['acolhido'] ?? null, FILTER_VALIDATE_INT);
            if (!$acolhidoId || $acolhidoId < 1) { http_response_code(400); exit('Selecione um acolhido.'); }
            $nomeAcolhido = (new \Abdm\Model\Acolhidos($pdo))->nomeAtivo($acolhidoId);
            if (!is_string($nomeAcolhido) || $nomeAcolhido === '') { http_response_code(404); exit('Acolhido não encontrado.'); }
            $titulo .= ' — ' . $nomeAcolhido;
        } elseif ($modo === 'meus') {
            $profissionalId = (int)$_SESSION['id_login'];
            $nomeProfissionalAtual = (string)(new \Abdm\Model\Usuarios($pdo))->nomeProfissional($profissionalId) ?: 'mim';
            $titulo = 'Evoluções registradas por ' . $nomeProfissionalAtual;
            if (array_key_exists('acolhido', $_GET)) {
                $acolhidoGet = $_GET['acolhido'];
                $acolhidoId = is_string($acolhidoGet) ? filter_var($acolhidoGet, FILTER_VALIDATE_INT) : false;
                if (!$acolhidoId || $acolhidoId < 1) { http_response_code(400); exit('A seleção do acolhido é inválida.'); }
                $nomeAcolhido = (new \Abdm\Model\Acolhidos($pdo))->nomeAtivo($acolhidoId);
                if (!is_string($nomeAcolhido) || $nomeAcolhido === '') { http_response_code(404); exit('Acolhido não encontrado.'); }
                $titulo = 'Evoluções de ' . $nomeAcolhido . ' registradas por ' . $nomeProfissionalAtual;
            }
        } elseif ($modo === 'profissional') {
            $profissionalId = filter_var($_GET['profissional'] ?? null, FILTER_VALIDATE_INT);
            if (!$profissionalId || $profissionalId < 1) { http_response_code(400); exit('Selecione um profissional.'); }
            $nomeProfissional = (new \Abdm\Model\Usuarios($pdo))->nomeProfissional($profissionalId);
            if (!is_string($nomeProfissional) || $nomeProfissional === '') { http_response_code(404); exit('Profissional não encontrado.'); }
            $titulo = 'Evoluções do profissional ' . $nomeProfissional;
        }

        $evolucoes = (new \Abdm\Model\Evolucoes($pdo))->paraImpressao($escopo, $dataInicial, $dataFinal, $acolhidoId, $profissionalId);

        $h = static fn($valor): string => htmlspecialchars((string)$valor, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $periodo = $escopo === 'dia'
            ? (new DateTimeImmutable($dataInicial))->format('d/m/Y')
            : ($escopo === 'periodo'
                ? (new DateTimeImmutable($dataInicial))->format('d/m/Y') . ' a ' . (new DateTimeImmutable($dataFinal))->format('d/m/Y')
                : 'Todo o histórico');
        $cabecalhoPath = ROOT_DIR . '/public/assets/imagens/cabecalho.jpg';
        $rodapePath = ROOT_DIR . '/public/assets/imagens/rodape.jpg';
        if (!is_file($cabecalhoPath) || !is_file($rodapePath)) {
            http_response_code(500);
            exit('Não foi possível preparar o cabeçalho e o rodapé da impressão.');
        }

        return [
            'titulo' => $titulo ?? null,
            'evolucoes' => $evolucoes ?? null,
            'h' => $h ?? null,
            'periodo' => $periodo ?? null,
            'cabecalhoPath' => $cabecalhoPath ?? null,
            'rodapePath' => $rodapePath ?? null,
        ];
    }
}

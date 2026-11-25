<?php

namespace Abdm\Controller;

use Abdm\Infraestrutura\ControleAcesso;
use Abdm\Model\HistoricoSaude;
use Abdm\Infraestrutura\Sessao;

use DateTimeImmutable;
use PDO;
use PDOException;
use Throwable;
use RuntimeException;
use InvalidArgumentException;

final class SaudeAcolhidoController
{
    private readonly ControleAcesso $controleAcesso;

    public function __construct(private readonly PDO $pdo)
    {
        $this->controleAcesso = new ControleAcesso($pdo);
    }

    public function executar(): void
    {
        $pdo = $this->pdo;
        $this->controleAcesso->exigirUsuarioAutenticado();
        $this->controleAcesso->exigirPermissao('saude_acolhido');

        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            http_response_code(405);
            exit('Método não permitido.');
        }
        Sessao::exigirTokenCsrfValido();

        $acolhidoPost = $_POST['acolhido_id'] ?? '';
        $acolhidoId = is_string($acolhidoPost) ? filter_var($acolhidoPost, FILTER_VALIDATE_INT) : false;
        $url = BASE_URL . 'views/saude_acolhido.php';
        if ($acolhidoId && $acolhidoId > 0) {
            $url .= '?acolhido=' . (int)$acolhidoId;
        }

        try {
            if (!(new HistoricoSaude($pdo))->saudeRegistrosTabelaDisponivel()) {
                throw new RuntimeException('O histórico de saúde não está disponível nesta instalação.');
            }
            if (!$acolhidoId || $acolhidoId < 1) {
                throw new InvalidArgumentException('Selecione um acolhido.');
            }

            $tipo = $_POST['tipo'] ?? '';
            $tiposPermitidos = ['consulta', 'exame', 'comportamento'];
            if (!is_string($tipo) || !in_array($tipo, $tiposPermitidos, true)) {
                throw new InvalidArgumentException('Selecione consulta, exame ou alteração comportamental.');
            }

            $dataPost = $_POST['data_evento'] ?? '';
            $dataEvento = is_string($dataPost) ? DateTimeImmutable::createFromFormat('!Y-m-d', $dataPost) : false;
            $errosData = DateTimeImmutable::getLastErrors();
            if (!$dataEvento || $dataEvento->format('Y-m-d') !== $dataPost
                || (is_array($errosData) && ($errosData['warning_count'] !== 0 || $errosData['error_count'] !== 0))
                || $dataPost > date('Y-m-d')) {
                throw new InvalidArgumentException('Informe uma data válida, sem data futura.');
            }

            $titulo = is_string($_POST['titulo'] ?? null) ? trim($_POST['titulo']) : '';
            $profissional = is_string($_POST['profissional'] ?? null) ? trim($_POST['profissional']) : '';
            $descricao = is_string($_POST['descricao'] ?? null) ? trim($_POST['descricao']) : '';
            $tamanhoTitulo = function_exists('mb_strlen') ? mb_strlen($titulo, 'UTF-8') : strlen($titulo);
            $tamanhoProfissional = function_exists('mb_strlen') ? mb_strlen($profissional, 'UTF-8') : strlen($profissional);
            $tamanhoDescricao = function_exists('mb_strlen') ? mb_strlen($descricao, 'UTF-8') : strlen($descricao);
            if ($tamanhoTitulo < 2 || $tamanhoTitulo > 180) {
                throw new InvalidArgumentException('Informe um título com até 180 caracteres.');
            }
            if ($tamanhoProfissional > 180) {
                throw new InvalidArgumentException('O campo profissional ou local aceita até 180 caracteres.');
            }
            if ($tamanhoDescricao < 2 || $tamanhoDescricao > 12000) {
                throw new InvalidArgumentException('O relato deve ter entre 2 e 12.000 caracteres.');
            }

            $stmt = (new \Abdm\Model\PersistenciaSaude($pdo))->prepararInsercaoRegistroSaude();
            $stmt->execute([
                $tipo,
                $dataEvento->format('Y-m-d'),
                $titulo,
                $profissional !== '' ? $profissional : null,
                $descricao,
                (int)$_SESSION['id_login'],
                date('Y-m-d H:i:s'),
                $acolhidoId,
            ]);
            if ($stmt->rowCount() === 0) {
                throw new InvalidArgumentException('Selecione um acolhido ativo para registrar o atendimento.');
            }
            $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'Registro de saúde salvo.'];
        } catch (InvalidArgumentException $exception) {
            $_SESSION['saude_form_retentativa'] = $_POST;
            $_SESSION['flash_message'] = ['type' => 'warning', 'message' => $exception->getMessage()];
        } catch (Throwable $exception) {
            error_log('Falha ao salvar registro de saúde. Código: ' . $exception->getCode());
            $_SESSION['saude_form_retentativa'] = $_POST;
            $_SESSION['flash_message'] = ['type' => 'danger', 'message' => $exception instanceof RuntimeException && !($exception instanceof PDOException)
                ? $exception->getMessage()
                : 'Não foi possível salvar o registro de saúde. Os dados digitados foram preservados.'];
        }

        header('Location: ' . $url);
        exit;
    }
}

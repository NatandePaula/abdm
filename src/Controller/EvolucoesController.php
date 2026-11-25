<?php

namespace Abdm\Controller;

use Abdm\Infraestrutura\ControleAcesso;
use Abdm\Infraestrutura\Sessao;

use DateTimeImmutable;
use PDO;
use Throwable;
use InvalidArgumentException;

final class EvolucoesController
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
        $this->controleAcesso->exigirPermissao('registrar_evolucao_acolhido');
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') { http_response_code(405); exit('Método não permitido.'); }
        Sessao::exigirTokenCsrfValido();

        $acolhidoId = filter_var($_POST['acolhido_id'] ?? null, FILTER_VALIDATE_INT);
        $data = is_string($_POST['data'] ?? null) ? $_POST['data'] : '';
        $hora = is_string($_POST['horario'] ?? null) ? $_POST['horario'] : '';
        $texto = is_string($_POST['registro'] ?? null) ? trim($_POST['registro']) : '';
        $url = BASE_URL . 'views/evolucoes.php' . ($acolhidoId ? '?acolhido=' . (int)$acolhidoId : '');

        try {
            if (!$acolhidoId || $acolhidoId < 1) throw new InvalidArgumentException('Selecione um acolhido.');
            $stmtAcolhido = (new \Abdm\Model\PersistenciaSaude($pdo))->prepararConsultaAcolhidoComIdPorId();
            $stmtAcolhido->execute([$acolhidoId]);
            if (!$stmtAcolhido->fetchColumn()) throw new InvalidArgumentException('O acolhido selecionado não foi encontrado.');
            $dataHora = DateTimeImmutable::createFromFormat('!Y-m-d H:i', $data . ' ' . $hora);
            $erros = DateTimeImmutable::getLastErrors();
            if (!$dataHora || $dataHora->format('Y-m-d H:i') !== $data . ' ' . $hora || (is_array($erros) && ($erros['warning_count'] || $erros['error_count'])) || $dataHora > new DateTimeImmutable()) {
                throw new InvalidArgumentException('Informe data e horário válidos, sem data futura.');
            }
            $tamanho = function_exists('mb_strlen') ? mb_strlen($texto, 'UTF-8') : strlen($texto);
            if ($tamanho < 1 || $tamanho > 12000) throw new InvalidArgumentException('A evolução deve ter entre 1 e 12.000 caracteres.');

            $stmtUsuario = (new \Abdm\Model\PersistenciaSaude($pdo))->prepararConsultaPerfilComIdUsuarioPorLoginIdLoginUnica();
            $stmtUsuario->execute([(int)$_SESSION['id_login']]);
            $usuario = $stmtUsuario->fetch(PDO::FETCH_ASSOC) ?: [];
            $nome = trim((string)($usuario['nome'] ?? $_SESSION['login'] ?? 'Profissional'));
            $stmt = (new \Abdm\Model\PersistenciaSaude($pdo))->prepararInsercaoEvolucao();
            $stmt->execute([
                (int)$acolhidoId,
                (int)$_SESSION['id_login'],
                !empty($usuario['idUsuario']) ? (int)$usuario['idUsuario'] : null,
                $dataHora->format('Y-m-d H:i:s'),
                $texto,
                $nome,
                !empty($usuario['atuacaoProfissional']) ? (string)$usuario['atuacaoProfissional'] : null,
                !empty($usuario['registroProfissional']) ? (string)$usuario['registroProfissional'] : null,
            ]);
            $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'Evolução registrada.'];
        } catch (InvalidArgumentException $exception) {
            $_SESSION['evolucao_form_retentativa'] = $_POST;
            $_SESSION['flash_message'] = ['type' => 'warning', 'message' => $exception->getMessage()];
        } catch (Throwable $exception) {
            error_log('Falha ao registrar evolução. Código: ' . $exception->getCode());
            $_SESSION['evolucao_form_retentativa'] = $_POST;
            $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Não foi possível registrar a evolução. Os dados digitados foram preservados.'];
        }
        header('Location: ' . $url);
        exit;
    }
}

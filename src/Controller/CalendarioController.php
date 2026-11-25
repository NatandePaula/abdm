<?php

namespace Abdm\Controller;

use Abdm\Model\Calendario;
use Abdm\Infraestrutura\ControleAcesso;
use Abdm\Infraestrutura\Sessao;

use PDO;
use PDOException;
use InvalidArgumentException;

final class CalendarioController
{
    private readonly ControleAcesso $controleAcesso;

    public function __construct(private readonly PDO $pdo)
    {
        $this->controleAcesso = new ControleAcesso($pdo);
    }

    public function configuracao(): array
    {
        return (new Calendario($this->pdo))->lerConfiguracaoCalendario();
    }

    public function executar(): void
    {
        $pdo = $this->pdo;
        $this->controleAcesso->exigirPermissao('gerenciar_calendario');
        Sessao::exigirTokenCsrfValido();

        $destino = 'views/gerenciar_calendario.php';
        $acao = $_POST['acao'] ?? '';
        $url = $_POST['url'] ?? '';

        try {
            if ($acao !== 'salvar' || !is_string($url)) {
                throw new InvalidArgumentException('Solicitação inválida.');
            }

            $url = trim($url);
            if (!Calendario::urlCalendarioIncorporadaPermitida($url)) {
                throw new InvalidArgumentException('Informe uma URL HTTPS válida de incorporação do Google Agenda.');
            }

            $stmt = (new \Abdm\Model\PersistenciaCalendario($pdo))->prepararInsercaoCalendarioConfiguracao();
            $stmt->execute([$url, (int)($_SESSION['id_login'] ?? 0)]);

            unset($_SESSION['calendar_config_retry']);
            $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'Endereço do calendário atualizado.'];
        } catch (InvalidArgumentException $e) {
            $_SESSION['calendar_config_retry'] = is_string($url) ? substr($url, 0, 8192) : '';
            $_SESSION['flash_message'] = ['type' => 'danger', 'message' => $e->getMessage()];
        } catch (PDOException $e) {
            error_log('Falha ao atualizar a configuração do calendário. Código: ' . $e->getCode());
            $_SESSION['calendar_config_retry'] = is_string($url) ? substr($url, 0, 8192) : '';
            $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Não foi possível salvar o endereço do calendário nesta instalação.'];
        }

        header('Location: ' . BASE_URL . $destino);
        exit;
    }
}

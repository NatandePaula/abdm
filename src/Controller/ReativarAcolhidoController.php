<?php

namespace Abdm\Controller;

use Abdm\Infraestrutura\ControleAcesso;
use Abdm\Infraestrutura\Sessao;

use PDO;
use PDOException;

final class ReativarAcolhidoController
{
    private readonly ControleAcesso $controleAcesso;

    public function __construct(private readonly PDO $pdo)
    {
        $this->controleAcesso = new ControleAcesso($pdo);
    }

    public function executar(): void
    {
        $pdo = $this->pdo;
        $this->controleAcesso->exigirPermissao('acolhidos');

        if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id_acolhido'])) {
            Sessao::exigirTokenCsrfValido();

            $idAcolhido = (int)$_POST['id_acolhido'];

            try {
                // Atualiza o status para 'ativo' e limpa os dados de desligamento
                $stmt = (new \Abdm\Model\PersistenciaAcolhidos($pdo))->prepararAtualizacaoAcolhidoParaReativacao();
                $stmt->execute([$idAcolhido]);

                $_SESSION['flash_message'] = [
                    'type' => 'success',
                    'message' => 'Acolhido reativado com sucesso!'
                ];
                header("Location: ../views/acolhidos.php");
                exit();

            } catch (PDOException $e) {
                error_log('Falha ao reativar o acolhido. Código: ' . $e->getCode());
                $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Não foi possível reativar o acolhido.'];
                header("Location: ../views/acolhidos_desligados.php");
                exit;
            }

        } else {
            header("Location: ../views/acolhidos.php");
            exit;
        }
    }
}

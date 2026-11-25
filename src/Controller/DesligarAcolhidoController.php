<?php

namespace Abdm\Controller;

use Abdm\Infraestrutura\ControleAcesso;
use Abdm\Infraestrutura\Sessao;

use DateTimeImmutable;
use PDO;
use PDOException;

final class DesligarAcolhidoController
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

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            Sessao::exigirTokenCsrfValido();
            if (isset($_POST['id_acolhido']) && isset($_POST['motivo_desligamento'])) {

                $idAcolhido = filter_var($_POST['id_acolhido'], FILTER_VALIDATE_INT);
                $motivo = $_POST['motivo_desligamento'];
                $dataDesligamento = $_POST['data_desligamento'] ?? '';
                if (!is_string($motivo) || !is_string($dataDesligamento) || !$idAcolhido) {
                    http_response_code(400);
                    exit('Invalid deactivation data.');
                }
                $motivo = trim($motivo);
                $dataValidada = DateTimeImmutable::createFromFormat('!Y-m-d', $dataDesligamento);

                if ($motivo === '' || !$dataValidada || $dataValidada->format('Y-m-d') !== $dataDesligamento) {
                    http_response_code(400);
                    exit('Data e motivo do desligamento são obrigatórios.');
                }

                try {
                    $stmt = (new \Abdm\Model\PersistenciaAcolhidos($pdo))->prepararAtualizacaoAcolhidoParaDesligamento();
                    $stmt->execute([$dataDesligamento, $motivo, $idAcolhido]);

                    $_SESSION['flash_message'] = [
                        'type' => 'warning',
                        'message' => 'Acolhido desligado com sucesso!'
                    ];
                    header("Location: ../views/acolhidos.php");
                    exit();

                } catch (PDOException $e) {
                    error_log('Falha ao desligar o acolhido. Código: ' . $e->getCode());
                    $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Não foi possível desligar o acolhido.'];
                    header("Location: ../views/acolhidos.php");
                    exit;
                }

            } else {
                header("Location: ../views/acolhidos.php");
                exit;
            }
        }
    }
}

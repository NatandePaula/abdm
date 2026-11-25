<?php

namespace Abdm\Controller;

use Abdm\Infraestrutura\ControleAcesso;
use Abdm\Model\DadosFormulario;
use Abdm\Infraestrutura\Sessao;

use PDO;
use PDOException;
use Exception;

final class PeriodosController
{
    private readonly ControleAcesso $controleAcesso;

    public function __construct(private readonly PDO $pdo)
    {
        $this->controleAcesso = new ControleAcesso($pdo);
    }

    public function executar(): void
    {
        $pdo = $this->pdo;
        $this->controleAcesso->exigirPermissao('prescricoes');

        if ($_SERVER["REQUEST_METHOD"] !== "POST" || !isset($_POST['acao'])) {
            header("Location: ../views/gerenciar_periodos.php");
            exit;
        }
        Sessao::exigirTokenCsrfValido();

        $acao = $_POST['acao'];

        try {
            $pdo->beginTransaction();

            if ($acao === 'criar_periodo') {
                $stmt = (new \Abdm\Model\PersistenciaHorarios($pdo))->prepararInsercaoPeriodo();
                $stmt->execute([
                    DadosFormulario::limparDados($_POST['nome']),
                    (int)$_POST['ordem']
                ]);
                $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'Período criado com sucesso!'];
            }

            elseif ($acao === 'editar_periodo') {
                $stmt = (new \Abdm\Model\PersistenciaHorarios($pdo))->prepararAtualizacaoPeriodoDeNome();
                $stmt->execute([
                    DadosFormulario::limparDados($_POST['nome']),
                    (int)$_POST['ordem'],
                    (int)$_POST['id']
                ]);
                $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'Período atualizado com sucesso!'];
            }

            elseif ($acao === 'excluir_periodo') {
                $id = (int)$_POST['id'];

                // Verificar se o período está em uso
                $stmtCheck1 = (new \Abdm\Model\PersistenciaHorarios($pdo))->prepararConsultaHorarioDaPrescricaoPorPeriodoId();
                $stmtCheck1->execute([$id]);
                $count1 = $stmtCheck1->fetchColumn();

                $stmtCheck2 = (new \Abdm\Model\PersistenciaHorarios($pdo))->prepararConsultaHorarioNomeadoPorPeriodoId();
                $stmtCheck2->execute([$id]);
                $count2 = $stmtCheck2->fetchColumn();

                if ($count1 > 0 || $count2 > 0) {
                    throw new Exception("Não é possível excluir. Este período está em uso em prescrições ou horários nomeados.");
                }

                $stmtDelete = (new \Abdm\Model\PersistenciaHorarios($pdo))->prepararExclusaoPeriodo();
                $stmtDelete->execute([$id]);
                $_SESSION['flash_message'] = ['type' => 'warning', 'message' => 'Período excluído com sucesso.'];
            }

            $pdo->commit();

        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            if ($e instanceof PDOException) {
                error_log('Falha na operação de períodos. Código: ' . $e->getCode());
                $errorMessage = 'Erro interno ao salvar o período.';
            } else {
                $errorMessage = $e->getMessage();
            }
            $_SESSION['flash_message'] = [
                'type' => 'danger',
                'message' => 'Erro: ' . $errorMessage
            ];
        }

        header("Location: ../views/gerenciar_periodos.php");
        exit();
    }
}

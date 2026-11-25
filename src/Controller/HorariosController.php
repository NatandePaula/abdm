<?php

namespace Abdm\Controller;

use Abdm\Infraestrutura\ControleAcesso;
use Abdm\Model\DadosFormulario;
use Abdm\Infraestrutura\Sessao;

use PDO;
use PDOException;
use Exception;

final class HorariosController
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
            header("Location: ../views/gerenciar_horarios.php");
            exit;
        }
        Sessao::exigirTokenCsrfValido();

        $acao = $_POST['acao'];

        try {
            $pdo->beginTransaction();

            if ($acao === 'criar_horario') {
                $stmt = (new \Abdm\Model\PersistenciaHorarios($pdo))->prepararInsercaoHorarioNomeado();
                $stmt->execute([
                    DadosFormulario::limparDados($_POST['nome']),
                    DadosFormulario::limparDados($_POST['horario']),
                    (int)$_POST['periodo_id']
                ]);
                $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'Horário pré-definido criado com sucesso!'];
            }

            elseif ($acao === 'editar_horario') {
                $stmt = (new \Abdm\Model\PersistenciaHorarios($pdo))->prepararAtualizacaoHorarioNomeadoDeNome();
                $stmt->execute([
                    DadosFormulario::limparDados($_POST['nome']),
                    DadosFormulario::limparDados($_POST['horario']),
                    (int)$_POST['periodo_id'],
                    (int)$_POST['id']
                ]);
                $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'Horário pré-definido atualizado com sucesso!'];
            }

            elseif ($acao === 'excluir_horario') {
                $id = (int)$_POST['id'];

                // Verificar se o horário está em uso em prescrições ativas
                $stmtCheck = (new \Abdm\Model\PersistenciaHorarios($pdo))->prepararConsultaHorarioDaPrescricaoComHorarioPorHorario();
                $stmtCheck->execute([$id]);
                if ($stmtCheck->fetchColumn() > 0) {
                    throw new Exception("Não é possível excluir. Este horário está em uso em uma ou mais prescrições ativas.");
                }

                $stmtDelete = (new \Abdm\Model\PersistenciaHorarios($pdo))->prepararExclusaoHorarioNomeado();
                $stmtDelete->execute([$id]);
                $_SESSION['flash_message'] = ['type' => 'warning', 'message' => 'Horário pré-definido excluído com sucesso.'];
            }

            $pdo->commit();

        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            if ($e instanceof PDOException) {
                error_log('Falha na operação de horários. Código: ' . $e->getCode());
                $errorMessage = 'Erro interno ao salvar o horário.';
            } else {
                $errorMessage = $e->getMessage();
            }
            $_SESSION['flash_message'] = [
                'type' => 'danger',
                'message' => 'Erro: ' . $errorMessage
            ];
        }

        header("Location: ../views/gerenciar_horarios.php");
        exit();
    }
}

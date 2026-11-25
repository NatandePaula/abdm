<?php

namespace Abdm\Controller;

use PDO;

final class TelaMedicamentoController
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function formulario(): array
    {
        $pdo = $this->pdo;
        $medicamentoEditar = null;
        if (array_key_exists('editar', $_GET)) {
            $idMedicamentoEditar = filter_var($_GET['editar'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            if (!$idMedicamentoEditar) {
                $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Medicamento inválido para edição.'];
                header('Location: ' . BASE_URL . 'views/medicamentos_estoque.php');
                exit;
            }

            $medicamentoEditar = (new \Abdm\Model\Medicamentos($pdo))->ficha($idMedicamentoEditar) ?: null;
            if ($medicamentoEditar === null) {
                $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Medicamento não encontrado.'];
                header('Location: ' . BASE_URL . 'views/medicamentos_estoque.php');
                exit;
            }
        }
        $modoEdicao = $medicamentoEditar !== null;
        $valorFormulario = static fn(string $campo): string => htmlspecialchars((string)($medicamentoEditar[$campo] ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        return [
            'medicamentoEditar' => $medicamentoEditar ?? null,
            'aceitaEquivalencia' => (new \Abdm\Model\EstoqueMedicamentos($pdo))->aceitaEquivalenciaNoCadastro(),
            'modoEdicao' => $modoEdicao ?? null,
            'valorFormulario' => $valorFormulario ?? null,
        ];
    }
}

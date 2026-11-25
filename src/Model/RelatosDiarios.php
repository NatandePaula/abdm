<?php

namespace Abdm\Model;

use PDO;
use Throwable;

final class RelatosDiarios
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function edicaoRelatoDisponivel(): bool
    {
        $pdo = $this->pdo;
        try {
            $colunas = $pdo->query('SHOW COLUMNS FROM relatos_diarios')->fetchAll(PDO::FETCH_COLUMN, 0);
            return in_array('editado_em', $colunas, true) && in_array('editado_por_login', $colunas, true);
        } catch (Throwable $exception) {
            error_log('Falha ao consultar as colunas de edição dos relatos. Código: ' . $exception->getCode());
            return false;
        }
    }
}

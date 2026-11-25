<?php

namespace Abdm\Model;

use PDO;

final class Medicamentos
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function ficha(int $id): array|false
    {
        $stmt = $this->pdo->prepare('SELECT * FROM medicamentos WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function selecao(): array
    {
        return $this->pdo->query("SELECT id, nome_comercial, principio_ativo, apresentacao, unidade_base FROM medicamentos ORDER BY nome_comercial")->fetchAll();
    }
}

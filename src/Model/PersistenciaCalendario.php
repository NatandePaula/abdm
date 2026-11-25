<?php

namespace Abdm\Model;

use PDO;
use PDOStatement;

final class PersistenciaCalendario
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function prepararInsercaoCalendarioConfiguracao(): PDOStatement
    {
        return $this->pdo->prepare('INSERT INTO calendario_configuracao (id, url, atualizado_por_login, atualizado_em)
         VALUES (1, ?, ?, NOW())
         ON DUPLICATE KEY UPDATE url = VALUES(url), atualizado_por_login = VALUES(atualizado_por_login), atualizado_em = NOW()');
    }
}

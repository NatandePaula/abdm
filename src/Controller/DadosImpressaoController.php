<?php

namespace Abdm\Controller;

use Abdm\Model\Usuarios;
use PDO;

final class DadosImpressaoController
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function nomeUsuarioImpressaoAtual(): string
    {
        $loginId = (int)($_SESSION['id_login'] ?? 0);
        if ($loginId > 0) {
            $nome = (new Usuarios($this->pdo))->nomeParaImpressao($loginId);
            if (is_string($nome) && trim($nome) !== '') {
                return trim($nome);
            }
        }

        return (string)($_SESSION['username'] ?? $_SESSION['login'] ?? 'Usuário não identificado');
    }
}

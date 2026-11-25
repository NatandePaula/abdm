<?php

namespace Abdm\Controller;

use Abdm\Infraestrutura\ControleAcesso;
use Abdm\Infraestrutura\Sessao;
use PDO;

final class SessaoController
{
    public function __construct(private PDO $pdo)
    {
    }

    public static function exigirPost(): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            header('Allow: POST');
            http_response_code(405);
            exit('Método não permitido.');
        }
    }

    public function renovar(): void
    {
        (new ControleAcesso($this->pdo))->exigirUsuarioAutenticado();
        Sessao::exigirTokenCsrfValido();
        $_SESSION['last_activity'] = time();
        http_response_code(200);
    }

    public function encerrar(): void
    {
        if (Sessao::sessaoExpirouPorInatividade()) {
            Sessao::limparSessaoAtual();
            header('Location: ' . BASE_URL . 'index.php?timeout=1');
            return;
        }

        Sessao::exigirTokenCsrfValido();
        Sessao::limparSessaoAtual();
        header('Location: ' . BASE_URL . 'index.php');
    }
}

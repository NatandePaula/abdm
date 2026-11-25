<?php

namespace Abdm\Controller;

use Abdm\Infraestrutura\ControleAcesso;
use Abdm\Model\PainelEstoque;
use PDO;

final class PainelEstoqueController
{
    private readonly PainelEstoque $painelEstoque;

    public function __construct(private readonly ControleAcesso $controleAcesso, PDO $pdo)
    {
        $this->painelEstoque = new PainelEstoque($pdo);
    }

    public function dadosParaExibir(): array
    {
        $this->controleAcesso->exigirPermissao('estoque');

        return $this->painelEstoque->consultar();
    }
}

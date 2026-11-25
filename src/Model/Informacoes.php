<?php

namespace Abdm\Model;

use PDO;

final class Informacoes
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function links(): array
    {
        return $this->pdo->query('SELECT idInformacaoLink, titulo, url FROM informacao_links ORDER BY ordem, idInformacaoLink')->fetchAll(PDO::FETCH_ASSOC);
    }

    public function linksDoMenu(): array
    {
        return $this->pdo->query('SELECT titulo, url FROM informacao_links ORDER BY ordem, idInformacaoLink')->fetchAll(PDO::FETCH_ASSOC);
    }
}

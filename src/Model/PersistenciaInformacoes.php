<?php

namespace Abdm\Model;

use PDO;
use PDOStatement;

final class PersistenciaInformacoes
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function prepararConsultaLinkInformativoComIdInformacaoLinkPorIdInformacaoLink(): PDOStatement
    {
        return $this->pdo->prepare('SELECT idInformacaoLink FROM informacao_links WHERE idInformacaoLink = ?');
    }

    public function prepararAtualizacaoLinkInformativoDeTitulo(): PDOStatement
    {
        return $this->pdo->prepare('UPDATE informacao_links SET titulo = ?, url = ? WHERE idInformacaoLink = ?');
    }

    public function consultarLinkInformativo(): PDOStatement
    {
        return $this->pdo->query('SELECT COALESCE(MAX(ordem), 0) + 10 FROM informacao_links');
    }

    public function prepararInsercaoLinkInformativo(): PDOStatement
    {
        return $this->pdo->prepare('INSERT INTO informacao_links (titulo, url, ordem) VALUES (?, ?, ?)');
    }

    public function prepararExclusaoLinkInformativo(): PDOStatement
    {
        return $this->pdo->prepare('DELETE FROM informacao_links WHERE idInformacaoLink = ?');
    }
}

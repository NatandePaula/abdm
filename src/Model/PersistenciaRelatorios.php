<?php

namespace Abdm\Model;

use PDO;
use PDOStatement;

final class PersistenciaRelatorios
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function prepararExclusaoMencao(): PDOStatement
    {
        return $this->pdo->prepare('DELETE FROM relatos_diarios_acolhidos WHERE idRelato = ?');
    }

    public function prepararInsercaoMencao(): PDOStatement
    {
        return $this->pdo->prepare('INSERT INTO relatos_diarios_acolhidos (idRelato, acolhido_id) VALUES (?, ?)');
    }

    public function prepararConsultaRelatoComIdRelatoPorIdRelatoComBloqueio(): PDOStatement
    {
        return $this->pdo->prepare('SELECT idRelato, data_relato, criado_em, conteudo, periodo, autorLogin_idLogin FROM relatos_diarios WHERE idRelato = ? FOR UPDATE');
    }

    public function prepararConsultaMencaoComAcolhidoIdPorIdRelato(): PDOStatement
    {
        return $this->pdo->prepare('SELECT acolhido_id FROM relatos_diarios_acolhidos WHERE idRelato = ?');
    }

    public function prepararAtualizacaoRelatoDePeriodo(): PDOStatement
    {
        return $this->pdo->prepare('UPDATE relatos_diarios SET periodo = ?, conteudo = ?, editado_em = ?, editado_por_login = ? WHERE idRelato = ? AND autorLogin_idLogin = ?');
    }

    public function prepararInsercaoRelato(): PDOStatement
    {
        return $this->pdo->prepare('INSERT INTO relatos_diarios (data_relato, periodo, conteudo, autorLogin_idLogin, criado_em) VALUES (?, ?, ?, ?, ?)');
    }

    public function prepararConsultaRelatoComDataRelatoPorIdRelato(): PDOStatement
    {
        return $this->pdo->prepare('SELECT data_relato FROM relatos_diarios WHERE idRelato = ?');
    }
}

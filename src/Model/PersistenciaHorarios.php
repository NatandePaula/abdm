<?php

namespace Abdm\Model;

use PDO;
use PDOStatement;

final class PersistenciaHorarios
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function prepararInsercaoPeriodo(): PDOStatement
    {
        return $this->pdo->prepare("INSERT INTO periodos (nome, ordem) VALUES (?, ?)");
    }

    public function prepararAtualizacaoPeriodoDeNome(): PDOStatement
    {
        return $this->pdo->prepare("UPDATE periodos SET nome = ?, ordem = ? WHERE id = ?");
    }

    public function prepararConsultaHorarioDaPrescricaoPorPeriodoId(): PDOStatement
    {
        return $this->pdo->prepare("SELECT COUNT(*) FROM prescricao_horarios WHERE periodo_id = ?");
    }

    public function prepararConsultaHorarioNomeadoPorPeriodoId(): PDOStatement
    {
        return $this->pdo->prepare("SELECT COUNT(*) FROM horarios_nomeados WHERE periodo_id = ?");
    }

    public function prepararExclusaoPeriodo(): PDOStatement
    {
        return $this->pdo->prepare("DELETE FROM periodos WHERE id = ?");
    }

    public function prepararInsercaoHorarioNomeado(): PDOStatement
    {
        return $this->pdo->prepare("INSERT INTO horarios_nomeados (nome, horario, periodo_id) VALUES (?, ?, ?)");
    }

    public function prepararAtualizacaoHorarioNomeadoDeNome(): PDOStatement
    {
        return $this->pdo->prepare("UPDATE horarios_nomeados SET nome = ?, horario = ?, periodo_id = ? WHERE id = ?");
    }

    public function prepararConsultaHorarioDaPrescricaoComHorarioPorHorario(): PDOStatement
    {
        return $this->pdo->prepare("
            SELECT COUNT(ph.id) FROM prescricao_horarios ph
            JOIN prescricoes p ON ph.prescricao_id = p.id
            WHERE ph.horario = (SELECT horario FROM horarios_nomeados WHERE id = ?) AND p.status = 'ativa'
        ");
    }

    public function prepararExclusaoHorarioNomeado(): PDOStatement
    {
        return $this->pdo->prepare("DELETE FROM horarios_nomeados WHERE id = ?");
    }
}

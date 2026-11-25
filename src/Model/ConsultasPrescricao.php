<?php

namespace Abdm\Model;

use PDO;

final class ConsultasPrescricao
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function horariosDasPrescricoes(array $ids): array
    {
        $marcadores = implode(',', array_fill(0, count($ids), '?'));
        
        $sql = "SELECT * FROM prescricao_horarios WHERE prescricao_id IN ($marcadores) ORDER BY horario";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($ids);
        return $stmt->fetchAll();
    }

    public function nomesDeHorarios(): array
    {
        return $this->pdo->query("SELECT horario, nome FROM horarios_nomeados")->fetchAll(PDO::FETCH_KEY_PAIR);
    }

    public function periodos(): array
    {
        return $this->pdo->query("SELECT * FROM periodos ORDER BY ordem")->fetchAll();
    }

    public function paraImpressao(int $id): array
    {
        $stmt = $this->pdo->prepare("
    SELECT p.*, m.nome_comercial, m.apresentacao, m.unidade_base
    FROM prescricoes p
    JOIN medicamentos m ON p.medicamento_id = m.id
    WHERE p.acolhido_id = ? AND p.status = 'ativa'
    ORDER BY m.nome_comercial
");
        $stmt->execute([$id]);
        return $stmt->fetchAll();
    }

    public function horariosPorPeriodo(array $parametros): array
    {
        $marcadores = implode(',', array_fill(0, count($parametros), '?'));
        $stmt = $this->pdo->prepare("
        SELECT ph.*, p.id AS periodo_id, p.nome AS periodo_nome, p.ordem AS periodo_ordem
        FROM prescricao_horarios ph
        JOIN periodos p ON ph.periodo_id = p.id
        WHERE prescricao_id IN ($marcadores) 
        ORDER BY p.ordem, ph.horario
    ");
        $stmt->execute($parametros);
        return $stmt->fetchAll();
    }

    public function suspensasEFinalizadas(int $id): array
    {
        $stmt = $this->pdo->prepare("
    SELECT 
        p.*, 
        m.nome_comercial 
    FROM prescricoes p
    JOIN medicamentos m ON p.medicamento_id = m.id
    WHERE p.status = 'suspensa' AND p.acolhido_id = ?
    ORDER BY p.data_status_alterado DESC
");
        $stmt->execute([$id]);
        return $stmt->fetchAll();
    }

    public function horariosNomeados(): array
    {
        return $this->pdo->query("
    SELECT hn.*, p.nome as periodo_nome 
    FROM horarios_nomeados hn
    JOIN periodos p ON hn.periodo_id = p.id
    ORDER BY p.ordem, hn.horario
")->fetchAll();
    }

    public function ativas(int $id): array
    {
        $stmt = $this->pdo->prepare("
    SELECT p.*, m.nome_comercial, m.apresentacao, m.unidade_base
    FROM prescricoes p 
    JOIN medicamentos m ON p.medicamento_id = m.id
    WHERE p.acolhido_id = ? AND p.status = 'ativa'
    ORDER BY p.status, p.data_inicio DESC
");
        $stmt->execute([$id]);
        return $stmt->fetchAll();
    }

    public function horariosParaGerenciar(): array
    {
        return $this->pdo->query("
    SELECT hn.*, p.nome as periodo_nome 
    FROM horarios_nomeados hn
    JOIN periodos p ON hn.periodo_id = p.id
    ORDER BY p.ordem, hn.horario
")->fetchAll();
    }
}

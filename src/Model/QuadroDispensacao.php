<?php

namespace Abdm\Model;

use PDO;

final class QuadroDispensacao
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function prescricoesAtivas(string $dataSelecionada, bool $aceitaEquivalencia = false): array
    {
        $colunaEquivalencia = $aceitaEquivalencia ? ', m.gotas_por_ml' : '';
        $sql = "
            SELECT 
                p.id as prescricao_id,
                p.tipo_posologia, p.intervalo_horas, p.intervalo_inicio, 
                p.quantidade_por_dose as qtd_intervalo, p.unidade_dose as unidade_intervalo,
                a.id as acolhido_id,
                m.id as medicamento_id, m.nome_comercial, m.unidade_base {$colunaEquivalencia}
            FROM prescricoes p
            JOIN acolhido a ON p.acolhido_id = a.id
            JOIN medicamentos m ON p.medicamento_id = m.id
            WHERE p.status = 'ativa'
              AND a.status = 'ativo'
              AND p.data_inicio <= ?
              AND (p.data_fim IS NULL OR p.data_fim >= ?)
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$dataSelecionada, $dataSelecionada]);
        return $stmt->fetchAll();
    }

    public function dispensacoesDoDia(string $dataSelecionada): array
    {
        $sql = "
            SELECT 
                d.prescricao_id, d.horario_previsto, d.horario_dispensado, d.usuario_responsavel, u.nome AS nome_responsavel, d.status, d.observacao
            FROM dispensacoes d
            LEFT JOIN login l ON l.login = d.usuario_responsavel
            LEFT JOIN usuario u ON u.idUsuario = (
                SELECT MIN(u2.idUsuario)
                FROM usuario u2
                WHERE u2.Login_idLogin = l.idLogin
            )
            WHERE d.data_dispensacao = :data_selecionada
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['data_selecionada' => $dataSelecionada]);
        return $stmt->fetchAll();
    }

    public function horariosDasPrescricoes(array $parametros): array
    {
        $marcadores = implode(',', array_fill(0, count($parametros), '?'));
        $stmt = $this->pdo->prepare("SELECT id, prescricao_id, horario, quantidade_por_dose, unidade_dose FROM prescricao_horarios WHERE prescricao_id IN ($marcadores)");
        $stmt->execute($parametros);
        return $stmt->fetchAll();
    }
}

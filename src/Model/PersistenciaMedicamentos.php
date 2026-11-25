<?php

namespace Abdm\Model;

use PDO;
use PDOStatement;

final class PersistenciaMedicamentos
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function prepararInsercaoMedicamento(): PDOStatement
    {
        return $this->pdo->prepare("INSERT INTO medicamentos (nome_comercial, principio_ativo, apresentacao, unidade_base) VALUES (?, ?, ?, ?)");
    }

    public function consultarColunaComCOLUMNTYPEPorTableSchema(): PDOStatement
    {
        return $this->pdo->query("SELECT COLUMN_TYPE FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'prescricoes' AND column_name = 'tipo_uso'");
    }

    public function prepararInsercaoPrescricao(): PDOStatement
    {
        return $this->pdo->prepare("INSERT INTO prescricoes (acolhido_id, medicamento_id, tipo_posologia, doses_por_dia, intervalo_horas, intervalo_inicio, quantidade_por_dose, unidade_dose, via_administracao, tipo_uso, data_inicio, data_fim, medico_responsavel, observacoes, instrucao_posologia) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    }

    public function prepararInsercaoHorarioDaPrescricao(): PDOStatement
    {
        return $this->pdo->prepare("INSERT INTO prescricao_horarios (prescricao_id, horario, periodo_id, quantidade_por_dose, unidade_dose) VALUES (?, ?, ?, ?, ?)");
    }

    public function prepararAtualizacaoPrescricaoDeMedicamentoId(): PDOStatement
    {
        return $this->pdo->prepare("UPDATE prescricoes SET
            medicamento_id = ?, tipo_posologia = ?, doses_por_dia = ?, intervalo_horas = ?, intervalo_inicio = ?,
            quantidade_por_dose = ?, unidade_dose = ?, via_administracao = ?, tipo_uso = ?,
            data_inicio = ?, data_fim = ?, medico_responsavel = ?, observacoes = ?, instrucao_posologia = ?
            WHERE id = ?");
    }

    public function prepararAtualizacaoPrescricaoDeStatus(): PDOStatement
    {
        return $this->pdo->prepare("UPDATE prescricoes SET status = ?, data_status_alterado = NOW(), motivo_suspensao = ? WHERE id = ?");
    }

    public function prepararExclusaoPrescricao(): PDOStatement
    {
        return $this->pdo->prepare("DELETE FROM prescricoes WHERE id = ?");
    }
}

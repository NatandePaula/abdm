<?php

namespace Abdm\Model;

use PDO;

final class PainelEstoque
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function consultar(): array
    {
        $stmtAcolhidos = $this->pdo->query("SELECT id, nome FROM acolhido WHERE status = 'ativo' ORDER BY nome ASC");
        $acolhidosAtivos = $stmtAcolhidos->fetchAll();

        $medicamentos = $this->pdo->query("SELECT * FROM medicamentos ORDER BY principio_ativo ASC, apresentacao ASC")->fetchAll();

        $saldoPorMedicamento = $this->pdo->query("
            SELECT medicamento_id, SUM(quantidade_atual_base) as total
            FROM medicamentos_estoque
            GROUP BY medicamento_id
        ")->fetchAll(PDO::FETCH_KEY_PAIR);

        $consumoPorMedicamento = $this->pdo->query("
            SELECT
                p.medicamento_id,
                SUM(
                    CASE
                        WHEN p.tipo_posologia = 'intervalo' AND p.intervalo_horas > 0
                            AND p.unidade_dose = m.unidade_base
                            AND (m.unidade_base = 'ml' OR MOD(p.quantidade_por_dose, 1) = 0
                                OR (m.unidade_base = 'comprimido' AND MOD(p.quantidade_por_dose, 0.50) = 0))
                            THEN p.quantidade_por_dose * (24 / p.intervalo_horas)
                        WHEN p.tipo_posologia = 'horario_fixo' THEN COALESCE(h.consumo_diario_fixo, 0)
                        ELSE 0
                    END
                ) as consumo_total
            FROM prescricoes p
            LEFT JOIN (
                SELECT ph.prescricao_id, SUM(ph.quantidade_por_dose) as consumo_diario_fixo
                FROM prescricao_horarios ph
                JOIN prescricoes p2 ON p2.id = ph.prescricao_id
                JOIN medicamentos m2 ON m2.id = p2.medicamento_id
                WHERE ph.unidade_dose = m2.unidade_base
                  AND (m2.unidade_base = 'ml' OR MOD(ph.quantidade_por_dose, 1) = 0
                      OR (m2.unidade_base = 'comprimido' AND MOD(ph.quantidade_por_dose, 0.50) = 0))
                GROUP BY ph.prescricao_id
            ) h ON p.id = h.prescricao_id
            JOIN acolhido a ON p.acolhido_id = a.id
            JOIN medicamentos m ON m.id = p.medicamento_id
            WHERE p.status = 'ativa'
              AND a.status = 'ativo'
              AND p.tipo_uso = 'continuo'
              AND p.data_inicio <= CURDATE()
              AND (p.data_fim IS NULL OR p.data_fim >= CURDATE())
            GROUP BY p.medicamento_id
        ")->fetchAll(PDO::FETCH_KEY_PAIR);

        $acolhidosPorMedicamento = $this->pdo->query("
            SELECT
                p.medicamento_id,
                GROUP_CONCAT(DISTINCT a.nome ORDER BY a.nome SEPARATOR ', ') as nomes_acolhidos
            FROM prescricoes p
            JOIN acolhido a ON p.acolhido_id = a.id
            WHERE p.status = 'ativa'
              AND a.status = 'ativo'
              AND p.data_inicio <= CURDATE()
              AND (p.data_fim IS NULL OR p.data_fim >= CURDATE())
            GROUP BY p.medicamento_id
        ")->fetchAll(PDO::FETCH_KEY_PAIR);


        $itensEstoque = [];
        foreach ($medicamentos as $medicamento) {
            $medicamentoId = $medicamento['id'];

            $totalEstoque = $saldoPorMedicamento[$medicamentoId] ?? 0;
            $consumoDiario = $consumoPorMedicamento[$medicamentoId] ?? 0;
            $nomesAcolhidos = $acolhidosPorMedicamento[$medicamentoId] ?? null;

            $previsaoDias = 0;
            if ($consumoDiario > 0 && $totalEstoque > 0) {
                $previsaoDias = floor($totalEstoque / $consumoDiario);
            }

            $nivelAlerta = '';
            if (($totalEstoque <= 0 && $consumoDiario > 0) || ($previsaoDias > 0 && $previsaoDias <= 7)) {
                $nivelAlerta = 'table-danger';
            } elseif ($previsaoDias > 0 && $previsaoDias <= 15) {
                $nivelAlerta = 'table-warning';
            }

            $itensEstoque[$medicamentoId] = [
                'dados' => $medicamento,
                'total' => $totalEstoque,
                'consumo_diario' => $consumoDiario,
                'previsao_dias' => $previsaoDias,
                'alerta' => $nivelAlerta,
                'nomes_acolhidos' => $nomesAcolhidos
            ];
        }

        return [
            'acolhidosAtivos' => $acolhidosAtivos,
            'itensEstoque' => $itensEstoque,
        ];
    }
}

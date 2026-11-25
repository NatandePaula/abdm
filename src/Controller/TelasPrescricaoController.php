<?php

namespace Abdm\Controller;

use PDO;

final class TelasPrescricaoController
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function atualizarPrescricoesExpiradas(): void
    {
        (new \Abdm\Model\HistoricoSaude($this->pdo))->saudeFinalizarPrescricoesExpiradas();
    }

    public function acolhidosParaPrescricao(): array
    {
        return (new \Abdm\Model\Acolhidos($this->pdo))->selecaoParaPrescricoes();
    }

    public function quadroDoAcolhido(int $id): ?array
    {
        $acolhido = (new \Abdm\Model\Acolhidos($this->pdo))->nome($id);
        if (!$acolhido) {
            return null;
        }

        $consultas = new \Abdm\Model\ConsultasPrescricao($this->pdo);
        $prescricoes = $consultas->ativas($id);
        $horariosPorPrescricao = [];
        if ($prescricoes) {
            foreach ($consultas->horariosDasPrescricoes(array_column($prescricoes, 'id')) as $horario) {
                $horariosPorPrescricao[$horario['prescricao_id']][] = $horario;
            }
        }

        return [
            'acolhido' => $acolhido,
            'prescricoes' => $prescricoes,
            'horariosPorPrescricao' => $horariosPorPrescricao,
            'periodos' => $consultas->periodos(),
            'horariosNomeados' => $consultas->horariosNomeados(),
            'medicamentos' => (new \Abdm\Model\Medicamentos($this->pdo))->selecao(),
        ];
    }

    public function gerenciarHorarios(): array
    {
        $pdo = $this->pdo;
        $periodosDisponiveis = (new \Abdm\Model\ConsultasPrescricao($pdo))->periodos();

        $horariosNomeados = (new \Abdm\Model\ConsultasPrescricao($pdo))->horariosParaGerenciar();

        return [
            'periodos_disponiveis' => $periodosDisponiveis ?? null,
            'horarios_nomeados' => $horariosNomeados ?? null,
        ];
    }

    public function gerenciarPeriodos(): array
    {
        $pdo = $this->pdo;
        $periodos = (new \Abdm\Model\ConsultasPrescricao($pdo))->periodos();

        return [
            'periodos' => $periodos ?? null,
        ];
    }

    public function historico(): array
    {
        $pdo = $this->pdo;
        $idAcolhido = (int)$_GET['id_acolhido'];

        // Busca dados do acolhido
        $acolhido = (new \Abdm\Model\Acolhidos($pdo))->nome($idAcolhido);

        if (!$acolhido) {
            echo "<div class='container my-5 alert alert-danger'>Acolhido não encontrado.</div>";
            require_once ROOT_DIR . '/src/View/templates/includes/rodapeInterno.inc.php';
            exit;
        }

        // Busca prescrições suspensas para este acolhido
        $prescricoesSuspensas = (new \Abdm\Model\ConsultasPrescricao($pdo))->suspensasEFinalizadas($idAcolhido);

        return [
            'id_acolhido' => $idAcolhido ?? null,
            'acolhido' => $acolhido ?? null,
            'prescricoes_suspensas' => $prescricoesSuspensas ?? null,
        ];
    }

    public function paraImpressao(): array
    {
        $pdo = $this->pdo;
        // 2. Validação e busca de dados
        if (!isset($_GET['id_acolhido']) || !isset($_GET['tipo'])) {
            die("Parâmetros inválidos.");
        }
        $idAcolhido = (int)$_GET['id_acolhido'];
        $tipoImpressao = in_array($_GET['tipo'], ['plano', 'receituario']) ? $_GET['tipo'] : 'plano';

        $acolhido = (new \Abdm\Model\Acolhidos($pdo))->nome($idAcolhido);
        if (!$acolhido) {
            die("Acolhido não encontrado.");
        }

        $periodos = (new \Abdm\Model\ConsultasPrescricao($pdo))->periodos();

        $prescricoesAtivas = (new \Abdm\Model\ConsultasPrescricao($pdo))->paraImpressao($idAcolhido);

        $horarios_por_prescricao = [];
        $prescricaoIds = array_column($prescricoesAtivas, 'id');

        if (!empty($prescricaoIds)) {
            $todosHorarios = (new \Abdm\Model\ConsultasPrescricao($pdo))->horariosPorPeriodo($prescricaoIds);
            foreach ($todosHorarios as $horario) {
                $horarios_por_prescricao[$horario['prescricao_id']][] = $horario;
            }
        }

        // Agrupar as medicações pelo período e horário exato da dose
        $medicacoesPorHorario = [];
        $medicacoesPorIntervalo = [];

        foreach ($prescricoesAtivas as $presc) {
            if ($presc['tipo_posologia'] === 'intervalo') {
                $medicacoesPorIntervalo[] = $presc;
            }
            elseif ($presc['tipo_posologia'] === 'horario_fixo' && isset($horarios_por_prescricao[$presc['id']])) {
                foreach ($horarios_por_prescricao[$presc['id']] as $h) {
                    $horarioTime = date('H:i', strtotime($h['horario']));
                    $chaveHorario = (int)$h['periodo_id'] . '|' . $horarioTime;

                    if (!isset($medicacoesPorHorario[$chaveHorario])) {
                        $medicacoesPorHorario[$chaveHorario] = [
                            'periodo_id' => (int)$h['periodo_id'],
                            'periodo_nome' => $h['periodo_nome'],
                            'periodo_ordem' => (int)$h['periodo_ordem'],
                            'horario' => $horarioTime,
                            'medicamentos' => [],
                        ];
                    }

                    $medicacoesPorHorario[$chaveHorario]['medicamentos'][] = [
                        'nome' => $presc['nome_comercial'],
                        'apresentacao' => $presc['apresentacao'],
                        'dose' => \Abdm\Model\HistoricoSaude::saudeFormatarDose($h['quantidade_por_dose'], $h['unidade_dose'])
                    ];
                }
            }
        }

        usort($medicacoesPorHorario, static function (array $a, array $b): int {
            $ordemPeriodo = $a['periodo_ordem'] <=> $b['periodo_ordem'];
            return $ordemPeriodo !== 0 ? $ordemPeriodo : strcmp($a['horario'], $b['horario']);
        });

        // 3. Montar o HTML do conteúdo para o PDF

        return [
            'tipo_impressao' => $tipoImpressao ?? null,
            'acolhido' => $acolhido ?? null,
            'periodos' => $periodos ?? null,
            'prescricoes_ativas' => $prescricoesAtivas ?? null,
            'horarios_por_prescricao' => $horarios_por_prescricao ?? null,
            'medicacoes_por_horario' => $medicacoesPorHorario ?? null,
            'medicacoes_por_intervalo' => $medicacoesPorIntervalo ?? null,
            'presc' => $presc ?? null,
            'h' => $h ?? null,
        ];
    }
}

<?php

namespace Abdm\Controller;

use PDO;
use DateTimeImmutable;
use DateTime;

final class TelaDispensacaoController
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function atualizarPrescricoesExpiradas(): void
    {
        (new \Abdm\Model\HistoricoSaude($this->pdo))->saudeFinalizarPrescricoesExpiradas();
    }

    public function preparar(): array
    {
        $pdo = $this->pdo;
        $dataSelecionada = $_GET['data'] ?? date('Y-m-d');
        if (!is_string($dataSelecionada)) {
            http_response_code(400);
            exit('Data inválida.');
        }
        $dataValidada = DateTimeImmutable::createFromFormat('!Y-m-d', $dataSelecionada);
        if (!$dataValidada || $dataValidada->format('Y-m-d') !== $dataSelecionada) {
            http_response_code(400);
            exit('Data inválida.');
        }
        $acolhidosAtivos = (new \Abdm\Model\Acolhidos($pdo))->selecaoAtivos();

        // Buscar horários nomeados para mapeamento (TIME => NOME)
        $horariosNomeadosMap = (new \Abdm\Model\ConsultasPrescricao($pdo))->nomesDeHorarios();

        // 2. Buscar todas as prescrições ativas (horário fixo e intervalo)
        $estoque = new \Abdm\Model\EstoqueMedicamentos($pdo);
        $prescricoesAtivas = (new \Abdm\Model\QuadroDispensacao($pdo))->prescricoesAtivas($dataSelecionada, $estoque->aceitaEquivalenciaNoCadastro());

        $horariosFixos = [];
        $prescricaoIds = array_column($prescricoesAtivas, 'prescricao_id');
        if (!empty($prescricaoIds)) {
            $horariosFixosRaw = (new \Abdm\Model\QuadroDispensacao($pdo))->horariosDasPrescricoes($prescricaoIds);
            foreach($horariosFixosRaw as $h) {
                $horariosFixos[$h['prescricao_id']][] = $h;
            }
        }

        // 3. Gerar todos os "eventos de administração" para o dia
        $eventos = [];
        $todosHorariosPrevistos = [];

        foreach ($prescricoesAtivas as $presc) {
            if ($presc['tipo_posologia'] === 'horario_fixo') {
                if (isset($horariosFixos[$presc['prescricao_id']])) {
                    foreach ($horariosFixos[$presc['prescricao_id']] as $h) {
                        $horarioPrevisto = date('H:i', strtotime($h['horario']));
                        $nomeHorario = $horariosNomeadosMap[$h['horario']] ?? null;
                        $eventos[] = [
                            'horario_previsto' => $horarioPrevisto,
                            'horario_id' => $h['id'], // ID real do horário
                            'quantidade_por_dose' => $h['quantidade_por_dose'],
                            'unidade_dose' => $h['unidade_dose'],
                            'nome_horario' => $nomeHorario,
                            'prescricao' => $presc
                        ];
                        $todosHorariosPrevistos[$horarioPrevisto] = true;
                    }
                }
            } elseif ($presc['tipo_posologia'] === 'intervalo' && $presc['intervalo_horas'] > 0) {
                $startTime = strtotime($presc['intervalo_inicio']);
                $intervalSeconds = $presc['intervalo_horas'] * 3600;

                for ($i = 0; $i < 24 / $presc['intervalo_horas']; $i++) {
                    $horarioTs = $startTime + ($i * $intervalSeconds);
                    // Garante que o horário calculado ainda está no mesmo dia (ou até 24h depois do início)
                    if ($horarioTs < strtotime('+1 day', $startTime)) {
                        $horarioPrevisto = date('H:i', $horarioTs);
                        $eventos[] = [
                            'horario_previsto' => $horarioPrevisto,
                            'horario_id' => null, // Não há ID real para intervalos
                            'quantidade_por_dose' => $presc['qtd_intervalo'],
                            'unidade_dose' => $presc['unidade_intervalo'],
                            'nome_horario' => null,
                            'prescricao' => $presc
                        ];
                        $todosHorariosPrevistos[$horarioPrevisto] = true;
                    }
                }
            }
        }

        // 4. Obter horários únicos, ordená-los e criar nomes de exibição
        $horariosDasLinhas = array_keys($todosHorariosPrevistos);

        $horarioDisplayNames = [];
        foreach ($horariosDasLinhas as $horarioTime) {
            $displayName = $horarioTime;
            // Encontra o primeiro nome de horário nomeado para este horário, se houver
            foreach($eventos as $evento) {
                if ($evento['horario_previsto'] === $horarioTime && $evento['nome_horario']) {
                    $displayName = $evento['nome_horario'];
                    break;
                }
            }
            $horarioDisplayNames[$horarioTime] = $displayName;
        }
        sort($horariosDasLinhas);

        // 5. Buscar dispensações já realizadas na data
        $dispensacoesDoDiaRaw = (new \Abdm\Model\QuadroDispensacao($pdo))->dispensacoesDoDia($dataSelecionada);

        // Mapeia dispensações para busca rápida (chave: "prescricao_id-HH:MM")
        $dispensacoesDoDia = [];
        foreach ($dispensacoesDoDiaRaw as $disp) {
            $key = $disp['prescricao_id'] . '-' . date('H:i', strtotime($disp['horario_previsto']));
            $dispensacoesDoDia[$key] = $disp;
        }

        // 6. Montar o quadro de dispensação final (Horário como linha, Acolhido como coluna)
        $quadroDispensacao = [];
        foreach ($eventos as $evento) {
            $horarioPrevisto = $evento['horario_previsto'];
            $acolhidoId = $evento['prescricao']['acolhido_id'];
            $prescricaoId = $evento['prescricao']['prescricao_id'];

            // Verifica se este evento já foi dispensado
            $dispKey = $prescricaoId . '-' . $horarioPrevisto;
            $dispensadoInfo = $dispensacoesDoDia[$dispKey] ?? null;

            // Agrupa por horário, depois por acolhido
            $quadroDispensacao[$horarioPrevisto][$acolhidoId][] = [
                'prescricao' => $evento['prescricao'],
                'horario_id' => $evento['horario_id'], // ID real (horário fixo) ou null (intervalo)
                'quantidade_por_dose' => $evento['quantidade_por_dose'],
                'unidade_dose' => $evento['unidade_dose'],
                'nome_horario' => $evento['nome_horario'],
                'exige_equivalencia' => $evento['unidade_dose'] === 'gota'
                    && $evento['prescricao']['unidade_base'] === 'ml'
                    && $estoque->equivalenciaGotasPorMl((int)$evento['prescricao']['medicamento_id'], $evento['prescricao']) === null,
                'dispensado' => $dispensadoInfo
            ];
        }

        $pendenciasPorHorario = [];
        foreach ($quadroDispensacao as $horario => $acolhidosNoHorario) {
            foreach ($acolhidosNoHorario as $itensDoAcolhido) {
                foreach ($itensDoAcolhido as $item) {
                    if (empty($item['dispensado']) && !$item['exige_equivalencia']) {
                        $pendenciasPorHorario[$horario] = ($pendenciasPorHorario[$horario] ?? 0) + 1;
                    }
                }
            }
        }

        return [
            'data_selecionada' => $dataSelecionada ?? null,
            'acolhidos_ativos' => $acolhidosAtivos ?? null,
            'presc' => $presc ?? null,
            'horarios_das_linhas' => $horariosDasLinhas ?? null,
            'horario_display_names' => $horarioDisplayNames ?? null,
            'display_name' => $displayName ?? null,
            'disp' => $disp ?? null,
            'quadro_dispensacao' => $quadroDispensacao ?? null,
            'pendencias_por_horario' => $pendenciasPorHorario ?? null,
            'horario' => $horario ?? null,
            'item' => $item ?? null,
        ];
    }
}

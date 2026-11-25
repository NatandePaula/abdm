<?php

namespace Abdm\Model;

use DateTimeImmutable;
use PDO;
use Throwable;
use RuntimeException;

final class HistoricoSaude
{
    private ?bool $tabelaDisponivel = null;

    public function __construct(private readonly PDO $pdo)
    {
    }

    public function saudeRegistrosTabelaDisponivel(): bool
    {
        if ($this->tabelaDisponivel === null) {
            $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?');
            $stmt->execute(['saude_acolhido_registros']);
            $this->tabelaDisponivel = (int)$stmt->fetchColumn() > 0;
        }
        return $this->tabelaDisponivel;
    }

    public function saudeResumoPrescricao(int $prescricaoId): ?array
    {
        $pdo = $this->pdo;
        $stmt = $pdo->prepare(
            'SELECT p.id, p.acolhido_id, a.nome AS acolhido_nome, p.medicamento_id,
                m.nome_comercial, m.principio_ativo, m.apresentacao,
                p.tipo_posologia, p.intervalo_horas, p.intervalo_inicio,
                p.quantidade_por_dose, p.unidade_dose, p.via_administracao,
                p.tipo_uso, p.data_inicio, p.data_fim, p.medico_responsavel,
                p.observacoes, p.instrucao_posologia, p.status, p.motivo_suspensao
         FROM prescricoes p
         JOIN acolhido a ON a.id = p.acolhido_id
         JOIN medicamentos m ON m.id = p.medicamento_id
         WHERE p.id = ?'
        );
        $stmt->execute([$prescricaoId]);
        $prescricao = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$prescricao) {
            return null;
        }

        $horarios = [];
        if ($prescricao['tipo_posologia'] === 'horario_fixo') {
            $stmtHorarios = $pdo->prepare('SELECT horario, quantidade_por_dose, unidade_dose FROM prescricao_horarios WHERE prescricao_id = ? ORDER BY horario, id');
            $stmtHorarios->execute([$prescricaoId]);
            foreach ($stmtHorarios->fetchAll(PDO::FETCH_ASSOC) as $horario) {
                $horarios[] = substr((string)$horario['horario'], 0, 5)
                    . ' — ' . self::saudeFormatarDose($horario['quantidade_por_dose'] ?? null, $horario['unidade_dose'] ?? '');
            }
        }

        return self::saudeFormatarResumoPrescricao($prescricao, $horarios);
    }

    public static function saudeFormatarDose($quantidade, $unidade): string
    {
        if (!is_numeric($quantidade) || (float)$quantidade <= 0) {
            return 'Dose não informada';
        }
        $valor = (float)$quantidade;
        $numero = rtrim(rtrim(number_format($valor, 2, '.', ''), '0'), '.');
        $unidade = trim((string)$unidade);
        if ($unidade === '') {
            return $numero;
        }

        $formas = [
            'comprimido' => ['comprimido', 'comprimidos'],
            'capsula' => ['cápsula', 'cápsulas'],
            'ml' => ['ml', 'ml'],
            'gota' => ['gota', 'gotas'],
            'unidade' => ['unidade', 'unidades'],
        ];
        $unidades = $formas[$unidade] ?? [$unidade, $unidade . 's'];
        return $numero . ' ' . $unidades[$valor === 1.0 ? 0 : 1];
    }

    public static function saudeFormatarResumoPrescricao(array $prescricao, array $horarios = []): array
    {
        $dataFormatada = static function ($valor): string {
            if (!is_string($valor) || $valor === '' || $valor === '0000-00-00') {
                return 'Não informada';
            }
            $data = DateTimeImmutable::createFromFormat('!Y-m-d', substr($valor, 0, 10));
            return $data ? $data->format('d/m/Y') : $valor;
        };

        if (($prescricao['tipo_posologia'] ?? '') === 'intervalo') {
            $intervaloHoras = (int)($prescricao['intervalo_horas'] ?? 0);
            $unidadeIntervalo = $intervaloHoras === 1 ? 'hora' : 'horas';
            $posologia = 'A cada ' . $intervaloHoras . ' ' . $unidadeIntervalo . ', a partir de '
                . substr((string)($prescricao['intervalo_inicio'] ?? ''), 0, 5) . ' — '
                . self::saudeFormatarDose($prescricao['quantidade_por_dose'] ?? null, $prescricao['unidade_dose'] ?? '');
        } else {
            $posologia = $horarios ? implode('; ', $horarios) : 'Horários não cadastrados';
        }

        $vigencia = $dataFormatada($prescricao['data_inicio'] ?? null);
        if (!empty($prescricao['data_fim'])) {
            $vigencia .= ' a ' . $dataFormatada($prescricao['data_fim']);
        } else {
            $vigencia .= ' sem data final';
        }

        $status = [
            'ativa' => 'Ativa',
            'suspensa' => 'Suspensa',
            'finalizada' => 'Finalizada',
        ][(string)($prescricao['status'] ?? '')] ?? (string)($prescricao['status'] ?? '');

        return [
            '_acolhido_id' => (int)($prescricao['acolhido_id'] ?? 0),
            'Acolhido' => (string)($prescricao['acolhido_nome'] ?? ''),
            'Medicamento' => (string)($prescricao['nome_comercial'] ?? ''),
            'Princípio ativo' => (string)($prescricao['principio_ativo'] ?? ''),
            'Apresentação' => (string)($prescricao['apresentacao'] ?? ''),
            'Posologia' => $posologia,
            'Via de administração' => (string)($prescricao['via_administracao'] ?? ''),
            'Tipo de uso' => Prescricao::prescricaoRotuloTipoUso($prescricao['tipo_uso'] ?? null),
            'Vigência' => $vigencia,
            'Prescritor' => (string)($prescricao['medico_responsavel'] ?? ''),
            'Instruções' => (string)($prescricao['instrucao_posologia'] ?? ''),
            'Observações' => (string)($prescricao['observacoes'] ?? ''),
            'Status atual' => $status,
            'Motivo da suspensão' => (string)($prescricao['motivo_suspensao'] ?? ''),
        ];
    }

    public function saudeRegistrarEventoMedicamento(int $acolhidoId,
        int $prescricaoId,
        string $tipo,
        string $titulo,
        string $descricao,
        array $detalhes,
        bool $registradoPeloSistema = false
    ): void {
        $pdo = $this->pdo;
        if (!$this->saudeRegistrosTabelaDisponivel()) {
            static $avisoRegistrado = false;
            if (!$avisoRegistrado) {
                error_log('Histórico de saúde indisponível: a tabela de registros não foi encontrada.');
                $avisoRegistrado = true;
            }
            return;
        }

        $tituloCortado = function_exists('mb_substr')
            ? mb_substr($titulo, 0, 180, 'UTF-8')
            : substr($titulo, 0, 180);
        $stmt = $pdo->prepare(
            'INSERT INTO saude_acolhido_registros
            (acolhido_id, tipo, data_evento, titulo, profissional, descricao, detalhes_json, prescricao_id, autor_login_id, criado_em)
         VALUES (?, ?, ?, ?, NULL, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $acolhidoId,
            $tipo,
            date('Y-m-d'),
            $tituloCortado,
            $descricao,
            json_encode($detalhes, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR),
            $prescricaoId,
            $registradoPeloSistema ? null : ((int)($_SESSION['id_login'] ?? 0) ?: null),
            date('Y-m-d H:i:s'),
        ]);
    }

    public function saudeFinalizarPrescricoesExpiradas(): int
    {
        $pdo = $this->pdo;
        if (!$this->saudeRegistrosTabelaDisponivel()) {
            return 0;
        }

        $stmtExpiradas = $pdo->query(
            "SELECT id FROM prescricoes
         WHERE status = 'ativa' AND data_fim IS NOT NULL AND data_fim < CURDATE()
         ORDER BY data_fim, id"
        );
        $idsExpirados = array_map('intval', $stmtExpiradas->fetchAll(PDO::FETCH_COLUMN));
        if (!$idsExpirados) {
            return 0;
        }

        $iniciouTransacao = !$pdo->inTransaction();
        if ($iniciouTransacao) {
            $pdo->beginTransaction();
        }

        $finalizadas = 0;
        try {
            $stmtFinalizar = $pdo->prepare(
                "UPDATE prescricoes SET status = 'finalizada', data_status_alterado = NOW()
             WHERE id = ? AND status = 'ativa' AND data_fim IS NOT NULL AND data_fim < CURDATE()"
            );

            foreach ($idsExpirados as $prescricaoId) {
                $snapshotAnterior = $this->saudeResumoPrescricao($prescricaoId);
                if (!$snapshotAnterior) {
                    continue;
                }

                $stmtFinalizar->execute([$prescricaoId]);
                if ($stmtFinalizar->rowCount() !== 1) {
                    continue;
                }

                $snapshotAtual = $this->saudeResumoPrescricao($prescricaoId);
                if (!$snapshotAtual) {
                    throw new RuntimeException('Não foi possível carregar a prescrição finalizada automaticamente.');
                }

                $this->saudeRegistrarEventoMedicamento((int)$snapshotAtual['_acolhido_id'],
                    $prescricaoId,
                    'prescricao_finalizada_automaticamente',
                    'Prescrição finalizada automaticamente: ' . $snapshotAtual['Medicamento'],
                    'A data final da prescrição foi atingida e o sistema encerrou o medicamento automaticamente.',
                    ['antes' => $snapshotAnterior, 'atual' => $snapshotAtual],
                    true
                );
                $finalizadas++;
            }

            if ($iniciouTransacao) {
                $pdo->commit();
            }
        } catch (Throwable $erro) {
            if ($iniciouTransacao && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $erro;
        }

        return $finalizadas;
    }
}

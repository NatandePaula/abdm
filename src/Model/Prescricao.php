<?php

declare(strict_types=1);

namespace Abdm\Model;

use PDO;
use InvalidArgumentException;

final class Prescricao
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public static function prescricaoValidarTipoUso(mixed $tipoUso): string
    {
        $tiposPermitidos = ['continuo', 'eventual', 'periodo_definido', 'se_necessario'];
        if (!is_string($tipoUso) || !in_array($tipoUso, $tiposPermitidos, true)) {
            throw new InvalidArgumentException('Selecione um tipo de uso válido para a prescrição.');
        }

        return $tipoUso;
    }

    public static function prescricaoTipoUsoAceitoPeloEnum(mixed $definicaoColuna, string $tipoUso): bool
    {
        $tiposPermitidos = ['continuo', 'eventual', 'periodo_definido', 'se_necessario'];
        return is_string($definicaoColuna)
            && in_array($tipoUso, $tiposPermitidos, true)
            && str_contains($definicaoColuna, "'" . $tipoUso . "'");
    }

    public static function prescricaoPrepararHorariosFixos(mixed $horarios): array
    {
        if (!is_array($horarios)) {
            throw new InvalidArgumentException('A lista de horários da prescrição é inválida.');
        }

        $horariosValidos = [];
        foreach ($horarios as $horario) {
            if (!is_array($horario)) {
                throw new InvalidArgumentException('Um dos horários da prescrição é inválido.');
            }

            $horaRecebida = $horario['hora'] ?? '';
            $periodoId = $horario['periodo_id'] ?? '';
            if (!is_string($horaRecebida) || (!is_string($periodoId) && !is_int($periodoId))) {
                throw new InvalidArgumentException('Um dos horários da prescrição é inválido.');
            }

            $hora = trim($horaRecebida);
            if ($hora === '' || $periodoId === '' || $periodoId === null) {
                continue;
            }

            if (!preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d(?::[0-5]\d)?$/D', $hora)) {
                throw new InvalidArgumentException('Informe um horário válido para cada dose fixa.');
            }

            $periodoIdTexto = (string)$periodoId;
            if (!preg_match('/^[1-9]\d*$/D', $periodoIdTexto)) {
                throw new InvalidArgumentException('Selecione um período válido para cada horário.');
            }

            $horariosValidos[] = [
                ...$horario,
                'hora' => $hora,
                'periodo_id' => (int)$periodoIdTexto,
            ];
        }

        return $horariosValidos;
    }

    public function prescricaoRemoverHorariosParaEdicao(int $prescricaoId): void
    {
        $pdo = $this->pdo;
        // As dispensações conservam o vínculo com a prescrição e a hora prevista.
        $stmt = $pdo->prepare(
            'UPDATE dispensacoes d JOIN prescricao_horarios h ON h.id = d.prescricao_horario_id '
            . 'SET d.prescricao_horario_id = NULL WHERE h.prescricao_id = ?'
        );
        $stmt->execute([$prescricaoId]);
        $pdo->prepare('DELETE FROM prescricao_horarios WHERE prescricao_id = ?')->execute([$prescricaoId]);
    }

    public static function prescricaoCalcularDosesPorDia(string $tipoPosologia, mixed $intervaloHoras, array $horarios): int
    {
        if ($tipoPosologia === 'horario_fixo') {
            return count($horarios);
        }

        if ($tipoPosologia === 'intervalo') {
            if (!is_int($intervaloHoras) && !is_string($intervaloHoras)) {
                throw new InvalidArgumentException('Informe o intervalo da prescrição em horas.');
            }
            $intervaloTexto = trim((string)$intervaloHoras);
            if (!preg_match('/^\d+$/D', $intervaloTexto)) {
                throw new InvalidArgumentException('Informe o intervalo da prescrição em horas.');
            }

            $intervalo = (int)$intervaloTexto;
            if ($intervalo < 1 || $intervalo > 24) {
                throw new InvalidArgumentException('O intervalo da prescrição deve estar entre 1 e 24 horas.');
            }

            // Corresponde ao número de ocorrências geradas pelo ciclo de 24 horas da dispensação.
            return intdiv(24 + $intervalo - 1, $intervalo);
        }

        throw new InvalidArgumentException('Selecione um tipo de posologia válido.');
    }

    public static function prescricaoRotuloTipoUso(mixed $tipoUso): string
    {
        return match ($tipoUso) {
            'continuo' => 'Contínuo',
            'eventual' => 'Eventual',
            'periodo_definido' => 'Período definido',
            'se_necessario' => 'Se necessário',
            default => 'Não informado',
        };
    }
}

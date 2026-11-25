<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class AuxiliaresSaudeTest extends TestCase
{
    public function testFormataDoseComUnidadesNoSingularEPlural(): void
    {
        self::assertSame('1 comprimido', \Abdm\Model\HistoricoSaude::saudeFormatarDose(1, 'comprimido'));
        self::assertSame('2 comprimidos', \Abdm\Model\HistoricoSaude::saudeFormatarDose(2, 'comprimido'));
        self::assertSame('15 gotas', \Abdm\Model\HistoricoSaude::saudeFormatarDose(15, 'gota'));
    }

    public function testInformaDoseNaoInformadaParaQuantidadesInvalidas(): void
    {
        self::assertSame('Dose não informada', \Abdm\Model\HistoricoSaude::saudeFormatarDose(null, 'gota'));
        self::assertSame('Dose não informada', \Abdm\Model\HistoricoSaude::saudeFormatarDose(0, 'comprimido'));
    }

    public function testFormataPosologiaPorIntervaloEVigenciaDaPrescricao(): void
    {
        $resumo = \Abdm\Model\HistoricoSaude::saudeFormatarResumoPrescricao([
            'tipo_posologia' => 'intervalo',
            'intervalo_horas' => 1,
            'intervalo_inicio' => '08:30:00',
            'quantidade_por_dose' => 1,
            'unidade_dose' => 'capsula',
            'data_inicio' => '2026-11-01',
            'data_fim' => '2026-11-05',
            'status' => 'suspensa',
        ]);

        self::assertSame('A cada 1 hora, a partir de 08:30 — 1 cápsula', $resumo['Posologia']);
        self::assertSame('01/11/2026 a 05/11/2026', $resumo['Vigência']);
        self::assertSame('Suspensa', $resumo['Status atual']);
    }

    public function testUsaOsHorariosInformadosNaPosologiaRegular(): void
    {
        $resumo = \Abdm\Model\HistoricoSaude::saudeFormatarResumoPrescricao(
            ['tipo_posologia' => 'horarios'],
            ['08:00 — 1 comprimido', '20:00 — 2 comprimidos']
        );

        self::assertSame('08:00 — 1 comprimido; 20:00 — 2 comprimidos', $resumo['Posologia']);
    }
}

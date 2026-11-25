<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class PrescricaoDosesTest extends TestCase
{
    public function testContaCadaHorarioFixoAceitoComoUmaDoseDiaria(): void
    {
        $horarios = \Abdm\Model\Prescricao::prescricaoPrepararHorariosFixos([
            ['hora' => '08:00', 'periodo_id' => '1'],
            ['hora' => '13:30', 'periodo_id' => '2'],
            ['hora' => '20:00', 'periodo_id' => '3'],
        ]);

        self::assertSame(3, \Abdm\Model\Prescricao::prescricaoCalcularDosesPorDia('horario_fixo', null, $horarios));
    }

    public function testIgnoraLinhaVaziaDeHorarioFixoComoFaziaOGravador(): void
    {
        $horarios = \Abdm\Model\Prescricao::prescricaoPrepararHorariosFixos([
            ['hora' => '08:00', 'periodo_id' => '1'],
            ['hora' => '', 'periodo_id' => ''],
        ]);

        self::assertCount(1, $horarios);
        self::assertSame(1, \Abdm\Model\Prescricao::prescricaoCalcularDosesPorDia('horario_fixo', null, $horarios));
    }

    public function testAceitaHorarioExistenteComSegundosNaEdicaoDaDose(): void
    {
        $horarios = \Abdm\Model\Prescricao::prescricaoPrepararHorariosFixos([
            ['hora' => '08:00:00', 'periodo_id' => '1', 'quantidade_por_dose' => '0.50'],
            ['hora' => '14:30:15', 'periodo_id' => '2', 'quantidade_por_dose' => '14.50'],
        ]);

        self::assertSame(['08:00:00', '14:30:15'], array_column($horarios, 'hora'));
        self::assertSame(['0.50', '14.50'], array_column($horarios, 'quantidade_por_dose'));
    }

    public function testRecusaHorarioComSegundosInvalidos(): void
    {
        $this->expectException(InvalidArgumentException::class);
        \Abdm\Model\Prescricao::prescricaoPrepararHorariosFixos([['hora' => '08:00:61', 'periodo_id' => '1']]);
    }

    public function testCalculaDosesPorIntervalosQueDividemOuNaoOdia(): void
    {
        self::assertSame(3, \Abdm\Model\Prescricao::prescricaoCalcularDosesPorDia('intervalo', '8', []));
        self::assertSame(4, \Abdm\Model\Prescricao::prescricaoCalcularDosesPorDia('intervalo', '7', []));
        self::assertSame(1, \Abdm\Model\Prescricao::prescricaoCalcularDosesPorDia('intervalo', '24', []));
    }

    public function testRecusaIntervaloForaDoCicloDeVinteEQuatroHoras(): void
    {
        $this->expectException(InvalidArgumentException::class);
        \Abdm\Model\Prescricao::prescricaoCalcularDosesPorDia('intervalo', '25', []);
    }

    public function testRecusaIntervaloAusenteOuNaoInteiro(): void
    {
        $this->expectException(InvalidArgumentException::class);
        \Abdm\Model\Prescricao::prescricaoCalcularDosesPorDia('intervalo', '8.5', []);
    }

    public function testRecusaTipoDePosologiaDesconhecido(): void
    {
        $this->expectException(InvalidArgumentException::class);
        \Abdm\Model\Prescricao::prescricaoCalcularDosesPorDia('dose_unica', null, []);
    }

    public function testValidaOsTiposDeUsoDoFormularioEValoresLegados(): void
    {
        self::assertSame('eventual', \Abdm\Model\Prescricao::prescricaoValidarTipoUso('eventual'));
        self::assertSame('periodo_definido', \Abdm\Model\Prescricao::prescricaoValidarTipoUso('periodo_definido'));
        self::assertSame('se_necessario', \Abdm\Model\Prescricao::prescricaoValidarTipoUso('se_necessario'));
        self::assertSame('Período definido', \Abdm\Model\Prescricao::prescricaoRotuloTipoUso('periodo_definido'));
        self::assertSame('Se necessário', \Abdm\Model\Prescricao::prescricaoRotuloTipoUso('se_necessario'));
    }

    public function testRecusaTipoDeUsoQueNaoExisteNoModeloDaAplicacao(): void
    {
        $this->expectException(InvalidArgumentException::class);
        \Abdm\Model\Prescricao::prescricaoValidarTipoUso('semanal');
    }

    public function testDetectaQuandoOEnumDoBancoAindaNaoFoiAmpliado(): void
    {
        self::assertFalse(\Abdm\Model\Prescricao::prescricaoTipoUsoAceitoPeloEnum("enum('continuo','eventual')", 'se_necessario'));
        self::assertTrue(\Abdm\Model\Prescricao::prescricaoTipoUsoAceitoPeloEnum(
            "enum('continuo','eventual','periodo_definido','se_necessario')",
            'se_necessario'
        ));
        self::assertFalse(\Abdm\Model\Prescricao::prescricaoTipoUsoAceitoPeloEnum("enum('continuo','eventual')", ''));
    }
}

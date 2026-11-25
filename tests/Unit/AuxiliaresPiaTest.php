<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class AuxiliaresPiaTest extends TestCase
{
    public function testReconheceDatasDeCalendarioValidasEInvalidas(): void
    {
        self::assertTrue(\Abdm\Model\Pia::piaDataValida('2024-02-29'));
        self::assertTrue(\Abdm\Model\Pia::piaDataValida('2026-11-17'));
        self::assertFalse(\Abdm\Model\Pia::piaDataValida('2025-02-29'));
        self::assertFalse(\Abdm\Model\Pia::piaDataValida('17/11/2026'));
    }

    public function testFormataSomenteDatasValidasParaExibicao(): void
    {
        self::assertSame('29/02/2024', \Abdm\Model\Pia::piaDataExibicao('2024-02-29'));
        self::assertSame('', \Abdm\Model\Pia::piaDataExibicao('2025-02-29'));
        self::assertSame('', \Abdm\Model\Pia::piaDataExibicao(null));
    }

    public function testEscapaTextoAntesDeExibiLoComoHtml(): void
    {
        self::assertSame('&lt;script title=&#039;teste&#039;&gt;&amp;', \Abdm\Model\Pia::piaEscaparHtml("<script title='teste'>&"));
    }

    public function testLeArraysJsonERejeitaJsonInvalido(): void
    {
        self::assertSame(['campo' => 'valor'], \Abdm\Model\Pia::piaDadosJsonParaLista('{"campo":"valor"}'));
        self::assertSame([], \Abdm\Model\Pia::piaDadosJsonParaLista('{json invalido'));
        self::assertSame([], \Abdm\Model\Pia::piaDadosJsonParaLista(null));
    }

    public function testSeparaOpcoesDoCampoERemoveEntradasVazias(): void
    {
        self::assertSame(['Sim', 'Não', 'Não sabe'], \Abdm\Model\Pia::piaOpcoesCampo(['opcoes' => ' Sim | Não || Não sabe | ']));
        self::assertSame([], \Abdm\Model\Pia::piaOpcoesCampo(['opcoes' => '']));
    }

    public function testContaERecortaTextoUtf8PorCaractere(): void
    {
        self::assertSame(4, \Abdm\Model\Pia::piaTextoTamanho('Ação'));
        self::assertSame('João', \Abdm\Model\Pia::piaTextoRecortar('João Silva', 4));
    }

    public function testOmiteSecaoFixaDoServicoDasSecoesEditaveis(): void
    {
        $secoes = (new \Abdm\Model\Pia())->piaSecoes();

        self::assertArrayHasKey('acolhido', $secoes);
        self::assertArrayNotHasKey('identificacao_servico', $secoes);
    }
}

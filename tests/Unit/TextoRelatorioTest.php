<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class TextoRelatorioTest extends TestCase
{
    public function testFiltroDeDatasNavegadorEPdfUsaOMesmoLimite(): void
    {
        self::assertTrue(\Abdm\View\TextoRelatorio::relatorioDataFiltroValida('2026-09-10', '2026-09-10'));
        self::assertFalse(\Abdm\View\TextoRelatorio::relatorioDataFiltroValida('2026-09-11', '2026-09-10'));
        self::assertFalse(\Abdm\View\TextoRelatorio::relatorioDataFiltroValida('2026-02-29', '2026-09-10'));
        self::assertFalse(\Abdm\View\TextoRelatorio::relatorioDataFiltroValida("2026-09-10\0", '2026-09-10'));
    }

    public function testDestacaMencaoSelecionadaEEscapaHtml(): void
    {
        $html = \Abdm\View\TextoRelatorio::formatarConteudoRelatorioHtml(
            'Observação sobre @Ana Pereira. <script>alert(1)</script>',
            [['nome' => 'Ana Pereira']]
        );

        self::assertStringContainsString('<strong>@Ana Pereira</strong>', $html);
        self::assertStringContainsString('&lt;script&gt;', $html);
        self::assertStringNotContainsString('<script>', $html);
    }

    public function testDestacaTermosPesquisadosSemTrataLosComoExpressoesRegulares(): void
    {
        $html = \Abdm\View\TextoRelatorio::formatarConteudoRelatorioHtml('Queda (na escada).', [], '(na');

        self::assertStringContainsString('<mark>(na</mark>', $html);
    }

    public function testConverteQuebrasDeLinhaENaoDestacaNomesParciais(): void
    {
        $html = \Abdm\View\TextoRelatorio::formatarConteudoRelatorioHtml("Primeira linha\n@Anabela", [['nome' => 'Ana']]);

        self::assertStringContainsString("Primeira linha<br>\n@Anabela", $html);
        self::assertStringNotContainsString('<strong>', $html);
    }
}

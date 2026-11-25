<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class DadosAcolhidoTest extends TestCase
{
    public function testNormalizaCepAntesDeSalvar(): void
    {
        self::assertSame('12345678', \Abdm\Model\DadosAcolhido::acolhidoCepParaBanco('12.345-678'));
        self::assertSame('', \Abdm\Model\DadosAcolhido::acolhidoCepParaBanco('', 'CEP', false));
    }

    public function testRejeitaCepComQuantidadeDiferenteDeOitoDigitos(): void
    {
        $this->expectException(InvalidArgumentException::class);
        \Abdm\Model\DadosAcolhido::acolhidoCepParaBanco('12345-67');
    }

    public function testMesclaDadosComplementaresESuprimeNumeroDoCpf(): void
    {
        $dados = \Abdm\Model\DadosAcolhido::acolhidoDadosComplementaresParaEdicao([
            'dados_cadastrais_json' => '{"preferencia":"legada","documentos":{"sus":{"situacao":"possui","numero":"antigo"}}}',
            'dados_complementares_json' => '{"preferencia":"atualizada","documentos":{"cpf":{"situacao":"possui","numero":"12345678901"},"sus":{"situacao":"possui","numero":"898001234567890"}}}',
            'cartao_sus' => '',
        ]);

        self::assertSame('atualizada', $dados['preferencia']);
        self::assertSame('898001234567890', $dados['documentos']['sus']['numero']);
        self::assertSame('', $dados['documentos']['cpf']['numero']);
    }

    public function testFiltraNumerosDeDocumentosEOpcoesInvalidasDaSubmissao(): void
    {
        $postOriginal = $_POST;
        try {
            $_POST = ['dados_acolhido' => [
                'documentos' => [
                    'cpf' => ['situacao' => 'possui', 'numero' => '123.456.789-00'],
                    'rg' => ['situacao' => 'nao_possui', 'numero' => 'NAO-SALVAR'],
                ],
                'beneficios' => ['nenhum', 'bpc', 'opcao_invalida'],
                'atividade_remunerada' => 'nao',
                'laudo_medico' => 'sim',
                'envolvimento_substancias' => ['tabaco', 'opcao_invalida'],
            ]];

            $json = \Abdm\Model\DadosAcolhido::acolhidoDadosComplementaresFormularioParaJson();
        } finally {
            $_POST = $postOriginal;
        }

        $dados = json_decode((string)$json, true);
        self::assertSame('', $dados['documentos']['cpf']['numero']);
        self::assertSame('', $dados['documentos']['rg']['numero']);
        self::assertSame(['nenhum'], $dados['beneficios']);
        self::assertSame('sim', $dados['laudo_medico']);
        self::assertSame(['tabaco'], $dados['envolvimento_substancias']);
    }
}

<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class ValidacaoUsuarioTest extends TestCase
{
    public function testAceitaCpfValidoERejeitaCpfInvalidoOuRepetido(): void
    {
        self::assertTrue(\Abdm\Model\ValidacaoUsuario::usuariosCpfValido('52998224725'));
        self::assertFalse(\Abdm\Model\ValidacaoUsuario::usuariosCpfValido('52998224724'));
        self::assertFalse(\Abdm\Model\ValidacaoUsuario::usuariosCpfValido('11111111111'));
        self::assertFalse(\Abdm\Model\ValidacaoUsuario::usuariosCpfValido('5299822472'));
    }

    public function testAceitaSenhaNoTamanhoMinimo(): void
    {
        \Abdm\Model\ValidacaoUsuario::validarSenhaNova('abc123', 'abc123');
        self::assertSame(6, \Abdm\Model\ValidacaoUsuario::usuariosComprimento('abc123'));
    }

    public function testRejeitaSenhaAbaixoDoTamanhoMinimo(): void
    {
        $this->expectException(InvalidArgumentException::class);
        \Abdm\Model\ValidacaoUsuario::validarSenhaNova('abc12', 'abc12');
    }

    public function testRejeitaConfirmacaoDeSenhaDiferente(): void
    {
        $this->expectException(InvalidArgumentException::class);
        \Abdm\Model\ValidacaoUsuario::validarSenhaNova('abc123', 'abc124');
    }

    public function testRejeitaSenhaComMaisDeSetentaEDoisBytes(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $senha = str_repeat('a', 73);
        \Abdm\Model\ValidacaoUsuario::validarSenhaNova($senha, $senha);
    }

    public function testRemoveEspacosNasBordasDoLoginERejeitaEspacosInternos(): void
    {
        self::assertSame('operador', \Abdm\Model\ValidacaoUsuario::validarNomeLogin(' operador '));
        $this->expectException(InvalidArgumentException::class);
        \Abdm\Model\ValidacaoUsuario::validarNomeLogin('operador 2');
    }

    public function testValidaDataOpcionalEDatasReaisDoMandato(): void
    {
        self::assertNull(\Abdm\Model\ValidacaoUsuario::usuariosValidarDataMandato(null, 'início'));
        self::assertSame('2024-02-29', \Abdm\Model\ValidacaoUsuario::usuariosValidarDataMandato('2024-02-29', 'início'));
    }

    public function testRejeitaDataInvalidaDoMandato(): void
    {
        $this->expectException(InvalidArgumentException::class);
        \Abdm\Model\ValidacaoUsuario::usuariosValidarDataMandato('2025-02-29', 'início');
    }

    public function testRemoveEspacosDeValoresDoPerfilERejeitaValoresLongos(): void
    {
        self::assertSame('Ana', \Abdm\Model\ValidacaoUsuario::usuariosValorPerfil(['nome' => ' Ana '], 'nome', 45, true));
        self::assertNull(\Abdm\Model\ValidacaoUsuario::usuariosValorPerfil([], 'email', 45));

        $this->expectException(InvalidArgumentException::class);
        \Abdm\Model\ValidacaoUsuario::usuariosValorPerfil(['nome' => 'quatro'], 'nome', 3);
    }
}

<?php

namespace Abdm\Model;

use InvalidArgumentException;

final class ValidacaoUsuario
{
    public static function usuariosComprimento(string $valor): int
    {
        if (function_exists('mb_strlen')) {
            return mb_strlen($valor, 'UTF-8');
        }
        $resultado = preg_match_all('/./us', $valor, $correspondencias);
        if ($resultado === false) {
            throw new InvalidArgumentException('Há texto com codificação inválida.');
        }
        return $resultado;
    }

    public static function validarSenhaNova(string $senha, string $confirmacao): void
    {
        if (self::usuariosComprimento($senha) < 6 || strlen($senha) > 72) {
            throw new InvalidArgumentException('A senha deve ter pelo menos 6 caracteres e no máximo 72 bytes.');
        }
        if (!hash_equals($senha, $confirmacao)) {
            throw new InvalidArgumentException('A confirmação da senha não confere.');
        }
    }

    public static function validarNomeLogin(string $login): string
    {
        $login = trim($login);
        if (self::usuariosComprimento($login) < 3 || self::usuariosComprimento($login) > 45 || preg_match('/[\s\p{C}]/u', $login) !== 0) {
            throw new InvalidArgumentException('O usuário deve ter de 3 a 45 caracteres, sem espaços.');
        }
        return $login;
    }

    public static function usuariosCpfValido(string $cpf): bool
    {
        if (!preg_match('/^\d{11}$/', $cpf) || preg_match('/^(\d)\1{10}$/', $cpf)) {
            return false;
        }
        for ($tamanho = 9; $tamanho <= 10; $tamanho++) {
            $soma = 0;
            for ($i = 0; $i < $tamanho; $i++) {
                $soma += (int)$cpf[$i] * (($tamanho + 1) - $i);
            }
            $digito = ($soma * 10) % 11;
            $digito = $digito === 10 ? 0 : $digito;
            if ($digito !== (int)$cpf[$tamanho]) {
                return false;
            }
        }
        return true;
    }

    public static function usuariosValorPerfil(array $perfil, string $campo, int $limite, bool $obrigatorio = false): ?string
    {
        $valor = $perfil[$campo] ?? '';
        if (!is_string($valor)) {
            throw new InvalidArgumentException('Dados do perfil inválidos.');
        }
        $valor = trim($valor);
        if ($valor === '') {
            if ($obrigatorio) {
                throw new InvalidArgumentException('Preencha o campo ' . $campo . '.');
            }
            return null;
        }
        if (self::usuariosComprimento($valor) > $limite) {
            throw new InvalidArgumentException('O campo ' . $campo . ' excede o limite de ' . $limite . ' caracteres.');
        }
        return $valor;
    }

    public static function usuariosValidarDataMandato(?string $data, string $campo): ?string
    {
        if ($data === null) {
            return null;
        }
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $data)
            || !checkdate((int)substr($data, 5, 2), (int)substr($data, 8, 2), (int)substr($data, 0, 4))) {
            throw new InvalidArgumentException('Informe uma data valida para ' . $campo . '.');
        }
        return $data;
    }
}

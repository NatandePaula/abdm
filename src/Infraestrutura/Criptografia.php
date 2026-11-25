<?php

namespace Abdm\Infraestrutura;

final class Criptografia
{
    /**
     * Criptografa uma string usando AES-256.
     */
    public static function criptografar($dados)
    {
        if ($dados === null || $dados === '') return null;

        $ivLength = openssl_cipher_iv_length(ENCRYPTION_METHOD);
        $iv = openssl_random_pseudo_bytes($ivLength);

        $encrypted = openssl_encrypt($dados, ENCRYPTION_METHOD, ENCRYPTION_KEY, 0, $iv);

        // Retorna o IV junto com o texto criptografado para a descriptografia.
        return base64_encode($encrypted . '::' . $iv);
    }

    /**
     * Descriptografa uma string que foi criptografada com a função criptografar().
     */
    public static function descriptografar($dadosCriptografados)
    {
        if (empty($dadosCriptografados)) return 'Não informado';

        // Adiciona o modo estrito para garantir que a entrada seja um base64 válido.
        $decoded = base64_decode($dadosCriptografados, true);
        if ($decoded === false || strpos($decoded, '::') === false) {
            return $dadosCriptografados; // Retorna o dado original se não parecer criptografado.
        }

        $parts = explode('::', $decoded, 2);
        if (count($parts) !== 2) {
            // A verificação strpos deve prevenir isso, mas é uma boa salvaguarda.
            return 'Formato de criptografia inválido';
        }
        list($encrypted, $iv) = $parts;

        // Valida se o tamanho do IV corresponde ao esperado pelo método de cifra.
        $expectedIvLength = openssl_cipher_iv_length(ENCRYPTION_METHOD);
        if (strlen($iv) !== $expectedIvLength) {
            return 'IV de criptografia com tamanho incorreto';
        }

        $decrypted = openssl_decrypt($encrypted, ENCRYPTION_METHOD, ENCRYPTION_KEY, 0, $iv);

        return $decrypted !== false ? $decrypted : 'Erro de descriptografia';
    }
}

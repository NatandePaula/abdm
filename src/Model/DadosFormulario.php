<?php

namespace Abdm\Model;

final class DadosFormulario
{
    /**
     * Limpa uma string para inserção no banco, convertendo vazios para NULL.
     */
    public static function limparDados($dado)
    {
        return ($dado !== '' && $dado !== null) ? trim($dado) : null;
    }
}

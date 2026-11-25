<?php

namespace Abdm\Model;

use PDO;
use PDOStatement;

final class PersistenciaAutenticacao
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function prepararConsultaContaComIdLoginPorLogin(): PDOStatement
    {
        return $this->pdo->prepare("SELECT idLogin, senha, is_admin, troca_senha_obrigatoria, senha_temporaria_expira_em, senha_alterada_em,
                           CASE WHEN senha_temporaria_expira_em IS NULL OR senha_temporaria_expira_em <= CURRENT_TIMESTAMP THEN 1 ELSE 0 END AS senha_temporaria_expirada
                    FROM login WHERE login = :login AND ativo = 1");
    }

    public function prepararAtualizacaoContaDeSenha(): PDOStatement
    {
        return $this->pdo->prepare('UPDATE login SET senha = ? WHERE idLogin = ? AND ativo = 1 '
                                        . 'AND BINARY senha = BINARY ? AND senha_alterada_em = ?');
    }
}

<?php

namespace Abdm\Model;

use PDO;

final class Usuarios
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function contas(): array
    {
        return $this->pdo->query("SELECT l.idLogin, l.login, l.ativo, l.is_admin,
        u.idUsuario, u.nome AS perfil_nome, u.cpf AS perfil_cpf, u.dataNascimento AS perfil_dataNascimento,
        u.email AS perfil_email, u.registroProfissional AS perfil_registroProfissional, u.telefone AS perfil_telefone,
        u.atuacaoProfissional AS perfil_atuacaoProfissional, u.estadoCivil AS perfil_estadoCivil,
        u.cargoConselho AS perfil_cargoConselho, u.inicioMandatoConselho AS perfil_inicioMandatoConselho,
        u.fimMandatoConselho AS perfil_fimMandatoConselho, u.dependentes AS perfil_dependentes,
        u.comorbidades AS perfil_comorbidades, u.CBO AS perfil_CBO, u.matricula AS perfil_matricula,
        u.IsFuncionario AS perfil_IsFuncionario, e.estado AS endereco_estado, e.cidade AS endereco_cidade,
        e.rua AS endereco_rua, e.numero AS endereco_numero, e.cep AS endereco_cep
    FROM login l
    LEFT JOIN usuario u ON u.idUsuario = (SELECT MIN(u2.idUsuario) FROM usuario u2 WHERE u2.Login_idLogin = l.idLogin)
    LEFT JOIN endereco e ON e.idEndereco = u.Endereco_idEndereco
    ORDER BY l.login")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function perfisDuplicados(): array
    {
        return $this->pdo->query('SELECT Login_idLogin FROM usuario GROUP BY Login_idLogin HAVING COUNT(*) > 1')->fetchAll(PDO::FETCH_COLUMN);
    }

    public function permissoes(): array
    {
        return $this->pdo->query('SELECT Login_idLogin, funcionalidade FROM login_funcionalidades')->fetchAll(PDO::FETCH_ASSOC);
    }

    public function nomeProfissional(int $id): mixed
    {
        $stmt = $this->pdo->prepare("SELECT COALESCE(NULLIF(u.nome, ''), l.login) FROM login l LEFT JOIN usuario u ON u.idUsuario = (SELECT MIN(u2.idUsuario) FROM usuario u2 WHERE u2.Login_idLogin = l.idLogin) WHERE l.idLogin = ?");
        $stmt->execute([$id]);
        return $stmt->fetchColumn();
    }

    public function profissionais(): array
    {
        return $this->pdo->query("SELECT DISTINCT l.idLogin, l.ativo, COALESCE(NULLIF(u.nome, ''), l.login) AS nome FROM login l JOIN usuario u ON u.Login_idLogin = l.idLogin WHERE u.IsFuncionario = 1 ORDER BY nome")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function nomeDoPerfil(int $id): mixed
    {
        $stmt = $this->pdo->prepare('SELECT nome FROM usuario WHERE Login_idLogin = ? ORDER BY idUsuario LIMIT 1');
        $stmt->execute([$id]);
        return $stmt->fetchColumn();
    }

    public function nomeParaImpressao(int $loginId): mixed
    {
        $stmt = $this->pdo->prepare(
            "SELECT COALESCE(NULLIF(u.nome, ''), NULLIF(l.login, ''))
             FROM login l
             LEFT JOIN usuario u ON u.idUsuario = (
                 SELECT MIN(u2.idUsuario) FROM usuario u2 WHERE u2.Login_idLogin = l.idLogin
             )
             WHERE l.idLogin = ?"
        );
        $stmt->execute([$loginId]);
        return $stmt->fetchColumn();
    }
}

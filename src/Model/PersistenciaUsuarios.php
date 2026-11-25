<?php

namespace Abdm\Model;

use PDO;
use PDOStatement;

final class PersistenciaUsuarios
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function prepararConsultaPerfilPorMatriculaUnica(): PDOStatement
    {
        return $this->pdo->prepare('SELECT 1 FROM usuario WHERE matricula = ? LIMIT 1');
    }

    public function consultarPerfilComLoginIdLoginComBloqueio(): PDOStatement
    {
        return $this->pdo->query('SELECT Login_idLogin, cpf FROM usuario FOR UPDATE');
    }

    public function prepararExclusaoPermissaoDaConta(): PDOStatement
    {
        return $this->pdo->prepare('DELETE FROM login_funcionalidades WHERE Login_idLogin = ?');
    }

    public function prepararInsercaoPermissaoDaConta(): PDOStatement
    {
        return $this->pdo->prepare('INSERT INTO login_funcionalidades (Login_idLogin, funcionalidade) VALUES (?, ?)');
    }

    public function prepararConsultaPermissaoLegadaComIdPermisoesPorCodUnica(): PDOStatement
    {
        return $this->pdo->prepare("SELECT idPermisoes FROM permissao WHERE cod = 'ABDM1' LIMIT 1");
    }

    public function prepararConsultaPerfilComIdUsuarioPorLoginIdLoginComBloqueio(): PDOStatement
    {
        return $this->pdo->prepare('SELECT idUsuario, Endereco_idEndereco FROM usuario WHERE Login_idLogin = ? ORDER BY idUsuario FOR UPDATE');
    }

    public function prepararConsultaPerfilPorEnderecoIdEndereco(): PDOStatement
    {
        return $this->pdo->prepare('SELECT COUNT(*) FROM usuario WHERE Endereco_idEndereco = ? AND Login_idLogin <> ?');
    }

    public function prepararInsercaoEndereco(): PDOStatement
    {
        return $this->pdo->prepare('INSERT INTO endereco (estado, cidade, rua, numero, cep) VALUES (?, ?, ?, ?, ?)');
    }

    public function prepararAtualizacaoEnderecoDeEstado(): PDOStatement
    {
        return $this->pdo->prepare('UPDATE endereco SET estado = ?, cidade = ?, rua = ?, numero = ?, cep = ? WHERE idEndereco = ?');
    }

    public function prepararAtualizacaoPerfilDeNome(): PDOStatement
    {
        return $this->pdo->prepare('UPDATE usuario SET nome = ?, cpf = ?, dataNascimento = ?, email = ?, registroProfissional = ?, telefone = ?, atuacaoProfissional = ?, Endereco_idEndereco = ?, Login_idLogin = ?, Permisoes_idPermisoes = ?, estadoCivil = ?, cargoConselho = ?, inicioMandatoConselho = ?, fimMandatoConselho = ?, dependentes = ?, comorbidades = ?, CBO = ?, matricula = ?, IsFuncionario = ? WHERE idUsuario = ?');
    }

    public function prepararInsercaoPerfil(): PDOStatement
    {
        return $this->pdo->prepare('INSERT INTO usuario (nome, cpf, dataNascimento, email, registroProfissional, telefone, atuacaoProfissional, Endereco_idEndereco, Login_idLogin, Permisoes_idPermisoes, estadoCivil, cargoConselho, inicioMandatoConselho, fimMandatoConselho, dependentes, comorbidades, CBO, matricula, IsFuncionario) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
    }

    public function prepararConsultaContaPorLoginUnica(): PDOStatement
    {
        return $this->pdo->prepare('SELECT 1 FROM login WHERE login = ? LIMIT 1');
    }

    public function prepararConsultaPerfilPorEmailUnica(): PDOStatement
    {
        return $this->pdo->prepare('SELECT 1 FROM usuario WHERE email = ? LIMIT 1');
    }

    public function prepararInsercaoConta(): PDOStatement
    {
        return $this->pdo->prepare('INSERT INTO login (login, senha, ativo, is_admin, troca_senha_obrigatoria) VALUES (?, ?, 1, 0, 0)');
    }

    public function prepararConsultaPerfilDaContaComBloqueio(): PDOStatement
    {
        return $this->pdo->prepare('SELECT idLogin, is_admin FROM login WHERE idLogin = ? FOR UPDATE');
    }

    public function prepararConsultaPerfilComMatriculaPorLoginIdLoginUnicaComBloqueio(): PDOStatement
    {
        return $this->pdo->prepare('SELECT matricula FROM usuario WHERE Login_idLogin = ? ORDER BY idUsuario LIMIT 1 FOR UPDATE');
    }

    public function prepararConsultaDeLoginDeOutraConta(): PDOStatement
    {
        return $this->pdo->prepare('SELECT 1 FROM login WHERE login = ? AND idLogin <> ? LIMIT 1');
    }

    public function prepararConsultaPerfilComIdUsuarioPorEmailUnica(): PDOStatement
    {
        return $this->pdo->prepare('SELECT idUsuario FROM usuario WHERE email = ? AND Login_idLogin <> ? LIMIT 1');
    }

    public function prepararAtualizacaoContaDeLogin(): PDOStatement
    {
        return $this->pdo->prepare('UPDATE login SET login = ? WHERE idLogin = ?');
    }

    public function prepararConsultaSituacaoDaContaComBloqueio(): PDOStatement
    {
        return $this->pdo->prepare('SELECT idLogin, ativo, is_admin FROM login WHERE idLogin = ? FOR UPDATE');
    }

    public function consultarContaComIdLoginPorAtivoComBloqueio(): PDOStatement
    {
        return $this->pdo->query('SELECT idLogin FROM login WHERE ativo = 1 AND is_admin = 1 FOR UPDATE');
    }

    public function prepararAtualizacaoContaDeIsAdmin(): PDOStatement
    {
        return $this->pdo->prepare('UPDATE login SET is_admin = ? WHERE idLogin = ?');
    }

    public function prepararAtualizacaoContaDeAtivo(): PDOStatement
    {
        return $this->pdo->prepare('UPDATE login SET ativo = ? WHERE idLogin = ?');
    }

    public function prepararConsultaIdentificadorDaContaComBloqueio(): PDOStatement
    {
        return $this->pdo->prepare('SELECT idLogin FROM login WHERE idLogin = ? FOR UPDATE');
    }

    public function prepararAtualizacaoContaDeSenhaTemporaria(): PDOStatement
    {
        return $this->pdo->prepare('UPDATE login SET senha = ?, troca_senha_obrigatoria = 1, senha_temporaria_expira_em = DATE_ADD(CURRENT_TIMESTAMP, INTERVAL 24 HOUR), senha_alterada_em = CURRENT_TIMESTAMP(6) WHERE idLogin = ?');
    }

    public function prepararConsultaContaComSenhaPorIdLoginComBloqueio(): PDOStatement
    {
        return $this->pdo->prepare('SELECT senha, troca_senha_obrigatoria FROM login WHERE idLogin = ? FOR UPDATE');
    }

    public function prepararAtualizacaoContaDeSenhaDefinitiva(): PDOStatement
    {
        return $this->pdo->prepare('UPDATE login SET senha = ?, troca_senha_obrigatoria = 0, senha_temporaria_expira_em = NULL, senha_alterada_em = CURRENT_TIMESTAMP(6) WHERE idLogin = ?');
    }

    public function prepararConsultaContaComSenhaAlteradaEmPorIdLogin(): PDOStatement
    {
        return $this->pdo->prepare('SELECT senha_alterada_em FROM login WHERE idLogin = ?');
    }
}

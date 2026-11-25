<?php

namespace Abdm\Model;

use PDO;
use PDOStatement;

final class PersistenciaAcolhidos
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function prepararConsultaAcolhidoComEnderecoIdEnderecoPorId(): PDOStatement
    {
        return $this->pdo->prepare("SELECT Endereco_idEndereco, curador_id, dados_cadastrais_json, dados_complementares_json, tipo_documento, documento_identificacao, cpf, cartao_sus, cartao_municipal_saude, tipoBeneficio FROM acolhido WHERE id = ?");
    }

    public function prepararAtualizacaoEnderecoDeCep(): PDOStatement
    {
        return $this->pdo->prepare("UPDATE endereco SET cep=?, rua=?, numero=?, cidade=?, estado=? WHERE idEndereco=?");
    }

    public function prepararAtualizacaoCuradorDeNome(): PDOStatement
    {
        return $this->pdo->prepare("UPDATE curador SET nome=?, cpf=?, datanNascimento=?, grauParentesco=?, telefone=?, telefone_2=? WHERE id=?");
    }

    public function prepararConsultaCuradorComIdEnderecoPorId(): PDOStatement
    {
        return $this->pdo->prepare("SELECT idEndereco FROM curador WHERE id=?");
    }

    public function prepararInsercaoEndereco(): PDOStatement
    {
        return $this->pdo->prepare("INSERT INTO endereco (cep, rua, numero, cidade, estado) VALUES (?,?,?,?,?)");
    }

    public function prepararInsercaoCuradorComSegundoTelefone(): PDOStatement
    {
        return $this->pdo->prepare("INSERT INTO curador (nome, cpf, datanNascimento, grauParentesco, telefone, telefone_2, idEndereco) VALUES (?,?,?,?,?,?,?)");
    }

    public function prepararAtualizacaoAcolhidoDeNome(): PDOStatement
    {
        return $this->pdo->prepare("UPDATE acolhido SET
            nome=?, cpf=?, dataNascimento=?, sexo=?, tipoBeneficio=?, historico=?, `motivoInstitucionalizacao`=?,
            dataAcolhimento=?, origemAcolhimento=?, curador_id=?,
            nome_mae=?, nome_pai=?, tipo_documento=?, documento_identificacao=?,
            cartao_municipal_saude=?, cartao_sus=?, plano_assistencial_funerario=?, info_saude=?, info_nutri=?,
            endereco_referencia=?, endereco_observacao=?, dados_complementares_json=?
            WHERE id=?");
    }

    public function prepararExclusaoCidDoAcolhido(): PDOStatement
    {
        return $this->pdo->prepare("DELETE FROM cidacolhido WHERE acolhido_id=?");
    }

    public function prepararInsercaoCidDoAcolhido(): PDOStatement
    {
        return $this->pdo->prepare("INSERT INTO cidacolhido (codigo, descricao, acolhido_id) VALUES (?, ?, ?)");
    }

    public function prepararExclusaoFamiliar(): PDOStatement
    {
        return $this->pdo->prepare("DELETE FROM familiar WHERE acolhido_id=?");
    }

    public function prepararInsercaoFamiliar(): PDOStatement
    {
        return $this->pdo->prepare("INSERT INTO familiar (nome, grauParentesco, dataNascimento, telefone, acolhido_id, idEndereco) VALUES (?, ?, ?, ?, ?, ?)");
    }

    public function prepararConsultaPiaComIdPIAPorAcolhidoIdUnica(): PDOStatement
    {
        return $this->pdo->prepare('SELECT idPIA FROM pia WHERE acolhido_id = ? ORDER BY idPIA DESC LIMIT 1');
    }

    public function prepararAtualizacaoPiaDeDataAtualizacao(): PDOStatement
    {
        return $this->pdo->prepare('UPDATE pia SET dataAtualizacao = CURDATE(), atualizado_por_login = ?, atualizado_em = NOW() WHERE idPIA = ?');
    }

    public function prepararInsercaoPia(): PDOStatement
    {
        return $this->pdo->prepare("INSERT INTO pia (acolhido_id, dataAtualizacao, dados_json, estrutura_json) VALUES (?, CURDATE(), '{}', '[]')");
    }

    public function prepararInsercaoCurador(): PDOStatement
    {
        return $this->pdo->prepare("INSERT INTO curador (nome, cpf, datanNascimento, grauParentesco, telefone, idEndereco)
                           VALUES (?, ?, ?, ?, ?, ?)");
    }

    public function prepararInsercaoAcolhido(): PDOStatement
    {
        return $this->pdo->prepare("INSERT INTO acolhido (
            nome, cpf, dataNascimento, sexo, tipoBeneficio, historico, `motivoInstitucionalizacao`,
            dataAcolhimento, origemAcolhimento, matricula, curador_id, Endereco_idEndereco,
            nome_mae, nome_pai, tipo_documento, documento_identificacao, cartao_municipal_saude, cartao_sus,
            plano_assistencial_funerario, info_saude, info_nutri, endereco_referencia, endereco_observacao,
            dados_complementares_json
        ) VALUES (
            ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
        )");
    }

    public function prepararAtualizacaoAcolhidoParaDesligamento(): PDOStatement
    {
        return $this->pdo->prepare("UPDATE acolhido SET status = 'desligado', dataDesligamento = ?, motivoDesligamento = ? WHERE id = ?");
    }

    public function prepararAtualizacaoAcolhidoParaReativacao(): PDOStatement
    {
        return $this->pdo->prepare("UPDATE acolhido SET status = 'ativo', dataDesligamento = NULL, motivoDesligamento = NULL WHERE id = ?");
    }
}

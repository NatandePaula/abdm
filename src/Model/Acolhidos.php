<?php

namespace Abdm\Model;

use PDO;

final class Acolhidos
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function ativos(): array
    {
        $sql = "SELECT id, nome, cpf, dataNascimento, sexo, dataAcolhimento, matricula FROM acolhido WHERE status = 'ativo' ORDER BY nome ASC";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll();
    }

    public function desligados(): array
    {
        $sql = "SELECT id, nome, matricula, dataAcolhimento, dataDesligamento, motivoDesligamento FROM acolhido WHERE status = 'desligado' ORDER BY dataDesligamento DESC";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll();
    }

    public function ficha(int $id): array|false
    {
        $stmt = $this->pdo->prepare("SELECT * FROM acolhido WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function cids(int $id): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM cidacolhido WHERE acolhido_id = ?");
        $stmt->execute([$id]);
        return $stmt->fetchAll();
    }

    public function endereco(int $id): array|false
    {
        $stmt = $this->pdo->prepare("SELECT * FROM endereco WHERE idEndereco = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function familiaresParaEdicao(int $id): array
    {
        $stmt = $this->pdo->prepare("
    SELECT f.*, e.cep as endereco_cep, e.rua as endereco_rua, e.numero as endereco_numero, e.cidade as endereco_cidade, e.estado as endereco_estado 
    FROM familiar f 
    LEFT JOIN endereco e ON f.idEndereco = e.idEndereco 
    WHERE f.acolhido_id = ?
");
        $stmt->execute([$id]);
        return $stmt->fetchAll();
    }

    public function curador(int $id): array|false
    {
        $stmt = $this->pdo->prepare("SELECT * FROM curador WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function familiares(int $id): array
    {
        $stmt = $this->pdo->prepare("
    SELECT f.*, e.cep, e.rua, e.numero, e.cidade, e.estado 
    FROM familiar f 
    LEFT JOIN endereco e ON f.idEndereco = e.idEndereco 
    WHERE f.acolhido_id = ?
");
        $stmt->execute([$id]);
        return $stmt->fetchAll();
    }

    public function nomeAtivo(int $id): mixed
    {
        $stmt = $this->pdo->prepare("SELECT nome FROM acolhido WHERE id = ? AND status = 'ativo'");
        $stmt->execute([$id]);
        return $stmt->fetchColumn();
    }

    public function selecaoAtivos(): array
    {
        return $this->pdo->query("SELECT id, nome FROM acolhido WHERE status = 'ativo' ORDER BY nome")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function identificacaoAtivo(int $id): array|false
    {
        $stmt = $this->pdo->prepare("SELECT id, nome FROM acolhido WHERE id = ? AND status = 'ativo'");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function nome(int $id): array|false
    {
        $stmt = $this->pdo->prepare("SELECT nome FROM acolhido WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function ativosParaRelatorio(): array
    {
        return $this->pdo->query("SELECT id, nome, status FROM acolhido WHERE status = 'ativo' ORDER BY nome")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function selecaoParaPrescricoes(): array
    {
        return $this->pdo->query("SELECT id, nome, matricula FROM acolhido WHERE status = 'ativo' ORDER BY nome ASC")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function fichaAtiva(int $id): array|false
    {
        $stmt = $this->pdo->prepare("SELECT * FROM acolhido WHERE id = ? AND status = 'ativo'");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function selecaoParaPia(): array
    {
        return $this->pdo->query("SELECT id, nome, dataNascimento, dataAcolhimento, status FROM acolhido WHERE status = 'ativo' ORDER BY nome")->fetchAll(PDO::FETCH_ASSOC);
    }
}

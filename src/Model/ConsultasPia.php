<?php

namespace Abdm\Model;

use PDO;

final class ConsultasPia
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function assinantesDisponiveis(): array
    {
        $sqlUsuarios = 'SELECT u.idUsuario, u.nome, u.atuacaoProfissional, u.registroProfissional, u.cargoConselho, u.inicioMandatoConselho, u.fimMandatoConselho, l.idLogin, l.ativo FROM usuario u JOIN login l ON l.idLogin = u.Login_idLogin WHERE l.ativo = 1 ORDER BY u.nome';
        return $this->pdo->query($sqlUsuarios)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function camposDoModelo(): array
    {
        return $this->pdo->query("SELECT chave, secao, rotulo, tipo FROM pia_campos WHERE ativo = 1 AND origem = 'planejamento' AND tipo <> 'evolution' ORDER BY secao, ordem, idCampo")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function camposCadastraisDoModelo(): array
    {
        return $this->pdo->query("SELECT chave, rotulo FROM pia_campos WHERE ativo = 1 AND origem = 'cadastro' ORDER BY secao, ordem, idCampo")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function fichaParaImpressao(int $id): array|false
    {
        $stmt = $this->pdo->prepare('SELECT p.*, a.*, a.nome AS acolhido_nome, a.dataNascimento AS acolhido_nascimento, a.dataAcolhimento AS data_acolhimento, a.motivoInstitucionalizacao AS cadastro_motivo, e.cep AS cadastro_cep, e.rua AS cadastro_rua, e.numero AS cadastro_numero, e.cidade AS cadastro_cidade, e.estado AS cadastro_estado FROM pia p JOIN acolhido a ON a.id = p.acolhido_id LEFT JOIN endereco e ON e.idEndereco = a.Endereco_idEndereco WHERE p.idPIA = ?');
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function assinaturasParaImpressao(int $id): array
    {
        $stmt = $this->pdo->prepare('SELECT nome_assinante, modo, atuacao_profissional, registro_profissional, cargo_conselho, inicio_mandato, fim_mandato, ordem FROM pia_assinaturas WHERE pia_idPIA = ? ORDER BY ordem, idAssinatura');
        $stmt->execute([$id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function familiaresParaImpressao(int $id): array
    {
        $stmt = $this->pdo->prepare('SELECT f.nome, f.dataNascimento, f.telefone, f.grauParentesco, e.cep, e.rua, e.numero, e.cidade, e.estado FROM familiar f LEFT JOIN endereco e ON e.idEndereco = f.idEndereco WHERE f.acolhido_id = ? ORDER BY f.nome');
        $stmt->execute([$id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function cidsParaImpressao(int $id): array
    {
        $stmt = $this->pdo->prepare('SELECT codigo, descricao FROM cidacolhido WHERE acolhido_id = ? ORDER BY codigo');
        $stmt->execute([$id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function curadorParaImpressao(int $id): array|false
    {
        $stmt = $this->pdo->prepare('SELECT c.nome, c.cpf, c.datanNascimento, c.grauParentesco, c.telefone, c.telefone_2, e.cep, e.rua, e.numero, e.cidade, e.estado FROM curador c LEFT JOIN endereco e ON e.idEndereco = c.idEndereco WHERE c.id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function camposDePlanejamento(): array
    {
        return $this->pdo->query("SELECT idCampo, chave, secao, rotulo, tipo, opcoes, ordem, origem FROM pia_campos WHERE ativo = 1 AND origem = 'planejamento' AND tipo <> 'evolution' ORDER BY secao, ordem, idCampo")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function cadastroParaImpressao(): array
    {
        return $this->pdo->query("SELECT chave, secao, rotulo, tipo, opcoes, ordem, origem, ativo FROM pia_campos WHERE origem = 'cadastro' AND ativo = 1 ORDER BY secao, ordem, idCampo")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function camposGerenciaveis(): array
    {
        return $this->pdo->query("SELECT idCampo, chave, secao, origem, rotulo, tipo, opcoes, ordem, ativo, criado_por_login FROM pia_campos WHERE tipo <> 'evolution' ORDER BY secao, ordem, idCampo")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function camposAtivos(): array
    {
        return $this->pdo->query("SELECT idCampo, chave, secao, rotulo, tipo, opcoes, ordem, origem FROM pia_campos WHERE ativo = 1 AND tipo <> 'evolution' ORDER BY secao, ordem, idCampo")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function camposCadastraisAtivos(): array
    {
        return $this->pdo->query("SELECT idCampo, chave, secao, rotulo, tipo, opcoes, ordem, origem, ativo FROM pia_campos WHERE origem = 'cadastro' AND ativo = 1 ORDER BY secao, ordem, idCampo")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function idMaisRecente(int $id): mixed
    {
        $stmt = $this->pdo->prepare('SELECT idPIA FROM pia WHERE acolhido_id = ? ORDER BY idPIA DESC LIMIT 1');
        $stmt->execute([$id]);
        return $stmt->fetchColumn();
    }

    public function fichaDeAcolhidoAtivo(int $id): array|false
    {
        $stmt = $this->pdo->prepare('SELECT p.*, a.nome AS acolhido_nome, a.dataNascimento AS acolhido_nascimento, a.sexo, a.dataAcolhimento AS data_acolhimento, a.dataDesligamento AS cadastro_data_desligamento, a.motivoDesligamento AS cadastro_motivo_desligamento, a.status AS acolhido_status, a.cpf AS acolhido_cpf, a.matricula AS acolhido_matricula, a.curador_id, a.tipoBeneficio AS cadastro_beneficio, a.nome_mae AS cadastro_nome_mae, a.nome_pai AS cadastro_nome_pai, a.tipo_documento, a.documento_identificacao, a.cartao_municipal_saude, a.cartao_sus, a.plano_assistencial_funerario, a.info_saude, a.info_nutri, a.historico, a.motivoInstitucionalizacao AS cadastro_motivo, a.origemAcolhimento AS cadastro_origem, a.dados_cadastrais_json, a.dados_complementares_json, a.endereco_referencia, a.endereco_observacao, a.Endereco_idEndereco FROM pia p JOIN acolhido a ON a.id = p.acolhido_id WHERE p.idPIA = ? AND a.status = \'ativo\'');
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function assinantesAtuais(int $id): array
    {
        $stmt = $this->pdo->prepare('SELECT pa.usuario_idUsuario, pa.modo, u.nome, l.ativo FROM pia_assinaturas pa JOIN usuario u ON u.idUsuario = pa.usuario_idUsuario LEFT JOIN login l ON l.idLogin = u.Login_idLogin WHERE pa.pia_idPIA = ? ORDER BY pa.ordem, pa.idAssinatura');
        $stmt->execute([$id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function enderecoCadastro(int $id): array|false
    {
        $stmt = $this->pdo->prepare('SELECT cep, rua, numero, cidade, estado FROM endereco WHERE idEndereco = ?');
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function familiaresCadastro(int $id): array
    {
        $stmt = $this->pdo->prepare('SELECT f.nome, f.dataNascimento, f.telefone, f.grauParentesco, e.rua, e.numero, e.cidade, e.estado, e.cep FROM familiar f LEFT JOIN endereco e ON e.idEndereco = f.idEndereco WHERE f.acolhido_id = ? ORDER BY f.nome');
        $stmt->execute([$id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function cidsCadastro(int $id): array
    {
        $stmt = $this->pdo->prepare('SELECT codigo, descricao FROM cidacolhido WHERE acolhido_id = ? ORDER BY codigo');
        $stmt->execute([$id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function curadorCadastro(int $id): array|false
    {
        $stmt = $this->pdo->prepare('SELECT c.nome, c.cpf, c.grauParentesco, c.datanNascimento, c.telefone, c.telefone_2, e.rua, e.numero, e.cidade, e.estado, e.cep FROM curador c LEFT JOIN endereco e ON e.idEndereco = c.idEndereco WHERE c.id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function inicializarFaltantes(): void
    {
        $this->pdo->exec("INSERT INTO pia (acolhido_id, dados_json, estrutura_json) SELECT a.id, '{}', '[]' FROM acolhido a WHERE NOT EXISTS (SELECT 1 FROM pia p0 WHERE p0.acolhido_id = a.id)");
    }

    public function planosDosAtivos(): array
    {
        return $this->pdo->query('SELECT p.idPIA, p.dataAtualizacao, p.atualizado_em, p.dados_json, a.id AS acolhido_id, a.nome AS acolhido_nome, a.status AS acolhido_status, (SELECT GROUP_CONCAT(pa.nome_assinante ORDER BY pa.ordem SEPARATOR ", ") FROM pia_assinaturas pa WHERE pa.pia_idPIA = p.idPIA) AS nomes_assinantes FROM acolhido a JOIN pia p ON p.idPIA = (SELECT MAX(p2.idPIA) FROM pia p2 WHERE p2.acolhido_id = a.id) WHERE a.status = \'ativo\' ORDER BY a.nome, p.idPIA DESC')->fetchAll(PDO::FETCH_ASSOC);
    }
}

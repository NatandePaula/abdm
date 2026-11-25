<?php

namespace Abdm\Model;

use PDO;
use PDOStatement;

final class PersistenciaPia
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function prepararConsultaAcolhidoComIdPorId(): PDOStatement
    {
        return $this->pdo->prepare('SELECT id FROM acolhido WHERE id = ?');
    }

    public function prepararConsultaPiaComIdPIAPorAcolhidoIdUnica(): PDOStatement
    {
        return $this->pdo->prepare('SELECT idPIA FROM pia WHERE acolhido_id = ? ORDER BY idPIA DESC LIMIT 1');
    }

    public function consultarCampoPiaComIdCampoPorAtivo(): PDOStatement
    {
        return $this->pdo->query("SELECT idCampo, chave, secao, rotulo, tipo, opcoes, ordem, origem FROM pia_campos WHERE ativo = 1 AND origem = 'planejamento' AND tipo <> 'evolution' ORDER BY secao, ordem, idCampo");
    }

    public function prepararConsultaPiaComAcolhidoIdPorIdPIA(): PDOStatement
    {
        return $this->pdo->prepare('SELECT acolhido_id, dados_json, estrutura_json FROM pia WHERE idPIA = ?');
    }

    public function prepararConsultaAssinaturaPiaComUsuarioIdUsuarioPorPiaIdPIA(): PDOStatement
    {
        return $this->pdo->prepare('SELECT usuario_idUsuario FROM pia_assinaturas WHERE pia_idPIA = ?');
    }

    public function prepararConsultaPerfilPorIdUsuario(): PDOStatement
    {
        return $this->pdo->prepare('SELECT u.idUsuario, u.nome, u.atuacaoProfissional, u.registroProfissional, u.cargoConselho, u.inicioMandatoConselho, u.fimMandatoConselho FROM usuario u JOIN login l ON l.idLogin = u.Login_idLogin WHERE u.idUsuario = ? AND l.ativo = 1');
    }

    public function prepararAtualizacaoPiaDeDataAtualizacao(): PDOStatement
    {
        return $this->pdo->prepare('UPDATE pia SET dataAtualizacao = ?, dados_json = ?, estrutura_json = ?, atualizado_por_login = ?, atualizado_em = NOW() WHERE idPIA = ? AND acolhido_id = ?');
    }

    public function prepararExclusaoAssinaturaPia(): PDOStatement
    {
        return $this->pdo->prepare('DELETE FROM pia_assinaturas WHERE pia_idPIA = ?');
    }

    public function prepararInsercaoPia(): PDOStatement
    {
        return $this->pdo->prepare('INSERT INTO pia (acolhido_id, dataAtualizacao, dados_json, estrutura_json, criado_por_login, atualizado_por_login, criado_em, atualizado_em) VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())');
    }

    public function prepararInsercaoAssinaturaPia(): PDOStatement
    {
        return $this->pdo->prepare('INSERT INTO pia_assinaturas (pia_idPIA, usuario_idUsuario, modo, nome_assinante, atuacao_profissional, registro_profissional, cargo_conselho, inicio_mandato, fim_mandato, ordem) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
    }

    public function prepararInsercaoPiaServicoConfiguracao(): PDOStatement
    {
        return $this->pdo->prepare('INSERT INTO pia_servico_configuracao (id, nome, endereco, telefone, atualizado_por_login, atualizado_em)
             VALUES (1, ?, ?, ?, ?, CURRENT_TIMESTAMP)
             ON DUPLICATE KEY UPDATE nome = VALUES(nome), endereco = VALUES(endereco), telefone = VALUES(telefone), atualizado_por_login = VALUES(atualizado_por_login), atualizado_em = CURRENT_TIMESTAMP');
    }

    public function consultarSecaoPia(): PDOStatement
    {
        return $this->pdo->query('SELECT COALESCE(MAX(ordem), 100) + 10 FROM pia_secoes');
    }

    public function prepararInsercaoSecaoPia(): PDOStatement
    {
        return $this->pdo->prepare('INSERT INTO pia_secoes (chave, titulo, ordem, fixa, ativo, criado_por_login) VALUES (?, ?, ?, 0, 1, ?)');
    }

    public function prepararConsultaSecaoPiaComFixaPorChave(): PDOStatement
    {
        return $this->pdo->prepare('SELECT fixa, ordem FROM pia_secoes WHERE chave = ?');
    }

    public function prepararAtualizacaoSecaoPiaDeTitulo(): PDOStatement
    {
        return $this->pdo->prepare('UPDATE pia_secoes SET titulo = ? WHERE chave = ?');
    }

    public function prepararConsultaSecaoPiaComAtivoPorChave(): PDOStatement
    {
        return $this->pdo->prepare('SELECT ativo FROM pia_secoes WHERE chave = ?');
    }

    public function prepararAtualizacaoSecaoPiaDeAtivo(): PDOStatement
    {
        return $this->pdo->prepare('UPDATE pia_secoes SET ativo = ? WHERE chave = ?');
    }

    public function prepararConsultaSecaoPiaComFixaPorChaveComBloqueio(): PDOStatement
    {
        return $this->pdo->prepare('SELECT fixa, ordem FROM pia_secoes WHERE chave = ? FOR UPDATE');
    }

    public function prepararExclusaoCampoPiaPorSecao(): PDOStatement
    {
        return $this->pdo->prepare('DELETE FROM pia_campos WHERE secao = ?');
    }

    public function prepararExclusaoSecaoPia(): PDOStatement
    {
        return $this->pdo->prepare('DELETE FROM pia_secoes WHERE chave = ?');
    }

    public function prepararConsultaCampoPiaPorSecao(): PDOStatement
    {
        return $this->pdo->prepare('SELECT COALESCE(MAX(ordem), 0) + 10 FROM pia_campos WHERE secao = ?');
    }

    public function prepararInsercaoCampoPia(): PDOStatement
    {
        return $this->pdo->prepare('INSERT INTO pia_campos (chave, secao, origem, rotulo, tipo, opcoes, ordem, ativo, criado_por_login) VALUES (?, ?, ?, ?, ?, ?, ?, 1, ?)');
    }

    public function prepararConsultaCampoPiaComTipoPorIdCampo(): PDOStatement
    {
        return $this->pdo->prepare('SELECT tipo, secao, origem, criado_por_login FROM pia_campos WHERE idCampo = ?');
    }

    public function prepararAtualizacaoCampoPiaDeRotulo(): PDOStatement
    {
        return $this->pdo->prepare('UPDATE pia_campos SET rotulo = ?, opcoes = ? WHERE idCampo = ?');
    }

    public function prepararConsultaCampoPiaComAtivoPorIdCampo(): PDOStatement
    {
        return $this->pdo->prepare('SELECT ativo, secao, origem FROM pia_campos WHERE idCampo = ?');
    }

    public function prepararAtualizacaoCampoPiaDeAtivo(): PDOStatement
    {
        return $this->pdo->prepare('UPDATE pia_campos SET ativo = ? WHERE idCampo = ?');
    }

    public function prepararConsultaCampoPiaComSecaoPorIdCampo(): PDOStatement
    {
        return $this->pdo->prepare('SELECT secao, criado_por_login FROM pia_campos WHERE idCampo = ?');
    }

    public function prepararExclusaoCampoPiaPorId(): PDOStatement
    {
        return $this->pdo->prepare('DELETE FROM pia_campos WHERE idCampo = ?');
    }
}

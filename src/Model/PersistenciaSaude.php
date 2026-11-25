<?php

namespace Abdm\Model;

use PDO;
use PDOStatement;

final class PersistenciaSaude
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function prepararInsercaoRegistroSaude(): PDOStatement
    {
        return $this->pdo->prepare("INSERT INTO saude_acolhido_registros
            (acolhido_id, tipo, data_evento, titulo, profissional, descricao, detalhes_json, prescricao_id, autor_login_id, criado_em)
         SELECT id, ?, ?, ?, ?, ?, NULL, NULL, ?, ?
         FROM acolhido
         WHERE id = ? AND status = 'ativo'");
    }

    public function prepararConsultaAcolhidoComIdPorId(): PDOStatement
    {
        return $this->pdo->prepare("SELECT id FROM acolhido WHERE id = ? AND status = 'ativo'");
    }

    public function prepararConsultaPerfilComIdUsuarioPorLoginIdLoginUnica(): PDOStatement
    {
        return $this->pdo->prepare('SELECT idUsuario, nome, atuacaoProfissional, registroProfissional FROM usuario WHERE Login_idLogin = ? ORDER BY idUsuario LIMIT 1');
    }

    public function prepararInsercaoEvolucao(): PDOStatement
    {
        return $this->pdo->prepare('INSERT INTO evolucao_acolhido (acolhido_id, autor_login_id, usuario_idUsuario, registrado_em, registro, profissional_nome, atuacao_profissional, registro_profissional) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
    }
}

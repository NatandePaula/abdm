<?php

namespace Abdm\Model;

use PDO;

final class ConsultasRelatorio
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function consultar(string $escopo, string $dataInicial, string $dataFinal, string $termoBusca, ?int $acolhidoId, bool $suporteEdicao): array
    {
        $colunasEdicaoSql = $suporteEdicao
            ? 'r.editado_em, r.editado_por_login,'
            : 'NULL AS editado_em, NULL AS editado_por_login,';

        $sqlRelatos = "
            SELECT r.idRelato, r.data_relato, r.periodo, r.conteudo, r.criado_em,
                   r.autorLogin_idLogin, $colunasEdicaoSql
                   COALESCE(NULLIF(u.nome, ''), l.login) AS nome_autor
            FROM relatos_diarios r
            JOIN login l ON l.idLogin = r.autorLogin_idLogin
            LEFT JOIN usuario u ON u.idUsuario = (
                SELECT MIN(u2.idUsuario)
                FROM usuario u2
                WHERE u2.Login_idLogin = l.idLogin
            )
            WHERE 1 = 1
        ";
        $parametrosRelatos = [];
        if ($escopo === 'dia') {
            $sqlRelatos .= ' AND r.data_relato = ?';
            $parametrosRelatos[] = $dataInicial;
        } elseif ($escopo === 'periodo') {
            $sqlRelatos .= ' AND r.data_relato BETWEEN ? AND ?';
            $parametrosRelatos[] = $dataInicial;
            $parametrosRelatos[] = $dataFinal;
        }
        if ($termoBusca !== '') {
            $termoLike = str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $termoBusca);
            $sqlRelatos .= " AND r.conteudo LIKE ? ESCAPE '!'";
            $parametrosRelatos[] = '%' . $termoLike . '%';
        }
        if ($acolhidoId !== null) {
            $sqlRelatos .= ' AND EXISTS (
                SELECT 1 FROM relatos_diarios_acolhidos ra
                WHERE ra.idRelato = r.idRelato AND ra.acolhido_id = ?
            )';
            $parametrosRelatos[] = $acolhidoId;
        }
        $sqlRelatos .= " ORDER BY r.data_relato, FIELD(r.periodo, 'dia', 'noite'), r.criado_em, r.idRelato";
        $stmtRelatos = $this->pdo->prepare($sqlRelatos);
        $stmtRelatos->execute($parametrosRelatos);
        return $stmtRelatos->fetchAll(PDO::FETCH_ASSOC);
    }

    public function mencoes(array $parametros): array
    {
        $marcadores = implode(',', array_fill(0, count($parametros), '?'));
        $stmt = $this->pdo->prepare("SELECT ra.idRelato, a.nome FROM relatos_diarios_acolhidos ra JOIN acolhido a ON a.id = ra.acolhido_id WHERE ra.idRelato IN ($marcadores) ORDER BY a.nome");
        $stmt->execute($parametros);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function mencoesComIdentificador(array $parametros): array
    {
        $marcadores = implode(',', array_fill(0, count($parametros), '?'));
        $stmt = $this->pdo->prepare("
        SELECT ra.idRelato, a.id, a.nome, a.status
        FROM relatos_diarios_acolhidos ra
        JOIN acolhido a ON a.id = ra.acolhido_id
        WHERE ra.idRelato IN ($marcadores)
        ORDER BY a.nome
    ");
        $stmt->execute($parametros);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function nomeAutor(int $id): mixed
    {
        $stmt = $this->pdo->prepare("
    SELECT COALESCE(NULLIF(u.nome, ''), l.login) AS nome
    FROM login l
    LEFT JOIN usuario u ON u.idUsuario = (
        SELECT MIN(u2.idUsuario)
        FROM usuario u2
        WHERE u2.Login_idLogin = l.idLogin
    )
    WHERE l.idLogin = ?
");
        $stmt->execute([$id]);
        return $stmt->fetchColumn();
    }

    public function acolhidosParaMencoes(array $idsExistentes): array
    {
        $sql = "SELECT id, nome FROM acolhido WHERE status = 'ativo'";
        if ($idsExistentes) {
            $marcadores = implode(',', array_fill(0, count($idsExistentes), '?'));
            $sql .= " OR id IN ($marcadores)";
        }
        $stmt = $this->pdo->prepare($sql . ' ORDER BY nome');
        $stmt->execute($idsExistentes);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

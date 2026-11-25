<?php

namespace Abdm\Model;

use PDO;

final class RegistrosSaude
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function consultar(?int $acolhidoId, string $inicio, string $fim, string $tipoFiltro, string $termo): array
    {
        $sql = "SELECT r.*, a.nome AS acolhido_nome,
                       COALESCE(NULLIF(u.nome, ''), l.login,
                                CASE WHEN r.tipo = 'prescricao_finalizada_automaticamente' THEN 'Sistema' ELSE 'Usuário removido' END) AS autor_nome
                FROM saude_acolhido_registros r
                JOIN acolhido a ON a.id = r.acolhido_id
                LEFT JOIN login l ON l.idLogin = r.autor_login_id
                LEFT JOIN usuario u ON u.idUsuario = (
                    SELECT MIN(u2.idUsuario) FROM usuario u2 WHERE u2.Login_idLogin = r.autor_login_id
                )
                WHERE a.status = 'ativo'";
        $parametros = [];
        if ($acolhidoId !== null) { $sql .= ' AND r.acolhido_id = ?'; $parametros[] = $acolhidoId; }
        if ($inicio !== '') { $sql .= ' AND r.data_evento >= ?'; $parametros[] = $inicio; }
        if ($fim !== '') { $sql .= ' AND r.data_evento <= ?'; $parametros[] = $fim; }
        if ($tipoFiltro !== '') { $sql .= ' AND r.tipo = ?'; $parametros[] = $tipoFiltro; }
        if ($termo !== '') {
            $sql .= " AND (r.titulo LIKE ? ESCAPE '!' OR r.descricao LIKE ? ESCAPE '!' OR r.profissional LIKE ? ESCAPE '!' OR a.nome LIKE ? ESCAPE '!' OR l.login LIKE ? ESCAPE '!')";
            $termoSeguro = str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $termo);
            for ($i = 0; $i < 5; $i++) $parametros[] = '%' . $termoSeguro . '%';
        }
        $sql .= ' ORDER BY r.data_evento DESC, r.criado_em DESC, r.idSaude DESC';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($parametros);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

<?php

namespace Abdm\Model;

use PDO;

final class Evolucoes
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function consultar(int $acolhidoId, ?int $loginId, string $inicio, string $fim, string $termo): array
    {
        $sql = "SELECT e.*, a.nome AS acolhido_nome FROM evolucao_acolhido e JOIN acolhido a ON a.id = e.acolhido_id WHERE e.acolhido_id = ? AND a.status = 'ativo'";
        $parametros = [$acolhidoId];
        if ($loginId !== null) {
            $sql .= ' AND (e.autor_login_id = ? OR e.usuario_idUsuario IN (SELECT u2.idUsuario FROM usuario u2 WHERE u2.Login_idLogin = ?))';
            $parametros[] = $loginId;
            $parametros[] = $loginId;
        }
        if ($inicio !== '') { $sql .= ' AND DATE(e.registrado_em) >= ?'; $parametros[] = $inicio; }
        if ($fim !== '') { $sql .= ' AND DATE(e.registrado_em) <= ?'; $parametros[] = $fim; }
        if ($termo !== '') {
            $sql .= " AND (e.registro LIKE ? ESCAPE '!' OR e.profissional_nome LIKE ? ESCAPE '!')";
            $termoSeguro = str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $termo);
            $parametros[] = '%' . $termoSeguro . '%';
            $parametros[] = '%' . $termoSeguro . '%';
        }
        $sql .= ' ORDER BY e.registrado_em DESC, e.idEvolucao DESC';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($parametros);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function paraImpressao(string $escopo, string $dataInicial, string $dataFinal, ?int $acolhidoId, ?int $profissionalId): array
    {
        $parametros = [];
        $condicoes = ["a.status = 'ativo'"];
        if ($escopo === 'dia') {
            $condicoes[] = 'DATE(e.registrado_em) = ?';
            $parametros[] = $dataInicial;
        } elseif ($escopo === 'periodo') {
            $condicoes[] = 'DATE(e.registrado_em) BETWEEN ? AND ?';
            $parametros[] = $dataInicial;
            $parametros[] = $dataFinal;
        }
        
        if ($acolhidoId !== null) {
            $condicoes[] = 'e.acolhido_id = ?';
            $parametros[] = $acolhidoId;
        }
        if ($profissionalId !== null) {
            $condicoes[] = '(e.autor_login_id = ? OR e.usuario_idUsuario IN (SELECT u2.idUsuario FROM usuario u2 WHERE u2.Login_idLogin = ?))';
            $parametros[] = $profissionalId;
            $parametros[] = $profissionalId;
        }

        $sql = "SELECT e.*, a.nome AS acolhido_nome
                FROM evolucao_acolhido e
                JOIN acolhido a ON a.id = e.acolhido_id
                WHERE " . implode(' AND ', $condicoes) . '
                ORDER BY a.nome, e.registrado_em, e.idEvolucao';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($parametros);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

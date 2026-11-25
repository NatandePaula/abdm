<?php

namespace Abdm\Model;

use DateTimeImmutable;
use PDO;
use Exception;
use RuntimeException;
use InvalidArgumentException;
use LogicException;

final class EstoqueMedicamentos
{
    public function __construct(private readonly PDO $pdo, private readonly ?array $gotasPorMl = null)
    {
    }

    public function registrarEntradaNaUnidadeBase(
        int $medicamentoId,
        int $quantidadeEntrada,
        string $usuarioResponsavel
    ): int {
        $consulta = $this->pdo->prepare('SELECT unidade_base FROM medicamentos WHERE id = ? FOR UPDATE');
        $consulta->execute([$medicamentoId]);
        $unidadeBase = $consulta->fetchColumn();
        if ($unidadeBase === false) {
            throw new InvalidArgumentException('Medicamento não encontrado.');
        }

        return $this->registrarEntradaEstoque($medicamentoId, $quantidadeEntrada, $unidadeBase, 1, $usuarioResponsavel);
    }

    public static function converterGotasEmCentesimosDeMl(int $gotas, int $gotasPorMl): int
    {
        if ($gotas < 1 || $gotasPorMl < 1 || $gotasPorMl > 10000) {
            throw new InvalidArgumentException('Informe uma quantidade inteira de gotas e uma equivalência válida em mL.');
        }

        $centesimos = intdiv($gotas * 100 + intdiv($gotasPorMl, 2), $gotasPorMl);
        if ($centesimos < 1) {
            throw new InvalidArgumentException('O volume da dose é inferior à precisão do estoque em mL.');
        }

        return $centesimos;
    }

    public function registrarEntradaEstoque(
        int $medicamentoId,
        int $quantidadeEntrada,
        string $unidadeEntrada,
        int $fatorConversao,
        string $usuarioResponsavel
    ): int {
        $pdo = $this->pdo;
        if ($medicamentoId < 1 || $quantidadeEntrada < 1 || $fatorConversao < 1) {
            throw new InvalidArgumentException('Os dados da entrada de estoque são inválidos.');
        }

        $quantidadeBase = $quantidadeEntrada * $fatorConversao;
        if ($quantidadeBase > 2147483647) {
            throw new InvalidArgumentException('A quantidade convertida excede o limite permitido.');
        }

        // Preenche as colunas legadas obrigatórias de lote e validade.
        $stmt = $pdo->prepare(
            'INSERT INTO medicamentos_estoque '
            . '(medicamento_id, lote, data_validade, quantidade_entrada, unidade_entrada, fator_conversao, quantidade_atual_base) '
            . "VALUES (?, '', '9999-12-31', ?, ?, ?, ?)"
        );
        $stmt->execute([
            $medicamentoId,
            $quantidadeEntrada,
            $unidadeEntrada,
            $fatorConversao,
            $quantidadeBase,
        ]);
        $estoqueId = (int)$pdo->lastInsertId();

        $stmtMovimentacao = $pdo->prepare(
            "INSERT INTO medicamentos_movimentacoes (estoque_id, tipo, quantidade_movimentada, usuario_responsavel) "
            . "VALUES (?, 'entrada', ?, ?)"
        );
        $stmtMovimentacao->execute([$estoqueId, $quantidadeBase, $usuarioResponsavel]);

        return $estoqueId;
    }

    public static function estoqueQuantidadeEmCentesimos(mixed $quantidade): int
    {
        if ((!is_string($quantidade) && !is_int($quantidade))
            || !preg_match('/^([0-9]{1,12})(?:[.,]([0-9]{1,2}))?$/D', (string)$quantidade, $partes)
        ) {
            throw new InvalidArgumentException('Informe uma quantidade válida com até duas casas decimais.');
        }
        return (int)$partes[1] * 100 + (int)str_pad($partes[2] ?? '', 2, '0');
    }

    public static function estoqueQuantidadeDecimal(int $centesimos): string
    {
        return sprintf('%d.%02d', intdiv($centesimos, 100), $centesimos % 100);
    }

    public static function validarDoseComprimido(mixed $quantidade): int
    {
        $centesimos = self::estoqueQuantidadeEmCentesimos($quantidade);
        if ($centesimos === 0 || $centesimos % 50 !== 0) {
            throw new InvalidArgumentException('Para comprimidos, informe uma quantidade positiva em múltiplos de 0,5.');
        }
        return $centesimos;
    }

    public function aceitaEquivalenciaNoCadastro(): bool
    {
        return (bool) $this->pdo->query("SHOW COLUMNS FROM medicamentos LIKE 'gotas_por_ml'")->fetch();
    }

    public function salvarEquivalenciaGotasPorMl(int $medicamentoId, ?int $gotasPorMl): void
    {
        if (!$this->aceitaEquivalenciaNoCadastro()) {
            if ($gotasPorMl !== null) {
                throw new InvalidArgumentException('A atualização do banco é necessária para salvar a equivalência no cadastro.');
            }
            return;
        }

        $stmt = $this->pdo->prepare('UPDATE medicamentos SET gotas_por_ml = ? WHERE id = ?');
        $stmt->execute([$gotasPorMl, $medicamentoId]);
    }

    public function equivalenciaGotasPorMl(int $medicamentoId, array $medicamento): ?int
    {
        $equivalencias = $this->gotasPorMl ?? (defined('GOTAS_POR_ML') ? GOTAS_POR_ML : []);
        $valor = array_key_exists('gotas_por_ml', $medicamento)
            ? $medicamento['gotas_por_ml']
            : ($equivalencias[$medicamentoId] ?? null);
        $fator = filter_var($valor, FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1, 'max_range' => 10000],
        ]);

        return $fator === false ? null : $fator;
    }

    public function registrarSaidaEstoque(
        int $medicamentoId,
        mixed $quantidade,
        string $tipoMovimentacao,
        string $observacao,
        ?string $unidadeDose = null
    ): void {
        $pdo = $this->pdo;
        $quantidadeSaida = self::estoqueQuantidadeEmCentesimos($quantidade);
        if ($quantidadeSaida === 0) {
            throw new InvalidArgumentException('A quantidade de saída deve ser maior que zero.');
        }
        if (!$pdo->inTransaction()) {
            throw new LogicException('A baixa de estoque exige uma transação ativa.');
        }

        $stmtUnidade = $pdo->prepare('SELECT * FROM medicamentos WHERE id = ?');
        $stmtUnidade->execute([$medicamentoId]);
        $medicamento = $stmtUnidade->fetch(PDO::FETCH_ASSOC);
        if (!$medicamento) {
            throw new InvalidArgumentException('Medicamento não encontrado.');
        }
        $unidadeBase = $medicamento['unidade_base'];
        if ($unidadeBase === 'ml' && $unidadeDose === 'gota') {
            if ($quantidadeSaida % 100 !== 0) {
                throw new InvalidArgumentException('A dose em gotas deve ser inteira.');
            }
            $fator = $this->equivalenciaGotasPorMl($medicamentoId, $medicamento);
            if ($fator === null) {
                throw new InvalidArgumentException('Informe a equivalência em gotas por mL no cadastro do medicamento.');
            }

            $gotas = intdiv($quantidadeSaida, 100);
            $quantidadeSaida = self::converterGotasEmCentesimosDeMl($gotas, $fator);
            $observacao .= sprintf(' Baixa aproximada de %s mL para %d gotas (%d gotas/mL).',
                self::estoqueQuantidadeDecimal($quantidadeSaida), $gotas, $fator);
        } elseif ($unidadeDose !== null && $unidadeDose !== $unidadeBase) {
            throw new InvalidArgumentException('A unidade da dose difere da unidade base do medicamento. Revise a prescrição antes de dispensar.');
        }
        if ($quantidadeSaida % 100 !== 0) {
            if ($unidadeBase === 'comprimido' && $unidadeDose === 'comprimido') {
                self::validarDoseComprimido($quantidade);
            } elseif ($unidadeBase !== 'ml') {
                throw new InvalidArgumentException('Esta unidade exige uma quantidade inteira. Revise o cadastro ou a prescrição.');
            }
            $colunasDecimais = $pdo->query(
                "SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE()
             AND DATA_TYPE = 'decimal' AND NUMERIC_PRECISION >= 14 AND NUMERIC_SCALE = 2
             AND ((TABLE_NAME = 'medicamentos_estoque' AND COLUMN_NAME = 'quantidade_atual_base')
               OR (TABLE_NAME = 'medicamentos_movimentacoes' AND COLUMN_NAME = 'quantidade_movimentada'))"
            )->fetchColumn();
            if ((int)$colunasDecimais !== 2) {
                throw new RuntimeException('O banco de dados ainda precisa da atualização de precisão do estoque para registrar quantidades fracionárias. Solicite a atualização ao administrador.');
            }
        }
        $registrosEstoque = $pdo->prepare(
            'SELECT id, quantidade_atual_base FROM medicamentos_estoque '
            . 'WHERE medicamento_id = ? AND quantidade_atual_base > 0 ORDER BY data_entrada ASC, id ASC FOR UPDATE'
        );
        $registrosEstoque->execute([$medicamentoId]);

        $quantidadeRestante = $quantidadeSaida;

        while ($registroEstoque = $registrosEstoque->fetch()) {
            if ($quantidadeRestante <= 0) {
                break;
            }

            $quantidadeRetirada = min(self::estoqueQuantidadeEmCentesimos($registroEstoque['quantidade_atual_base']), $quantidadeRestante);
            $decimalRetirado = self::estoqueQuantidadeDecimal($quantidadeRetirada);

            $atualizarSaldo = $pdo->prepare(
                'UPDATE medicamentos_estoque SET quantidade_atual_base = quantidade_atual_base - CAST(? AS DECIMAL(14,2)) WHERE id = ?'
            );
            $atualizarSaldo->execute([$decimalRetirado, $registroEstoque['id']]);

            $registrarMovimentacao = $pdo->prepare(
                'INSERT INTO medicamentos_movimentacoes (estoque_id, tipo, quantidade_movimentada, usuario_responsavel, observacao) '
                . 'VALUES (?, ?, ?, ?, ?)'
            );
            $registrarMovimentacao->execute([
                $registroEstoque['id'],
                $tipoMovimentacao,
                $decimalRetirado,
                $_SESSION['username'] ?? 'Sistema',
                $observacao,
            ]);

            $quantidadeRestante -= $quantidadeRetirada;
        }

        if ($quantidadeRestante > 0) {
            throw new Exception('Estoque insuficiente. Faltaram ' . self::estoqueQuantidadeDecimal($quantidadeRestante) . ' unidades.');
        }
    }

    public function atualizarCadastroMedicamento(int $medicamentoId, array $dadosMedicamento): void
    {
        $pdo = $this->pdo;
        if ($medicamentoId < 1 || count($dadosMedicamento) !== 4) {
            throw new InvalidArgumentException('Dados inválidos para edição do medicamento.');
        }

        $stmtAtual = $pdo->prepare('SELECT id, unidade_base FROM medicamentos WHERE id = ? FOR UPDATE');
        $stmtAtual->execute([$medicamentoId]);
        $medicamentoAtual = $stmtAtual->fetch(PDO::FETCH_ASSOC);
        if (!$medicamentoAtual) {
            throw new InvalidArgumentException('Medicamento não encontrado.');
        }

        if ($medicamentoAtual['unidade_base'] !== $dadosMedicamento[3]) {
            $stmtVinculos = $pdo->prepare(
                'SELECT (SELECT COUNT(*) FROM medicamentos_estoque WHERE medicamento_id = ?) '
                . '+ (SELECT COUNT(*) FROM prescricoes WHERE medicamento_id = ?)'
            );
            $stmtVinculos->execute([$medicamentoId, $medicamentoId]);
            if ((int)$stmtVinculos->fetchColumn() > 0) {
                throw new InvalidArgumentException('A unidade base não pode ser alterada enquanto houver estoque ou prescrições vinculadas.');
            }
        }

        $stmtAtualizar = $pdo->prepare(
            'UPDATE medicamentos SET nome_comercial = ?, principio_ativo = ?, apresentacao = ?, unidade_base = ? WHERE id = ?'
        );
        $stmtAtualizar->execute([...$dadosMedicamento, $medicamentoId]);
    }

    public function excluirMedicamentoSemVinculos(int $medicamentoId): void
    {
        $pdo = $this->pdo;
        // O bloqueio do cadastro serializa a exclusão com novos vínculos pelas chaves estrangeiras.
        $stmt = $pdo->prepare('SELECT id FROM medicamentos WHERE id = ? FOR UPDATE');
        $stmt->execute([$medicamentoId]);
        if (!$stmt->fetchColumn()) {
            throw new InvalidArgumentException('Medicamento não encontrado para exclusão.');
        }

        $stmt = $pdo->prepare(
            'SELECT (SELECT SUM(quantidade_atual_base) FROM medicamentos_estoque WHERE medicamento_id = ?) AS saldo, '
            . "EXISTS(SELECT 1 FROM prescricoes WHERE medicamento_id = ? AND status = 'ativa') AS prescricao_ativa, "
            . 'EXISTS(SELECT 1 FROM medicamentos_estoque WHERE medicamento_id = ?) AS tem_estoque, '
            . 'EXISTS(SELECT 1 FROM prescricoes WHERE medicamento_id = ?) AS tem_prescricao'
        );
        $stmt->execute(array_fill(0, 4, $medicamentoId));
        $vinculos = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($vinculos['saldo'] > 0) {
            throw new InvalidArgumentException('Não é possível excluir. O estoque do medicamento precisa estar zerado.');
        }
        if ($vinculos['prescricao_ativa']) {
            throw new InvalidArgumentException('Não é possível excluir. O medicamento ainda possui prescrições ativas.');
        }
        if ($vinculos['tem_estoque'] || $vinculos['tem_prescricao']) {
            throw new InvalidArgumentException('Não é possível excluir. O medicamento está associado a prescrições ou histórico de estoque.');
        }

        $stmt = $pdo->prepare('DELETE FROM medicamentos WHERE id = ?');
        $stmt->execute([$medicamentoId]);
    }

    public function registrarDispensacao(
        array $item,
        string $data,
        string $status,
        string $observacao
    ): string
    {
        $pdo = $this->pdo;
        $horarioIdInput = $item['prescricao_horario_id'] ?? '';
        $prescricaoIdInput = $item['prescricao_id'] ?? '';
        $horarioInput = $item['horario_previsto'] ?? '';
        if (!is_string($horarioInput)) {
            throw new Exception('Horário previsto inválido.');
        }

        $horarioId = $horarioIdInput === '' ? null : filter_var($horarioIdInput, FILTER_VALIDATE_INT);
        $prescricaoId = $prescricaoIdInput === '' ? null : filter_var($prescricaoIdInput, FILTER_VALIDATE_INT);
        if (($horarioIdInput !== '' && (!$horarioId || $horarioId < 1)) || ($prescricaoIdInput !== '' && (!$prescricaoId || $prescricaoId < 1))) {
            throw new Exception('Prescrição ou horário inválido.');
        }

        if ($horarioId) {
            $stmt = $pdo->prepare("SELECT p.id, p.acolhido_id, p.medicamento_id, p.status, p.data_inicio, p.data_fim, p.tipo_posologia, ph.horario, ph.quantidade_por_dose, ph.unidade_dose FROM prescricao_horarios ph JOIN prescricoes p ON p.id = ph.prescricao_id JOIN acolhido a ON a.id = p.acolhido_id WHERE ph.id = ? AND a.status = 'ativo' FOR UPDATE");
            $stmt->execute([$horarioId]);
            $prescricao = $stmt->fetch();
            if (!$prescricao || $prescricao['tipo_posologia'] !== 'horario_fixo') {
                throw new Exception('Horário de prescrição inválido.');
            }
            $horarioPrevisto = $prescricao['horario'];
        } elseif ($prescricaoId) {
            $stmt = $pdo->prepare("SELECT p.id, p.acolhido_id, p.medicamento_id, p.status, p.data_inicio, p.data_fim, p.tipo_posologia, p.intervalo_horas, p.intervalo_inicio, p.quantidade_por_dose, p.unidade_dose FROM prescricoes p JOIN acolhido a ON a.id = p.acolhido_id WHERE p.id = ? AND a.status = 'ativo' FOR UPDATE");
            $stmt->execute([$prescricaoId]);
            $prescricao = $stmt->fetch();
            if (!$prescricao || $prescricao['tipo_posologia'] !== 'intervalo') {
                throw new Exception('Prescrição por intervalo inválida.');
            }
            if (!preg_match('/^(?:[01]\\d|2[0-3]):[0-5]\\d$/', $horarioInput)) {
                throw new Exception('Horário previsto inválido.');
            }

            $inicio = DateTimeImmutable::createFromFormat('!H:i:s', $prescricao['intervalo_inicio']);
            if (!$inicio) {
                $inicio = DateTimeImmutable::createFromFormat('!H:i', substr($prescricao['intervalo_inicio'], 0, 5));
            }
            $intervaloMinutos = (float)$prescricao['intervalo_horas'] * 60;
            $intervaloMinutosInt = (int)round($intervaloMinutos);
            if (!$inicio || $intervaloMinutosInt < 1 || $intervaloMinutosInt > 1440 || abs($intervaloMinutos - $intervaloMinutosInt) > 0.0001) {
                throw new Exception('Intervalo configurado inválido.');
            }
            $minutoInicio = ((int)$inicio->format('H') * 60) + (int)$inicio->format('i');
            [$horaInformada, $minutoInformado] = array_map('intval', explode(':', $horarioInput));
            $deltaMinutos = (($horaInformada * 60 + $minutoInformado) - $minutoInicio + 1440) % 1440;
            if ($deltaMinutos % $intervaloMinutosInt !== 0) {
                throw new Exception('O horário não pertence à posologia da prescrição.');
            }
            $horarioPrevisto = $horarioInput . ':00';
        } else {
            throw new Exception('Prescrição não informada.');
        }

        if ($prescricao['status'] !== 'ativa' || $prescricao['data_inicio'] > $data || ($prescricao['data_fim'] && $prescricao['data_fim'] < $data)) {
            throw new Exception('A prescrição não estava ativa na data informada.');
        }
        foreach (['acolhido_id', 'medicamento_id'] as $campo) {
            if (array_key_exists($campo, $item) && $item[$campo] !== null && (int)$prescricao[$campo] !== filter_var($item[$campo], FILTER_VALIDATE_INT)) {
                throw new InvalidArgumentException('Os dados enviados não correspondem à prescrição selecionada.');
            }
        }
        if (!in_array($status, ['dispensado', 'recusado', 'nao_administrado'], true)) {
            throw new Exception('Status da dispensação inválido.');
        }

        $quantidade = $prescricao['quantidade_por_dose'];
        if (self::estoqueQuantidadeEmCentesimos($quantidade) === 0 || empty($prescricao['unidade_dose'])) {
            throw new Exception('A dose prescrita é inválida.');
        }

        $stmtDuplicada = $pdo->prepare('SELECT 1 FROM dispensacoes WHERE prescricao_id = ? AND data_dispensacao = ? AND horario_previsto = ? LIMIT 1 FOR UPDATE');
        $stmtDuplicada->execute([$prescricao['id'], $data, $horarioPrevisto]);
        if ($stmtDuplicada->fetchColumn()) {
            throw new Exception('Esta dose já possui um registro de dispensação.');
        }

        $stmtDispensacao = $pdo->prepare("INSERT INTO dispensacoes (prescricao_id, prescricao_horario_id, acolhido_id, medicamento_id, quantidade_dispensada, horario_previsto, data_dispensacao, horario_dispensado, usuario_responsavel, status, observacao) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmtDispensacao->execute([
            $prescricao['id'], $horarioId, $prescricao['acolhido_id'], $prescricao['medicamento_id'],
            $quantidade, $horarioPrevisto, $data, date('H:i:s'), $_SESSION['username'] ?? 'Sistema',
            $status, DadosFormulario::limparDados($observacao)
        ]);

        if ($status === 'dispensado') {
            $this->registrarSaidaEstoque((int)$prescricao['medicamento_id'], $quantidade, 'dispensacao', 'Dispensado via quadro de dispensação.', $prescricao['unidade_dose']);
        }

        return date('H:i', strtotime($horarioPrevisto));
    }

    public function registrarDispensacoesLote(array $itens, string $data, string $horario): int
    {
        foreach ($itens as $item) {
            if (!is_array($item)) {
                throw new InvalidArgumentException('Item da lista inválido.');
            }
            if ($this->registrarDispensacao($item, $data, 'dispensado', '') !== $horario) {
                throw new InvalidArgumentException('A lista contém uma dose de outro horário.');
            }
        }
        return count($itens);
    }
}

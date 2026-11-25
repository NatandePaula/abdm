<?php

namespace Abdm\Controller;

use Abdm\Infraestrutura\ControleAcesso;
use Abdm\Model\DadosFormulario;
use Abdm\Model\EstoqueMedicamentos;
use Abdm\Model\HistoricoSaude;
use Abdm\Model\Prescricao;
use Abdm\Infraestrutura\Sessao;

use DateTimeImmutable;
use PDO;
use PDOException;
use Throwable;
use Exception;
use InvalidArgumentException;
use JsonException;
use Error;

final class MedicamentoController
{
    private readonly ControleAcesso $controleAcesso;
    private readonly EstoqueMedicamentos $estoque;
    private readonly HistoricoSaude $historicoSaude;
    private readonly Prescricao $prescricao;

    public function __construct(private readonly PDO $pdo)
    {
        $this->controleAcesso = new ControleAcesso($pdo);
        $this->estoque = new EstoqueMedicamentos($pdo);
        $this->historicoSaude = new HistoricoSaude($pdo);
        $this->prescricao = new Prescricao($pdo);
    }

    private static function medicamentoTextoDoFormulario(string $campo, int $limite, bool $obrigatorio = false): ?string
    {
        $valor = $_POST[$campo] ?? '';
        if (!is_string($valor)) {
            throw new InvalidArgumentException('Um dos campos do medicamento é inválido.');
        }

        $valor = trim($valor);
        $tamanho = function_exists('mb_strlen') ? mb_strlen($valor, 'UTF-8') : strlen($valor);
        if (($obrigatorio && $valor === '') || $tamanho > $limite) {
            throw new InvalidArgumentException('Preencha os campos obrigatórios e respeite o limite de caracteres.');
        }

        return $valor === '' ? null : $valor;
    }

    private static function medicamentoDadosDoFormulario(): array
    {
        $unidadesPermitidas = ['comprimido', 'ml', 'gota', 'unidade', 'capsula'];
        $unidadeBase = self::medicamentoTextoDoFormulario('unidade_base', 20, true);
        if (!in_array($unidadeBase, $unidadesPermitidas, true)) {
            throw new InvalidArgumentException('A unidade base selecionada é inválida.');
        }

        return [
            self::medicamentoTextoDoFormulario('nome_comercial', 255, true),
            self::medicamentoTextoDoFormulario('principio_ativo', 255, true),
            self::medicamentoTextoDoFormulario('apresentacao', 255),
            $unidadeBase,
        ];
    }

    private static function gotasPorMlDoFormulario(string $unidadeBase): ?int
    {
        $valor = $_POST['gotas_por_ml'] ?? '';
        if ($valor === '' || $unidadeBase !== 'ml') {
            return null;
        }
        $gotasPorMl = filter_var($valor, FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1, 'max_range' => 10000],
        ]);
        if ($gotasPorMl === false) {
            throw new InvalidArgumentException('Informe um número inteiro válido de gotas por mL.');
        }
        return $gotasPorMl;
    }

    private static function medicamentoIdDoFormulario(string $campo, bool $opcional = false): ?int
    {
        $valor = $_POST[$campo] ?? '';
        if ($opcional && $valor === '') {
            return null;
        }
        $id = filter_var($valor, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($id === false) {
            throw new InvalidArgumentException('Um dos identificadores informados é inválido.');
        }
        return $id;
    }

    private static function quantidadeDaDoseDoFormulario(array $dose): mixed
    {
        $quantidade = $dose['quantidade_por_dose'] ?? null;
        return ($dose['unidade_dose'] ?? null) === 'comprimido'
            ? EstoqueMedicamentos::estoqueQuantidadeDecimal(EstoqueMedicamentos::validarDoseComprimido($quantidade))
            : $quantidade;
    }

    public function executar(): void
    {
        $pdo = $this->pdo;
        $this->controleAcesso->exigirUsuarioAutenticado();

        if ($_SERVER["REQUEST_METHOD"] !== "POST" || !isset($_POST['acao'])) {
            header("Location: ../views/medicamentos_estoque.php");
            exit;
        }

        $acao = $_POST['acao'];
        if (!is_string($acao)) {
            http_response_code(400);
            exit('Ação inválida.');
        }
        $permissaoPorAcao = [
            'cadastrar_medicamento' => 'estoque',
            'atualizar_medicamento' => 'estoque',
            'excluir_medicamento' => 'estoque',
            'registrar_entrada' => 'estoque',
            'registrar_saida' => 'estoque',
            'adicionar_prescricao' => 'prescricoes',
            'editar_prescricao' => 'prescricoes',
            'alterar_status_prescricao' => 'prescricoes',
            'excluir_prescricao' => 'prescricoes',
            'registrar_dispensacoes_lote' => 'dispensacao',
            'registrar_dispensacao' => 'dispensacao',
        ];
        if (!isset($permissaoPorAcao[$acao])) {
            http_response_code(400);
            exit('Ação desconhecida.');
        }
        $this->controleAcesso->exigirPermissao($permissaoPorAcao[$acao]);

        Sessao::exigirTokenCsrfValido();

        $destinoRedirecionamento = null;
        try {
            $pdo->beginTransaction();

            match ($acao) {
                'cadastrar_medicamento' => $this->cadastrarMedicamento(),
                'atualizar_medicamento' => $this->atualizarMedicamento(),
                'adicionar_prescricao' => $this->adicionarPrescricao(),
                'editar_prescricao' => $this->editarPrescricao(),
                'alterar_status_prescricao' => $this->alterarStatusPrescricao(),
                'excluir_medicamento' => $this->excluirMedicamento(),
                'excluir_prescricao' => $this->excluirPrescricao(),
                'registrar_entrada' => $this->registrarEntrada(),
                'registrar_saida' => $this->registrarSaida(),
                'registrar_dispensacoes_lote' => $this->registrarDispensacoesLote(),
                'registrar_dispensacao' => $this->registrarDispensacao(),
            };

            if ($acao === 'atualizar_medicamento') {
                $destinoRedirecionamento = '../views/medicamentos_estoque.php';
            }

            $pdo->commit();

        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            if ($e instanceof PDOException || $e instanceof Error) {
                error_log('Falha na operação do módulo de medicamentos. Código: ' . $e->getCode());
                $mensagemErro = 'Erro interno ao salvar a operação.';
            } else {
                $mensagemErro = $e->getMessage();
            }
            $_SESSION['flash_message'] = [
                'type' => 'danger',
                'message' => 'Erro: ' . $mensagemErro
            ];
        }

        if ($destinoRedirecionamento === null) {
            $origem = parse_url($_SERVER['HTTP_REFERER'] ?? '');
            $caminho = $origem['path'] ?? '';
            $diretorioViews = BASE_URL . 'views/';
            $viewsPermitidas = [
                $diretorioViews . 'cadastrar_medicamento.php',
                $diretorioViews . 'medicamentos_estoque.php',
                $diretorioViews . 'prescricoes.php',
                $diretorioViews . 'prescricoes_suspensas_acolhido.php',
                $diretorioViews . 'dispensacao.php',
            ];
            $destinoRedirecionamento = $diretorioViews . 'medicamentos_estoque.php';
            if (in_array($caminho, $viewsPermitidas, true)) {
                $consulta = $origem['query'] ?? '';
                $destinoRedirecionamento = $caminho . ($consulta === '' ? '' : '?' . $consulta);
            }
        }
        header('Location: ' . $destinoRedirecionamento);
        exit();
    }

    private function cadastrarMedicamento(): void
    {
        $dadosMedicamento = self::medicamentoDadosDoFormulario();
        $stmt = (new \Abdm\Model\PersistenciaMedicamentos($this->pdo))->prepararInsercaoMedicamento();
        $stmt->execute($dadosMedicamento);
        $this->estoque->salvarEquivalenciaGotasPorMl((int)$this->pdo->lastInsertId(), self::gotasPorMlDoFormulario($dadosMedicamento[3]));
        $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'Medicamento cadastrado com sucesso!'];
    }

    private function atualizarMedicamento(): void
    {
        $idMedicamento = filter_var($_POST['medicamento_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if (!$idMedicamento) {
            throw new InvalidArgumentException('Medicamento inválido para edição.');
        }

        $dadosMedicamento = self::medicamentoDadosDoFormulario();
        $this->estoque->atualizarCadastroMedicamento((int)$idMedicamento, $dadosMedicamento);
        if (array_key_exists('gotas_por_ml', $_POST) || $dadosMedicamento[3] !== 'ml') {
            $this->estoque->salvarEquivalenciaGotasPorMl((int)$idMedicamento, self::gotasPorMlDoFormulario($dadosMedicamento[3]));
        }
        $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'Cadastro do medicamento atualizado com sucesso.'];
    }

    private function adicionarPrescricao(): void
    {
        $acolhidoId = self::medicamentoIdDoFormulario('acolhido_id');
        $medicamentoId = self::medicamentoIdDoFormulario('medicamento_id');
        $tipoPosologia = $_POST['tipo_posologia'] ?? null;
        if (!is_string($tipoPosologia)) {
            throw new InvalidArgumentException('Selecione um tipo de posologia válido.');
        }
        $tipoUso = Prescricao::prescricaoValidarTipoUso($_POST['tipo_uso'] ?? null);
        $tipoUsoNoBanco = (new \Abdm\Model\PersistenciaMedicamentos($this->pdo))->consultarColunaComCOLUMNTYPEPorTableSchema()->fetchColumn();
        if (!Prescricao::prescricaoTipoUsoAceitoPeloEnum($tipoUsoNoBanco, $tipoUso)) {
            throw new InvalidArgumentException('Este tipo de uso não está disponível nesta instalação. Solicite suporte ao administrador.');
        }
        $horariosRecebidos = $_POST['horarios'] ?? [];
        $horariosPrescricao = $tipoPosologia === 'horario_fixo'
            ? Prescricao::prescricaoPrepararHorariosFixos($horariosRecebidos)
            : [];
        $dosesPorDia = Prescricao::prescricaoCalcularDosesPorDia(
            $tipoPosologia,
            $_POST['intervalo_horas'] ?? null,
            $horariosPrescricao
        );

        $stmtPresc = (new \Abdm\Model\PersistenciaMedicamentos($this->pdo))->prepararInsercaoPrescricao();
        $stmtPresc->execute([
            $acolhidoId,
            $medicamentoId,
            $tipoPosologia,
            $dosesPorDia,
            ($tipoPosologia === 'intervalo') ? DadosFormulario::limparDados($_POST['intervalo_horas']) : null, // intervalo_horas
            ($tipoPosologia === 'intervalo') ? DadosFormulario::limparDados($_POST['intervalo_inicio']) : null, // intervalo_inicio
            ($tipoPosologia === 'intervalo') ? self::quantidadeDaDoseDoFormulario($_POST) : null,
            ($tipoPosologia === 'intervalo') ? $_POST['unidade_dose'] : null, // unidade_dose para intervalo
            DadosFormulario::limparDados($_POST['via_administracao']),
            $tipoUso,
            $_POST['data_inicio'],
            DadosFormulario::limparDados($_POST['data_fim']),
            DadosFormulario::limparDados($_POST['medico_responsavel']),
            DadosFormulario::limparDados($_POST['observacoes']),
            DadosFormulario::limparDados($_POST['instrucao_posologia'])
        ]);
        $prescricaoId = $this->pdo->lastInsertId();

        if ($tipoPosologia === 'horario_fixo') {
            $stmtHorario = (new \Abdm\Model\PersistenciaMedicamentos($this->pdo))->prepararInsercaoHorarioDaPrescricao();
            foreach ($horariosPrescricao as $horario) {
                $stmtHorario->execute([$prescricaoId, $horario['hora'], $horario['periodo_id'], self::quantidadeDaDoseDoFormulario($horario), $horario['unidade_dose']]);
            }
        }

        $snapshotNova = $this->historicoSaude->saudeResumoPrescricao((int)$prescricaoId);
        if (!$snapshotNova) {
            throw new Exception('Não foi possível recuperar os dados da prescrição criada.');
        }
        $this->historicoSaude->saudeRegistrarEventoMedicamento((int)$snapshotNova['_acolhido_id'],
            (int)$prescricaoId,
            'prescricao_criada',
            'Nova prescrição: ' . $snapshotNova['Medicamento'],
            'Nova prescrição registrada no sistema.',
            ['atual' => $snapshotNova]
        );

        $this->pdo->commit();
        $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'Prescrição adicionada com sucesso!'];
        header('Location: ../views/prescricoes.php?id_acolhido=' . $acolhidoId);
        exit();
    }

    private function editarPrescricao(): void
    {
        $prescricaoId = self::medicamentoIdDoFormulario('prescricao_id');
        $acolhidoId = self::medicamentoIdDoFormulario('acolhido_id');
        $medicamentoId = self::medicamentoIdDoFormulario('medicamento_id');
        $tipoPosologia = $_POST['tipo_posologia'] ?? null;
        if (!is_string($tipoPosologia)) {
            throw new InvalidArgumentException('Selecione um tipo de posologia válido.');
        }
        $tipoUso = Prescricao::prescricaoValidarTipoUso($_POST['tipo_uso'] ?? null);
        $tipoUsoNoBanco = (new \Abdm\Model\PersistenciaMedicamentos($this->pdo))->consultarColunaComCOLUMNTYPEPorTableSchema()->fetchColumn();
        if (!Prescricao::prescricaoTipoUsoAceitoPeloEnum($tipoUsoNoBanco, $tipoUso)) {
            throw new InvalidArgumentException('Este tipo de uso não está disponível nesta instalação. Solicite suporte ao administrador.');
        }
        $horariosRecebidos = $_POST['horarios'] ?? [];
        $horariosPrescricao = $tipoPosologia === 'horario_fixo'
            ? Prescricao::prescricaoPrepararHorariosFixos($horariosRecebidos)
            : [];
        $dosesPorDia = Prescricao::prescricaoCalcularDosesPorDia(
            $tipoPosologia,
            $_POST['intervalo_horas'] ?? null,
            $horariosPrescricao
        );
        $snapshotAnterior = $this->historicoSaude->saudeResumoPrescricao($prescricaoId);
        if (!$snapshotAnterior || (int)$snapshotAnterior['_acolhido_id'] !== $acolhidoId) {
            throw new InvalidArgumentException('A prescrição selecionada não pertence ao acolhido informado.');
        }

        $stmtUpdatePresc = (new \Abdm\Model\PersistenciaMedicamentos($this->pdo))->prepararAtualizacaoPrescricaoDeMedicamentoId();
        $stmtUpdatePresc->execute([
            $medicamentoId,
            $tipoPosologia,
            $dosesPorDia,
            ($tipoPosologia === 'intervalo') ? DadosFormulario::limparDados($_POST['intervalo_horas']) : null, // intervalo_horas
            ($tipoPosologia === 'intervalo') ? DadosFormulario::limparDados($_POST['intervalo_inicio']) : null, // intervalo_inicio
            ($tipoPosologia === 'intervalo') ? self::quantidadeDaDoseDoFormulario($_POST) : null,
            ($tipoPosologia === 'intervalo') ? $_POST['unidade_dose'] : null, // unidade_dose para intervalo
            DadosFormulario::limparDados($_POST['via_administracao']),
            $tipoUso,
            $_POST['data_inicio'],
            DadosFormulario::limparDados($_POST['data_fim']),
            DadosFormulario::limparDados($_POST['medico_responsavel']),
            DadosFormulario::limparDados($_POST['observacoes']),
            DadosFormulario::limparDados($_POST['instrucao_posologia']),
            $prescricaoId
        ]);

        $this->prescricao->prescricaoRemoverHorariosParaEdicao($prescricaoId);

        if ($tipoPosologia === 'horario_fixo') {
            $stmtHorario = (new \Abdm\Model\PersistenciaMedicamentos($this->pdo))->prepararInsercaoHorarioDaPrescricao();
            foreach ($horariosPrescricao as $horario) {
                $stmtHorario->execute([$prescricaoId, $horario['hora'], $horario['periodo_id'], self::quantidadeDaDoseDoFormulario($horario), $horario['unidade_dose']]);
            }
        }

        $snapshotAtual = $this->historicoSaude->saudeResumoPrescricao($prescricaoId);
        if (!$snapshotAtual) {
            throw new Exception('Não foi possível recuperar a prescrição atualizada para o histórico de saúde.');
        }
        $alteracoes = [];
        foreach ($snapshotAtual as $campo => $valorAtual) {
            $valorAnterior = $snapshotAnterior[$campo] ?? '';
            if ($valorAnterior !== $valorAtual) {
                $alteracoes[$campo] = ['antes' => $valorAnterior, 'depois' => $valorAtual];
            }
        }
        if ($alteracoes) {
            $this->historicoSaude->saudeRegistrarEventoMedicamento((int)$snapshotAtual['_acolhido_id'],
                $prescricaoId,
                'prescricao_alterada',
                'Prescrição atualizada: ' . $snapshotAtual['Medicamento'],
                'Dados da prescrição alterados.',
                ['alteracoes' => $alteracoes, 'atual' => $snapshotAtual]
            );
        }

        $this->pdo->commit();
        $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'Prescrição atualizada com sucesso!'];
        header("Location: ../views/prescricoes.php?id_acolhido=" . $acolhidoId);
        exit();
    }

    private function alterarStatusPrescricao(): void
    {
        $prescricaoId = self::medicamentoIdDoFormulario('prescricao_id');
        $novoStatus = $_POST['novo_status'] ?? null;
        $acolhidoId = self::medicamentoIdDoFormulario('acolhido_id');
        $snapshotAnterior = $this->historicoSaude->saudeResumoPrescricao($prescricaoId);
        if (!$snapshotAnterior || (int)$snapshotAnterior['_acolhido_id'] !== $acolhidoId) {
            throw new InvalidArgumentException('A prescrição selecionada não pertence ao acolhido informado.');
        }

        if (!in_array($novoStatus, ['ativa', 'suspensa', 'finalizada'], true)) {
            throw new Exception("Status inválido.");
        }

        $motivo = ($novoStatus === 'suspensa') ? DadosFormulario::limparDados($_POST['motivo_suspensao']) : null;

        $stmt = (new \Abdm\Model\PersistenciaMedicamentos($this->pdo))->prepararAtualizacaoPrescricaoDeStatus();
        $stmt->execute([$novoStatus, $motivo, $prescricaoId]);

        $snapshotAtual = $this->historicoSaude->saudeResumoPrescricao($prescricaoId);
        if (!$snapshotAtual) {
            throw new Exception('Não foi possível recuperar o status atualizado para o histórico de saúde.');
        }
        if ($snapshotAnterior !== $snapshotAtual) {
            $statusAlterado = $snapshotAnterior['Status atual'] !== $snapshotAtual['Status atual'];
            $tipoEventoStatus = [
                'suspensa' => 'medicamento_suspenso',
                'ativa' => 'medicamento_reativado',
                'finalizada' => 'medicamento_finalizado',
            ][$novoStatus] ?? 'prescricao_alterada';
            if (!$statusAlterado) $tipoEventoStatus = 'prescricao_alterada';
            $descricaoStatus = $statusAlterado
                ? 'Status alterado de ' . $snapshotAnterior['Status atual'] . ' para ' . $snapshotAtual['Status atual'] . '.'
                : 'Dados da prescrição revisados; status permanece ' . $snapshotAtual['Status atual'] . '.';
            if ($motivo !== null && $motivo !== '') {
                $descricaoStatus .= ' Motivo: ' . $motivo;
            }
            $tituloEventoStatus = $statusAlterado
                ? 'Prescrição ' . (function_exists('mb_strtolower') ? mb_strtolower($snapshotAtual['Status atual'], 'UTF-8') : strtolower($snapshotAtual['Status atual'])) . ': ' . $snapshotAtual['Medicamento']
                : 'Prescrição atualizada: ' . $snapshotAtual['Medicamento'];
            $this->historicoSaude->saudeRegistrarEventoMedicamento((int)$snapshotAtual['_acolhido_id'],
                $prescricaoId,
                $tipoEventoStatus,
                $tituloEventoStatus,
                $descricaoStatus,
                ['antes' => $snapshotAnterior, 'atual' => $snapshotAtual]
            );
        }

        $this->pdo->commit();
        $_SESSION['flash_message'] = ['type' => 'info', 'message' => 'Status da prescrição alterado com sucesso!'];
        header("Location: ../views/prescricoes.php?id_acolhido=" . $acolhidoId);
        exit();
    }

    private function excluirMedicamento(): void
    {
        $medicamentoId = filter_var($_POST['medicamento_id'] ?? null, FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1],
        ]);
        if (!$medicamentoId) {
            throw new InvalidArgumentException('Medicamento inválido para exclusão.');
        }
        $this->estoque->excluirMedicamentoSemVinculos($medicamentoId);
        $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'Medicamento excluído com sucesso.'];
    }

    private function excluirPrescricao(): void
    {
        $prescricaoId = self::medicamentoIdDoFormulario('prescricao_id');
        $acolhidoId = self::medicamentoIdDoFormulario('acolhido_id');

        $snapshotAnterior = $this->historicoSaude->saudeResumoPrescricao($prescricaoId);
        if (!$snapshotAnterior || (int)$snapshotAnterior['_acolhido_id'] !== $acolhidoId) {
            throw new InvalidArgumentException('A prescrição selecionada não pertence ao acolhido informado.');
        }
        $this->historicoSaude->saudeRegistrarEventoMedicamento($acolhidoId,
            $prescricaoId,
            'prescricao_removida',
            'Prescrição removida: ' . $snapshotAnterior['Medicamento'],
            'A prescrição foi removida permanentemente do módulo de medicamentos.',
            ['antes' => $snapshotAnterior]
        );

        // O banco aplica as regras de referência dos horários e das dispensações.
        $stmt = (new \Abdm\Model\PersistenciaMedicamentos($this->pdo))->prepararExclusaoPrescricao();
        $stmt->execute([$prescricaoId]);

        $this->pdo->commit();
        $_SESSION['flash_message'] = ['type' => 'warning', 'message' => 'Prescrição excluída permanentemente.'];
        header("Location: ../views/prescricoes.php?id_acolhido=" . $acolhidoId);
        exit();
    }

    private function registrarEntrada(): void
    {
        $medicamentoId = filter_var($_POST['medicamento_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $quantidadeEntrada = filter_var($_POST['quantidade_entrada'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if (!$medicamentoId || !$quantidadeEntrada) {
            throw new InvalidArgumentException('Informe o medicamento e uma quantidade inteira maior que zero.');
        }

        $modoEntrada = $_POST['modo_entrada'] ?? 'direta';
        $usuario = (string)($_SESSION['username'] ?? 'Sistema');

        if ($modoEntrada === 'direta') {
            $this->estoque->registrarEntradaNaUnidadeBase($medicamentoId, $quantidadeEntrada, $usuario);
        } elseif ($modoEntrada === 'embalagem') {
            $fator = filter_var($_POST['fator_conversao'] ?? null, FILTER_VALIDATE_INT, [
                'options' => ['min_range' => 1],
            ]);
            if ($fator === false) {
                throw new InvalidArgumentException('Informe uma quantidade inteira maior que zero por embalagem.');
            }

            $this->estoque->registrarEntradaEstoque(
                $medicamentoId,
                $quantidadeEntrada,
                'embalagem',
                $fator,
                $usuario
            );
        } else {
            throw new InvalidArgumentException('Selecione uma forma de entrada válida.');
        }

        $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'Entrada de estoque registrada com sucesso!'];
    }

    private function registrarSaida(): void
    {
        $medicamentoId = filter_var($_POST['medicamento_id'] ?? null, FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1],
        ]);
        if (!$medicamentoId) {
            throw new InvalidArgumentException('Informe um medicamento válido.');
        }
        $quantidadeSaida = $_POST['quantidade'] ?? '';
        $motivo = is_string($_POST['motivo'] ?? null) ? $_POST['motivo'] : '';
        $observacao = is_string($_POST['observacao'] ?? null) ? $_POST['observacao'] : '';
        if (!in_array($motivo, ['doacao', 'ajuste'], true)) {
            throw new InvalidArgumentException('Selecione um motivo de saída válido.');
        }

        $this->estoque->registrarSaidaEstoque($medicamentoId, $quantidadeSaida, $motivo, $observacao);

        $_SESSION['flash_message'] = ['type' => 'warning', 'message' => 'Saída de estoque registrada com sucesso!'];
    }

    private function registrarDispensacoesLote(): void
    {
        $dataDispensacao = $_POST['data_dispensacao'] ?? '';
        $horarioLote = $_POST['horario_lote'] ?? '';
        $itemsJson = $_POST['items_json'] ?? '';
        if (!is_string($dataDispensacao) || !is_string($horarioLote) || !is_string($itemsJson) || strlen($itemsJson) > 65535) {
            throw new Exception('Dados do lote inválidos.');
        }

        $dataValidada = DateTimeImmutable::createFromFormat('!Y-m-d', $dataDispensacao);
        if (!$dataValidada || $dataValidada->format('Y-m-d') !== $dataDispensacao || $dataDispensacao > date('Y-m-d')) {
            throw new Exception('Data da dispensação inválida.');
        }
        if (!preg_match('/^(?:[01]\\d|2[0-3]):[0-5]\\d$/', $horarioLote)) {
            throw new Exception('Horário do lote inválido.');
        }

        try {
            $items = json_decode($itemsJson, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new Exception('Lista de medicamentos inválida.');
        }
        if (!is_array($items) || count($items) < 1 || count($items) > 300) {
            throw new Exception('Não há medicamentos pendentes para esse horário.');
        }

        $registrados = $this->estoque->registrarDispensacoesLote($items, $dataDispensacao, $horarioLote);

        $this->pdo->commit();
        $_SESSION['flash_message'] = ['type' => 'success', 'message' => $registrados . ' medicamento(s) dispensado(s) às ' . $horarioLote . '.'];
        header('Location: ../views/dispensacao.php?data=' . rawurlencode($dataDispensacao));
        exit();
    }

    private function registrarDispensacao(): void
    {
        $dataDispensacao = $_POST['data_dispensacao'] ?? '';
        $status = $_POST['status'] ?? '';
        $observacao = $_POST['observacao'] ?? '';
        if (!is_string($dataDispensacao) || str_contains($dataDispensacao, "\0")) {
            throw new InvalidArgumentException('Data da dispensação inválida.');
        }
        $dataValidada = DateTimeImmutable::createFromFormat('!Y-m-d', $dataDispensacao);
        if (!$dataValidada || $dataValidada->format('Y-m-d') !== $dataDispensacao || $dataDispensacao > date('Y-m-d')) {
            throw new InvalidArgumentException('Data da dispensação inválida.');
        }
        if (!is_string($status) || !is_string($observacao)) {
            throw new InvalidArgumentException('Status ou observação inválidos.');
        }

        $this->estoque->registrarDispensacao([
            'prescricao_horario_id' => self::medicamentoIdDoFormulario('prescricao_horario_id', true),
            'prescricao_id' => self::medicamentoIdDoFormulario('prescricao_id', true),
            'horario_previsto' => $_POST['horario_previsto'] ?? '',
            'acolhido_id' => self::medicamentoIdDoFormulario('acolhido_id'),
            'medicamento_id' => self::medicamentoIdDoFormulario('medicamento_id'),
        ], $dataDispensacao, $status, $observacao);

        $this->pdo->commit();
        $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'Dispensação registrada com sucesso!'];
        header("Location: ../views/dispensacao.php?data=" . rawurlencode($dataDispensacao));
        exit();
    }
}

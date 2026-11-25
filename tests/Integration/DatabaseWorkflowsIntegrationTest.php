<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class DatabaseWorkflowsIntegrationTest extends TestCase
{
    private static ?PDO $pdo = null;

    protected function setUp(): void
    {
        $testDatabase = getenv('ABDM_TEST_DB_NAME');
        $configuredDatabase = getenv('ABDM_DB_NAME');
        $databaseHost = strtolower(trim((string)getenv('ABDM_DB_HOST')));

        if (!is_string($testDatabase) || $testDatabase === '') {
            self::markTestSkipped('Defina ABDM_TEST_DB_NAME para habilitar a integração em um banco descartável.');
        }
        if (!preg_match('/^abdm_test_[a-z0-9_]+$/i', $testDatabase)
            || $testDatabase === 'abdm'
            || $configuredDatabase !== $testDatabase
            || !in_array($databaseHost, ['127.0.0.1', 'localhost', '::1'], true)
        ) {
            self::fail('A integração só pode conectar a um banco local com prefixo abdm_test_.');
        }

        if (self::$pdo === null) {
            if (getenv('ABDM_ENV') !== 'local') {
                self::fail('A integração só pode iniciar com ABDM_ENV=local.');
            }
            foreach (['ABDM_DB_USER', 'ABDM_DB_PASSWORD', 'ABDM_ENCRYPTION_KEY'] as $variavel) {
                if (getenv($variavel) === false) {
                    self::fail('Defina explicitamente a configuração isolada de teste em ' . $variavel . '.');
                }
            }

            $config = require dirname(__DIR__) . '/fixtures/config_banco.php';
            require_once dirname(__DIR__, 2) . '/config/aplicacao.php';
            if (!isset($pdo) || !$pdo instanceof PDO) {
                self::fail('A conexão MariaDB de teste não foi criada.');
            }
            self::$pdo = $pdo;
            $GLOBALS['pdo'] = self::$pdo;
        }

        if ((string)self::$pdo->query('SELECT DATABASE()')->fetchColumn() !== $testDatabase) {
            self::fail('O nome do banco conectado não corresponde a ABDM_TEST_DB_NAME.');
        }

        self::$pdo->exec("SET SESSION sql_mode = 'STRICT_TRANS_TABLES,NO_ZERO_DATE,NO_ZERO_IN_DATE,NO_ENGINE_SUBSTITUTION'");
        $_SESSION = [];
        self::$pdo->beginTransaction();
    }

    protected function tearDown(): void
    {
        if (self::$pdo instanceof PDO && self::$pdo->inTransaction()) {
            self::$pdo->rollBack();
        }
        $_SESSION = [];
    }

    public function testConfiguracaoForneceTokenCsrfELeDadosCriptografados(): void
    {
        $token = \Abdm\Infraestrutura\Sessao::tokenCsrf();
        self::assertMatchesRegularExpression('/^[0-9a-f]{64}$/', $token);
        self::assertSame($token, \Abdm\Infraestrutura\Sessao::tokenCsrf());
        self::assertStringContainsString($token, \Abdm\Infraestrutura\Sessao::campoTokenCsrf());

        $postAnterior = $_POST;
        $_POST = ['csrf_token' => $token];
        try {
            \Abdm\Infraestrutura\Sessao::exigirTokenCsrfValido();
        } finally {
            $_POST = $postAnterior;
        }

        $textoFicticio = 'Dado fictício para validar o formato legado';
        $cifrado = \Abdm\Infraestrutura\Criptografia::criptografar($textoFicticio);
        self::assertNotSame($cifrado, \Abdm\Infraestrutura\Criptografia::criptografar($textoFicticio));
        self::assertSame($textoFicticio, \Abdm\Infraestrutura\Criptografia::descriptografar($cifrado));
        self::assertSame('Texto legado', \Abdm\Infraestrutura\Criptografia::descriptografar('Texto legado'));
        self::assertSame('IV de criptografia com tamanho incorreto', \Abdm\Infraestrutura\Criptografia::descriptografar(base64_encode('abc::curto')));
    }

    public function testLogoutExigePostComTokenValidoEEncerraSessaoExpirada(): void
    {
        foreach ([
            ['GET', 'token-ficticio', 'ativa', 405, true],
            ['POST', 'ausente', 'ativa', 403, true],
            ['POST', 'invalido', 'ativa', 403, true],
            ['POST', 'token-ficticio', 'ativa', 302, false],
            ['POST', 'ausente', 'expirada', 302, false],
        ] as [$metodo, $token, $estado, $status, $sessao]) {
            $processo = proc_open(
                [PHP_BINARY, dirname(__DIR__) . '/fixtures/encerramento_sessao.php', $metodo, $token, $estado],
                [1 => ['pipe', 'w']],
                $canais
            );
            self::assertIsResource($processo);
            $saida = (string)stream_get_contents($canais[1]);
            fclose($canais[1]);
            self::assertSame(0, proc_close($processo));
            self::assertStringEndsWith(json_encode(['status' => $status, 'sessao' => $sessao], JSON_THROW_ON_ERROR), $saida);
        }
    }

    public function testSchemaDasMigracoesMantemCalculoDerivadoEValorLegado(): void
    {
        $modoSql = (string)self::$pdo->query('SELECT @@SESSION.sql_mode')->fetchColumn();
        self::assertStringContainsString('STRICT_TRANS_TABLES', $modoSql);

        $tipoUso = (string)self::$pdo->query(
            "SELECT COLUMN_TYPE FROM information_schema.COLUMNS "
            . "WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'prescricoes' AND COLUMN_NAME = 'tipo_uso'"
        )->fetchColumn();
        self::assertTrue(\Abdm\Model\Prescricao::prescricaoTipoUsoAceitoPeloEnum($tipoUso, 'periodo_definido'));
        self::assertTrue(\Abdm\Model\Prescricao::prescricaoTipoUsoAceitoPeloEnum($tipoUso, 'se_necessario'));

        self::assertSame(1, $this->countRows(
            "SELECT COUNT(*) FROM prescricoes WHERE tipo_posologia = 'horario_fixo' AND tipo_uso = 'continuo' AND doses_por_dia = 2"
        ));
        self::assertSame(1, $this->countRows(
            "SELECT COUNT(*) FROM prescricoes WHERE tipo_posologia = 'intervalo' AND tipo_uso = 'continuo' AND intervalo_horas = 8 AND doses_por_dia = 3"
        ));
        self::assertSame(1, $this->countRows(
            "SELECT COUNT(*) FROM prescricoes WHERE tipo_posologia = 'horario_fixo' AND tipo_uso = '' AND doses_por_dia = 0"
        ));

        $novoAcolhido = $this->criarAcolhidoFicticio();
        $novoMedicamento = $this->criarMedicamentoFicticio();
        $prescricao = self::$pdo->prepare(
            "INSERT INTO prescricoes "
            . "(acolhido_id, medicamento_id, tipo_posologia, doses_por_dia, intervalo_horas, intervalo_inicio, "
            . "quantidade_por_dose, unidade_dose, tipo_uso, data_inicio) "
            . "VALUES (?, ?, 'intervalo', ?, 8, '06:00:00', 1, 'comprimido', 'periodo_definido', CURDATE())"
        );
        $prescricao->execute([
            $novoAcolhido,
            $novoMedicamento,
            \Abdm\Model\Prescricao::prescricaoCalcularDosesPorDia('intervalo', 8, []),
        ]);
        self::assertSame(3, (int)self::$pdo->query('SELECT doses_por_dia FROM prescricoes WHERE id = LAST_INSERT_ID()')->fetchColumn());
    }

    public function testCadastroDeAcolhidoEAtualizacaoDoPiaPersistemComRelacaoValida(): void
    {
        $acolhidoId = $this->criarAcolhidoFicticio();
        $dadosIniciais = json_encode(['objetivo_ficticio' => 'organizar rotina'], JSON_THROW_ON_ERROR);
        $stmt = self::$pdo->prepare(
            'INSERT INTO pia (acolhido_id, dataAtualizacao, dados_json, criado_em) VALUES (?, CURDATE(), ?, NOW())'
        );
        $stmt->execute([$acolhidoId, $dadosIniciais]);
        $piaId = (int)self::$pdo->lastInsertId();

        $dadosAtualizados = json_encode([
            'objetivo_ficticio' => 'organizar rotina',
            'acao_ficticia' => 'revisar semanalmente',
        ], JSON_THROW_ON_ERROR);
        $atualizar = self::$pdo->prepare('UPDATE pia SET dados_json = ?, dataAtualizacao = CURDATE() WHERE idPIA = ?');
        $atualizar->execute([$dadosAtualizados, $piaId]);

        $salvo = json_decode((string)self::$pdo->query('SELECT dados_json FROM pia WHERE idPIA = ' . $piaId)->fetchColumn(), true, 512, JSON_THROW_ON_ERROR);
        self::assertSame('revisar semanalmente', $salvo['acao_ficticia']);
        self::assertSame($acolhidoId, (int)self::$pdo->query('SELECT acolhido_id FROM pia WHERE idPIA = ' . $piaId)->fetchColumn());
    }

    public function testAutenticacaoDeSessaoEPermissoesDoUsuario(): void
    {
        $login = 'TCC2_' . bin2hex(random_bytes(5));
        $senha = 'SenhaFicticia_2026';
        $stmt = self::$pdo->prepare('INSERT INTO login (login, senha, ativo, is_admin) VALUES (?, ?, 1, 0)');
        $stmt->execute([$login, password_hash($senha, PASSWORD_DEFAULT)]);
        $loginId = (int)self::$pdo->lastInsertId();
        $permissao = self::$pdo->prepare('INSERT INTO login_funcionalidades (Login_idLogin, funcionalidade) VALUES (?, ?)');
        $permissao->execute([$loginId, 'pia']);

        $conta = self::$pdo->prepare('SELECT senha_alterada_em, senha FROM login WHERE idLogin = ?');
        $conta->execute([$loginId]);
        $dadosConta = $conta->fetch(PDO::FETCH_ASSOC);
        self::assertTrue(password_verify($senha, $dadosConta['senha']));

        $_SESSION = [
            'loggedin' => true,
            'id_login' => $loginId,
            'username' => $login,
            'senha_alterada_em' => (string)$dadosConta['senha_alterada_em'],
            'last_activity' => time(),
        ];
        (new \Abdm\Infraestrutura\ControleAcesso(self::$pdo))->exigirUsuarioAutenticado();

        self::assertTrue((new \Abdm\Infraestrutura\ControleAcesso(self::$pdo))->usuarioPode('pia'));
        self::assertFalse((new \Abdm\Infraestrutura\ControleAcesso(self::$pdo))->usuarioPode('estoque'));

        $adminLogin = 'TCC2_ADMIN_' . bin2hex(random_bytes(4));
        $criarAdmin = self::$pdo->prepare('INSERT INTO login (login, senha, ativo, is_admin) VALUES (?, ?, 1, 1)');
        $criarAdmin->execute([$adminLogin, password_hash($senha, PASSWORD_DEFAULT)]);
        $adminId = (int)self::$pdo->lastInsertId();
        $_SESSION['id_login'] = $adminId;
        $_SESSION['is_admin'] = true;
        self::assertTrue((new \Abdm\Infraestrutura\ControleAcesso(self::$pdo))->usuarioPode('dispensacao'));
    }

    public function testCadastroProfissionalFicticioVinculaContaEnderecoEPermissaoLegada(): void
    {
        $login = 'TCC2_PERFIL_' . bin2hex(random_bytes(4));
        $criarLogin = self::$pdo->prepare('INSERT INTO login (login, senha, ativo, is_admin) VALUES (?, ?, 1, 0)');
        $criarLogin->execute([$login, password_hash('SenhaFicticia_2026', PASSWORD_DEFAULT)]);
        $loginId = (int)self::$pdo->lastInsertId();

        $endereco = self::$pdo->prepare('INSERT INTO endereco (estado, cidade, rua, numero, cep) VALUES (?, ?, ?, ?, ?)');
        $endereco->execute(['ES', 'Cidade Fictícia', 'Rua de Teste', 10, '29000000']);
        $enderecoId = (int)self::$pdo->lastInsertId();

        $permissao = self::$pdo->prepare('INSERT INTO permissao (cod, nome, descricao) VALUES (?, ?, ?)');
        $permissao->execute(['ABDM1', 'Perfil de teste', 'Registro fictício da suíte de integração']);
        $permissaoId = (int)self::$pdo->lastInsertId();

        $perfil = self::$pdo->prepare(
            'INSERT INTO usuario (nome, cpf, dataNascimento, email, registroProfissional, telefone, atuacaoProfissional, '
            . 'Endereco_idEndereco, Login_idLogin, Permisoes_idPermisoes, estadoCivil, cargoConselho, inicioMandatoConselho, '
            . 'fimMandatoConselho, dependentes, comorbidades, CBO, matricula, IsFuncionario) '
            . 'VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $perfil->execute([
            'TCC2 profissional fictício', 'TCC2TESTE01', '1990-01-01', null, null, null, 'Atuação fictícia',
            $enderecoId, $loginId, $permissaoId, null, 'Conselheiro fictício', '2026-01-01', '2026-12-31',
            null, null, null, 'F2' . bin2hex(random_bytes(5)), 1,
        ]);
        $usuarioId = (int)self::$pdo->lastInsertId();

        $consulta = self::$pdo->prepare(
            'SELECT u.nome, u.IsFuncionario, l.ativo, p.cod, e.cep '
            . 'FROM usuario u JOIN login l ON l.idLogin = u.Login_idLogin '
            . 'JOIN permissao p ON p.idPermisoes = u.Permisoes_idPermisoes '
            . 'JOIN endereco e ON e.idEndereco = u.Endereco_idEndereco WHERE u.idUsuario = ?'
        );
        $consulta->execute([$usuarioId]);
        $salvo = $consulta->fetch(PDO::FETCH_ASSOC);

        self::assertSame('TCC2 profissional fictício', $salvo['nome']);
        self::assertSame(1, (int)$salvo['IsFuncionario']);
        self::assertSame(1, (int)$salvo['ativo']);
        self::assertSame('ABDM1', $salvo['cod']);
        self::assertSame('29000000', $salvo['cep']);
    }

    public function testEntradaDeEstoqueRegistraConversaoELancamentoRF017(): void
    {
        $_SESSION['username'] = 'TCC2_OPERADOR_FICTICIO';
        $medicamentoId = $this->criarMedicamentoFicticio();

        $estoqueId = (new \Abdm\Model\EstoqueMedicamentos(self::$pdo))->registrarEntradaEstoque($medicamentoId,
            2,
            'caixa',
            30,
            $_SESSION['username']
        );

        $consultaEstoque = self::$pdo->prepare(
            'SELECT quantidade_entrada, unidade_entrada, fator_conversao, quantidade_atual_base '
            . 'FROM medicamentos_estoque WHERE id = ? AND medicamento_id = ?'
        );
        $consultaEstoque->execute([$estoqueId, $medicamentoId]);
        $estoque = $consultaEstoque->fetch(PDO::FETCH_ASSOC);
        self::assertSame(2, (int)$estoque['quantidade_entrada']);
        self::assertSame('caixa', $estoque['unidade_entrada']);
        self::assertSame(30, (int)$estoque['fator_conversao']);
        self::assertSame(60, (int)$estoque['quantidade_atual_base']);
        self::assertSame(1, $this->countRows("SELECT COUNT(*) FROM medicamentos_movimentacoes WHERE estoque_id = {$estoqueId} AND tipo = 'entrada' AND quantidade_movimentada = 60"));
    }

    public function testEntradaNaUnidadeBaseUsaCadastroEMantemFatorUm(): void
    {
        $medicamentoId = $this->criarMedicamentoFicticio();
        self::$pdo->prepare("UPDATE medicamentos SET unidade_base = 'ml' WHERE id = ?")->execute([$medicamentoId]);

        $estoqueId = (new \Abdm\Model\EstoqueMedicamentos(self::$pdo))
            ->registrarEntradaNaUnidadeBase($medicamentoId, 30, 'TCC2');
        $consulta = self::$pdo->prepare(
            'SELECT unidade_entrada, fator_conversao, quantidade_atual_base FROM medicamentos_estoque WHERE id = ?'
        );
        $consulta->execute([$estoqueId]);
        $registro = $consulta->fetch(PDO::FETCH_ASSOC);
        self::assertSame('ml', $registro['unidade_entrada']);
        self::assertSame(1, (int)$registro['fator_conversao']);
        self::assertSame('30.00', $registro['quantidade_atual_base']);
    }

    public function testDispensacaoEmGotasMantemDoseEBaixaVolumeAproximado(): void
    {
        [, $medicamentoId, $horarioId] = $this->criarPrescricaoComEstoque(10, 5, 'gota');
        self::$pdo->prepare("UPDATE medicamentos SET unidade_base = 'ml' WHERE id = ?")->execute([$medicamentoId]);

        $estoque = new \Abdm\Model\EstoqueMedicamentos(self::$pdo, [$medicamentoId => 30]);
        if ($estoque->aceitaEquivalenciaNoCadastro()) {
            $estoque->salvarEquivalenciaGotasPorMl($medicamentoId, 30);
        }
        $estoque->registrarDispensacao(['prescricao_horario_id' => $horarioId], date('Y-m-d'), 'dispensado', '');

        $consulta = self::$pdo->prepare(
            "SELECT e.quantidade_atual_base, m.quantidade_movimentada, m.observacao
               FROM medicamentos_estoque e JOIN medicamentos_movimentacoes m ON m.estoque_id = e.id
              WHERE e.medicamento_id = ? AND m.tipo = 'dispensacao'"
        );
        $consulta->execute([$medicamentoId]);
        $movimento = $consulta->fetch(PDO::FETCH_ASSOC);
        self::assertSame('9.83', $movimento['quantidade_atual_base']);
        self::assertSame('0.17', $movimento['quantidade_movimentada']);
        self::assertStringContainsString('0.17 mL para 5 gotas', $movimento['observacao']);

        $consulta = self::$pdo->prepare('SELECT quantidade_dispensada FROM dispensacoes WHERE prescricao_horario_id = ?');
        $consulta->execute([$horarioId]);
        self::assertSame('5.00', $consulta->fetchColumn());
    }

    public function testConsultasDeRelatorioPreservamMencoesDaTelaEDoPdf(): void
    {
        $pdo = self::$pdo;
        $acolhidoId = $this->criarAcolhidoFicticio();
        $conta = $pdo->prepare('INSERT INTO login (login, senha, ativo, is_admin) VALUES (?, ?, 1, 0)');
        $conta->execute(['relato_' . bin2hex(random_bytes(5)), password_hash('FicticiaTeste', PASSWORD_DEFAULT)]);
        $loginId = (int)$pdo->lastInsertId();
        $relato = $pdo->prepare(
            "INSERT INTO relatos_diarios (data_relato, periodo, conteudo, autorLogin_idLogin, criado_em)
             VALUES (CURDATE(), 'dia', 'Relato fictício', ?, NOW())"
        );
        $relato->execute([$loginId]);
        $relatoId = (int)$pdo->lastInsertId();
        $pdo->prepare('INSERT INTO relatos_diarios_acolhidos (idRelato, acolhido_id) VALUES (?, ?)')
            ->execute([$relatoId, $acolhidoId]);

        $consultas = new \Abdm\Model\ConsultasRelatorio($pdo);
        $tela = $consultas->mencoesComIdentificador([$relatoId]);
        self::assertCount(1, $tela);
        self::assertSame($acolhidoId, (int)$tela[0]['id']);
        self::assertSame($relatoId, (int)$tela[0]['idRelato']);
        self::assertSame([['idRelato' => $tela[0]['idRelato'], 'nome' => $tela[0]['nome']]], $consultas->mencoes([$relatoId]));
    }

    public function testEdicaoDeMedicamentoAtualizaDadosERestringeUnidadeComVinculosRF020(): void
    {
        $medicamentoId = $this->criarMedicamentoFicticio();
        (new \Abdm\Model\EstoqueMedicamentos(self::$pdo))->atualizarCadastroMedicamento($medicamentoId, [
            'Medicamento fictício atualizado',
            'Princípio fictício atualizado',
            'Apresentação fictícia',
            'comprimido',
        ]);

        $consulta = self::$pdo->prepare('SELECT nome_comercial, principio_ativo, apresentacao, unidade_base FROM medicamentos WHERE id = ?');
        $consulta->execute([$medicamentoId]);
        self::assertSame([
            'nome_comercial' => 'Medicamento fictício atualizado',
            'principio_ativo' => 'Princípio fictício atualizado',
            'apresentacao' => 'Apresentação fictícia',
            'unidade_base' => 'comprimido',
        ], $consulta->fetch(PDO::FETCH_ASSOC));

        (new \Abdm\Model\EstoqueMedicamentos(self::$pdo))->registrarEntradaEstoque($medicamentoId, 1, 'caixa', 10, 'TCC2');
        try {
            (new \Abdm\Model\EstoqueMedicamentos(self::$pdo))->atualizarCadastroMedicamento($medicamentoId, [
                'Medicamento fictício', 'Princípio fictício', null, 'ml',
            ]);
            self::fail('A unidade base não deve mudar enquanto houver estoque vinculado.');
        } catch (InvalidArgumentException $erro) {
            self::assertStringContainsString('unidade base não pode ser alterada', $erro->getMessage());
        }
        self::assertSame('comprimido', $this->unidadeBase($medicamentoId));
    }

    public function testExclusaoPreservaHistoricoMesmoComEstoqueZerado(): void
    {
        $medicamentoId = $this->criarMedicamentoFicticio();
        (new \Abdm\Model\EstoqueMedicamentos(self::$pdo))->registrarEntradaEstoque($medicamentoId, 1, 'unidade', 1, 'Teste');
        (new \Abdm\Model\EstoqueMedicamentos(self::$pdo))->registrarSaidaEstoque($medicamentoId, 1, 'ajuste', 'Teste');

        try {
            (new \Abdm\Model\EstoqueMedicamentos(self::$pdo))->excluirMedicamentoSemVinculos($medicamentoId);
            self::fail('O histórico de estoque deve impedir a exclusão.');
        } catch (InvalidArgumentException $exception) {
            self::assertStringContainsString('histórico de estoque', $exception->getMessage());
        }

        $stmt = self::$pdo->prepare(
            'SELECT COUNT(*) FROM medicamentos_movimentacoes m '
            . 'JOIN medicamentos_estoque e ON e.id = m.estoque_id WHERE e.medicamento_id = ?'
        );
        $stmt->execute([$medicamentoId]);
        self::assertSame(2, (int)$stmt->fetchColumn());
        self::assertSame(0, $this->quantidadeTotalEstoque($medicamentoId));
    }

    public function testPrescricaoFixaEPorIntervaloGravamDosesDerivadasEmModoEstrito(): void
    {
        $acolhidoId = $this->criarAcolhidoFicticio();
        $medicamentoId = $this->criarMedicamentoFicticio();
        $stmtPeriodo = self::$pdo->prepare('INSERT INTO periodos (nome, ordem) VALUES (?, 1)');
        $stmtPeriodo->execute(['TCC2 fictício ' . bin2hex(random_bytes(3))]);
        $periodoId = (int)self::$pdo->lastInsertId();

        $horariosFixos = \Abdm\Model\Prescricao::prescricaoPrepararHorariosFixos([
            ['hora' => '08:00', 'periodo_id' => (string)$periodoId, 'quantidade_por_dose' => '1', 'unidade_dose' => 'comprimido'],
            ['hora' => '20:00', 'periodo_id' => (string)$periodoId, 'quantidade_por_dose' => '1', 'unidade_dose' => 'comprimido'],
        ]);
        $dosesFixas = \Abdm\Model\Prescricao::prescricaoCalcularDosesPorDia('horario_fixo', null, $horariosFixos);
        $stmtPrescricao = self::$pdo->prepare(
            "INSERT INTO prescricoes (acolhido_id, medicamento_id, tipo_posologia, doses_por_dia, tipo_uso, data_inicio) "
            . "VALUES (?, ?, 'horario_fixo', ?, 'se_necessario', CURDATE())"
        );
        $stmtPrescricao->execute([$acolhidoId, $medicamentoId, $dosesFixas]);
        $prescricaoId = (int)self::$pdo->lastInsertId();

        $stmtHorario = self::$pdo->prepare(
            'INSERT INTO prescricao_horarios (prescricao_id, horario, periodo_id, quantidade_por_dose, unidade_dose) VALUES (?, ?, ?, 1, ?)'
        );
        foreach ($horariosFixos as $horario) {
            $stmtHorario->execute([$prescricaoId, $horario['hora'], $horario['periodo_id'], $horario['unidade_dose']]);
        }

        $stmtIntervalo = self::$pdo->prepare(
            "INSERT INTO prescricoes (acolhido_id, medicamento_id, tipo_posologia, doses_por_dia, intervalo_horas, intervalo_inicio, "
            . "quantidade_por_dose, unidade_dose, tipo_uso, data_inicio) "
            . "VALUES (?, ?, 'intervalo', ?, 8, '06:00:00', 1, 'comprimido', 'periodo_definido', CURDATE())"
        );
        $stmtIntervalo->execute([
            $acolhidoId,
            $medicamentoId,
            \Abdm\Model\Prescricao::prescricaoCalcularDosesPorDia('intervalo', 8, []),
        ]);

        self::assertSame(2, (int)self::$pdo->query('SELECT doses_por_dia FROM prescricoes WHERE id = ' . $prescricaoId)->fetchColumn());
        self::assertSame(2, $this->countRows('SELECT COUNT(*) FROM prescricao_horarios WHERE prescricao_id = ' . $prescricaoId));
        self::assertSame(3, (int)self::$pdo->query('SELECT doses_por_dia FROM prescricoes WHERE id = LAST_INSERT_ID()')->fetchColumn());
    }

    public function testDispensacaoRegistraDoseBaixaEstoqueELancaMovimentacao(): void
    {
        $_SESSION['username'] = 'TCC2_DISPENSADOR_FICTICIO';
        [$acolhidoId, $medicamentoId, $horarioId] = $this->criarPrescricaoComEstoque(10, 2);

        $horario = (new \Abdm\Model\EstoqueMedicamentos(self::$pdo))->registrarDispensacao(['prescricao_horario_id' => (string)$horarioId],
            date('Y-m-d'),
            'dispensado',
            'Registro fictício de teste'
        );

        self::assertSame('08:00', $horario);
        self::assertSame(1, $this->countRows(
            "SELECT COUNT(*) FROM dispensacoes WHERE acolhido_id = {$acolhidoId} AND medicamento_id = {$medicamentoId} AND status = 'dispensado'"
        ));
        self::assertSame(8, $this->quantidadeTotalEstoque($medicamentoId));
        self::assertSame(1, $this->countRows("SELECT COUNT(*) FROM medicamentos_movimentacoes WHERE tipo = 'dispensacao' AND observacao = 'Dispensado via quadro de dispensacao.'"));
    }

    public function testDispensacaoRecusadaFazRollbackCompletoSeEstoqueForInsuficiente(): void
    {
        $_SESSION['username'] = 'TCC2_DISPENSADOR_FICTICIO';
        [$acolhidoId, $medicamentoId, $horarioId] = $this->criarPrescricaoComEstoque(1, 2);
        self::$pdo->exec('SAVEPOINT antes_da_dispensacao');

        try {
            (new \Abdm\Model\EstoqueMedicamentos(self::$pdo))->registrarDispensacao(['prescricao_horario_id' => (string)$horarioId],
                date('Y-m-d'),
                'dispensado',
                'Registro fictício de estoque insuficiente'
            );
            self::fail('A dispensação deveria falhar por falta de estoque.');
        } catch (Exception $erro) {
            self::assertStringContainsString('Estoque insuficiente', $erro->getMessage());
        }

        self::$pdo->exec('ROLLBACK TO SAVEPOINT antes_da_dispensacao');
        self::assertSame(1, $this->quantidadeTotalEstoque($medicamentoId));
        self::assertSame(0, $this->countRows("SELECT COUNT(*) FROM dispensacoes WHERE acolhido_id = {$acolhidoId} AND medicamento_id = {$medicamentoId}"));
        self::assertSame(0, $this->countRows("SELECT COUNT(*) FROM medicamentos_movimentacoes WHERE tipo = 'dispensacao'"));
    }

    public function testBancoRejeitaReferenciaDePiaInexistente(): void
    {
        try {
            self::$pdo->exec("INSERT INTO pia (acolhido_id, dados_json) VALUES (2147483647, '{\"ficticio\":true}')");
            self::fail('A chave estrangeira deveria rejeitar o acolhido inexistente.');
        } catch (PDOException $erro) {
            self::assertSame('23000', (string)$erro->getCode());
        }
    }

    public function testRotaDeImpressaoDePrescricaoGeraPdfComDadosFicticios(): void
    {
        $acolhidoId = $this->criarAcolhidoFicticio();
        $medicamentoId = $this->criarMedicamentoFicticio();
        $periodo = self::$pdo->prepare('INSERT INTO periodos (nome, ordem) VALUES (?, 1)');
        $periodo->execute(['TCC2 PDF ' . bin2hex(random_bytes(3))]);
        $periodoId = (int)self::$pdo->lastInsertId();
        $prescricao = self::$pdo->prepare(
            "INSERT INTO prescricoes (acolhido_id, medicamento_id, tipo_posologia, doses_por_dia, tipo_uso, data_inicio) "
            . "VALUES (?, ?, 'horario_fixo', 1, 'continuo', CURDATE())"
        );
        $prescricao->execute([$acolhidoId, $medicamentoId]);
        $prescricaoId = (int)self::$pdo->lastInsertId();
        $horario = self::$pdo->prepare(
            "INSERT INTO prescricao_horarios (prescricao_id, horario, periodo_id, quantidade_por_dose, unidade_dose) "
            . "VALUES (?, '08:00:00', ?, 1, 'comprimido')"
        );
        $horario->execute([$prescricaoId, $periodoId]);

        $login = 'TCC2_PDF_' . bin2hex(random_bytes(4));
        $conta = self::$pdo->prepare('INSERT INTO login (login, senha, ativo, is_admin) VALUES (?, ?, 1, 0)');
        $conta->execute([$login, password_hash('SenhaFicticia_2026', PASSWORD_DEFAULT)]);
        $loginId = (int)self::$pdo->lastInsertId();
        $permissao = self::$pdo->prepare('INSERT INTO login_funcionalidades (Login_idLogin, funcionalidade) VALUES (?, ?)');
        $permissao->execute([$loginId, 'prescricoes']);
        $versaoSenha = self::$pdo->prepare('SELECT senha_alterada_em FROM login WHERE idLogin = ?');
        $versaoSenha->execute([$loginId]);
        $_SESSION = [
            'loggedin' => true,
            'id_login' => $loginId,
            'username' => $login,
            'senha_alterada_em' => (string)$versaoSenha->fetchColumn(),
            'last_activity' => time(),
        ];

        $getAnterior = $_GET;
        $_GET = ['id_acolhido' => (string)$acolhidoId, 'tipo' => 'plano'];
        $pdo = self::$pdo;
        $nivelBufferInicial = ob_get_level();
        ob_start();
        try {
            include dirname(__DIR__, 2) . '/src/View/templates/gerar_prescricao_pdf.php';
            $pdf = (string)ob_get_contents();
        } finally {
            while (ob_get_level() > $nivelBufferInicial) {
                ob_end_clean();
            }
            $_GET = $getAnterior;
        }

        self::assertStringStartsWith('%PDF-', $pdf);
        self::assertGreaterThan(1000, strlen($pdf));
    }

    private function criarAcolhidoFicticio(): int
    {
        $stmt = self::$pdo->prepare(
            "INSERT INTO acolhido (nome, cpf, matricula, status) VALUES (?, ?, ?, 'ativo')"
        );
        $stmt->execute([
            'TCC2 acolhido fictício',
            'TCC2-FICTICIO-' . bin2hex(random_bytes(5)),
            'T2' . bin2hex(random_bytes(5)),
        ]);
        return (int)self::$pdo->lastInsertId();
    }

    private function criarMedicamentoFicticio(): int
    {
        $stmt = self::$pdo->prepare(
            "INSERT INTO medicamentos (nome_comercial, principio_ativo, apresentacao, unidade_base) VALUES (?, ?, ?, 'comprimido')"
        );
        $token = bin2hex(random_bytes(4));
        $stmt->execute(['TCC2 medicamento ' . $token, 'TCC2 princípio fictício', 'Apresentação de teste']);
        return (int)self::$pdo->lastInsertId();
    }

    public function testDispensacoesConcorrentesRegistramUmaUnicaDose(): void
    {
        $this->verificarConsumoConcorrente('dose', 10, 4, 6, 1, 'Esta dose já possui um registro de dispensação.');
    }

    public function testDispensacoesFracionariasPreservamSaldoEValoresGravados(): void
    {
        foreach (['0.25' => '9.75', '0.50' => '9.50', '1.50' => '8.50'] as $dose => $saldo) {
            [$acolhidoId, $medicamentoId, $horarioId] = $this->criarPrescricaoComEstoque(10, $dose, 'ml');
            $item = [
                'prescricao_horario_id' => $horarioId,
                'acolhido_id' => $acolhidoId,
                'medicamento_id' => $medicamentoId,
            ];
            self::assertSame('08:00', (new \Abdm\Model\EstoqueMedicamentos(self::$pdo))->registrarDispensacao($item, date('Y-m-d'), 'dispensado', ''));
            $this->verificarQuantidadesFracionarias($medicamentoId, $saldo, $dose);
        }
    }

    public function testSaidasFracionariasSucessivasConsomemEntradasEmOrdem(): void
    {
        [, $medicamentoId, $horarioId] = $this->criarPrescricaoComEstoque(1, '0.75', 'ml');
        $segundaEntrada = (new \Abdm\Model\EstoqueMedicamentos(self::$pdo))->registrarEntradaEstoque($medicamentoId, 1, 'frasco', 2, 'Teste');
        (new \Abdm\Model\EstoqueMedicamentos(self::$pdo))->registrarDispensacao(['prescricao_horario_id' => $horarioId], date('Y-m-d'), 'dispensado', '');
        (new \Abdm\Model\EstoqueMedicamentos(self::$pdo))->registrarSaidaEstoque($medicamentoId, '0.50', 'ajuste', '');
        $this->verificarQuantidadesFracionarias($medicamentoId, '1.75', '0.75');
        $stmt = self::$pdo->prepare('SELECT quantidade_atual_base FROM medicamentos_estoque WHERE id = ?');
        $stmt->execute([$segundaEntrada]);
        self::assertSame('1.75', $stmt->fetchColumn());
        (new \Abdm\Model\EstoqueMedicamentos(self::$pdo))->registrarSaidaEstoque($medicamentoId, '1.75', 'ajuste', '');
        $this->verificarQuantidadesFracionarias($medicamentoId, '0.00', '0.75');
    }

    public function testLoteComFalhaReverteDispensacaoEBaixasFracionarias(): void
    {
        $pdo = self::$pdo;
        [, $primeiroMedicamento, $primeiroHorario] = $this->criarPrescricaoComEstoque(2, '0.25', 'ml');
        [, $segundoMedicamento, $segundoHorario] = $this->criarPrescricaoComEstoque(1, '1.25', 'ml');
        $pdo->exec('SAVEPOINT iniciar_lote');
        try {
            (new \Abdm\Model\EstoqueMedicamentos($pdo))->registrarDispensacoesLote([
                ['prescricao_horario_id' => $primeiroHorario],
                ['prescricao_horario_id' => $segundoHorario],
            ], date('Y-m-d'), '08:00');
            self::fail('O lote deve falhar por falta de estoque.');
        } catch (Exception $exception) {
            self::assertStringContainsString('Estoque insuficiente', $exception->getMessage());
            $pdo->exec('ROLLBACK TO SAVEPOINT iniciar_lote');
        }
        $this->verificarQuantidadesFracionarias($primeiroMedicamento, '2.00', null);
        $this->verificarQuantidadesFracionarias($segundoMedicamento, '1.00', null);
    }

    public function testLoteFracionarioConfirmaSaldoDeCadaMedicamento(): void
    {
        [, $primeiroMedicamento, $primeiroHorario] = $this->criarPrescricaoComEstoque(2, '0.25', 'ml');
        [, $segundoMedicamento, $segundoHorario] = $this->criarPrescricaoComEstoque(1, '0.50', 'ml');
        self::assertSame(2, (new \Abdm\Model\EstoqueMedicamentos(self::$pdo))->registrarDispensacoesLote([
            ['prescricao_horario_id' => $primeiroHorario],
            ['prescricao_horario_id' => $segundoHorario],
        ], date('Y-m-d'), '08:00'));
        $this->verificarQuantidadesFracionarias($primeiroMedicamento, '1.75', '0.25');
        $this->verificarQuantidadesFracionarias($segundoMedicamento, '0.50', '0.50');
    }

    public function testDispensaMeioComprimidoEQuatorzeComprimidosEMeio(): void
    {
        [, $medicamentoFixo, $horario] = $this->criarPrescricaoComEstoque(15, '0.50');
        [, $medicamentoIntervalo, $horarioIntervalo] = $this->criarPrescricaoComEstoque(15, '14.50');
        $pdo = self::$pdo;
        $consulta = $pdo->prepare('SELECT prescricao_id FROM prescricao_horarios WHERE id = ?');
        $consulta->execute([$horarioIntervalo]);
        $idIntervalo = (int)$consulta->fetchColumn();
        $pdo->prepare("UPDATE prescricoes SET tipo_posologia = 'intervalo', intervalo_horas = 8, intervalo_inicio = '08:00' WHERE id = ?")
            ->execute([$idIntervalo]);

        self::assertSame(50, \Abdm\Model\EstoqueMedicamentos::validarDoseComprimido('0,5'));
        self::assertSame(1450, \Abdm\Model\EstoqueMedicamentos::validarDoseComprimido('14,5'));
        self::assertSame('08:00', (new \Abdm\Model\EstoqueMedicamentos($pdo))->registrarDispensacao(['prescricao_horario_id' => $horario], date('Y-m-d'), 'dispensado', ''));
        self::assertSame(1, (new \Abdm\Model\EstoqueMedicamentos($pdo))->registrarDispensacoesLote([
            ['prescricao_id' => $idIntervalo, 'horario_previsto' => '08:00'],
        ], date('Y-m-d'), '08:00'));
        $this->verificarQuantidadesFracionarias($medicamentoFixo, '14.50', '0.50');
        $this->verificarQuantidadesFracionarias($medicamentoIntervalo, '0.50', '14.50');
    }

    public function testSubstituicaoDosHorariosNaEdicaoPreservaDispensacao(): void
    {
        [, $medicamentoId, $horario] = $this->criarPrescricaoComEstoque(2, '0.50');
        $pdo = self::$pdo;
        (new \Abdm\Model\EstoqueMedicamentos($pdo))->registrarDispensacao(['prescricao_horario_id' => $horario], date('Y-m-d'), 'dispensado', '');
        $stmt = $pdo->prepare('SELECT prescricao_id FROM prescricao_horarios WHERE id = ?');
        $stmt->execute([$horario]);
        $prescricaoId = (int)$stmt->fetchColumn();

        (new \Abdm\Model\Prescricao($pdo))->prescricaoRemoverHorariosParaEdicao($prescricaoId);

        $stmt->execute([$horario]);
        self::assertFalse($stmt->fetchColumn());
        $stmt = $pdo->prepare('SELECT prescricao_horario_id, horario_previsto FROM dispensacoes WHERE prescricao_id = ?');
        $stmt->execute([$prescricaoId]);
        self::assertSame(['prescricao_horario_id' => null, 'horario_previsto' => '08:00:00'], $stmt->fetch(PDO::FETCH_ASSOC));
        $this->verificarQuantidadesFracionarias($medicamentoId, '1.50', '0.50');
    }

    public function testBaixaRecusaUnidadeIncompativelEFracaoForaDaRegra(): void
    {
        [, $medicamentoId] = $this->criarPrescricaoComEstoque(2, 1);
        foreach (['ml' => ['1.00', 'unidade'], 'comprimido' => ['0.25', 'múltiplos de 0,5']] as $unidade => [$quantidade, $mensagem]) {
            try {
                (new \Abdm\Model\EstoqueMedicamentos(self::$pdo))->registrarSaidaEstoque($medicamentoId, $quantidade, 'dispensacao', '', $unidade);
                self::fail('A configuração da dose deve ser recusada.');
            } catch (InvalidArgumentException $exception) {
                self::assertStringContainsString($mensagem, $exception->getMessage());
            }
        }
        $this->verificarQuantidadesFracionarias($medicamentoId, '2.00', null);
    }

    private function verificarQuantidadesFracionarias(int $medicamentoId, string $saldo, ?string $dose): void
    {
        $pdo = self::$pdo;
        $stmt = $pdo->prepare('SELECT SUM(quantidade_atual_base) FROM medicamentos_estoque WHERE medicamento_id = ?');
        $stmt->execute([$medicamentoId]);
        self::assertSame($saldo, $stmt->fetchColumn());
        $stmt = $pdo->prepare('SELECT quantidade_dispensada FROM dispensacoes WHERE medicamento_id = ?');
        $stmt->execute([$medicamentoId]);
        self::assertSame($dose === null ? [] : [$dose], $stmt->fetchAll(PDO::FETCH_COLUMN));
        $stmt = $pdo->prepare(
            "SELECT SUM(m.quantidade_movimentada) FROM medicamentos_movimentacoes m
             JOIN medicamentos_estoque e ON e.id = m.estoque_id WHERE e.medicamento_id = ? AND m.tipo = 'dispensacao'"
        );
        $stmt->execute([$medicamentoId]);
        self::assertSame($dose, $stmt->fetchColumn());
    }

    public function testSaidasConcorrentesComSaldoSuficienteRegistramDuasBaixas(): void
    {
        $this->verificarConsumoConcorrente('saida', 10, 4, 2, 2, 'confirmado');
    }

    public function testSaidaConcorrenteSemSaldoReverteBaixaParcial(): void
    {
        $this->verificarConsumoConcorrente('saida', 6, 4, 2, 1, 'Estoque insuficiente.');
    }

    public function testSaidasFracionariasConcorrentesPreservamCentesimos(): void
    {
        $this->verificarConsumoConcorrente('saida', 1, '0.25', '0.50', 2, 'confirmado', 'ml');
    }

    private function verificarConsumoConcorrente(
        string $operacao,
        int $estoque,
        int|string $quantidade,
        int|string $saldoFinal,
        int $saidasEsperadas,
        string $resultadoEsperado,
        string $unidade = 'comprimido'
    ): void {
        $pdo = self::$pdo;
        [$acolhidoId, $medicamentoId, $horarioId] = $this->criarPrescricaoComEstoque($estoque, $quantidade, $unidade);
        $periodo = $pdo->prepare('SELECT periodo_id FROM prescricao_horarios WHERE id = ?');
        $periodo->execute([$horarioId]);
        $periodoId = (int)$periodo->fetchColumn();
        $pdo->commit();

        $processo = null;
        $canais = [];
        try {
            $pdo->beginTransaction();
            $data = date('Y-m-d');
            if ($operacao === 'dose') {
                (new \Abdm\Model\EstoqueMedicamentos($pdo))->registrarDispensacao(['prescricao_horario_id' => $horarioId], $data, 'dispensado', '');
            } else {
                (new \Abdm\Model\EstoqueMedicamentos($pdo))->registrarSaidaEstoque($medicamentoId, $quantidade, 'ajuste', 'Teste concorrente');
            }

            $alvo = $operacao === 'dose' ? $horarioId : $medicamentoId;
            $processo = proc_open(
                [PHP_BINARY, dirname(__DIR__) . '/fixtures/operacao_concorrente.php', $operacao, (string)$alvo, (string)$quantidade, $data],
                [1 => ['pipe', 'w']],
                $canais
            );
            self::assertIsResource($processo);
            $conexao = (int)fgets($canais[1]);
            self::assertGreaterThan(0, $conexao);

            // Só libera a primeira transação depois de observar a consulta da segunda no servidor.
            $consulta = $pdo->prepare('SELECT INFO FROM information_schema.PROCESSLIST WHERE ID = ?');
            $limite = microtime(true) + 5;
            do {
                $consulta->execute([$conexao]);
                $sqlEmExecucao = (string)$consulta->fetchColumn();
                if (str_contains($sqlEmExecucao, 'FOR UPDATE')) {
                    break;
                }
                usleep(20000);
            } while (microtime(true) < $limite);
            self::assertStringContainsString('FOR UPDATE', $sqlEmExecucao);

            $pdo->commit();
            self::assertStringStartsWith($resultadoEsperado, (string)stream_get_contents($canais[1]));
            $saldoEmCentesimos = \Abdm\Model\EstoqueMedicamentos::estoqueQuantidadeEmCentesimos($saldoFinal);
            $consultaSaldo = $pdo->prepare('SELECT SUM(quantidade_atual_base) FROM medicamentos_estoque WHERE medicamento_id = ?');
            $consultaSaldo->execute([$medicamentoId]);
            self::assertSame($saldoEmCentesimos, \Abdm\Model\EstoqueMedicamentos::estoqueQuantidadeEmCentesimos($consultaSaldo->fetchColumn()));

            $movimentos = $pdo->prepare(
                "SELECT COUNT(*) AS total, SUM(m.quantidade_movimentada) AS quantidade "
                . 'FROM medicamentos_movimentacoes m JOIN medicamentos_estoque e ON e.id = m.estoque_id '
                . "WHERE e.medicamento_id = ? AND m.tipo <> 'entrada'"
            );
            $movimentos->execute([$medicamentoId]);
            $totais = $movimentos->fetch(PDO::FETCH_ASSOC);
            self::assertSame($saidasEsperadas, (int)$totais['total']);
            self::assertSame($estoque * 100 - $saldoEmCentesimos, \Abdm\Model\EstoqueMedicamentos::estoqueQuantidadeEmCentesimos($totais['quantidade']));
            $doses = $pdo->prepare('SELECT COUNT(*) FROM dispensacoes WHERE medicamento_id = ?');
            $doses->execute([$medicamentoId]);
            self::assertSame($operacao === 'dose' ? 1 : 0, (int)$doses->fetchColumn());
        } finally {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            if (is_resource($processo)) {
                stream_get_contents($canais[1]);
                fclose($canais[1]);
                proc_close($processo);
            }
            $pdo->beginTransaction();
            try {
                $pdo->prepare('DELETE FROM medicamentos WHERE id = ?')->execute([$medicamentoId]);
                $pdo->prepare('DELETE FROM acolhido WHERE id = ?')->execute([$acolhidoId]);
                $pdo->prepare('DELETE FROM periodos WHERE id = ?')->execute([$periodoId]);
                $pdo->commit();
            } finally {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
            }
        }
    }

    private function criarPrescricaoComEstoque(int $estoqueInicial, int|string $dose, string $unidade = 'comprimido'): array
    {
        $acolhidoId = $this->criarAcolhidoFicticio();
        $medicamentoId = $this->criarMedicamentoFicticio();
        $pdo = self::$pdo;
        $pdo->prepare('UPDATE medicamentos SET unidade_base = ? WHERE id = ?')->execute([$unidade, $medicamentoId]);
        (new \Abdm\Model\EstoqueMedicamentos(self::$pdo))->registrarEntradaEstoque($medicamentoId,
            $estoqueInicial,
            'unidade de teste',
            1,
            'TCC2'
        );

        $periodo = self::$pdo->prepare('INSERT INTO periodos (nome, ordem) VALUES (?, 1)');
        $periodo->execute(['TCC2 período ' . bin2hex(random_bytes(3))]);
        $periodoId = (int)self::$pdo->lastInsertId();

        $prescricao = self::$pdo->prepare(
            "INSERT INTO prescricoes (acolhido_id, medicamento_id, tipo_posologia, doses_por_dia, quantidade_por_dose, unidade_dose, tipo_uso, data_inicio) "
            . "VALUES (?, ?, 'horario_fixo', 1, ?, ?, 'continuo', CURDATE())"
        );
        $prescricao->execute([$acolhidoId, $medicamentoId, $dose, $unidade]);
        $prescricaoId = (int)self::$pdo->lastInsertId();

        $horario = self::$pdo->prepare(
            "INSERT INTO prescricao_horarios (prescricao_id, horario, periodo_id, quantidade_por_dose, unidade_dose) VALUES (?, '08:00:00', ?, ?, ?)"
        );
        $horario->execute([$prescricaoId, $periodoId, $dose, $unidade]);

        return [$acolhidoId, $medicamentoId, (int)self::$pdo->lastInsertId()];
    }

    private function quantidadeTotalEstoque(int $medicamentoId): int
    {
        $stmt = self::$pdo->prepare('SELECT COALESCE(SUM(quantidade_atual_base), 0) FROM medicamentos_estoque WHERE medicamento_id = ?');
        $stmt->execute([$medicamentoId]);
        return (int)$stmt->fetchColumn();
    }

    private function unidadeBase(int $medicamentoId): string
    {
        $stmt = self::$pdo->prepare('SELECT unidade_base FROM medicamentos WHERE id = ?');
        $stmt->execute([$medicamentoId]);
        return (string)$stmt->fetchColumn();
    }

    private function countRows(string $sql): int
    {
        return (int)self::$pdo->query($sql)->fetchColumn();
    }
}

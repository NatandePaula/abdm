<?php

namespace Abdm\Controller;

use Abdm\Infraestrutura\ControleAcesso;
use Abdm\Infraestrutura\Criptografia;
use Abdm\Infraestrutura\Sessao;
use Abdm\Model\ValidacaoUsuario;

use PDO;
use PDOException;
use Throwable;
use RuntimeException;
use InvalidArgumentException;

final class UsuariosController
{
    private readonly ControleAcesso $controleAcesso;

    public function __construct(private readonly PDO $pdo)
    {
        $this->controleAcesso = new ControleAcesso($pdo);
    }

    private static function usuariosTextoDoFormulario(string $campo): string
    {
        $valor = $_POST[$campo] ?? '';
        if (!is_string($valor)) {
            throw new InvalidArgumentException('Dados enviados inválidos.');
        }
        return $valor;
    }

    private static function usuariosGerarSenhaTemporaria(): string
    {
        return bin2hex(random_bytes(16));
    }

    private static function usuariosGuardarFormularioRetentativa(string $acao): void
    {
        if (!in_array($acao, ['criar_usuario', 'atualizar_usuario'], true)) {
            return;
        }

        $formulario = ['acao' => $acao];
        if (isset($_POST['login']) && is_string($_POST['login'])) {
            $formulario['login'] = substr($_POST['login'], 0, 180);
        }
        if ($acao === 'atualizar_usuario') {
            $idLogin = filter_var($_POST['id_login'] ?? null, FILTER_VALIDATE_INT);
            if ($idLogin && $idLogin > 0) {
                $formulario['id_login'] = (int)$idLogin;
            }
        }

        foreach (['perfil' => ['nome', 'cpf', 'dataNascimento', 'email', 'registroProfissional', 'telefone', 'atuacaoProfissional', 'estadoCivil', 'cargoConselho', 'inicioMandatoConselho', 'fimMandatoConselho', 'dependentes', 'comorbidades', 'CBO', 'IsFuncionario'], 'endereco' => ['estado', 'cidade', 'rua', 'numero', 'cep']] as $grupo => $campos) {
            $entrada = $_POST[$grupo] ?? null;
            if (!is_array($entrada)) {
                continue;
            }
            foreach ($campos as $campo) {
                $valor = $entrada[$campo] ?? null;
                if (is_string($valor) && preg_match('//u', $valor) === 1) {
                    $formulario[$grupo][$campo] = substr($valor, 0, 2048);
                }
            }
        }

        $permissoes = $_POST['permissoes'] ?? [];
        if (is_array($permissoes)) {
            $catalogo = ControleAcesso::catalogoFuncionalidadesAplicacao();
            $formulario['permissoes'] = array_values(array_filter($permissoes, static function ($codigo) use ($catalogo): bool {
                return is_string($codigo) && isset($catalogo[$codigo]);
            }));
        }

        $_SESSION['usuarios_form_retentativa'] = $formulario;
    }

    private static function validarIdLoginDoFormulario(): int
    {
        $id = filter_var($_POST['id_login'] ?? null, FILTER_VALIDATE_INT);
        if (!$id || $id < 1) {
            throw new InvalidArgumentException('Usuário inválido.');
        }
        return $id;
    }

    private static function usuariosGerarMatricula(PDO $pdo, int $isFuncionario): string
    {
        $prefixo = $isFuncionario === 1 ? 'F' : 'T';
        for ($tentativa = 0; $tentativa < 20; $tentativa++) {
            $matricula = $prefixo . date('Ymd') . random_int(100, 999);
            $stmt = (new \Abdm\Model\PersistenciaUsuarios($pdo))->prepararConsultaPerfilPorMatriculaUnica();
            $stmt->execute([$matricula]);
            if (!$stmt->fetchColumn()) {
                return $matricula;
            }
        }
        throw new RuntimeException('Falha ao gerar uma matrícula única.');
    }

    private static function usuariosCpfJaCadastrado(PDO $pdo, string $cpf, ?int $loginIgnorado = null): bool
    {
        $stmt = (new \Abdm\Model\PersistenciaUsuarios($pdo))->consultarPerfilComLoginIdLoginComBloqueio();
        while ($registro = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if ($loginIgnorado !== null && (int)$registro['Login_idLogin'] === $loginIgnorado) {
                continue;
            }

            $cpfArmazenado = trim((string)($registro['cpf'] ?? ''));
            if ($cpfArmazenado === '') {
                continue;
            }

            $cpfDescriptografado = (string)Criptografia::descriptografar($cpfArmazenado);
            if (in_array($cpfDescriptografado, [
                'Formato de criptografia inválido',
                'IV de criptografia com tamanho incorreto',
                'Erro de descriptografia',
            ], true)) {
                throw new RuntimeException('Não foi possível conferir os CPFs cadastrados. Verifique a chave de criptografia.');
            }

            $cpfDigitosArmazenado = preg_replace('/\D+/', '', $cpfDescriptografado);
            if (is_string($cpfDigitosArmazenado) && hash_equals($cpf, $cpfDigitosArmazenado)) {
                return true;
            }
        }

        return false;
    }

    private static function usuariosPerfilDoFormulario(): array
    {
        $perfil = $_POST['perfil'] ?? null;
        $endereco = $_POST['endereco'] ?? null;
        if (!is_array($perfil) || !is_array($endereco)) {
            throw new InvalidArgumentException('Preencha os dados profissionais e o endereço.');
        }

        $nome = ValidacaoUsuario::usuariosValorPerfil($perfil, 'nome', 45, true);
        $cpfOriginal = ValidacaoUsuario::usuariosValorPerfil($perfil, 'cpf', 32, true);
        $cpf = preg_replace('/\D+/', '', $cpfOriginal);
        if (!is_string($cpf) || !ValidacaoUsuario::usuariosCpfValido($cpf)) {
            throw new InvalidArgumentException('Informe o CPF com 11 dígitos.');
        }

        $dataNascimento = ValidacaoUsuario::usuariosValorPerfil($perfil, 'dataNascimento', 10, true);
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dataNascimento) || !checkdate((int)substr($dataNascimento, 5, 2), (int)substr($dataNascimento, 8, 2), (int)substr($dataNascimento, 0, 4))) {
            throw new InvalidArgumentException('Informe uma data de nascimento valida.');
        }
        if ($dataNascimento > date('Y-m-d')) {
            throw new InvalidArgumentException('A data de nascimento não pode estar no futuro.');
        }

        $email = ValidacaoUsuario::usuariosValorPerfil($perfil, 'email', 45);
        if ($email !== null && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Informe um e-mail válido.');
        }
        $email = $email !== null ? strtolower($email) : null;
        $telefone = ValidacaoUsuario::usuariosValorPerfil($perfil, 'telefone', 32);
        if ($telefone !== null) {
            $telefone = preg_replace('/\D+/', '', $telefone);
            if (!is_string($telefone) || strlen($telefone) > 11) {
                throw new InvalidArgumentException('O telefone deve ter até 11 dígitos.');
            }
        }

        $cepOriginal = ValidacaoUsuario::usuariosValorPerfil($endereco, 'cep', 16, true);
        $cep = preg_replace('/\D+/', '', $cepOriginal);
        if (!is_string($cep) || strlen($cep) !== 8) {
            throw new InvalidArgumentException('Informe o CEP com 8 dígitos.');
        }

        $numeroOriginal = $endereco['numero'] ?? '';
        if (!is_string($numeroOriginal)) {
            throw new InvalidArgumentException('Número do endereço inválido.');
        }
        $numeroOriginal = trim($numeroOriginal);
        $numero = null;
        if ($numeroOriginal !== '') {
            $numero = filter_var($numeroOriginal, FILTER_VALIDATE_INT);
            if ($numero === false || $numero < 0) {
                throw new InvalidArgumentException('O número do endereço deve ser um inteiro não negativo.');
            }
        }

        $dependentesOriginal = $perfil['dependentes'] ?? '';
        if (!is_string($dependentesOriginal)) {
            throw new InvalidArgumentException('Quantidade de dependentes inválida.');
        }
        $dependentesOriginal = trim($dependentesOriginal);
        $dependentes = null;
        if ($dependentesOriginal !== '') {
            $dependentes = filter_var($dependentesOriginal, FILTER_VALIDATE_INT);
            if ($dependentes === false || $dependentes < 0) {
                throw new InvalidArgumentException('A quantidade de dependentes deve ser um inteiro não negativo.');
            }
        }

        $funcionario = $perfil['IsFuncionario'] ?? null;
        if (!is_string($funcionario) || !in_array($funcionario, ['0', '1'], true)) {
            throw new InvalidArgumentException('Informe se a conta pertence a um funcionário.');
        }

        $cargoConselho = ValidacaoUsuario::usuariosValorPerfil($perfil, 'cargoConselho', 45);
        $inicioMandato = ValidacaoUsuario::usuariosValidarDataMandato(
            ValidacaoUsuario::usuariosValorPerfil($perfil, 'inicioMandatoConselho', 10),
            'o inicio do mandato'
        );
        $fimMandato = ValidacaoUsuario::usuariosValidarDataMandato(
            ValidacaoUsuario::usuariosValorPerfil($perfil, 'fimMandatoConselho', 10),
            'o fim do mandato'
        );
        if ($cargoConselho === null) {
            $inicioMandato = null;
            $fimMandato = null;
        } elseif (($inicioMandato === null) !== ($fimMandato === null)) {
            throw new InvalidArgumentException('Preencha as duas datas do mandato ou deixe ambas vazias.');
        } elseif ($inicioMandato !== null && $inicioMandato > $fimMandato) {
            throw new InvalidArgumentException('O inicio do mandato deve ser anterior ou igual ao fim.');
        }

        return [
            'perfil' => [
                'nome' => $nome,
                'cpf' => $cpf,
                'dataNascimento' => $dataNascimento,
                'email' => $email,
                'registroProfissional' => ValidacaoUsuario::usuariosValorPerfil($perfil, 'registroProfissional', 10),
                'telefone' => $telefone,
                'atuacaoProfissional' => ValidacaoUsuario::usuariosValorPerfil($perfil, 'atuacaoProfissional', 120),
                'estadoCivil' => ValidacaoUsuario::usuariosValorPerfil($perfil, 'estadoCivil', 45),
                'cargoConselho' => $cargoConselho,
                'inicioMandatoConselho' => $inicioMandato,
                'fimMandatoConselho' => $fimMandato,
                'dependentes' => $dependentes,
                'comorbidades' => ValidacaoUsuario::usuariosValorPerfil($perfil, 'comorbidades', 200),
                'CBO' => ValidacaoUsuario::usuariosValorPerfil($perfil, 'CBO', 10),
                'IsFuncionario' => (int)$funcionario,
            ],
            'endereco' => [
                'estado' => ValidacaoUsuario::usuariosValorPerfil($endereco, 'estado', 45, true),
                'cidade' => ValidacaoUsuario::usuariosValorPerfil($endereco, 'cidade', 45, true),
                'rua' => ValidacaoUsuario::usuariosValorPerfil($endereco, 'rua', 45, true),
                'numero' => $numero,
                'cep' => $cep,
            ],
        ];
    }

    private static function usuariosPermissoesDoFormulario(): array
    {
        $selecionadas = $_POST['permissoes'] ?? [];
        if (!is_array($selecionadas)) {
            throw new InvalidArgumentException('Seleção de permissões inválida.');
        }
        $permitidas = ControleAcesso::catalogoFuncionalidadesAplicacao();
        $resultado = [];
        foreach ($selecionadas as $codigo) {
            if (!is_string($codigo) || !array_key_exists($codigo, $permitidas)) {
                throw new InvalidArgumentException('Foi enviada uma permissão desconhecida.');
            }
            $resultado[$codigo] = $codigo;
        }
        return array_values($resultado);
    }

    private static function usuariosSalvarPermissoes(PDO $pdo, int $idLogin, array $permissoes, bool $ehAdmin): void
    {
        $stmt = (new \Abdm\Model\PersistenciaUsuarios($pdo))->prepararExclusaoPermissaoDaConta();
        $stmt->execute([$idLogin]);
        if ($ehAdmin) {
            return;
        }

        $stmt = (new \Abdm\Model\PersistenciaUsuarios($pdo))->prepararInsercaoPermissaoDaConta();
        foreach ($permissoes as $permissao) {
            $stmt->execute([$idLogin, $permissao]);
        }
    }

    private static function usuariosSalvarPerfil(PDO $pdo, int $idLogin, array $dados): void
    {
        $perfil = $dados['perfil'];
        $endereco = $dados['endereco'];
        $cpfCriptografado = Criptografia::criptografar($perfil['cpf']);
        if (!is_string($cpfCriptografado) || $cpfCriptografado === '') {
            throw new RuntimeException('Não foi possível proteger o CPF do usuário.');
        }

        $stmt = (new \Abdm\Model\PersistenciaUsuarios($pdo))->prepararConsultaPermissaoLegadaComIdPermisoesPorCodUnica();
        $stmt->execute();
        $idPermissaoBase = $stmt->fetchColumn();
        if (!$idPermissaoBase) {
            throw new RuntimeException('As permissões de usuário não estão disponíveis nesta instalação.');
        }

        $stmt = (new \Abdm\Model\PersistenciaUsuarios($pdo))->prepararConsultaPerfilComIdUsuarioPorLoginIdLoginComBloqueio();
        $stmt->execute([$idLogin]);
        $perfisExistentes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (count($perfisExistentes) > 1) {
            throw new RuntimeException('Esta conta possui mais de um perfil profissional vinculado. Resolva a duplicidade antes de editar.');
        }

        if ($perfisExistentes) {
            $idUsuario = (int)$perfisExistentes[0]['idUsuario'];
            $idEndereco = (int)$perfisExistentes[0]['Endereco_idEndereco'];
            $stmt = (new \Abdm\Model\PersistenciaUsuarios($pdo))->prepararConsultaPerfilPorEnderecoIdEndereco();
            $stmt->execute([$idEndereco, $idLogin]);
            if ((int)$stmt->fetchColumn() > 0) {
                $idEndereco = null;
            }

            if ($idEndereco === null) {
                $stmt = (new \Abdm\Model\PersistenciaUsuarios($pdo))->prepararInsercaoEndereco();
                $stmt->execute([$endereco['estado'], $endereco['cidade'], $endereco['rua'], $endereco['numero'], $endereco['cep']]);
                $idEndereco = (int)$pdo->lastInsertId();
            } else {
                $stmt = (new \Abdm\Model\PersistenciaUsuarios($pdo))->prepararAtualizacaoEnderecoDeEstado();
                $stmt->execute([$endereco['estado'], $endereco['cidade'], $endereco['rua'], $endereco['numero'], $endereco['cep'], $idEndereco]);
            }

            $stmt = (new \Abdm\Model\PersistenciaUsuarios($pdo))->prepararAtualizacaoPerfilDeNome();
            $stmt->execute([
                $perfil['nome'], $cpfCriptografado, $perfil['dataNascimento'], $perfil['email'], $perfil['registroProfissional'],
                $perfil['telefone'], $perfil['atuacaoProfissional'], $idEndereco, $idLogin, $idPermissaoBase,
                $perfil['estadoCivil'], $perfil['cargoConselho'], $perfil['inicioMandatoConselho'], $perfil['fimMandatoConselho'],
                $perfil['dependentes'], $perfil['comorbidades'],
                $perfil['CBO'], $perfil['matricula'], $perfil['IsFuncionario'], $idUsuario,
            ]);
            return;
        }

        $stmt = (new \Abdm\Model\PersistenciaUsuarios($pdo))->prepararInsercaoEndereco();
        $stmt->execute([$endereco['estado'], $endereco['cidade'], $endereco['rua'], $endereco['numero'], $endereco['cep']]);
        $idEndereco = (int)$pdo->lastInsertId();

        $stmt = (new \Abdm\Model\PersistenciaUsuarios($pdo))->prepararInsercaoPerfil();
        $stmt->execute([
            $perfil['nome'], $cpfCriptografado, $perfil['dataNascimento'], $perfil['email'], $perfil['registroProfissional'],
            $perfil['telefone'], $perfil['atuacaoProfissional'], $idEndereco, $idLogin, $idPermissaoBase,
            $perfil['estadoCivil'], $perfil['cargoConselho'], $perfil['inicioMandatoConselho'], $perfil['fimMandatoConselho'],
            $perfil['dependentes'], $perfil['comorbidades'],
            $perfil['CBO'], $perfil['matricula'], $perfil['IsFuncionario'],
        ]);
    }

    public function executar(): void
    {
        $pdo = $this->pdo;
        $acao = $_POST['acao'] ?? '';
        if (!is_string($acao)) {
            http_response_code(400);
            exit('Ação inválida.');
        }

        $alteracaoPropria = $acao === 'alterar_senha_propria';
        if ($alteracaoPropria) {
            $this->controleAcesso->exigirUsuarioAutenticado();
        } else {
            $this->controleAcesso->exigirUsuarioAdministrador();
        }
        Sessao::exigirTokenCsrfValido();
        $retornoInativos = ($_POST['voltar_inativos'] ?? '') === '1';
        $destinoListaUsuarios = 'views/usuarios.php' . ($retornoInativos ? '?inativos=1' : '');

        $destino = $alteracaoPropria ? 'views/home.php' : $destinoListaUsuarios;
        $destinoErro = $alteracaoPropria
            ? 'views/minha_senha.php'
            : ($acao === 'criar_usuario' ? 'views/usuarios.php?novo=1' : $destinoListaUsuarios);
        $novoNomeNaSessao = null;

        try {
            $pdo->beginTransaction();

            if ($acao === 'criar_usuario') {
                $login = ValidacaoUsuario::validarNomeLogin(self::usuariosTextoDoFormulario('login'));
                $senha = self::usuariosTextoDoFormulario('senha');
                ValidacaoUsuario::validarSenhaNova($senha, self::usuariosTextoDoFormulario('confirmar_senha'));
                $dadosPerfil = self::usuariosPerfilDoFormulario();
                $permissoes = self::usuariosPermissoesDoFormulario();
                $dadosPerfil['perfil']['matricula'] = self::usuariosGerarMatricula($pdo, $dadosPerfil['perfil']['IsFuncionario']);

                $stmt = (new \Abdm\Model\PersistenciaUsuarios($pdo))->prepararConsultaContaPorLoginUnica();
                $stmt->execute([$login]);
                if ($stmt->fetchColumn()) {
                    throw new InvalidArgumentException('Esse nome de usuário já está em uso.');
                }

                $stmt = (new \Abdm\Model\PersistenciaUsuarios($pdo))->prepararConsultaPerfilPorEmailUnica();
                $stmt->execute([$dadosPerfil['perfil']['email']]);
                $emailJaCadastrado = (bool)$stmt->fetchColumn();
                if (self::usuariosCpfJaCadastrado($pdo, $dadosPerfil['perfil']['cpf']) || $emailJaCadastrado) {
                    throw new InvalidArgumentException('Esse CPF ou e-mail já está vinculado a outro cadastro profissional.');
                }

                $stmt = (new \Abdm\Model\PersistenciaUsuarios($pdo))->prepararInsercaoConta();
                $stmt->execute([$login, password_hash($senha, PASSWORD_DEFAULT)]);
                $idLogin = (int)$pdo->lastInsertId();
                self::usuariosSalvarPerfil($pdo, $idLogin, $dadosPerfil);
                self::usuariosSalvarPermissoes($pdo, $idLogin, $permissoes, false);
                $mensagemSucesso = 'Usuário e cadastro profissional criados.';
            } elseif ($acao === 'atualizar_usuario') {
                $idLogin = self::validarIdLoginDoFormulario();
                $destinoErro = 'views/usuarios.php?editar=' . $idLogin . ($retornoInativos ? '&inativos=1' : '');
                $login = ValidacaoUsuario::validarNomeLogin(self::usuariosTextoDoFormulario('login'));
                $dadosPerfil = self::usuariosPerfilDoFormulario();
                $permissoes = self::usuariosPermissoesDoFormulario();

                $stmt = (new \Abdm\Model\PersistenciaUsuarios($pdo))->prepararConsultaPerfilDaContaComBloqueio();
                $stmt->execute([$idLogin]);
                $conta = $stmt->fetch(PDO::FETCH_ASSOC);
                if (!$conta) {
                    throw new InvalidArgumentException('Usuário não encontrado.');
                }

                $stmt = (new \Abdm\Model\PersistenciaUsuarios($pdo))->prepararConsultaPerfilComMatriculaPorLoginIdLoginUnicaComBloqueio();
                $stmt->execute([$idLogin]);
                $matriculaAtual = $stmt->fetchColumn();
                $prefixoMatricula = $dadosPerfil['perfil']['IsFuncionario'] === 1 ? 'F' : 'T';
                $dadosPerfil['perfil']['matricula'] = is_string($matriculaAtual)
                    && preg_match('/^[FT]\d{11}$/', $matriculaAtual)
                    && $matriculaAtual[0] === $prefixoMatricula
                        ? $matriculaAtual
                        : self::usuariosGerarMatricula($pdo, $dadosPerfil['perfil']['IsFuncionario']);

                $stmt = (new \Abdm\Model\PersistenciaUsuarios($pdo))->prepararConsultaDeLoginDeOutraConta();
                $stmt->execute([$login, $idLogin]);
                if ($stmt->fetchColumn()) {
                    throw new InvalidArgumentException('Esse nome de usuário já está em uso.');
                }

                $stmt = (new \Abdm\Model\PersistenciaUsuarios($pdo))->prepararConsultaPerfilComIdUsuarioPorEmailUnica();
                $stmt->execute([$dadosPerfil['perfil']['email'], $idLogin]);
                $emailJaCadastrado = (bool)$stmt->fetchColumn();
                if (self::usuariosCpfJaCadastrado($pdo, $dadosPerfil['perfil']['cpf'], $idLogin) || $emailJaCadastrado) {
                    throw new InvalidArgumentException('Esse CPF ou e-mail já está vinculado a outro cadastro profissional.');
                }

                $stmt = (new \Abdm\Model\PersistenciaUsuarios($pdo))->prepararAtualizacaoContaDeLogin();
                $stmt->execute([$login, $idLogin]);
                self::usuariosSalvarPerfil($pdo, $idLogin, $dadosPerfil);
                self::usuariosSalvarPermissoes($pdo, $idLogin, $permissoes, (int)$conta['is_admin'] === 1);

                if ($idLogin === (int)$_SESSION['id_login']) {
                    $novoNomeNaSessao = $login;
                }
                $mensagemSucesso = 'Dados do usuário e permissões atualizados.';
            } elseif ($acao === 'alterar_perfil') {
                $idLogin = self::validarIdLoginDoFormulario();
                $perfilAdminInput = self::usuariosTextoDoFormulario('is_admin');
                if (!in_array($perfilAdminInput, ['0', '1'], true)) {
                    throw new InvalidArgumentException('Perfil de acesso inválido.');
                }
                $perfilAdmin = $perfilAdminInput === '1';
                if ($idLogin === (int)$_SESSION['id_login'] && !$perfilAdmin) {
                    throw new InvalidArgumentException('Não é possível remover o perfil administrador da sua própria conta.');
                }

                $stmt = (new \Abdm\Model\PersistenciaUsuarios($pdo))->prepararConsultaSituacaoDaContaComBloqueio();
                $stmt->execute([$idLogin]);
                $conta = $stmt->fetch(PDO::FETCH_ASSOC);
                if (!$conta) {
                    throw new InvalidArgumentException('Usuário não encontrado.');
                }
                if (!$perfilAdmin && (int)$conta['is_admin'] === 1 && (int)$conta['ativo'] === 1) {
                    $administradoresAtivos = (new \Abdm\Model\PersistenciaUsuarios($pdo))->consultarContaComIdLoginPorAtivoComBloqueio()->fetchAll(PDO::FETCH_COLUMN);
                    if (count($administradoresAtivos) <= 1) {
                        throw new InvalidArgumentException('O último administrador ativo não pode perder o perfil.');
                    }
                }

                $stmt = (new \Abdm\Model\PersistenciaUsuarios($pdo))->prepararAtualizacaoContaDeIsAdmin();
                $stmt->execute([$perfilAdmin ? 1 : 0, $idLogin]);
                if ($perfilAdmin) {
                    self::usuariosSalvarPermissoes($pdo, $idLogin, [], true);
                }
                $mensagemSucesso = $perfilAdmin ? 'Perfil administrador concedido; todas as funcionalidades estão liberadas.' : 'Perfil administrador removido.';
            } elseif ($acao === 'remover_acesso' || $acao === 'reativar_acesso') {
                $idLogin = self::validarIdLoginDoFormulario();
                $ativar = $acao === 'reativar_acesso';
                if ($ativar) {
                    $destino = 'views/usuarios.php';
                }
                if (!$ativar && $idLogin === (int)$_SESSION['id_login']) {
                    throw new InvalidArgumentException('Não é possível remover o acesso da sua própria conta.');
                }

                $stmt = (new \Abdm\Model\PersistenciaUsuarios($pdo))->prepararConsultaSituacaoDaContaComBloqueio();
                $stmt->execute([$idLogin]);
                $conta = $stmt->fetch(PDO::FETCH_ASSOC);
                if (!$conta) {
                    throw new InvalidArgumentException('Usuário não encontrado.');
                }

                if (!$ativar && (int)$conta['ativo'] === 1 && (int)$conta['is_admin'] === 1) {
                    $administradoresAtivos = (new \Abdm\Model\PersistenciaUsuarios($pdo))->consultarContaComIdLoginPorAtivoComBloqueio()->fetchAll(PDO::FETCH_COLUMN);
                    if (count($administradoresAtivos) <= 1) {
                        throw new InvalidArgumentException('O último administrador ativo não pode perder o acesso.');
                    }
                }

                $stmt = (new \Abdm\Model\PersistenciaUsuarios($pdo))->prepararAtualizacaoContaDeAtivo();
                $stmt->execute([$ativar ? 1 : 0, $idLogin]);
                $mensagemSucesso = $ativar ? 'Acesso reativado.' : 'Acesso removido. O histórico do usuário foi preservado.';
            } elseif ($acao === 'redefinir_senha') {
                $idLogin = self::validarIdLoginDoFormulario();
                if ($idLogin === (int)$_SESSION['id_login']) {
                    throw new InvalidArgumentException('Use a opção “Trocar minha senha” para alterar a senha da sua conta.');
                }

                $stmt = (new \Abdm\Model\PersistenciaUsuarios($pdo))->prepararConsultaIdentificadorDaContaComBloqueio();
                $stmt->execute([$idLogin]);
                if (!$stmt->fetchColumn()) {
                    throw new InvalidArgumentException('Usuário não encontrado.');
                }
                $senhaTemporaria = self::usuariosGerarSenhaTemporaria();
                $stmt = (new \Abdm\Model\PersistenciaUsuarios($pdo))->prepararAtualizacaoContaDeSenhaTemporaria();
                $stmt->execute([password_hash($senhaTemporaria, PASSWORD_DEFAULT), $idLogin]);
                $mensagemSucesso = 'Senha temporária: ' . $senhaTemporaria . '. É válida por 24 horas e deverá ser trocada no próximo acesso.';
            } elseif ($acao === 'alterar_senha_propria') {
                $senhaAtual = self::usuariosTextoDoFormulario('senha_atual');
                $senhaNova = self::usuariosTextoDoFormulario('senha_nova');
                ValidacaoUsuario::validarSenhaNova($senhaNova, self::usuariosTextoDoFormulario('confirmar_senha'));

                $stmt = (new \Abdm\Model\PersistenciaUsuarios($pdo))->prepararConsultaContaComSenhaPorIdLoginComBloqueio();
                $stmt->execute([(int)$_SESSION['id_login']]);
                $contaPropria = $stmt->fetch(PDO::FETCH_ASSOC);
                if (!$contaPropria || !is_string($contaPropria['senha'])) {
                    throw new InvalidArgumentException('Não foi possível localizar sua conta.');
                }
                $senhaArmazenada = $contaPropria['senha'];

                $ehHash = password_get_info($senhaArmazenada)['algo'] !== null;
                $senhaAtualValida = $ehHash
                    ? password_verify($senhaAtual, $senhaArmazenada)
                    : hash_equals($senhaArmazenada, $senhaAtual);
                if (!$senhaAtualValida) {
                    throw new InvalidArgumentException('A senha atual está incorreta.');
                }
                if ((int)$contaPropria['troca_senha_obrigatoria'] === 1 && hash_equals($senhaAtual, $senhaNova)) {
                    throw new InvalidArgumentException('Escolha uma senha diferente da senha temporária.');
                }

                $stmt = (new \Abdm\Model\PersistenciaUsuarios($pdo))->prepararAtualizacaoContaDeSenhaDefinitiva();
                $stmt->execute([password_hash($senhaNova, PASSWORD_DEFAULT), (int)$_SESSION['id_login']]);
                $stmtVersaoSenha = (new \Abdm\Model\PersistenciaUsuarios($pdo))->prepararConsultaContaComSenhaAlteradaEmPorIdLogin();
                $stmtVersaoSenha->execute([(int)$_SESSION['id_login']]);
                $_SESSION['senha_alterada_em'] = (string)$stmtVersaoSenha->fetchColumn();
                $mensagemSucesso = 'Sua senha foi alterada.';
            } else {
                throw new InvalidArgumentException('Ação desconhecida.');
            }

            $pdo->commit();
            if ($acao === 'atualizar_usuario') {
                $destino = $destinoListaUsuarios;
            }
            if ($alteracaoPropria) {
                session_regenerate_id(true);
                unset($_SESSION['csrf_token']);
                unset($_SESSION['force_password_change']);
            }
            if ($novoNomeNaSessao !== null) {
                $_SESSION['username'] = $novoNomeNaSessao;
            }
            $_SESSION['flash_message'] = ['type' => 'success', 'message' => $mensagemSucesso];
        } catch (InvalidArgumentException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $_SESSION['flash_message'] = ['type' => 'danger', 'message' => $e->getMessage()];
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            if ($e->getCode() === '23000') {
                $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'O CPF, o e-mail, a matrícula ou o nome de usuário já está em uso.'];
            } else {
                error_log('Falha no banco durante a gestão de usuários. Código: ' . $e->getCode());
                $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Não foi possível concluir a operação.'];
            }
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log('Falha na gestão de usuários. Código: ' . $e->getCode());
            $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Não foi possível concluir a operação.'];
        }

        if (($_SESSION['flash_message']['type'] ?? '') === 'danger') {
            self::usuariosGuardarFormularioRetentativa($acao);
        } elseif (in_array($acao, ['criar_usuario', 'atualizar_usuario'], true)) {
            unset($_SESSION['usuarios_form_retentativa']);
        }

        $tipoFlash = $_SESSION['flash_message']['type'] ?? 'danger';
        header('Location: ' . BASE_URL . ($tipoFlash === 'danger' ? $destinoErro : $destino));
        exit;
    }
}

<?php
[
    'modoInativos' => $modoInativos,
    'novoUsuario' => $novoUsuario,
    'urlListaUsuarios' => $urlListaUsuarios,
    'usuarios' => $usuarios,
    'duplicadosPerfil' => $duplicadosPerfil,
    'permissoesPorUsuario' => $permissoesPorUsuario,
    'usuarioEditar' => $usuarioEditar,
    'formularioRetentativa' => $formularioRetentativa,
    'retentativaCadastro' => $retentativaCadastro,
    'retentativaEdicao' => $retentativaEdicao,
] = $dadosView;

require_once ROOT_DIR . '/src/View/templates/includes/cabecalhoInterno.inc.php';
?>

<main class="<?php echo $usuarioEditar ? 'container-fluid px-3 px-lg-5 my-4' : 'container my-5'; ?>">
    <?php if ($usuarioEditar): ?>
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="h2 mb-1">Editar usuário</h1>
            <p class="text-body-secondary mb-0">Edite os dados, o endereço e as permissões desta conta.</p>
        </div>
        <a class="btn btn-outline-secondary" href="<?php echo htmlspecialchars($urlListaUsuarios, ENT_QUOTES, 'UTF-8'); ?>">Voltar para usuários</a>
    </div>
    <?php endif; ?>

    <?php if ($usuarioEditar): ?>
        <?php
        $idUsuario = (int)$usuarioEditar['idLogin'];
        $ehContaAtual = $idUsuario === (int)$_SESSION['id_login'];
        $ehAdmin = (int)$usuarioEditar['is_admin'] === 1;
        $perfilForm = [
            'nome' => $usuarioEditar['perfil_nome'], 'cpf' => $usuarioEditar['perfil_cpf'],
            'dataNascimento' => $usuarioEditar['perfil_dataNascimento'], 'email' => $usuarioEditar['perfil_email'],
            'registroProfissional' => $usuarioEditar['perfil_registroProfissional'], 'telefone' => $usuarioEditar['perfil_telefone'],
            'atuacaoProfissional' => $usuarioEditar['perfil_atuacaoProfissional'], 'estadoCivil' => $usuarioEditar['perfil_estadoCivil'],
            'cargoConselho' => $usuarioEditar['perfil_cargoConselho'],
            'inicioMandatoConselho' => $usuarioEditar['perfil_inicioMandatoConselho'],
            'fimMandatoConselho' => $usuarioEditar['perfil_fimMandatoConselho'],
            'dependentes' => $usuarioEditar['perfil_dependentes'],
            'comorbidades' => $usuarioEditar['perfil_comorbidades'], 'CBO' => $usuarioEditar['perfil_CBO'],
            'matricula' => $usuarioEditar['perfil_matricula'], 'IsFuncionario' => $usuarioEditar['perfil_IsFuncionario'],
        ];
        if ($retentativaEdicao && is_array($formularioRetentativa['perfil'] ?? null)) {
            $perfilForm = array_merge($perfilForm, $formularioRetentativa['perfil']);
        }
        $enderecoForm = [
            'estado' => $usuarioEditar['endereco_estado'], 'cidade' => $usuarioEditar['endereco_cidade'],
            'rua' => $usuarioEditar['endereco_rua'], 'numero' => $usuarioEditar['endereco_numero'],
            'cep' => $usuarioEditar['endereco_cep'],
        ];
        if ($retentativaEdicao && is_array($formularioRetentativa['endereco'] ?? null)) {
            $enderecoForm = array_merge($enderecoForm, $formularioRetentativa['endereco']);
        }
        $permissoesSelecionadas = $retentativaEdicao
            ? ($formularioRetentativa['permissoes'] ?? [])
            : ($permissoesPorUsuario[$idUsuario] ?? []);
        ?>
        <section class="card shadow-sm border-0 mb-4" aria-labelledby="editarUsuarioTitulo">
            <div class="card-header bg-body-tertiary d-flex flex-wrap justify-content-between align-items-center gap-2">
                <h2 class="h5 mb-0" id="editarUsuarioTitulo"><?php echo htmlspecialchars($perfilForm['nome'] ?: $usuarioEditar['login'], ENT_QUOTES, 'UTF-8'); ?></h2>
                <span class="badge <?php echo (int)$usuarioEditar['ativo'] === 1 ? 'text-bg-success' : 'text-bg-danger'; ?>"><?php echo (int)$usuarioEditar['ativo'] === 1 ? 'Ativo' : 'Desativado'; ?></span>
            </div>
            <div class="card-body">
                <?php if (isset($duplicadosPerfil[$idUsuario])): ?>
                    <div class="alert alert-danger mb-0">Há mais de um perfil profissional nesta conta. A edição está bloqueada até corrigir a duplicidade.</div>
                <?php else: ?>
                    <?php if (empty($usuarioEditar['idUsuario'])): ?>
                        <div class="alert alert-info">Esta conta ainda não tem cadastro profissional. Preencha os dados para criá-lo agora.</div>
                    <?php endif; ?>
                    <form action="<?php echo BASE_URL; ?>acoes/controllerUsuarios.php" method="POST" class="vstack gap-3" autocomplete="off">
                        <?php echo \Abdm\Infraestrutura\Sessao::campoTokenCsrf(); ?>
                        <input type="hidden" name="acao" value="atualizar_usuario">
                        <input type="hidden" name="id_login" value="<?php echo $idUsuario; ?>">
                        <input type="hidden" name="voltar_inativos" value="<?php echo $modoInativos ? '1' : '0'; ?>">
                        <div class="row g-3">
                            <div class="col-md-6 col-xl-4">
                                <label class="form-label" for="login-<?php echo $idUsuario; ?>">Nome de usuário *</label>
                                <input class="form-control" id="login-<?php echo $idUsuario; ?>" name="login" maxlength="45" minlength="3" value="<?php echo htmlspecialchars($usuarioEditar['login'], ENT_QUOTES, 'UTF-8'); ?>" required>
                            </div>
                        </div>
                        <?php
                        $prefixoCampos = 'editar-' . $idUsuario;
                        $formIsAdmin = $ehAdmin;
                        require ROOT_DIR . '/src/View/templates/includes/formUsuarioPerfil.inc.php';
                        ?>
                        <div class="d-flex flex-wrap justify-content-between gap-2 pt-2">
                            <a class="btn btn-outline-secondary" href="<?php echo htmlspecialchars($urlListaUsuarios, ENT_QUOTES, 'UTF-8'); ?>">Cancelar</a>
                            <button class="btn btn-primary" type="submit">Salvar e voltar para usuários</button>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </section>
        <section class="card shadow-sm border-0 mb-4" aria-label="Ações da conta">
            <div class="card-body d-flex flex-wrap gap-2">
                <?php if (!$ehContaAtual): ?>
                    <?php if ((int)$usuarioEditar['ativo'] === 1): ?>
                        <form action="<?php echo BASE_URL; ?>acoes/controllerUsuarios.php" method="POST" onsubmit="return confirm('Desativar esta conta? O histórico será preservado.');">
                            <?php echo \Abdm\Infraestrutura\Sessao::campoTokenCsrf(); ?><input type="hidden" name="acao" value="remover_acesso"><input type="hidden" name="id_login" value="<?php echo $idUsuario; ?>"><input type="hidden" name="voltar_inativos" value="<?php echo $modoInativos ? '1' : '0'; ?>">
                            <button class="btn btn-outline-danger" type="submit">Desativar usuário</button>
                        </form>
                    <?php else: ?>
                        <form action="<?php echo BASE_URL; ?>acoes/controllerUsuarios.php" method="POST">
                            <?php echo \Abdm\Infraestrutura\Sessao::campoTokenCsrf(); ?><input type="hidden" name="acao" value="reativar_acesso"><input type="hidden" name="id_login" value="<?php echo $idUsuario; ?>"><input type="hidden" name="voltar_inativos" value="<?php echo $modoInativos ? '1' : '0'; ?>">
                            <button class="btn btn-outline-success" type="submit">Ativar usuário</button>
                        </form>
                    <?php endif; ?>
                    <form action="<?php echo BASE_URL; ?>acoes/controllerUsuarios.php" method="POST" onsubmit="return confirm('Gerar uma senha temporária aleatória, válida por 24 horas?');">
                        <?php echo \Abdm\Infraestrutura\Sessao::campoTokenCsrf(); ?><input type="hidden" name="acao" value="redefinir_senha"><input type="hidden" name="id_login" value="<?php echo $idUsuario; ?>"><input type="hidden" name="voltar_inativos" value="<?php echo $modoInativos ? '1' : '0'; ?>">
                        <button class="btn btn-outline-warning" type="submit">Redefinir senha temporária</button>
                    </form>
                <?php else: ?>
                    <a class="btn btn-outline-secondary" href="<?php echo BASE_URL; ?>views/minha_senha.php">Trocar minha senha</a>
                <?php endif; ?>
            </div>
        </section>
    <?php elseif ($novoUsuario): ?>

    <section class="card shadow-sm border-0 mb-4" aria-labelledby="novoUsuarioTitulo">
        <div class="card-header bg-primary text-white d-flex flex-wrap justify-content-between align-items-center gap-2">
            <h2 class="h5 my-2 mb-0" id="novoUsuarioTitulo">Cadastrar usuário</h2>
            <a class="btn btn-outline-light" href="<?php echo BASE_URL; ?>views/usuarios.php">Voltar para usuários</a>
        </div>
        <div class="card-body">
            <form action="<?php echo BASE_URL; ?>acoes/controllerUsuarios.php" method="POST" class="vstack gap-3" autocomplete="off">
                <?php echo \Abdm\Infraestrutura\Sessao::campoTokenCsrf(); ?>
                <input type="hidden" name="acao" value="criar_usuario">
                <h3 class="h6 border-bottom pb-2">Dados de acesso</h3>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label" for="novoLogin">Nome de usuário *</label>
                        <input class="form-control" id="novoLogin" name="login" maxlength="45" minlength="3" autocomplete="username" value="<?php echo htmlspecialchars((string)($retentativaCadastro ? ($formularioRetentativa['login'] ?? '') : ''), ENT_QUOTES, 'UTF-8'); ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="novaSenha">Senha inicial *</label>
                        <input class="form-control" type="password" id="novaSenha" name="senha" minlength="6" maxlength="72" autocomplete="new-password" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="confirmarNovaSenha">Confirmar senha *</label>
                        <input class="form-control" type="password" id="confirmarNovaSenha" name="confirmar_senha" minlength="6" maxlength="72" autocomplete="new-password" required>
                    </div>
                </div>
                <?php
                $prefixoCampos = 'novo-usuario';
                $perfilForm = $retentativaCadastro && is_array($formularioRetentativa['perfil'] ?? null) ? $formularioRetentativa['perfil'] : [];
                $enderecoForm = $retentativaCadastro && is_array($formularioRetentativa['endereco'] ?? null) ? $formularioRetentativa['endereco'] : [];
                $permissoesSelecionadas = $retentativaCadastro && is_array($formularioRetentativa['permissoes'] ?? null) ? $formularioRetentativa['permissoes'] : [];
                $formIsAdmin = false;
                require ROOT_DIR . '/src/View/templates/includes/formUsuarioPerfil.inc.php';
                ?>
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <p class="form-text mb-0">Os campos com * são obrigatórios. Novos usuários recebem apenas as permissões marcadas; administradores têm acesso a todas as funcionalidades.</p>
                    <button class="btn btn-primary" type="submit">Cadastrar usuário</button>
                </div>
            </form>
        </div>
    </section>

    <?php else: ?>

    <?php $usuariosExibidos = array_values(array_filter($usuarios, static function (array $usuario) use ($modoInativos): bool {
        return (int)$usuario['ativo'] === ($modoInativos ? 0 : 1);
    })); ?>
    <section class="card shadow-lg border-0 rounded-lg" aria-labelledby="usuariosCadastradosTitulo">
        <div class="card-header <?php echo $modoInativos ? 'bg-secondary' : 'bg-primary'; ?> text-white d-flex flex-wrap justify-content-between align-items-center gap-2">
            <h2 class="h5 my-2 mb-0" id="usuariosCadastradosTitulo"><?php echo $modoInativos ? 'Lista de Usuários Desabilitados' : 'Lista de Usuários Ativos'; ?></h2>
            <div class="d-flex flex-wrap gap-2">
                <?php if ($modoInativos): ?>
                    <a href="<?php echo BASE_URL; ?>views/usuarios.php" class="btn btn-light text-secondary fw-bold">Voltar para Ativos</a>
                <?php else: ?>
                    <a href="<?php echo BASE_URL; ?>views/usuarios.php?inativos=1" class="btn btn-outline-light">Ver usuários desabilitados</a>
                <?php endif; ?>
                <a href="<?php echo BASE_URL; ?>views/usuarios.php?novo=1" class="btn btn-light text-primary fw-bold">Cadastrar usuário</a>
            </div>
        </div>
        <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th scope="col">Cadastro</th>
                        <th scope="col">Usuário</th>
                        <th scope="col">Perfil e acesso</th>
                        <th scope="col">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($usuariosExibidos as $usuario): ?>
                        <?php
                        $idUsuario = (int)$usuario['idLogin'];
                        $ehContaAtual = $idUsuario === (int)$_SESSION['id_login'];
                        $ehAdmin = (int)$usuario['is_admin'] === 1;
                        $perfilForm = [
                            'nome' => $usuario['perfil_nome'],
                            'cpf' => $usuario['perfil_cpf'],
                            'dataNascimento' => $usuario['perfil_dataNascimento'],
                            'email' => $usuario['perfil_email'],
                            'registroProfissional' => $usuario['perfil_registroProfissional'],
                            'telefone' => $usuario['perfil_telefone'],
                            'atuacaoProfissional' => $usuario['perfil_atuacaoProfissional'],
                            'estadoCivil' => $usuario['perfil_estadoCivil'],
                            'cargoConselho' => $usuario['perfil_cargoConselho'],
                            'inicioMandatoConselho' => $usuario['perfil_inicioMandatoConselho'],
                            'fimMandatoConselho' => $usuario['perfil_fimMandatoConselho'],
                            'dependentes' => $usuario['perfil_dependentes'],
                            'comorbidades' => $usuario['perfil_comorbidades'],
                            'CBO' => $usuario['perfil_CBO'],
                            'matricula' => $usuario['perfil_matricula'],
                            'IsFuncionario' => $usuario['perfil_IsFuncionario'],
                        ];
                        $enderecoForm = [
                            'estado' => $usuario['endereco_estado'],
                            'cidade' => $usuario['endereco_cidade'],
                            'rua' => $usuario['endereco_rua'],
                            'numero' => $usuario['endereco_numero'],
                            'cep' => $usuario['endereco_cep'],
                        ];
                        $permissoesSelecionadas = $permissoesPorUsuario[$idUsuario] ?? [];
                        $nomeExibicao = $usuario['perfil_nome'] ?: $usuario['login'];
                        ?>
                        <tr>
                            <td>
                                <strong><?php echo htmlspecialchars($nomeExibicao, ENT_QUOTES, 'UTF-8'); ?></strong>
                                <?php if (empty($usuario['idUsuario'])): ?>
                                    <span class="badge text-bg-warning d-block mt-1">Cadastro profissional pendente</span>
                                <?php elseif (isset($duplicadosPerfil[$idUsuario])): ?>
                                    <span class="badge text-bg-danger d-block mt-1">Perfis duplicados; solicite correção</span>
                                <?php else: ?>
                                    <small class="d-block text-body-secondary">Matrícula: <?php echo htmlspecialchars((string)$usuario['perfil_matricula'], ENT_QUOTES, 'UTF-8'); ?></small>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($usuario['login'], ENT_QUOTES, 'UTF-8'); ?><?php if ($ehContaAtual): ?><small class="d-block text-body-secondary">Sua conta</small><?php endif; ?></td>
                            <td>
                                <?php if ($ehAdmin): ?>
                                    <span class="badge text-bg-primary">Administrador · acesso total</span>
                                <?php else: ?>
                                    <span class="badge text-bg-secondary">Usuário</span>
                                    <small class="d-block text-body-secondary"><?php echo count($permissoesSelecionadas); ?> funcionalidade(s) liberada(s)</small>
                                <?php endif; ?>
                                <?php if ((int)$usuario['ativo'] === 1): ?>
                                    <span class="badge text-bg-success mt-1">Ativo</span>
                                <?php else: ?>
                                    <span class="badge text-bg-danger mt-1">Desabilitado</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end" style="min-width: 185px;">
                                <div class="d-flex justify-content-end flex-wrap gap-1">
                                    <a class="btn btn-sm btn-outline-primary" href="<?php echo BASE_URL; ?>views/usuarios.php?editar=<?php echo $idUsuario; ?><?php echo $modoInativos ? '&amp;inativos=1' : ''; ?>" title="Editar dados e permissões">
                                        <i class="bi bi-pencil-square me-1" aria-hidden="true"></i>Editar
                                    </a>
                                    <?php if (!$ehContaAtual && !$ehAdmin): ?>
                                        <form action="<?php echo BASE_URL; ?>acoes/controllerUsuarios.php" method="POST" class="d-inline">
                                            <?php echo \Abdm\Infraestrutura\Sessao::campoTokenCsrf(); ?>
                                            <input type="hidden" name="voltar_inativos" value="<?php echo $modoInativos ? '1' : '0'; ?>">
                                            <input type="hidden" name="acao" value="alterar_perfil">
                                            <input type="hidden" name="id_login" value="<?php echo $idUsuario; ?>">
                                            <input type="hidden" name="is_admin" value="1">
                                            <button class="btn btn-sm btn-outline-primary" type="submit" title="Tornar administrador" aria-label="Tornar administrador de <?php echo htmlspecialchars($nomeExibicao, ENT_QUOTES, 'UTF-8'); ?>"><i class="bi bi-shield-plus" aria-hidden="true"></i></button>
                                        </form>
                                    <?php elseif (!$ehContaAtual && $ehAdmin): ?>
                                        <form action="<?php echo BASE_URL; ?>acoes/controllerUsuarios.php" method="POST" class="d-inline" onsubmit="return confirm('Remover o perfil administrador desta conta?');">
                                            <?php echo \Abdm\Infraestrutura\Sessao::campoTokenCsrf(); ?>
                                            <input type="hidden" name="voltar_inativos" value="<?php echo $modoInativos ? '1' : '0'; ?>">
                                            <input type="hidden" name="acao" value="alterar_perfil">
                                            <input type="hidden" name="id_login" value="<?php echo $idUsuario; ?>">
                                            <input type="hidden" name="is_admin" value="0">
                                            <button class="btn btn-sm btn-outline-warning" type="submit" title="Remover perfil administrador" aria-label="Remover perfil administrador de <?php echo htmlspecialchars($nomeExibicao, ENT_QUOTES, 'UTF-8'); ?>"><i class="bi bi-shield-minus" aria-hidden="true"></i></button>
                                        </form>
                                    <?php endif; ?>

                                    <?php if (!$ehContaAtual): ?>
                                        <?php if ((int)$usuario['ativo'] === 1): ?>
                                            <form action="<?php echo BASE_URL; ?>acoes/controllerUsuarios.php" method="POST" class="d-inline" onsubmit="return confirm('Remover o acesso desta conta? O histórico será preservado.');">
                                                <?php echo \Abdm\Infraestrutura\Sessao::campoTokenCsrf(); ?>
                                                <input type="hidden" name="voltar_inativos" value="<?php echo $modoInativos ? '1' : '0'; ?>">
                                                <input type="hidden" name="acao" value="remover_acesso">
                                                <input type="hidden" name="id_login" value="<?php echo $idUsuario; ?>">
                                                <button class="btn btn-sm btn-outline-danger" type="submit" title="Desativar usuário" aria-label="Desativar <?php echo htmlspecialchars($nomeExibicao, ENT_QUOTES, 'UTF-8'); ?>"><i class="bi bi-person-x" aria-hidden="true"></i></button>
                                            </form>
                                        <?php else: ?>
                                            <form action="<?php echo BASE_URL; ?>acoes/controllerUsuarios.php" method="POST" class="d-inline">
                                                <?php echo \Abdm\Infraestrutura\Sessao::campoTokenCsrf(); ?>
                                                <input type="hidden" name="voltar_inativos" value="<?php echo $modoInativos ? '1' : '0'; ?>">
                                                <input type="hidden" name="acao" value="reativar_acesso">
                                                <input type="hidden" name="id_login" value="<?php echo $idUsuario; ?>">
                                                <button class="btn btn-sm btn-outline-success" type="submit" title="Ativar usuário" aria-label="Ativar <?php echo htmlspecialchars($nomeExibicao, ENT_QUOTES, 'UTF-8'); ?>"><i class="bi bi-person-check" aria-hidden="true"></i></button>
                                            </form>
                                        <?php endif; ?>
                                        <form action="<?php echo BASE_URL; ?>acoes/controllerUsuarios.php" method="POST" class="d-inline" onsubmit="return confirm('Gerar uma senha temporária aleatória, válida por 24 horas?');">
                                            <?php echo \Abdm\Infraestrutura\Sessao::campoTokenCsrf(); ?>
                                            <input type="hidden" name="voltar_inativos" value="<?php echo $modoInativos ? '1' : '0'; ?>">
                                            <input type="hidden" name="acao" value="redefinir_senha">
                                            <input type="hidden" name="id_login" value="<?php echo $idUsuario; ?>">
                                            <button class="btn btn-sm btn-outline-secondary" type="submit" title="Redefinir senha" aria-label="Redefinir senha de <?php echo htmlspecialchars($nomeExibicao, ENT_QUOTES, 'UTF-8'); ?>"><i class="bi bi-key" aria-hidden="true"></i></button>
                                        </form>
                                    <?php else: ?>
                                        <a class="btn btn-sm btn-outline-secondary" href="<?php echo BASE_URL; ?>views/minha_senha.php" title="Trocar minha senha" aria-label="Trocar minha senha"><i class="bi bi-key" aria-hidden="true"></i></a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($usuariosExibidos)): ?>
                        <tr><td colspan="4" class="text-center text-body-secondary py-4"><?php echo $modoInativos ? 'Nenhum usuário desabilitado.' : 'Nenhum usuário ativo.'; ?></td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        </div>
        <div class="card-footer text-center py-3">
            <div class="text-muted small">Total de usuários exibidos: <?php echo count($usuariosExibidos); ?></div>
        </div>
    </section>
    <?php endif; ?>
</main>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const cpfValido = (valor) => {
        const cpf = valor.replace(/\D/g, '');
        if (cpf.length !== 11 || /^(\d)\1{10}$/.test(cpf)) return false;
        for (let tamanho = 9; tamanho <= 10; tamanho++) {
            let soma = 0;
            for (let indice = 0; indice < tamanho; indice++) soma += Number(cpf[indice]) * (tamanho + 1 - indice);
            let digito = (soma * 10) % 11;
            if (digito === 10) digito = 0;
            if (digito !== Number(cpf[tamanho])) return false;
        }
        return true;
    };
    document.querySelectorAll('[data-cpf-field]').forEach((cpfInput) => {
        const atualizarCpf = () => {
            const digitos = cpfInput.value.replace(/\D/g, '').slice(0, 11);
            cpfInput.value = digitos
                .replace(/(\d{3})(\d)/, '$1.$2')
                .replace(/(\d{3})(\d)/, '$1.$2')
                .replace(/(\d{3})(\d{1,2})$/, '$1-$2');
            if (!digitos.length) {
                cpfInput.setCustomValidity('');
                cpfInput.classList.remove('is-valid', 'is-invalid');
            } else if (cpfValido(digitos)) {
                cpfInput.setCustomValidity('');
                cpfInput.classList.add('is-valid');
                cpfInput.classList.remove('is-invalid');
            } else {
                cpfInput.setCustomValidity(digitos.length < 11 ? 'O CPF deve conter 11 dígitos.' : 'Informe um CPF válido.');
                cpfInput.classList.add('is-invalid');
                cpfInput.classList.remove('is-valid');
            }
        };
        cpfInput.addEventListener('input', atualizarCpf);
        atualizarCpf();
    });

    document.querySelectorAll('[data-cep-field]').forEach((cepInput) => {
        const estado = document.getElementById(cepInput.dataset.estadoTarget);
        const cidade = document.getElementById(cepInput.dataset.cidadeTarget);
        const rua = document.getElementById(cepInput.dataset.ruaTarget);
        const status = document.getElementById(cepInput.dataset.cepStatus);

        cepInput.addEventListener('input', () => {
            const digitos = cepInput.value.replace(/\D/g, '').slice(0, 8);
            cepInput.value = digitos.length > 5 ? `${digitos.slice(0, 5)}-${digitos.slice(5)}` : digitos;
        });
        cepInput.addEventListener('blur', async () => {
            const cep = cepInput.value.replace(/\D/g, '');
            if (cep.length !== 8) {
                cepInput.classList.add('is-invalid');
                if (status) status.textContent = 'O CEP deve ter 8 dígitos.';
                return;
            }
            if (status) status.textContent = 'Consultando CEP…';
            try {
                const resposta = await fetch(`https://viacep.com.br/ws/${cep}/json/`);
                if (!resposta.ok) throw new Error('Falha na consulta');
                const dados = await resposta.json();
                if (dados.erro) throw new Error('CEP não encontrado.');
                if (estado) estado.value = dados.uf || '';
                if (cidade) cidade.value = dados.localidade || '';
                if (rua) rua.value = dados.logradouro || '';
                cepInput.classList.remove('is-invalid');
                cepInput.classList.add('is-valid');
                if (status) status.textContent = 'Endereço preenchido pelo CEP; confira e complete o número.';
            } catch (erro) {
                cepInput.classList.remove('is-valid');
                cepInput.classList.add('is-invalid');
                if (status) status.textContent = erro.message === 'CEP não encontrado.' ? erro.message : 'Não foi possível consultar o CEP. Você pode preencher o endereço manualmente.';
            }
        });
    });


});
</script>

<?php require_once ROOT_DIR . '/src/View/templates/includes/rodapeInterno.inc.php'; ?>

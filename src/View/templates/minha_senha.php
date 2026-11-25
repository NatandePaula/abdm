<?php
$controleAcesso ??= new \Abdm\Infraestrutura\ControleAcesso($pdo);
$controleAcesso->exigirUsuarioAutenticado();
require_once ROOT_DIR . '/src/View/templates/includes/cabecalhoInterno.inc.php';
?>

<main class="container my-5" style="max-width: 720px;">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-body-tertiary">
            <h1 class="h4 mb-0">Trocar minha senha</h1>
        </div>
        <div class="card-body">
            <?php if (!empty($_SESSION['force_password_change'])): ?>
                <div class="alert alert-warning" role="alert">Sua senha foi redefinida para uma senha temporária. Para continuar usando o sistema, troque-a agora.</div>
            <?php endif; ?>
            <p class="text-body-secondary">Informe sua senha atual e escolha uma nova senha com pelo menos 6 caracteres.</p>
            <form action="<?php echo BASE_URL; ?>acoes/controllerUsuarios.php" method="POST" class="vstack gap-3">
                <?php echo \Abdm\Infraestrutura\Sessao::campoTokenCsrf(); ?>
                <input type="hidden" name="acao" value="alterar_senha_propria">
                <div>
                    <label class="form-label" for="senhaAtual">Senha atual</label>
                    <input class="form-control" type="password" id="senhaAtual" name="senha_atual" autocomplete="current-password" required>
                </div>
                <div>
                    <label class="form-label" for="senhaNova">Nova senha</label>
                    <input class="form-control" type="password" id="senhaNova" name="senha_nova" minlength="6" maxlength="72" autocomplete="new-password" required>
                </div>
                <div>
                    <label class="form-label" for="confirmarSenha">Confirmar nova senha</label>
                    <input class="form-control" type="password" id="confirmarSenha" name="confirmar_senha" minlength="6" maxlength="72" autocomplete="new-password" required>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-primary" type="submit">Atualizar senha</button>
                    <?php if (empty($_SESSION['force_password_change'])): ?>
                        <?php if (!empty($_SESSION['is_admin'])): ?>
                            <a class="btn btn-outline-secondary" href="<?php echo BASE_URL; ?>views/usuarios.php">Voltar para usuários</a>
                        <?php else: ?>
                            <a class="btn btn-outline-secondary" href="<?php echo BASE_URL; ?>views/home.php">Voltar ao início</a>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
</main>

<?php require_once ROOT_DIR . '/src/View/templates/includes/rodapeInterno.inc.php'; ?>

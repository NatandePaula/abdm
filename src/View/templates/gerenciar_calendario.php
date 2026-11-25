<?php
$controleAcesso ??= new \Abdm\Infraestrutura\ControleAcesso($pdo);
$controleAcesso->exigirPermissao('gerenciar_calendario');

$configuracaoCalendario = (new \Abdm\Controller\CalendarioController($pdo))->configuracao();
$urlFormulario = $_SESSION['calendar_config_retry'] ?? $configuracaoCalendario['url'];
unset($_SESSION['calendar_config_retry']);
if (!is_string($urlFormulario)) {
    $urlFormulario = $configuracaoCalendario['url'];
}

require_once ROOT_DIR . '/src/View/templates/includes/cabecalhoInterno.inc.php';
?>

<main class="container-fluid px-3 px-lg-5 my-4">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h1 class="h2 mb-1">Gerenciar calendário</h1>
            <p class="text-body-secondary mb-0">Atualize o endereço da agenda exibida no sistema.</p>
        </div>
        <?php if ($controleAcesso->usuarioPode('calendario')): ?>
            <a class="btn btn-outline-secondary" href="<?php echo BASE_URL; ?>views/calendario.php">Voltar ao calendário</a>
        <?php else: ?>
            <a class="btn btn-outline-secondary" href="<?php echo BASE_URL; ?>views/home.php">Voltar ao início</a>
        <?php endif; ?>
    </div>

    <?php if (!$configuracaoCalendario['available']): ?>
        <div class="alert alert-warning" role="alert">O gerenciamento do calendário não está disponível nesta instalação. Solicite suporte ao administrador.</div>
    <?php endif; ?>

    <section class="card shadow-sm" aria-labelledby="configCalendarioTitulo">
        <div class="card-body p-4">
            <h2 class="h4 mb-3" id="configCalendarioTitulo">Endereço incorporado do Google Agenda</h2>
            <p class="text-body-secondary">
                No Google Agenda, abra as configurações da agenda, acesse <strong>Integrar agenda</strong> e copie o endereço da incorporação.
                Cole aqui a URL completa que começa com <code>https://calendar.google.com/calendar/embed</code>.
            </p>
            <form method="post" action="<?php echo BASE_URL; ?>acoes/controllerCalendario.php">
                <?php echo \Abdm\Infraestrutura\Sessao::campoTokenCsrf(); ?>
                <input type="hidden" name="acao" value="salvar">
                <div class="mb-3">
                    <label class="form-label" for="urlCalendario">URL do calendário</label>
                    <textarea class="form-control font-monospace" id="urlCalendario" name="url" rows="6" maxlength="8192" required <?php echo !$configuracaoCalendario['available'] ? 'disabled' : ''; ?>><?php echo htmlspecialchars($urlFormulario, ENT_QUOTES, 'UTF-8'); ?></textarea>
                    <div class="form-text">Aceita somente endereços HTTPS de incorporação do Google Agenda. O limite é de 8.192 caracteres.</div>
                </div>
                <button class="btn btn-primary" type="submit" <?php echo !$configuracaoCalendario['available'] ? 'disabled' : ''; ?>>Salvar endereço</button>
            </form>
        </div>
    </section>
</main>

<?php require_once ROOT_DIR . '/src/View/templates/includes/rodapeInterno.inc.php'; ?>

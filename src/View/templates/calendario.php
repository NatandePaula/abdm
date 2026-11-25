<?php
$controleAcesso ??= new \Abdm\Infraestrutura\ControleAcesso($pdo);
$controleAcesso->exigirPermissao('calendario');
$configuracaoCalendario = (new \Abdm\Controller\CalendarioController($pdo))->configuracao();
require_once ROOT_DIR . '/src/View/templates/includes/cabecalhoInterno.inc.php';
?>

<style>
    .calendar-frame {
        display: block;
        width: 100%;
        height: 2200px;
        border: 0;
        background: #fff;
    }

</style>

<main class="container-fluid py-3">
    <section class="card shadow-sm overflow-hidden" aria-labelledby="tituloCalendario">
        <div class="card-header bg-body-tertiary">
            <h1 class="h5 mb-0" id="tituloCalendario">
                <i class="bi bi-calendar3 me-2" aria-hidden="true"></i>Calendário
            </h1>
        </div>
        <div class="card-body p-0">
            <?php if (!$configuracaoCalendario['available']): ?>
                <div class="alert alert-warning m-3" role="status">
                    A configuracao do calendario nao esta disponivel nesta instalacao.
                </div>
            <?php elseif ($configuracaoCalendario['url'] !== ''): ?>
            <iframe
                class="calendar-frame"
                src="<?php echo htmlspecialchars($configuracaoCalendario['url'], ENT_QUOTES, 'UTF-8'); ?>"
                title="Calendário compartilhado do Google Agenda"
                loading="lazy"
                referrerpolicy="strict-origin-when-cross-origin">
            </iframe>
            <?php else: ?>
                <div class="alert alert-info m-3" role="status">
                    Nenhum calendario foi configurado.
                    <?php if ($controleAcesso->usuarioPode('gerenciar_calendario')): ?>
                        <a class="alert-link" href="<?php echo BASE_URL; ?>views/gerenciar_calendario.php">Configurar o endereco do calendario.</a>
                    <?php else: ?>
                        Solicite ao administrador que configure o endereco da agenda.
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>
</main>

<?php require_once ROOT_DIR . '/src/View/templates/includes/rodapeInterno.inc.php'; ?>

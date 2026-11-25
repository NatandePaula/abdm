<?php

[
    'linksInformacoes' => $linksInformacoes,
    'linkEditar' => $linkEditar,
    'link' => $link,
    'estaEditando' => $estaEditando,
] = $dadosView;

require_once ROOT_DIR . '/src/View/templates/includes/cabecalhoInterno.inc.php';
?>

<main class="container-fluid px-3 px-lg-5 my-4">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h1 class="h2 mb-1">Gerenciar links de informa&ccedil;&otilde;es</h1>
            <p class="text-body-secondary mb-0">Configure os atalhos exibidos no menu Informa&ccedil;&otilde;es.</p>
        </div>
        <a class="btn btn-outline-secondary" href="<?php echo BASE_URL; ?>views/home.php">Voltar ao in&iacute;cio</a>
    </div>

    <div class="row g-4 align-items-start">
        <section class="col-12 col-xl-4" aria-labelledby="formLinkTitulo">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <h2 class="h4 mb-3" id="formLinkTitulo"><?php echo $estaEditando ? 'Editar link' : 'Adicionar link'; ?></h2>
                    <form method="post" action="<?php echo BASE_URL; ?>acoes/controllerInformacoes.php">
                        <?php echo \Abdm\Infraestrutura\Sessao::campoTokenCsrf(); ?>
                        <input type="hidden" name="acao" value="salvar">
                        <?php if ($estaEditando): ?>
                            <input type="hidden" name="id" value="<?php echo (int)$linkEditar['idInformacaoLink']; ?>">
                        <?php endif; ?>

                        <div class="mb-3">
                            <label for="titulo" class="form-label">T&iacute;tulo</label>
                            <input class="form-control" type="text" id="titulo" name="titulo" maxlength="100" required value="<?php echo htmlspecialchars($linkEditar['titulo'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        </div>
                        <div class="mb-3">
                            <label for="url" class="form-label">Endere&ccedil;o do link</label>
                            <input class="form-control" type="url" id="url" name="url" maxlength="2048" placeholder="https://..." required value="<?php echo htmlspecialchars($linkEditar['url'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                            <div class="form-text">Use um endere&ccedil;o completo iniciado por http:// ou https://.</div>
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            <button class="btn btn-primary" type="submit"><?php echo $estaEditando ? 'Salvar altera&ccedil;&otilde;es' : 'Adicionar ao menu'; ?></button>
                            <?php if ($estaEditando): ?>
                                <a class="btn btn-outline-secondary" href="<?php echo BASE_URL; ?>views/gerenciar_informacoes.php">Cancelar edi&ccedil;&atilde;o</a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>
        </section>

        <section class="col-12 col-xl-8" aria-labelledby="linksCadastradosTitulo">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <h2 class="h4 mb-3" id="linksCadastradosTitulo">Links cadastrados <span class="badge text-bg-secondary"><?php echo count($linksInformacoes); ?></span></h2>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr><th scope="col">T&iacute;tulo</th><th scope="col">Endere&ccedil;o</th><th scope="col" class="text-end">A&ccedil;&otilde;es</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach ($linksInformacoes as $link): ?>
                                    <tr>
                                        <td class="fw-semibold"><?php echo htmlspecialchars($link['titulo'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><a href="<?php echo htmlspecialchars($link['url'], ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer" class="text-break"><?php echo htmlspecialchars($link['url'], ENT_QUOTES, 'UTF-8'); ?></a></td>
                                        <td>
                                            <div class="d-flex justify-content-end gap-2">
                                                <a class="btn btn-sm btn-outline-primary" href="<?php echo BASE_URL; ?>views/gerenciar_informacoes.php?editar=<?php echo (int)$link['idInformacaoLink']; ?>">Editar</a>
                                                <form method="post" action="<?php echo BASE_URL; ?>acoes/controllerInformacoes.php" onsubmit="return confirm('Remover este link do menu?');">
                                                    <?php echo \Abdm\Infraestrutura\Sessao::campoTokenCsrf(); ?>
                                                    <input type="hidden" name="acao" value="excluir">
                                                    <input type="hidden" name="id" value="<?php echo (int)$link['idInformacaoLink']; ?>">
                                                    <button class="btn btn-sm btn-outline-danger" type="submit">Remover</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if (!$linksInformacoes): ?>
                                    <tr><td colspan="3" class="text-center text-body-secondary py-4">Ainda n&atilde;o h&aacute; links cadastrados.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>
    </div>
</main>

<?php require_once ROOT_DIR . '/src/View/templates/includes/rodapeInterno.inc.php'; ?>

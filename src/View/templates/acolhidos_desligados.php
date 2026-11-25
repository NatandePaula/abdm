<?php
require_once ROOT_DIR.'/src/View/templates/includes/cabecalhoInterno.inc.php';

[
    'acolhidos' => $acolhidos,
] = $dadosView;

?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-12">
            <div class="card shadow-lg border-0 rounded-lg">
                <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
                    <h3 class="font-weight-light my-3 mb-0 d-flex align-items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="currentColor" class="bi bi-person-x-fill me-2" viewBox="0 0 16 16">
                          <path fill-rule="evenodd" d="M1 14s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1zm5-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6m6.146-2.854a.5.5 0 0 1 .708 0L14 6.293l1.146-1.147a.5.5 0 0 1 .708.708L14.707 7l1.147 1.146a.5.5 0 0 1-.708.708L14 7.707l-1.146 1.147a.5.5 0 0 1-.708-.708L13.293 7l-1.147-1.146a.5.5 0 0 1 0-.708z"/>
                        </svg>
                        Lista de Acolhidos Desligados
                    </h3>
                    <a href="<?php echo BASE_URL; ?>views/acolhidos.php" class="btn btn-light text-secondary fw-bold">
                        Voltar para Ativos
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover align-middle">
                            <thead class="table-dark">
                                <tr>
                                    <th>Matrícula</th>
                                    <th>Nome</th>
                                    <th>Data de Acolhimento</th>
                                    <th>Data de Desligamento</th>
                                    <th class="text-center">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(count($acolhidos) > 0): ?>
                                    <?php foreach($acolhidos as $acolhido): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($acolhido['matricula'] ?? 'N/A'); ?></td>
                                            <td class="fw-bold"><?php echo htmlspecialchars($acolhido['nome']); ?></td>
                                            <td><?php echo date('d/m/Y', strtotime($acolhido['dataAcolhimento'])); ?></td>
                                            <td><?php echo date('d/m/Y', strtotime($acolhido['dataDesligamento'])); ?></td>
                                            <td class="text-center">
                                                <a href="<?php echo BASE_URL; ?>views/verAcolhido.php?id=<?php echo $acolhido['id']; ?>" class="btn btn-sm btn-info text-white">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-eye-fill me-1" viewBox="0 0 16 16">
                                                        <path d="M10.5 8a2.5 2.5 0 1 1-5 0 2.5 2.5 0 0 1 5 0"/>
                                                        <path d="M0 8s3-5.5 8-5.5S16 8 16 8s-3 5.5-8 5.5S0 8 0 8m8 3.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7"/>
                                                    </svg>
                                                    Visualizar
                                                </a>
                                                <a href="#" class="btn btn-sm btn-success text-white" data-bs-toggle="modal" data-bs-target="#reativarModal<?php echo $acolhido['id']; ?>">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-counterclockwise me-1" viewBox="0 0 16 16">
                                                        <path fill-rule="evenodd" d="M8 3a5 5 0 1 1-4.546 2.914.5.5 0 0 0-.908-.417A6 6 0 1 0 8 2z"/>
                                                        <path d="M8 4.466V.534a.25.25 0 0 0-.41-.192L5.23 2.308a.25.25 0 0 0 0 .384l2.36 1.966A.25.25 0 0 0 8 4.466"/>
                                                    </svg>
                                                    Reativar
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted">Nenhum acolhido desligado até o momento.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if(count($acolhidos) > 0): ?>
        <?php foreach($acolhidos as $acolhido): ?>
        <!-- Modal de Reativação para o acolhido <?php echo $acolhido['id']; ?> -->
        <div class="modal fade" id="reativarModal<?php echo $acolhido['id']; ?>" tabindex="-1" aria-labelledby="reativarModalLabel<?php echo $acolhido['id']; ?>" aria-hidden="true">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title" id="reativarModalLabel<?php echo $acolhido['id']; ?>">Reativar Acolhido</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <form action="<?php echo BASE_URL; ?>acoes/controllerReativarAcolhido.php" method="POST">
                  <?php echo \Abdm\Infraestrutura\Sessao::campoTokenCsrf(); ?>
                <div class="modal-body">
                    <input type="hidden" name="id_acolhido" value="<?php echo $acolhido['id']; ?>">
                    <p>Você tem certeza que deseja reativar o acolhido <strong><?php echo htmlspecialchars($acolhido['nome']); ?></strong>?</p>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                  <button type="submit" class="btn btn-success">Confirmar Reativação</button>
                </div>
              </form>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php
require_once ROOT_DIR.'/src/View/templates/includes/rodapeInterno.inc.php';
?>

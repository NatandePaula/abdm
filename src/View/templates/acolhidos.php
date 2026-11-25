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
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h3 class="font-weight-light my-3 mb-0 d-flex align-items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="currentColor" class="bi bi-people-fill me-2" viewBox="0 0 16 16">
                            <path d="M7 14s-1 0-1-1 1-4 5-4 5 3 5 4-1 1-1 1zm4-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6m-5.784 6A2.24 2.24 0 0 1 5 13c0-1.355.68-2.75 1.936-3.72A6.3 6.3 0 0 0 5 9c-4 0-5 3-5 4s1 1 1 1zM4.5 8a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5"/>
                        </svg>
                        Lista de Acolhidos Ativos
                    </h3>
                    <div>
                        <a href="<?php echo BASE_URL; ?>views/acolhidos_desligados.php" class="btn btn-outline-light">
                            Ver Desligados
                        </a>
                        <a href="<?php echo BASE_URL; ?>views/cadastrarAcolhido.php" class="btn btn-light text-primary fw-bold ms-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-person-plus-fill me-1" viewBox="0 0 16 16">
                                <path d="M1 14s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1zm5-7a3 3 0 1 0 0-6 3 3 0 0 0 0 6"/>
                                <path fill-rule="evenodd" d="M13.5 5a.5.5 0 0 1 .5.5V7h1.5a.5.5 0 0 1 0 1H14v1.5a.5.5 0 0 1-1 0V8h-1.5a.5.5 0 0 1 0-1H13V5.5a.5.5 0 0 1 .5-.5"/>
                            </svg>
                            Novo Acolhido
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover align-middle">
                            <thead class="table-dark">
                                <tr>
                                    <th>Matrícula</th>
                                    <th>Nome</th>
                                    <th>Data de Nascimento</th>
                                    <th>Sexo</th>
                                    <th>Idade</th>
                                    <th>Data de Acolhimento</th>
                                    <th class="text-center">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(count($acolhidos) > 0): ?>
                                    <?php foreach($acolhidos as $acolhido): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($acolhido['matricula'] ?? 'N/A'); ?></td>
                                            <td class="fw-bold"><?php echo htmlspecialchars($acolhido['nome']); ?></td>
                                            <td><?php echo date('d/m/Y', strtotime($acolhido['dataNascimento'])); ?></td>
                                            <td><?php echo htmlspecialchars(\Abdm\Model\DadosAcolhido::acolhidoSexoRotulo($acolhido['sexo'] ?? null), ENT_QUOTES, 'UTF-8'); ?></td>
                                            <?php $idadeAcolhido = \Abdm\Model\DadosAcolhido::acolhidoIdade($acolhido['dataNascimento'] ?? null); ?>
                                            <td><?php echo $idadeAcolhido === null ? 'Não informada' : $idadeAcolhido . ($idadeAcolhido === 1 ? ' ano' : ' anos'); ?></td>
                                            <td><?php echo date('d/m/Y', strtotime($acolhido['dataAcolhimento'])); ?></td>
                                            <td class="text-center">
                                                <a href="<?php echo BASE_URL; ?>views/verAcolhido.php?id=<?php echo $acolhido['id']; ?>" class="btn btn-sm btn-info text-white">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-eye-fill me-1" viewBox="0 0 16 16">
                                                        <path d="M10.5 8a2.5 2.5 0 1 1-5 0 2.5 2.5 0 0 1 5 0"/>
                                                        <path d="M0 8s3-5.5 8-5.5S16 8 16 8s-3 5.5-8 5.5S0 8 0 8m8 3.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7"/>
                                                    </svg>
                                                    Visualizar
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="text-center py-4 text-muted">Nenhum acolhido registrado até o momento.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer text-center py-3">
                    <div class="text-muted small">Total de acolhidos registrados: <?php echo count($acolhidos); ?></div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
require_once ROOT_DIR.'/src/View/templates/includes/rodapeInterno.inc.php';
?>

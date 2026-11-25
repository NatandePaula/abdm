<?php
$controleAcesso ??= new \Abdm\Infraestrutura\ControleAcesso($pdo);
$controleAcesso->exigirPermissao('prescricoes');
require_once ROOT_DIR . '/src/View/templates/includes/cabecalhoInterno.inc.php';

if (!isset($_GET['id_acolhido'])) {
    echo "<div class='container my-5 alert alert-danger'>ID do acolhido não fornecido.</div>";
    require_once ROOT_DIR . '/src/View/templates/includes/rodapeInterno.inc.php';
    exit;
}

[
    'id_acolhido' => $id_acolhido,
    'acolhido' => $acolhido,
    'prescricoes_suspensas' => $prescricoes_suspensas,
] = (new \Abdm\Controller\TelasPrescricaoController($pdo))->historico();

?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-12">
            <div class="card shadow-lg border-0 rounded-lg">
                <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center">
                    <h3 class="font-weight-light my-3">Prescrições Suspensas de <?php echo htmlspecialchars($acolhido['nome']); ?></h3>
                    <a href="<?php echo BASE_URL; ?>views/prescricoes.php?id_acolhido=<?php echo $id_acolhido; ?>" class="btn btn-dark">Voltar às Prescrições</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Medicamento</th>
                                    <th>Data da Suspensão</th>
                                    <th>Período</th>
                                    <th>Motivo</th>
                                    <th class="text-center">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($prescricoes_suspensas)): ?>
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">Nenhuma prescrição suspensa para este acolhido.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($prescricoes_suspensas as $presc): ?>
                                        <tr>
                                            <td class="fw-bold"><?php echo htmlspecialchars($presc['nome_comercial']); ?></td>
                                            <td><?php echo (isset($presc['data_status_alterado']) && $presc['data_status_alterado']) ? date('d/m/Y', strtotime($presc['data_status_alterado'])) : 'Não informada'; ?></td>
                                            <td>Permanente</td>
                                            <td><?php echo htmlspecialchars($presc['motivo_suspensao']); ?></td>
                                            <td class="text-center">
                                                <form action="<?php echo BASE_URL; ?>acoes/controllerMedicamento.php" method="POST" class="d-inline">
                                                    <?php echo \Abdm\Infraestrutura\Sessao::campoTokenCsrf(); ?>
                                                    <input type="hidden" name="acao" value="alterar_status_prescricao">
                                                    <input type="hidden" name="prescricao_id" value="<?php echo $presc['id']; ?>">
                                                    <input type="hidden" name="acolhido_id" value="<?php echo $id_acolhido; ?>">
                                                    <input type="hidden" name="novo_status" value="ativa">
                                                    <button type="submit" class="btn btn-sm btn-success">Reativar</button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
require_once ROOT_DIR . '/src/View/templates/includes/rodapeInterno.inc.php';
?>

<?php

require_once dirname(__DIR__) . '/config/aplicacao.php';

$controleAcesso = new \Abdm\Infraestrutura\ControleAcesso($pdo);
$controleAcesso->exigirPermissao('prescricoes');
$dadosView = (new \Abdm\Controller\TelasPrescricaoController($pdo))->gerenciarPeriodos();

require ROOT_DIR . '/src/View/templates/gerenciar_periodos.php';

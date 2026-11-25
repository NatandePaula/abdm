<?php

require_once dirname(__DIR__) . '/config/aplicacao.php';

$controleAcesso = new \Abdm\Infraestrutura\ControleAcesso($pdo);
$controleAcesso->exigirPermissao('gerenciar_informacoes');
$dadosView = (new \Abdm\Controller\MenuInformacoesController($pdo))->gerenciar();

require ROOT_DIR . '/src/View/templates/gerenciar_informacoes.php';

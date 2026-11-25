<?php

require_once dirname(__DIR__) . '/config/aplicacao.php';

$controleAcesso = new \Abdm\Infraestrutura\ControleAcesso($pdo);
$dadosView = (new \Abdm\Controller\PainelEstoqueController($controleAcesso, $pdo))->dadosParaExibir();

require ROOT_DIR . '/src/View/templates/medicamentos_estoque.php';

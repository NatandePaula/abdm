<?php

require_once dirname(__DIR__) . '/config/aplicacao.php';

$controleAcesso = new \Abdm\Infraestrutura\ControleAcesso($pdo);
$controleAcesso->exigirPermissao('relatorio_diario');
$dadosView = (new \Abdm\Controller\TelasRelatorioController($pdo))->diario();

require ROOT_DIR . '/src/View/templates/relatorio.php';

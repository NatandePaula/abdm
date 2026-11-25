<?php

require_once dirname(__DIR__) . '/config/aplicacao.php';

$controleAcesso = new \Abdm\Infraestrutura\ControleAcesso($pdo);
$controleAcesso->exigirPermissao('acolhidos');
$dadosView = (new \Abdm\Controller\TelasAcolhidoController($pdo))->listarDesligados();

require ROOT_DIR . '/src/View/templates/acolhidos_desligados.php';

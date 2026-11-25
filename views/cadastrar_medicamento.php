<?php

require_once dirname(__DIR__) . '/config/aplicacao.php';

$controleAcesso = new \Abdm\Infraestrutura\ControleAcesso($pdo);
$controleAcesso->exigirPermissao('estoque');
$dadosView = (new \Abdm\Controller\TelaMedicamentoController($pdo))->formulario();

require ROOT_DIR . '/src/View/templates/cadastrar_medicamento.php';

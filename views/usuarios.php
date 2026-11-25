<?php

require_once dirname(__DIR__) . '/config/aplicacao.php';

$controleAcesso = new \Abdm\Infraestrutura\ControleAcesso($pdo);
$controleAcesso->exigirUsuarioAdministrador();
$dadosView = (new \Abdm\Controller\TelaUsuariosController($pdo))->preparar();

require ROOT_DIR . '/src/View/templates/usuarios.php';

<?php

require_once __DIR__ . '/config/aplicacao.php';

$mensagemErro = (new \Abdm\Controller\LoginController($pdo))->executar();

require ROOT_DIR . '/src/View/templates/login.php';

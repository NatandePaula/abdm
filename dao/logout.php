<?php

require_once dirname(__DIR__) . '/src/autoload.php';
\Abdm\Controller\SessaoController::exigirPost();

require_once dirname(__DIR__) . '/config/aplicacao.php';

(new \Abdm\Controller\SessaoController($pdo))->encerrar();

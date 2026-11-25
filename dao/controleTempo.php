<?php

require_once dirname(__DIR__) . '/config/aplicacao.php';

(new \Abdm\Controller\SessaoController($pdo))->renovar();

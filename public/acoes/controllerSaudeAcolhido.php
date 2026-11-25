<?php

require_once dirname(__DIR__, 2) . '/config/aplicacao.php';

(new \Abdm\Controller\SaudeAcolhidoController($pdo))->executar();

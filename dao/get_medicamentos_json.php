<?php

require_once dirname(__DIR__) . '/config/aplicacao.php';

$controleAcesso = new \Abdm\Infraestrutura\ControleAcesso($pdo);
(new \Abdm\Controller\CatalogoMedicamentosController($controleAcesso))->executar();

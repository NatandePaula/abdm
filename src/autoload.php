<?php

spl_autoload_register(static function (string $classe): void {
    $prefixo = 'Abdm\\';
    if (!str_starts_with($classe, $prefixo)) {
        return;
    }

    $nome = substr($classe, strlen($prefixo));
    $partes = explode('\\', $nome);
    foreach ($partes as $parte) {
        if (!preg_match('/^[A-Z][a-zA-Z0-9]*$/D', $parte)) {
            return;
        }
    }
    if (count($partes) < 2) {
        return;
    }

    $arquivo = __DIR__ . '/' . implode('/', $partes) . '.php';
    if (is_file($arquivo)) {
        require $arquivo;
    }
});

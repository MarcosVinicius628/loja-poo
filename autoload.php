<?php

spl_autoload_register(function (string $classe): void {
    $prefixo = 'App\\';
    if (strncmp($classe, $prefixo, strlen($prefixo)) !== 0) {
        return;
    }

    $classeRelativa = substr($classe, strlen($prefixo));
    $arquivo = __DIR__ . '/src/' . str_replace('\\', DIRECTORY_SEPARATOR, $classeRelativa) . '.php';
    if (is_file($arquivo)) {
        require_once $arquivo;
    }
});

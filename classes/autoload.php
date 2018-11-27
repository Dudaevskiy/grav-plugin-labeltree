<?php
spl_autoload_register(function ($class) {
    $prefix = 'Grav\Plugin\LabelTree\\';
    if (0 !== strpos($class, $prefix)) {
        return;
    }
    $file = __DIR__
        .DIRECTORY_SEPARATOR
        .str_replace('\\', DIRECTORY_SEPARATOR, substr(strtolower($class), strlen($prefix)))
        .'.php';
    if (!is_readable($file)) {
        return;
    }
    require $file;
});
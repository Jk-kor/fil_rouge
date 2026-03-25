<?php
// Fichier : config/init.php
session_start();

spl_autoload_register(function ($class) {
    $baseDir = __DIR__ . '/../classes/';
    $file = $baseDir . $class . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});
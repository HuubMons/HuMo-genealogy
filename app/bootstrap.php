<?php

require(__DIR__ . '/../vendor/autoload.php');

if (session_status() === PHP_SESSION_NONE) session_start();

if (isset($_GET['log_off'])) {
    session_destroy();
    header('Location: /');
    exit();
}

if (file_exists(__DIR__ . '/../env.local.php')) {
    $_ENV = require(__DIR__ . '/../env.local.php');
}

if (isset($_ENV['APP_ENV']) && 'dev' === $_ENV['APP_ENV']) {
    error_reporting(E_ALL);

    function dd(mixed $variable, bool $exit = true)
    {
        echo '<pre>';
        var_dump($variable);
        echo '</pre>';
        if (true === $exit) {
            exit();
        }
    }
}


include_once(__DIR__ . "/../include/db_login.php");

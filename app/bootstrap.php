<?php

require(__DIR__ . '/../vendor/autoload.php');

if(session_status() === PHP_SESSION_NONE) session_start();

if (isset($_GET['log_off'])) {
    session_destroy();
    header('Location: /');
    exit();
}

include_once(__DIR__ . "/../include/db_login.php");
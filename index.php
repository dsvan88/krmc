<?php
// error_reporting(E_ALL);
require_once $_SERVER['DOCUMENT_ROOT'] . '/app/config/bootstrap.php';

use app\core\Locale;
use app\core\Router;
use app\mappers\Users;

if (!isset($_SESSION['id']) && isset($_COOKIE[CFG_TOKEN_NAME])) {
    Users::sessionReturn($_COOKIE[CFG_TOKEN_NAME]);
}

Locale::setLocale();

Router::run();

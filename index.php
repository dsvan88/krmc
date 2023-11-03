<?
require_once $_SERVER['DOCUMENT_ROOT'] . '/app/config/bootstrap.php';

use app\core\Locale;
use app\core\Router;
use app\models\Users;

if (!isset($_SESSION['id']) && isset($_COOKIE[CFG_TOKEN_NAME])) {
    Users::sessionReturn($_COOKIE[CFG_TOKEN_NAME]);
}

if (!empty($_GET['lang']) && in_array($_GET['lang'], Locale::$langCodes, true))
    Locale::$langCode = $_GET['lang'];
// Locale::$langCode = 'en';
Router::run();

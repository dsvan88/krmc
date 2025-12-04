<?
use app\core\Env;

require_once $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';

spl_autoload_register(function ($class) {
    $class = str_replace('\\', '/', $class);
    if (file_exists("{$_SERVER['DOCUMENT_ROOT']}/$class")) {
        require_once "{$_SERVER['DOCUMENT_ROOT']}/$class";
    } elseif (file_exists("{$_SERVER['DOCUMENT_ROOT']}/$class.php")) {
        require_once "{$_SERVER['DOCUMENT_ROOT']}/$class.php";
    }
});

Env::init();

require_once 'config.php';
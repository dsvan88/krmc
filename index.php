<?

require_once $_SERVER['DOCUMENT_ROOT'] . '/app/config/config.php';

// use app\core\Locale;
use app\core\Router;
// use app\models\Users;

spl_autoload_register(function ($class) {
    $class = str_replace('\\', '/', $class);
    if (file_exists("{$_SERVER['DOCUMENT_ROOT']}/$class")) {
        require_once "{$_SERVER['DOCUMENT_ROOT']}/$class";
    } elseif (file_exists("{$_SERVER['DOCUMENT_ROOT']}/$class.php")) {
        require_once "{$_SERVER['DOCUMENT_ROOT']}/$class.php";
    } elseif (file_exists("{$_SERVER['DOCUMENT_ROOT']}/$class.class.php")) {
        require_once "{$_SERVER['DOCUMENT_ROOT']}/$class.class.php";
    }
});

/* if (isset($_COOKIE[CFG_TOKEN_NAME])) {
    Users::sessionReturn($_COOKIE[CFG_TOKEN_NAME]);
} */
// Locale::$langCode = 'ru';
// Locale::$langCode = 'en';
?>
<pre>
<? var_dump($_SERVER); ?>
</pre>
<?
Router::run();

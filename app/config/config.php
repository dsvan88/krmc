<?php
if (!session_id()) {
    session_start();
    if (!empty($_SERVER['HTTP_USER_AGENT']))
        $_SESSION['csrf'] = sha1($_SERVER['HTTP_USER_AGENT'] . session_id());
}

if (!defined('SQL_HOST')) {
    define('SQL_TYPE',     empty($_ENV['SQL_TYPE'])       ? 'pgsql' :     $_ENV['SQL_TYPE']);
    if (isset($_ENV['DATABASE_URL'])) {
        preg_match('/\/\/(.*?)\:(.*?)\@(.*?)\:(\d{1,5})\/(.*)/', $_ENV['DATABASE_URL'], $match);
        define('SQL_USER', $match[1]);
        define('SQL_PASS', $match[2]);
        define('SQL_HOST', $match[3]);
        define('SQL_PORT', $match[4]);
        define('SQL_DB', $match[5]);
    } else {
        define('SQL_HOST',  empty($_ENV['SQL_HOST'])    ? '127.0.0.1' :    $_ENV['SQL_HOST']);
        define('SQL_PORT',  empty($_ENV['SQL_PORT'])    ? '5432' :         $_ENV['SQL_PORT']);
        define('SQL_USER',  empty($_ENV['SQL_USER'])    ? 'postgres' :     $_ENV['SQL_USER']);
        define('SQL_PASS',  empty($_ENV['SQL_PASS'])    ? '' :             $_ENV['SQL_PASS']);
        define('SQL_DB',    empty($_ENV['SQL_DB'])      ? 'krmc_mvc' :     $_ENV['SQL_DB']);
    }

    define('SQL_TBL_GAMES', 'games');
    define('SQL_TBL_USERS', 'users');
    define('SQL_TBL_WEEKS', 'weeks');
    define('SQL_TBL_SETTINGS', 'settings');
    define('SQL_TBL_PAGES', 'pages');
    define('SQL_TBL_CONTACTS', 'contacts');
    define('SQL_TBL_TG_CHATS', 'tgchats');

    define('DATE_MARGE', 36000); //36000 = +10 часов к длительности вечера
    define('TIME_MARGE', 1800); //1800 = за полчаса до официально старта - открывает регистрация игроков на первую игру
    define('PASS_FAIL_TROTTLING', 30); //30 = 30 секунд после третьей неудачной попытки введения пароля и дополнительно, на каждую последующую попытку
    define('PASS_FAIL_MIN', 3); //3 = три спроби невірного введення даних авторизації для початку троттлінга
    define('TIMESTAMP_DAY', 86400);
    define('TIMESTAMP_WEEK', 604800);
    define('MAX_WEEKS_AHEAD', 6);

    define('CFG_DEBUG', true);
    define('CFG_SOFT_DELETE', true);
    define('CFG_NEWS_PER_PAGE', 6);
    define('CFG_MAX_SESSION_AGE', TIMESTAMP_WEEK); // 60*60*24*7 == 1 week

    define('ROOT_PASS_DEFAULT', empty($_ENV['ROOT_PASS_DEFAULT'])  ? 'admin1234' : $_ENV['ROOT_PASS_DEFAULT']);
    define('BACKUP_FREQ',   empty($_ENV['BACKUP_FREQ']) ? TIMESTAMP_DAY * 2 : $_ENV['BACKUP_FREQ']);
    define('APP_VERSION',   empty($_ENV['APP_VERSION'])     ? '0.17b' :     $_ENV['APP_VERSION']);
    define('APP_LOC', empty($_ENV['APP_LOC'])  ? 'product' : $_ENV['APP_LOC']);
    define('CLUB_NAME',     empty($_ENV['CLUB_NAME'])       ? 'Mafia Club Kryvyi Rih' :     $_ENV['CLUB_NAME']);
    define('CLUB_SNAME',    empty($_ENV['CLUB_SNAME'])      ? 'KRMC' :      $_ENV['CLUB_SNAME']);
    define('CFG_TOKEN_NAME', empty($_ENV['CFG_TOKEN_NAME'])  ? 'KRMCtoken' : $_ENV['CFG_TOKEN_NAME']);

    define('FILE_USRGALL', '/public/gallery/users/');
    define('FILE_MAINGALL', '/public/gallery/site/');
    define('SCRIPTS_STORAGE', '/public/scripts/');
    define('CFG_AUTHOR', 'DSVan');
    define('CSRF_NAME', '_token');
}

if (CFG_DEBUG) {
    ini_set('error_reporting', E_ALL);
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
}

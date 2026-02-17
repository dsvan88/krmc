<?php
if (empty($_ENV['ROOT_PASS_DEFAULT'])) {
    throw new RuntimeException('ROOT_PASS_DEFAULT not set');
}
define('APP_LOC', $_ENV['APP_LOC'] ?? 'product');

if (!session_id()) {
    session_set_cookie_params([
        'httponly' => true,
        'secure' => APP_LOC !== 'local',
        'samesite' => 'Strict'
    ]);
    session_start();
    session_regenerate_id(true);
    if (empty($_SESSION['csrf']))
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
}

if (!defined('SQL_HOST')) {
    define('SQL_TYPE',     $_ENV['SQL_TYPE'] ?? 'pgsql');
    if (isset($_ENV['DATABASE_URL'])) {
        preg_match('/\/\/(.*?)\:(.*?)\@(.*?)\:(\d{1,5})\/(.*)/', $_ENV['DATABASE_URL'], $match);
        define('SQL_USER', $match[1]);
        define('SQL_PASS', $match[2]);
        define('SQL_HOST', $match[3]);
        define('SQL_PORT', $match[4]);
        define('SQL_DB', $match[5]);
    } else {
        define('SQL_HOST',  $_ENV['SQL_HOST'] ?? '127.0.0.1');
        define('SQL_PORT',  $_ENV['SQL_PORT'] ?? '5432');
        define('SQL_USER',  $_ENV['SQL_USER'] ?? 'postgres');
        define('SQL_PASS',  $_ENV['SQL_PASS'] ?? '');
        define('SQL_DB',    $_ENV['SQL_DB'] ?? 'krmc_mvc');
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

    define('CFG_DEBUG', $_ENV['CFG_DEBUG'] ?? false);
    define('CFG_SOFT_DELETE', true);
    define('CFG_NEWS_PER_PAGE', 6);
    define('CFG_MAX_SESSION_AGE', TIMESTAMP_WEEK); // 60*60*24*7 == 1 week

    define('ROOT_PASS_DEFAULT', $_ENV['ROOT_PASS_DEFAULT']);
    define('BACKUP_FREQ',   $_ENV['BACKUP_FREQ'] ?? TIMESTAMP_DAY * 2);
    define('APP_VERSION',   $_ENV['APP_VERSION'] ?? '0.1');
    define('CLUB_NAME',     $_ENV['CLUB_NAME'] ?? 'Mafia Club Kryvyi Rih');
    define('CLUB_SNAME',    $_ENV['CLUB_SNAME'] ?? 'KRMC');
    define('CFG_TOKEN_NAME', $_ENV['CFG_TOKEN_NAME'] ?? 'KRMCtoken');

    define('FILE_USRGALL', '/public/gallery/users/');
    define('FILE_MAINGALL', '/public/gallery/site/');
    define('SCRIPTS_STORAGE', '/app/scripts/');
    define('SCRIPTS_PUBLIC', '/public/scripts/');
    define('STYLES_STORAGE', '/public/css/');
    define('CFG_AUTHOR', 'DSVan');
    define('CSRF_NAME', '_token');


    define('CFG_MAINTENCE', $_ENV['CFG_MAINTENCE'] ?? 0);
}

if (CFG_DEBUG) {
    ini_set('error_reporting', E_ALL);
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
}

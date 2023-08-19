<?php
if (!session_id()) {
    session_start();
    $_SESSION['csrf'] = sha1(session_id());
}
if (!defined('SQL_HOST')) {
    // Determine base for saving files (Heroku - as Blob in DB, on real filesystems - as files)
    define('SAVE_AS_LOCAL_FILE', false);
    define('PAGES_AS_LOCAL_FILE', false);
    if (isset($_ENV['DATABASE_URL'])) {
        preg_match('/\/\/(.*?)\:(.*?)\@(.*?)\:(\d{1,5})\/(.*)/', $_ENV['DATABASE_URL'], $match);
        define('SQL_USER', $match[1]);
        define('SQL_PASS', $match[2]);
        define('SQL_HOST', $match[3]);
        define('SQL_PORT', $match[4]);
        define('SQL_DB', $match[5]);
    } else {
        define('SQL_HOST', '127.0.0.1');
        define('SQL_PORT', '5432');
        define('SQL_USER', 'postgres');
        define('SQL_PASS', '');
        define('SQL_DB', 'krmc_mvc');
    }
    define('SQL_TBL_GAMES', 'games');
    define('SQL_TBL_USERS', 'users');
    define('SQL_TBL_EVEN', 'evenings');
    define('SQL_TBL_WEEKS', 'weeks');
    define('SQL_TBL_PLACES', 'places');
    define('SQL_TBL_SETTINGS', 'settings');
    // define('SQL_TBL_VOTES', 'votes');
    // define('SQL_TBL_COMM', 'comments');
    define('SQL_TBL_PAGES', 'pages');

    define('SQL_TBL_CONTACTS', 'contacts');
    define('SQL_TBL_TG_CHATS', 'tgchats');

    define('DATE_MARGE', 36000); //36000 = +10 часов к длительности вечера
    define('TIME_MARGE', 1800); //1800 = за полчаса до официально старта - открывает регистрация игроков на первую игру
    define('PASS_FAIL_TROTTLING', 30); //30 = 30 секунд после третьей неудачной попытки введения пароля и на каждую последующую попытку
    define('PASS_FAIL_MIN', 3); //3 = три спроби невірного введення даних авторизації для початку троттлінга
    define('TIMESTAMP_DAY', 86400);
    define('TIMESTAMP_WEEK', 604800);
    define('BACKUP_FREQ', TIMESTAMP_DAY*3);
    define('CFG_DEBUG', true);
    define('CFG_SOFT_DELETE', true);
    define('CFG_NEWS_PER_PAGE', 6);
    define('CFG_MAX_SESSION_AGE', TIMESTAMP_WEEK); // 60*60*24*7 == 1 week
    define('LOG_PREFIX', 'LogFile_');
    define('SCRIPT_VERSION', '0.15b');
    define('MAFCLUB_NAME', 'Mafia Club Kryvyi Rih');
    define('MAFCLUB_SNAME', 'KRMC');
    define('FILE_BACKUPS', '/public/backups');
    define('FILE_USRGALL', '/public/gallery/users/');
    define('FILE_MAINGALL', '/public/gallery/site/');
    define('CFG_AUTHOR', 'DSVan');
    define('CFG_TOKEN_NAME', 'KRMCtoken');
    define('CSRF_NAME', '_token');
}

if (CFG_DEBUG) {
    ini_set('error_reporting', E_ALL);
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
}

<?php

namespace app\libs;

use PDO;

class Db
{

    protected static $db;

    public static function connect()
    {
        if (!empty(self::$db))
            return self::$db;
        try {
            $pdo = new PDO('pgsql:host=' . SQL_HOST . ';port=' . SQL_PORT . ';dbname=' . SQL_DB, SQL_USER, SQL_PASS);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (\Throwable $th) {
            $pdo = self::initDb();
        }
        self::$db = $pdo;

        $checkTable = SQL_TBL_USERS;
        $result = $pdo->query("SELECT EXISTS ( SELECT FROM pg_tables WHERE schemaname = 'public' AND tablename  = '$checkTable');");
        $check = $result->fetchColumn();
        if (!$check) {
            self::dbFillDefaults();
        }

        return $pdo;
    }

    public static function query($query, $params = [], $fetchMode = 'All', $columns = 0)
    {
        // error_log($query);
        // error_log(json_encode($params, JSON_UNESCAPED_UNICODE));
        $stmt = self::connect()->prepare($query);
        $stmt->execute($params);
        if (strpos(trim($query), 'SELECT') === 0) {
            if ($fetchMode === 'All') {
                return $stmt->fetchAll();
            } elseif ($fetchMode === 'Column') {
                return $stmt->fetchColumn($columns);
            } elseif ($fetchMode === 'Num') {
                return $stmt->fetchAll(PDO::FETCH_NUM);
            } elseif ($fetchMode === 'Assoc') {
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
        } elseif (strpos(trim($query), 'INSERT') === 0) {
            return self::connect()->lastInsertId();
        }
        return true;
    }
    public static function massQuery($query, $params = [])
    {
        $stmt = self::connect()->prepare($query);
        foreach ($params as $num => $values) {
            $stmt->execute($values);
        }
        return true;
    }
    // Проверка наличия записей в базе по критериям
    public static function isExists($criteria, $table, $criteriaType = 'OR')
    {
        $keys = array_keys($criteria);
        $query = "SELECT COUNT(id) FROM $table WHERE ";

        for ($x = 0; $x < count($keys); $x++) {
            if (trim($criteria[$keys[$x]]) !== '') {
                $query .= $keys[$x] . " = :$keys[$x] $criteriaType ";
            }
        }
        return (self::query(substr($query, 0, -2 - strlen($criteriaType)), $criteria, 'Column')) > 0 ? true : false;
    }
    // Добавляет запись в SQL базу
    // $data - ассоциативный массив со значениями записи: array('column_name'=>'column_value',...)
    // $table - таблица в которую будет добавлена запись
    // возвращает последнюю запись из таблицы (id новой записи, если верно указан $g_id)
    public static function insert($data, $table)
    {
        $preKeys = [];
        if (count($data) === count($data, COUNT_RECURSIVE)) {
            $keys = array_keys($data);
            for ($x = 0; $x < count($keys); $x++)
                $preKeys[$x] = ':' . $keys[$x];
            $keys = implode(',', $keys);
            $preKeys = implode(',', $preKeys);
            return self::query("INSERT INTO $table ($keys) VALUES ($preKeys)", $data);
        } else {
            $keys = array_keys($data[0]);
            for ($x = 0; $x < count($keys); $x++)
                $preKeys[$x] = ':' . $keys[$x];
            $keys = implode(',', $keys);
            $preKeys = implode(',', $preKeys);
            self::massQuery("INSERT INTO $table ($keys) VALUES ($preKeys)", $data);
            return true;
        }
    }
    // Обновляет запись в базе
    // $data - ассоциативный массив со значениями записи: array('column_name'=>'column_value',...)
    // $where - ассоциативный массив со значенниями по которым искать запись для обновления ('key'=>'value') ('id'=>1)
    // $table - таблица в которую будет добавлена запись
    public static function update($data, $where, $table)
    {
        $query = "UPDATE $table SET ";
        foreach ($data as $k => $v)
            $query .= "$k = :$k,";
        $conditon = ' WHERE ';
        foreach ($where as $k => $v) {
            if (isset($data[$k])) {
                error_log(__METHOD__ . ": UPDATE cann’t work with same keys: $k in UPDATE-array and UPDATE-conditions!");
                die();
            }
            $data[$k] = $v;
            $conditon .= "$k = :$k OR";
        }
        if ($conditon === ' WHERE ') {
            error_log(__METHOD__ . ': There is no conditions for SQL UPDATE');
            die();
        }

        return self::query(substr($query, 0, -1) . substr($conditon, 0, -3), $data);
    }
    // Удаляет строку по её полю ID в таблице
    public static function delete($id, $table)
    {
        return self::query("DELETE FROM $table WHERE id = ?", [$id]);
    }
    //Очищает таблицу
    public static function tableTruncate($table)
    {
        return self::query("TRUNCATE ONLY $table");
    }

    public static function initDb()
    {
        $pdo = new PDO('pgsql:host=' . SQL_HOST . ';port=' . SQL_PORT, SQL_USER, SQL_PASS);
        $pdo->query('CREATE DATABASE ' . SQL_DB);
        $pdo = null;
        $pdo = new PDO('pgsql:host=' . SQL_HOST . ';port=' . SQL_PORT . ';dbname=' . SQL_DB, SQL_USER, SQL_PASS);
        return $pdo;
    }
    public static function dbFillDefaults()
    {

        $table = SQL_TBL_WEEKS;
        self::query(
            "CREATE TABLE IF NOT EXISTS $table (
                id INT GENERATED BY DEFAULT AS IDENTITY PRIMARY KEY,
                data JSON DEFAULT NULL,
                start INT NOT NULL DEFAULT '0',
                finish INT NOT NULL DEFAULT '0',
                created_at INT NOT NULL DEFAULT '0',
                updated_at INT NOT NULL DEFAULT '0'
            );"
        );
        $table = SQL_TBL_NEWS;
        self::query(
            "CREATE TABLE IF NOT EXISTS $table (
                id INT GENERATED BY DEFAULT AS IDENTITY PRIMARY KEY,
                title CHARACTER VARYING(250) NOT NULL DEFAULT '',
                subtitle CHARACTER VARYING(250) NOT NULL DEFAULT '',
                logo CHARACTER VARYING(250) NOT NULL DEFAULT '',
                html TEXT NULL DEFAULT NULL,
                date_add TIMESTAMP DEFAULT NOW(),
                date_delete INT NOT NULL DEFAULT '0',
                type VARCHAR(50) NOT NULL DEFAULT 'news'
            );"
        );
        $table = SQL_TBL_SETTINGS;
        self::query(
            "CREATE TABLE IF NOT EXISTS $table (
                id INT GENERATED BY DEFAULT AS IDENTITY PRIMARY KEY,
                type CHARACTER VARYING(30) NOT NULL DEFAULT 'pages',
                short_name CHARACTER VARYING(200) NOT NULL DEFAULT '',
                name CHARACTER VARYING(200) NOT NULL DEFAULT '',
                value TEXT NULL DEFAULT NULL,
                by_default TEXT NULL DEFAULT NULL,
                json_value JSON DEFAULT NULL
            );"
        );
        $table = SQL_TBL_USERS;
        $privilegeDefault = [
            'status' => '',
            'admin' => '',
            'rank' => 0
        ];
        $privilegeDefault = json_encode($privilegeDefault, JSON_UNESCAPED_UNICODE);
        $presonalDefault = [
            'fio' => '',
            'birthday' => 0,
            'gender' => '',
            'avatar' => ''
        ];
        $presonalDefault = json_encode($presonalDefault, JSON_UNESCAPED_UNICODE);
        $contactsDefault = [
            'email' => '',
            'telegram' => '',
            'telegramid' => ''
        ];
        $contactsDefault = json_encode($contactsDefault, JSON_UNESCAPED_UNICODE);
        $credoDefault = [
            'in_game' => '',
            'in_live' => ''
        ];
        $credoDefault = json_encode($credoDefault, JSON_UNESCAPED_UNICODE);
        self::query(
            "CREATE TABLE IF NOT EXISTS $table (
                id INT GENERATED BY DEFAULT AS IDENTITY PRIMARY KEY,
                name CHARACTER VARYING(250) NOT NULL DEFAULT '',
                login CHARACTER VARYING(250) NOT NULL DEFAULT '',
                password CHARACTER VARYING(250) NOT NULL DEFAULT '',
                privilege JSON DEFAULT '$privilegeDefault',
                personal JSON DEFAULT '$presonalDefault',
                contacts JSON DEFAULT '$contactsDefault',
                credo JSON DEFAULT '$credoDefault'
            );"
        );
        $table = SQL_TBL_TG_CHATS;
        self::query(
            "CREATE TABLE IF NOT EXISTS $table (
                id INT GENERATED BY DEFAULT AS IDENTITY PRIMARY KEY,
                uid CHARACTER VARYING(250) NOT NULL DEFAULT '',
                personal JSON DEFAULT NULL,
                data JSON DEFAULT NULL
            );"
        );
        if (!self::isExists(['id' => 1], SQL_TBL_USERS)) {
            $privilege = ['status' => 'admin', 'admin' => 1, 'rank' => 0];
            self::insert([
                'login' => 'admin',
                'password' => '$2y$10$QXBH7fo4T152f.Tfy6zBwOZF54VdfX6uGhK7DAgm/kFXLS/gtI5zK', //admin1234
                'privilege' => json_encode($privilege, JSON_UNESCAPED_UNICODE)
            ], SQL_TBL_USERS);
        }
        if (self::query('SELECT COUNT (id) FROM ' . SQL_TBL_SETTINGS, [], 'Column') < 1) {
            $settings = [
                ['img', 'MainLogo', 'Основний логотип', '/public/images/club_logo.png', '/public/images/club_logo.png'],
                ['img', 'MainFullLogo', 'Основний логотип', '/public/images/club_logo-full.png', '/public/images/club_logo-full.png'],
                ['img', 'MainLogoMini', 'Основний логотип', '/public/images/club_logo-mini.png', '/public/images/club_logo-mini.png'],
                ['img', 'profile', 'Профиль', '/public/images/profile.png', '/public/images/profile.png'],
                ['img', 'male', 'Профиль', '/public/images/male.png', '/public/images/male.png'],
                ['img', 'female', 'Профиль', '/public/images/female.png', '/public/images/female.png'],
                ['img', 'empty_avatar', 'Нет аватара', '/public/images/empty_avatar.png', '/public/images/empty_avatar.png'],
                ['img', 'news_default', 'Новость', '/public/images/news_default.png', '/public/images/news_default.png'],
                ['tg-bot', 'token_bota', 'Токен Бота', '', ''],
                ['tg-tech', 'tech_chat', 'Технический чат (лог ошибок)', '', ''],
                ['tg-main', 'main_group_chat', 'Основной груповой чат', '', ''],
                ['point', 'win', 'Балы за победу', '1.0', '1.0'],
                ['point', 'bm', 'Лучший ход', '0.0,0.0,0.25,0.4', '0.0,0.0,0.25,0.4'],
                ['point', 'fk_sheriff', 'Отстрел шерифа первым', '0.3', '0.3'],
                ['point', 'maf_dops', 'Допы живым мафам', '0.0,0.3,0.15,0.3', '0.0,0.3,0.15,0.3'],
                ['point', 'mir_dops', 'Допы живым мирным', '0.0,0.0,0.15,0.1', '0.0,0.0,0.15,0.1'],
                ['point', 'fouls', 'Штраф за дискв. фол', '0.3', '0.3'],
                ['pages', 'index', 'Про гру', '<p>Клубна гра Мафія, в класичному стилі вражає свою легкістю та складністю одночасно! Результат кожної гри, завжди залежить не тільки від особистого вкладу кожного окремого гравця, але й від команди в цілому. Так, ми чудово розуміємо, що до цього моменту - нічого нового, для командних видів ігор - не було...</p><p>Але є нюанси!</p><p>Адже, стосовно того, хто знаходиться у твоїй команді - інтрига зберігається до самого закінчення гри! Прокачайте, разом з нами, свої навички логіки, дедукції, емпатії, інтуїції, да й що там казати - телепатії, також!</p><p>Запрошуємо Вас, до нашого дружнього та кмітливого клубу гравців у Мафію!:)</p>', '']
            ];
            $array = [];
            $keys = ['type', 'short_name', 'name',  'value', 'by_default'];
            for ($i = 0; $i < count($settings); $i++) {
                $array[] = array_combine($keys, $settings[$i]);
            }
            self::insert($array, SQL_TBL_SETTINGS);
        }
        if (self::query('SELECT COUNT (id) FROM ' . SQL_TBL_NEWS, [], 'Column') < 1) {
            $promo = ['title' => 'Записываемся активнее!', 'subtitle' => 'Важен каждый игрок!', 'html' => 'Только Ваше участие позволяет клубу и другим игрокам становиться лучше!', 'type' => 'promo'];
            self::insert($promo, SQL_TBL_NEWS);
        }
    }
}

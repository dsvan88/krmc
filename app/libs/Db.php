<?php

namespace app\libs;

use app\models\Users;
use PDO;

class Db
{

    protected static $connection;
    protected static $table;
    protected static $type = SQL_TYPE;
    protected static $host = SQL_HOST;
    protected static $port = SQL_PORT;
    protected static $db = SQL_DB;
    protected static $user = SQL_USER;
    protected static $password = SQL_PASS;

    public function __construct($options)
    {
        foreach ($options as $option => $value) {
            self::$$option = $value;
        }
    }
    public static function connect()
    {
        if (!empty(self::$connection)) return self::$connection;

        try {
            $connection = new PDO(self::$type . ':host=' . self::$host . ';port=' . self::$port . ';dbname=' . self::$db, self::$user, self::$password);
            $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (\Throwable $th) {
            $connection = self::dbInit();
        }
        self::$connection = $connection;

        if (!self::isTableExists()) {
            self::dbFillDefaults();
        }

        return $connection;
    }
    public static function isTableExists(): bool
    {
        if (empty(static::$table)) return true;

        $table = static::$table;
        $query = 'SHOW TABLES LIKE ?';
        $args = [$table];
        if (SQL_TYPE === 'pgsql') {
            $query = 'SELECT EXISTS ( SELECT FROM pg_tables WHERE schemaname = ? AND tablename  = ? )';
            array_unshift($args, 'public');
        }
        $stmt = self::$connection->prepare($query);
        $stmt->execute($args);

        return boolval($stmt->fetchColumn());
    }
    public static function getTables()
    {
        $query = 'SELECT table_name as tablename FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = ?';
        $vars = [SQL_DB];
        if (SQL_TYPE === 'pgsql') {
            $query = 'SELECT tablename FROM pg_tables WHERE schemaname = ?';
            $vars = ['public'];
        }
        $result = self::query($query, $vars, 'Assoc');
        return empty($result) ? false : $result;
    }
    public static function checkQuery(string $query)
    {
        if (SQL_TYPE === 'mysql') return $query;
        if (SQL_TYPE === 'pgsql') {
            return str_replace(
                [
                    "->'$.",
                    'INT NOT NULL AUTO_INCREMENT',
                    ' RAND() ',
                    ' LONGTEXT ',
                    ' JSON ',
                ],
                [
                    "->>'",
                    'INT GENERATED BY DEFAULT AS IDENTITY',
                    ' RANDOM() ',
                    ' TEXT ',
                    ' JSONB ',
                ],
                $query
            );
        }
        return $query;
    }
    public static function query($query, $params = [], $fetchMode = 'All', $columns = 0)
    {
        // error_log($query);
        // error_log(json_encode($params, JSON_UNESCAPED_UNICODE));
        $query = self::checkQuery($query);
        // error_log($query);
        $stmt = self::connect()->prepare($query);
        try {
            $stmt->execute($params);
        } catch (\Throwable $error) {
            try {
                if (!self::isTableExists()) {
                    static::init();
                }
                $stmt->execute($params);
            } catch (\Throwable $error) {
                error_log($error);
                error_log($query);
                error_log(json_encode($params, JSON_UNESCAPED_UNICODE));
                return false;
            }
        }

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
    public static function isExists($criteria, string $table = null, $criteriaType = 'OR')
    {
        if (empty($table)) $table = static::$table;

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
    public static function insert($data, string $table = null)
    {
        if (empty($table)) $table = static::$table;

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
    public static function update($data, $where, $table = null, $method = 'AND ')
    {
        if (empty($table)) $table = static::$table;

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
            $conditon .= "$k = :$k $method";
        }
        if ($conditon === ' WHERE ') {
            error_log(__METHOD__ . ': There is no conditions for SQL UPDATE');
            die();
        }

        return self::query(substr($query, 0, -1) . substr($conditon, 0, -mb_strlen($method, 'UTF-8')), $data);
    }
    // Удаляет строку по её полю ID в таблице
    public static function delete(mixed $id, string $table = null)
    {
        if (empty($id)) return false;

        if (empty($table)) $table = static::$table;

        $query = "DELETE FROM $table WHERE id = ?";
        $params = [$id];

        if (is_array($id)) {
            $count = count($id);
            $places = '';
            for ($i = 0; $i < $count; $i++) {
                $places .= '?,';
                $params[] = $id;
            }
            $places = substr($places, 0, -1);
            $query = "DELETE FROM $table WHERE id IN ($places)";
            $params = $id;
        }

        return self::query($query, $params);
    }
    //Очищает таблицу
    public static function tableTruncate(string $table = null)
    {
        if (empty($table)) $table = static::$table;

        $query = "SET FOREIGN_KEY_CHECKS = 0;
                TRUNCATE table $table;
                SET FOREIGN_KEY_CHECKS = 1;";
        if (SQL_TYPE === 'pgsql') {
            $query = "TRUNCATE ONLY $table CASCADE";
        }
        return self::query($query);
    }
    //Удаляет таблицу/таблицы
    public static function dbDropTables($tables = null)
    {
        if (empty($tables)) {
            $_tables = self::getTables();
            foreach ($_tables as $table) {
                $tables[] = $table[0];
            }
        }
        if (empty($tables)) {
            return false;
        }

        if (is_string($tables)) {
            return self::query("DROP TABLE IF EXISTS $tables");
        }
        foreach ($tables as $table) {
            self::query("DROP TABLE IF EXISTS $table CASCADE");
        }
        return true;
    }
    public static function resetIncrement($table)
    {
        $maxId = self::query("SELECT id FROM $table ORDER BY id DESC LIMIT 1", [], 'Column');
        if (empty($maxId)) return true;
        $query = "ALTER TABLE $table AUTO_INCREMENT = $maxId";
        if (SQL_TYPE === 'pgsql') {
            $query = "SELECT setval(pg_get_serial_sequence('$table', 'id'), coalesce($maxId+1, 1), false) FROM $table;";
        }
        self::query($query);
    }
    public static function init()
    {
        return true;
    }
    public static function dbInit()
    {
        $connection = new PDO(self::$type . ':host=' . self::$host . ';port=' . self::$port, self::$user, self::$password);
        $connection->query('CREATE DATABASE ' . self::$db);
        $connection = null;
        $connection = new PDO(self::$type . ':host=' . self::$host . ';port=' . self::$port . ';dbname=' . self::$db, self::$user, self::$password);
        return $connection;
    }
    public static function dbFillDefaults()
    {
        $path = 'app/models';
        $modelsDir = "{$_SERVER['DOCUMENT_ROOT']}/{$path}";
        $modelsFiles = scandir(realpath($modelsDir));

        Users::init();
        $done = [Users::$table];

        foreach ($modelsFiles as $model) {
            if ($model === '.' || $model === '..' || is_dir($model))
                continue;

            $class = str_replace('/', '\\', $path . '/' . substr($model, 0, strpos($model, '.')));
            if (!empty($class::$foreign)) {
                foreach ($class::$foreign  as $foreign) {
                    if (method_exists($foreign, 'init') && !in_array($foreign::$table, $done, true)) {
                        $foreign::init();
                        $done[] = $foreign::$table;
                    }
                }
            }
            if (method_exists($class, 'init') && !in_array($class::$table, $done, true)) {
                $class::init();
            }
        }
    }
}

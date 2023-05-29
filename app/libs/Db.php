<?php

namespace app\libs;

use app\models\Users;
use PDO;
use Throwable;

class Db
{

    protected static $db;
    protected static $table;

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

        $check = self::isTableExists();
        if (!$check) {
            self::dbFillDefaults();
        }

        return $pdo;
    }
    public static function isTableExists(): bool
    {
        $table = static::$table;
        $result = self::$db->query("SELECT EXISTS ( SELECT FROM pg_tables WHERE schemaname = 'public' AND tablename  = '$table');");
        $tableName = $result->fetchColumn();
        return boolval($tableName);
    }
    public static function query($query, $params = [], $fetchMode = 'All', $columns = 0)
    {
        error_log($query);
        error_log(json_encode($params, JSON_UNESCAPED_UNICODE));
        $stmt = self::connect()->prepare($query);
        try {
            $stmt->execute($params);
        } catch (Throwable $th) {
            $check = self::isTableExists();
            if (!$check) {
                static::init();
            }
            $stmt->execute($params);
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
        return self::query("TRUNCATE ONLY $table CASCADE");
    }

    public static function init()
    {
        return true;
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
        $path = 'app/models';
        $modelsDir = "{$_SERVER['DOCUMENT_ROOT']}/{$path}";
        $modelsFiles = scandir(realpath($modelsDir));

        Users::init();

        foreach ($modelsFiles as $model) {
            if ($model === '.' || $model === '..' || is_dir($model) || $model === 'Users.php')
                continue;

            $class = str_replace('/', '\\', $path . '/' . substr($model, 0, strpos($model, '.')));
            if (method_exists($class, 'init')) {
                $class::init();
            }
        }
    }
}

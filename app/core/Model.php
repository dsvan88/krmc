<?php

namespace  app\core;

use app\libs\Db;

class Model extends Db
{
    public static $encryptedFields = [];
    public static $jsonFields = [];
    public static $foreign = [];

    public static function getAll(array $condition = [], string $andOr = 'AND '): array
    {
        $table = static::$table;
        $where = '';
        if (!empty($condition)) {
            $where =  'WHERE ' . self::modifyWhere($condition, $andOr);
        }
        $result = self::query("SELECT * FROM $table $where ORDER BY id", $condition, 'Assoc');

        if (empty($result)) return [];

        foreach ($result as $index => $item) {
            $result[$index] = static::decodeJson($item);
        }
        return $result;
    }
    public static function getIds(array $condition = []): array
    {
        $table = static::$table;
        $where = '';
        if (!empty($condition)) {
            $where = 'WHERE ' . self::modifyWhere($condition);
        }
        $queryResult = self::query("SELECT id FROM $table $where ORDER BY id", $condition, 'Num');
        if (empty($queryResult)) return [];

        $result = [];
        $count = count($queryResult);
        for ($i = 0; $i < $count; $i++) {
            $result[] = $queryResult[$i][0];
        }

        return $result;
    }
    public static function find(int $id)
    {
        // error_log(__METHOD__ . ': ' . $id);
        $table = static::$table;
        $result = self::query("SELECT * FROM $table WHERE id = ? LIMIT 1", [$id], 'Assoc');
        if (empty($result)) return [];

        return static::decodeJson($result[0]);
    }
    public static function findBy(string $column, string $data, int $limit = 0): array
    {
        $table = static::$table;
        $result = self::query("SELECT * FROM $table WHERE $column = ?" . (empty($limit) ? '' : ' LIMIT ' . $limit), [$data], 'Assoc');
        if (empty($result)) return [];

        foreach ($result as $index => $item) {
            if (empty($result[$index])) continue;
            $result[$index] = static::decodeJson($item);
        }
        return $result;
    }
    public static function findGroup(string $column, array $data, $limit = 0): array
    {
        $table = static::$table;
        $places = implode(', ', array_fill(0, count($data), '?'));
        $query = "SELECT * FROM $table WHERE $column IN ($places)";

        if ($limit > 0) $query .= ' LIMIT ' . $limit;

        $result = self::query($query, $data, 'Assoc');

        if (empty($result)) return [];

        foreach ($result as $index => $item) {
            $result[$index] = static::decodeJson($item);
        }

        return $result;
    }
    // Находит последнюю запись в таблице
    public static function last()
    {
        $table = static::$table;
        $result = self::query("SELECT * FROM $table ORDER BY id DESC LIMIT 1", [], 'Assoc');
        if (empty($result)) return false;
        return static::decodeJson($result[0]);
    }
    public static function decodeJson(array $array)
    {
        static::decryptFields($array);

        if (empty(static::$jsonFields))
            return $array;

        foreach (static::$jsonFields as $field) {
            if (empty($array[$field])) continue;
            $array[$field] = json_decode($array[$field], true);
        }
        return $array;
    }
    public static function decryptFields(array &$array):void
    {
        if (empty(static::$encryptedFields))    return;
        
        foreach (static::$jsonFields as $field) {
            if (empty($array[$field])) continue;
            $array[$field] = Tech::decrypt($array[$field], true);
        }
    }
    public static function modifyWhere(array &$condition = [], string $andOr = 'AND ')
    {
        $where = '';
        foreach ($condition as $key => $value) {
            if (!empty($value) && is_array($value)) {
                $key2 = str_replace(["'", '->$.'], '', $key);
                $string = '';
                foreach ($value as $index => $item) {
                    $string .= ":{$key2}{$index},";
                    $condition[$key2 . $index] = $item;
                }
                $string = mb_substr($string, 0, -1, 'UTF-8');
                $where .= "$key IN ($string) $andOr";
                unset($condition[$key]);
                continue;
            }
            $key2 = str_replace(["'", '->$.'], '', $key);
            $where .= "$key = :$key2 $andOr";
            if ($key2 !== $key) {
                $condition[$key2] = $value;
                unset($condition[$key]);
            }
        }
        $where = substr($where, 0, -mb_strlen($andOr, 'UTF-8'));
        return $where;
    }
}

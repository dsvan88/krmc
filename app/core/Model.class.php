<?php

namespace  app\core;

use app\libs\Db;

class Model extends Db
{
    public static function getAll($condition = [])
    {
        $table = static::$table;
        $where = '';
        if (!empty($condition)) {
            $where = 'WHERE ';
            foreach ($condition as $key => $value) {
                $where .= "$key = :$key OR ";
            }
            $where = substr($where, 0, -4);
        }
        return self::query("SELECT * FROM $table $where", $condition, 'Assoc');
    }
    public static function find($id){
        $table = static::$table;
        $result = self::query("SELECT * FROM $table WHERE id = ? LIMIT 1", [$id], 'Assoc');
        if (empty($result)) return false;
        return $result[0];
    }
    public static function findBy($column, $data)
    {
        $table = static::$table;
        $result = self::query("SELECT * FROM $table WHERE $column = ?", [$data], 'Assoc');
        if (empty($result)) return false;
        return $result;
    }
    public static function getSimpleArray($query, $params = [])
    {
        $result = [];
        $data = self::query($query, $params, 'Num');
        for ($i = 0; $i < count($data); $i++) {
            $result[$data[$i][0]] = $data[$i][1];
        }
        return $result;
    }
    public static function getRawArray($query, $params)
    {
        $result = [];
        $data = self::query($query, $params, 'Num');
        for ($i = 0; $i < count($data); $i++) {
            $result[] = $data[$i][0];
        }
        return $result;
    }
    public static function getSimpleString($query, $params, $sep = ',')
    {
        $result = '';
        $data = self::query($query, $params, 'Num');
        for ($i = 0; $i < count($data); $i++) {
            $result .= $data[$i][0] . $sep;
        }
        return $result;
    }
}

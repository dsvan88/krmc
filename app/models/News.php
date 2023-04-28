<?php

namespace app\models;

class News extends Pages
{
    public static function getPromo()
    {
        $table = self::$table;
        $result = self::query("SELECT * FROM $table WHERE type = ? ORDER BY id DESC LIMIT 1", ['promo'], 'Assoc');
        if (empty($result)) return false;
        return $result[0];
    }
}

<?php

namespace app\models;

use app\core\Model;

class Main extends Model
{
    public static function getNews()
    {
        // return self::query('SELECT * FROM ' . SQL_TBLMAIN . ' ORDER BY id');
    }
}

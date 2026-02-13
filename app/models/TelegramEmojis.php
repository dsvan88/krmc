<?php

namespace app\models;

use app\core\Model;

class TelegramEmojis extends Model
{
    public static $table = SQL_TBL_USERS;

    public static function get(int $collectId = 0, int $limit = 35, int $offset = 0): array
    {
        $collection = require "{$_SERVER['DOCUMENT_ROOT']}/app/locale/emoji-{$collectId}.php";
        return array_slice($collection, $offset, $limit, true);
    }

}
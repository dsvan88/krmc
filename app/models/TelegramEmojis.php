<?php

namespace app\models;

use app\core\Entities\User;
use app\core\Model;
use Exception;

class TelegramEmojis extends Model
{
    public static $table = SQL_TBL_USERS;
    public static int $count = 0;
    public static int $limit = 35;

    private static function findCollection(int $collectId = 0):array
    {
        $path = "{$_SERVER['DOCUMENT_ROOT']}/app/locale/emoji-{$collectId}.php";
        
        if (!file_exists($path)) return [];

        $collection = require "{$_SERVER['DOCUMENT_ROOT']}/app/locale/emoji-{$collectId}.php";

        return empty($collection) ? [] : $collection;
    }
    public static function set(int $userId = 0, int $collectId = 0, int $key = 0): string
    {
        $collection = static::findCollection($collectId);

        if (empty($collection) || empty($collection[$key])) return '';
        
        $emoji = $collection[$key];

        $user = User::create($userId);

        if (empty($user))
            throw new Exception(__METHOD__." User $userId cant be found.");
        
        $personal = $user->personal;
        $personal['emoji'] = $emoji;

        Users::update(['personal' => json_encode($personal, JSON_UNESCAPED_UNICODE)], ['id' => $userId]);

        return $emoji;
    }
    public static function get(int $collectId = 0, int $offset = 0): array
    {
        $collection = static::findCollection($collectId);

        static::$count = count($collection);

        return empty(static::$count)
            ? []
            : array_slice($collection, $offset, static::$limit, true);
    }

}
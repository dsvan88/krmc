<?php

namespace app\models;

use app\core\Model;
use app\core\Tech;
use Exception;

class SocialPoints extends Model
{

    public static $table = SQL_TBL_USERS;
    public static $user = [];

    public static $points = [
        'longMessage' => 1,
        'booking' => 5,
        'unsureBooking' => 3,
        'dayStarter' => 2,
    ];

    public static function getUserData(int $userId = 0): void
    {
        if (isset(static::$user['id']) && $userId == static::$user['id'])
            return;

        if (empty($userId))
            throw new Exception(__METHOD__ . ': Can’t find a user with a empty id');;

        static::$user = Users::find($userId);

        if (empty(static::$user))
            throw new Exception(__METHOD__ . ': Can’t find a user with such id: ' . $userId);

        return;
    }
    public static function set(int $point = 0, int $userId = 0): int
    {
        static::getUserData($userId);

        static::$user['privilege']['points'] = $point;
        static::update(
            ['privilege' => json_encode(static::$user['privilege'])],
            ['id' => static::$user['id']]
        );

        return $point;
    }
    public static function get(int $userId = 0): int
    {
        static::getUserData($userId);

        if (empty(static::$user['privilege']['points'])) {
            return static::set(0, $userId);
        }

        return static::$user['privilege']['points'];
    }
    public static function add(int $userId = 0, int $point = 0)
    {
        static::getUserData($userId);

        if (empty(static::$user['privilege']['points'])) {
            return static::set($point, $userId);
        }

        if (static::$user['privilege']['points'] + $point < 0) {
            return static::set(0, $userId);
        }

        return static::set(static::$user['privilege']['points'] + $point, $userId);
    }
    public static function minus(int $userId = 0, int $point = 0)
    {
        static::getUserData($userId);

        if (empty(static::$user['privilege']['points'])) {
            return static::set($point, $userId);
        }

        if (static::$user['privilege']['points'] - $point < 0) {
            return static::set(0, $userId);
        }

        return static::set(static::$user['privilege']['points'] - $point, $userId);
    }
}

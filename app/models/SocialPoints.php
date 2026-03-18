<?php

namespace app\models;

use app\core\Entities\User;
use app\core\Model;
use app\core\Tech;
use Exception;

class SocialPoints extends Model
{

    public static $table = SQL_TBL_USERS;
    public static ?User $target = null;

    public static $points = [
        'longMessage' => 1,
        'booking' => 5,
        'unsureBooking' => 3,
        'dayStarter' => 2,
    ];

    public static function getUserData(int $targetId = 0): void
    {
        if (isset(static::$target->id) && $targetId == static::$target->id)
            return;

        if (empty($targetId))
            throw new Exception(__METHOD__ . ': Can’t find a user with a empty id');;

        static::$target = User::create($targetId);

        if (empty(static::$target))
            throw new Exception(__METHOD__ . ': Can’t find a user with such id: ' . $targetId);

        return;
    }
    public static function set(int $point = 0, int $targetId = 0): int
    {
        static::getUserData($targetId);

        static::$target->profile['privilege']['points'] = $point;

        static::update(
            ['privilege' => json_encode(static::$target->profile['privilege'])],
            ['id' => static::$target->id]
        );

        return $point;
    }
    public static function get(int $targetId = 0): int
    {
        static::getUserData($targetId);

        $points = static::$target->points;

        return is_null($points) ? static::set(0, $targetId) : $points;
    }
    public static function add(int $targetId = 0, int $point = 0)
    {
        static::getUserData($targetId);

        $points = static::$target->points;

        if (is_null($points)) {
            return static::set($point, $targetId);
        }
        $points += $point;
        if ($points < 0) {
            return static::set(0, $targetId);
        }

        return static::set($points, $targetId);
    }
    public static function minus(int $targetId = 0, int $point = 0)
    {
        static::getUserData($targetId);

        $points = static::$target->points;

        if (is_null($points) || $points - $point < 0) {
            return static::set(0, $targetId);
        }

        return static::set($points - $point, $targetId);
    }
}

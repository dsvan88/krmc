<?php

namespace app\models;

use app\core\Model;
use Exception;

class Coupons extends Model
{
    public static $table = SQL_TBL_COUPONS;
    public static $jsonFields = ['used_on', 'options'];
    public static $types = [
        'once',
        'han', // here and now (for sales on evenings)
    ];

    public static function create(array $data = [])
    {
        extract($data);

        if (empty($userId))
            throw new Exception(__METHOD__ . ': owner can’t be empty.');

        if (!Users::isExists(['id' => $userId]))
            throw new Exception(__METHOD__ . ': owner doesn’t exists.');

        if (empty($type)) $type = 'once';
        else if (!in_array($type, static::$types, true))
            throw new Exception(__METHOD__ . ': unknown coupon’s type.');

        $options = [
            'discount' => empty($discount) ? 20 : $discount,
            'discount_type' => empty($discount_type) ? '%' : $discount_type,
        ];

        $coupon = [
            'owner' => $userId,
            'type' => $type,
            'options' => json_encode($options),
            'expired_at' => date('Y-m-d H:i:s', empty($expired) ? TIMESTAMP_DAY * 366 : $expired),
        ];

        $hex = (hash('xxh3', json_encode($coupon) . $_SERVER['REQUEST_TIME']));
        $coupon['id'] = gmp_strval(gmp_init("0x$hex"), 10);

        static::insert($coupon);

        return gmp_strval(gmp_init($coupon['id']), 16);
    }
    public static function decodeJson(array $coupon)
    {
        $coupon['id'] = gmp_strval(gmp_init($coupon['id']), 16);
        return parent::decodeJson($coupon);
    }
    public static function init()
    {
        $table = self::$table;
        self::query(
            "CREATE TABLE IF NOT EXISTS $table (
                id BIGINT UNSIGNED NOT NULL PRIMARY KEY,
                type CHARACTER VARYING(25) NOT NULL DEFAULT 'once',
                owner INT NOT NULL DEFAULT '0',
                used_on JSON DEFAULT NULL,
                options JSON DEFAULT NULL,
                expired_at TIMESTAMP DEFAULT NULL,
                created_at TIMESTAMP DEFAULT NOW(),
                updated_at TIMESTAMP DEFAULT NOW(),
                date_delete TIMESTAMP DEFAULT NULL
            );"
        );
    }
}

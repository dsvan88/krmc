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

    public static $coupons = [
        [
            'active' => true,
            'icon' => '🎫',
            'type' => 'once',
            'price' => 150,
            'options' => [
                'discount' => 20,
                'discount_type' => '%',
            ],
        ],
        [
            'active' => true,
            'icon' => '🎫',
            'type' => 'once',
            'price' => 300,
            'options' => [
                'discount' => 50,
                'discount_type' => '%',
            ],
        ],
        [
            'active' => true,
            'icon' => '🎫',
            'type' => 'once',
            'price' => 450,
            'options' => [
                'discount' => 100,
                'discount_type' => '%',
            ],
        ],
        [
            'active' => true,
            'icon' => '🎟',
            'type' => 'once',
            'price' => 150,
            'options' => [
                'discount' => 20,
                'discount_type' => 'hrn',
            ],
        ],
        [
            'active' => true,
            'icon' => '🎟',
            'type' => 'once',
            'price' => 200,
            'options' => [
                'discount' => 30,
                'discount_type' => 'hrn',
            ],
        ],
        [
            'active' => true,
            'icon' => '🎟',
            'type' => 'once',
            'price' => 300,
            'options' => [
                'discount' => 50,
                'discount_type' => 'hrn',
            ],
        ],
        [
            'active' => true,
            'icon' => '🎟',
            'type' => 'once',
            'price' => 400,
            'options' => [
                'discount' => 80,
                'discount_type' => 'hrn',
            ],
        ],
    ];


    public static function create(int $userId = 0, int $couponId = 0)
    {

        if (empty($userId))
            throw new Exception(__METHOD__ . ': owner can’t be empty.');

        if (!Users::isExists(['id' => $userId]))
            throw new Exception(__METHOD__ . ': owner doesn’t exists.');

        if (empty(static::$coupons[$couponId]))
            throw new Exception(__METHOD__ . ': unknown coupon’s data.');

        $coupon = static::$coupons[$couponId];

        $_coupon = [
            'owner' => $userId,
            'type' => $coupon['type'],
            'options' => json_encode($coupon['options']),
            'expired_at' => date('Y-m-d H:i:s', $coupon['expired'] ?? TIMESTAMP_DAY * 366),
        ];

        $hex = (hash('xxh3', json_encode($_coupon) . $_SERVER['REQUEST_TIME']));
        $_coupon['id'] = gmp_strval(gmp_init("0x$hex"), 10);

        static::insert($_coupon);

        return gmp_strval(gmp_init($_coupon['id']), 16);
    }
    public static function findCoupon(string $id){
        return static::find(gmp_strval(gmp_init("0x$id"), 10));
    }
    public static function use(string $id, array $usedOn = [] ):void
    {
        if (empty($id) || empty($usedOn)) return;
        static::update(['used_on' => json_encode($usedOn)], ['id' => gmp_strval(gmp_init("0x$id"), 10)]);
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

<?php

namespace app\models;

use app\core\Model;
use app\core\Tech;
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

    public static function prepeareIds(array &$coupons): void
    {
        if (is_array($coupons['id'])) {
            $ids = [];
            foreach ($coupons['id'] as $id)
                $ids[] = static::decodeId($id);
        } else {
            $ids = static::decodeId($coupons['id']);
        }
        $coupons['id'] = $ids;
    }
    public static function encodeId(string $int): string
    {
        return gmp_strval(gmp_init($int), 16);
    }
    public static function decodeId(string $hex): string
    {
        return gmp_strval(gmp_init("0x$hex"), 10);
    }
    /**
     * @param $coupon - array from Coupons or id of coupon
     */
    public static function isExpired($coupon): bool
    {
        if (empty($coupon))
            throw new Exception(__METHOD__. ' $coupon can’t be empty.');

        if (is_object($coupon)) {
            if ($coupon->expired_at){
                $offset = TIMESTAMP_YEAR+TIMESTAMP_DAY;
                return $coupon->expired_at > $offset && $_SERVER['REQUEST_TIME']+TIMESTAMP_DAY > $coupon->expired_at;
            }
            throw new Exception(__METHOD__. ' $coupon is invalid.');
        }
        if (is_array($coupon)) {
            if (isset($coupon['expired_at'])){
                $offset = TIMESTAMP_YEAR+TIMESTAMP_DAY;
                return $coupon['expired_at'] > $offset && $_SERVER['REQUEST_TIME']+TIMESTAMP_DAY > $coupon['expired_at'];
            }
            throw new Exception(__METHOD__. ' $coupon is invalid.');
        }
        $coupon = static::findBy('id', $coupon, 1);

        return $_SERVER['REQUEST_TIME']+TIMESTAMP_DAY < $coupon['expired_at'];

    }
    public static function getAll(array $coupons = [], string $andOr = 'AND '): array
    {
        if (empty($coupons) || empty($copons['id'])) return parent::getAll($coupons, $andOr);

        static::prepeareIds($coupons);

        return parent::getAll($coupons, $andOr);
    }
    public static function create(int $userId = 0, int $couponId = 0)
    {
        if (empty($userId) || !Users::isExists(['id' => $userId]))
            throw new Exception(__METHOD__ . ': invalid owner.');

        if (empty(static::$coupons[$couponId]))
            throw new Exception(__METHOD__ . ': unknown coupon’s data.');

        $coupon = static::$coupons[$couponId];

        $expired = $coupon['expired'] ?? TIMESTAMP_YEAR;
        $_coupon = [
            'owner' => $userId,
            'type' => $coupon['type'],
            'options' => json_encode($coupon['options']),
            'expired_at' => date('Y-m-d', $expired) .'T'. date('H:i:s', $expired),
        ];

        $hex = (hash('xxh3', json_encode($_coupon) . $_SERVER['REQUEST_TIME']));
        $_coupon['id'] = static::decodeId($hex);

        static::insert($_coupon);

        return $hex;
    }
    public static function findBy(string $column, string $data, int $limit = 0): array
    {
        if (empty($column) || $column !== 'id') return parent::findBy($column, $data, $limit);

        $result = parent::findBy($column, static::decodeId($data), $limit)[0];
        return $result;
    }
    public static function edit(string $id, array $data = []): void
    {
        if (empty($id) || empty($data)) return;
        static::update($data, ['id' => static::decodeId($id)]);
    }
    public static function decodeJson(array $coupon)
    {
        $coupon['id'] = static::encodeId($coupon['id']);
        $coupon['expired_at'] = strtotime($coupon['expired_at']);
        $coupon['created_at'] = strtotime($coupon['created_at']);
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

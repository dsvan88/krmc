<?php

namespace app\mappers;

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

    public static $statuses = [
        'idle',
        'ready',
        'applied',
        'expired',
    ];
    public static $discount_types = [
        '%',
        '₴',
        '$',
        '€',
    ];

    public static $blueprint = [
        'active' => true,
        'name' => 'Blueprint',
        'icon' => '🎫',
        'type' => 'once',
        'price' => 200,
        'options' => [
            'discount' => 50,
            'discount_type' => '%',
        ],
    ];

    /**
     * @param $coupon - array from Coupons or id of coupon
     */
    public static function isExpired($coupon): bool
    {
        if (empty($coupon))
            throw new Exception(__METHOD__ . ' $coupon can’t be empty.');

        $offset = TIMESTAMP_YEAR + TIMESTAMP_DAY;
        $expire = $_SERVER['REQUEST_TIME'] + TIMESTAMP_DAY;
        if (is_object($coupon)) {
            if ($coupon->expired_at) {
                return $coupon->expired_at > $offset && $expire > $coupon->expired_at;
            }
            throw new Exception(__METHOD__ . ' $coupon is invalid.');
        }
        if (is_array($coupon)) {
            if (isset($coupon['expired_at'])) {
                return $coupon['expired_at'] > $offset && $expire > $coupon['expired_at'];
            }
            throw new Exception(__METHOD__ . ' $coupon is invalid.');
        }
        $coupon = static::findBy('id', $coupon, 1);

        return $expire < $coupon['expired_at'];
    }
    public static function getTypes(): array
    {
        $setting = Settings::findBy('type', 'coupons', 1);

        return empty($setting[0]['setting']) ? [] : $setting[0]['setting'];
    }
    public static function create(int $userId = 0, int $couponId = 0, string $status = 'ready')
    {
        if (empty($userId) || !Users::isExists(['id' => $userId]))
            throw new Exception(__METHOD__ . ': invalid owner.');

        $coupon = static::getTypes()[$couponId] ?? null;

        if (is_null($coupon))
            throw new Exception(__METHOD__ . ': unknown coupon’s data.');

        $expired = $coupon['expired'] ?? TIMESTAMP_YEAR;
        $_coupon = [
            'owner' => $userId,
            'type' => $coupon['type'],
            'status' => $status,
            'options' => json_encode($coupon['options']),
            'expired_at' => date('Y-m-d', $expired) . 'T' . date('H:i:s', $expired),
        ];

        $_coupon['code'] =  (hash('xxh3', json_encode($_coupon) . $_SERVER['REQUEST_TIME']));

        static::insert($_coupon);

        return $_coupon['code'];
    }
    public static function decodeJson(array $coupon)
    {
        $coupon['expired_at'] = strtotime($coupon['expired_at']);
        $coupon['created_at'] = strtotime($coupon['created_at']);
        return parent::decodeJson($coupon);
    }
    public static function init()
    {
        $table = self::$table;
        self::query(
            "CREATE TABLE IF NOT EXISTS $table (
                id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
                code CHARACTER VARYING(64) DEFAULT NULL,
                type CHARACTER VARYING(25) NOT NULL DEFAULT 'once',
                status CHARACTER VARYING(25) NOT NULL DEFAULT 'idle',
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

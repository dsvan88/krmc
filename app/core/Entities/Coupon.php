<?php

namespace  app\core\Entities;

use app\core\Tech;
use app\models\Coupons;

class Coupon extends Entity
{
    public ?User $owner = null;
    public string $type = 'once';
    public ?array $used_on = null;
    public array $options = [];
    public ?string $expired_at = null;
    public ?string $created_at = null;
    public ?string $updated_at = null;
    public static $model = Coupons::class;

    public static $defaults = [
        'owner' => null,
        'type' => 'once',
        'used_on' => null,
        'options' => [],
        'expired_at' => null,
        'created_at' => null,
        'updated_at' => null,
    ];
    public function init($id): bool
    {
        $this->id = $id;

        foreach (static::$defaults as $k => $v) {
            if (empty(static::$cache[$k]) || $k === 'owner') continue;
            $this->$k = static::$cache[$k];
        }
        $this->owner = User::create(static::$cache['owner']);

        return true;
    }
    public static function validate($id): ?string
    {
        $id = (string) $id;

        return empty($id) ? null : $id;
    }
    public static function find($id): bool
    {
        $data = static::$model::findCoupon($id);

        if (empty($data)) return false;

        static::$cache = $data;
        return true;
    }
    public function __get($name)
    {
        return $this->$name ?? null;
    }
}

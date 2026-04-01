<?php

namespace  app\core\Entities;

use app\core\Tech;
use app\mappers\Coupons;
use Exception;

class Coupon extends Entity
{
    public ?User $owner = null;
    public string $type = 'once';
    public ?array $used_on = null;
    public array $options = [];
    public string $status = 'ready';
    public ?string $expired_at = null;
    public ?string $created_at = null;
    public ?string $updated_at = null;

    public static $model = Coupons::class;

    public static $defaults = [
        'owner' => null,
        'type' => 'once',
        'status' => 'ready',
        'used_on' => null,
        'options' => [],
        'expired_at' => null,
        'created_at' => null,
    ];
    public function init($id): bool
    {
        $this->id = $id;

        foreach (static::$defaults as $k => $v) {
            if (empty(static::$cache[$k]) || $k === 'owner') continue;
            $this->$k = static::$cache[$k];
        }
        if ($this->isExpired())
            $this->status = 'expired';

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
        $data = static::$model::findBy('id', $id, 1);

        if (empty($data)) return false;

        static::$cache = $data;
        return true;
    }
    public static function fromArray(array $data = []): ?Coupon
    {
        if (empty($data)) return null;
        static::$cache = $data;
        return new static($data['id']);
    }
    public function __get($name)
    {
        return $this->$name ?? null;
    }
    public function isExpired(): bool
    {
        return Coupons::isExpired(['expired_at' => $this->expired_at]);
    }
    public function recall(?Day $day = null): ?Coupon
    {
        if (empty($day))
            throw new Exception(__METHOD__ . ' $day can’t be empty.');
        
        $this->used_on = null;
        $i = array_search($this->id, $day->coupons, true);
        
        if (empty($i)) return $this;

        unset($day->coupons[$i]);
        $day->coupons = array_values($day->coupons);

        return $this;
    }
    public function apply(?Day $day = null): ?Coupon
    {
        if (empty($day))
            throw new Exception(__METHOD__ . ' $day can’t be empty.');

        $this->status = 'applied';
        $this->used_on = ['dayId' => $day->dayId, 'weekId' => $day->weekId];
        $day->coupons[] = $this->id;
        return $this;
    }
    public function expire(?Day $day = null): ?Coupon
    {
        $expired = date('Y-m-d', $day->timestamp ?? $_SERVER['REQUEST_TIME']) . 'T' . $day->time ?? date('H:i:s',$_SERVER['REQUEST_TIME']);
        $this->expired_at = strtotime($expired);
        return $this;
    }
    public function save()
    {
        $coupon = [];
        $dates = ['expired_at', 'created_at'];
        foreach (static::$defaults as $k => $v) {
            if (in_array($k, Coupons::$jsonFields, true)){
                $coupon[$k] = isset($this->$k) ? json_encode($this->$k) : $v;
                continue;
            }
            if ($k === 'owner'){
                $coupon['owner'] = $this->owner->id;
                continue;
            }
            if (in_array($k, $dates, true)){
                $coupon[$k] = date('Y-m-d', $this->$k) . 'T' . date('H:i:s',$this->$k);
                continue;
            }
            $coupon[$k] = $this->$k ?? $v;
        }
        return Coupons::edit($this->id, $coupon);
    }
}

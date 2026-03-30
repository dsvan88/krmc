<?php

namespace app\Services;

use app\core\Entities\Coupon;
use app\core\Entities\Day;
use app\core\Tech;
use app\models\Coupons;

class CouponService
{
    public static function burn(?Day $day = null): void
    {
        if (empty($day) || empty($day->coupons)) return;
        $coupons = [];
        foreach ($day->coupons as $c) {
            $coupons[] = Coupon::create();
        }

        $method = 'recall';
        if ($day->status === 'finished')
            $method = 'expire';

        foreach ($coupons as $coupon) {
            $coupon->$method($day);
        }
        $coupon->save();
    }
    public static function apply(?Day $day = null, int $userId = 0): void
    {
        if (empty($day) || empty($userId)) return;

        $coupons = Coupons::findBy('owner', $userId);

        if (empty($coupons)) return;

        $coupon = null;
        foreach ($coupons as $c) {
            if (empty($c['used_on'])) {
                if (Coupons::isExpired($c)) continue;

                if (empty($coupon)) {
                    $coupon = Coupon::create($c['id']);
                }
                continue;
            }
            if ($c['used_on']['weekId'] == $day->weekId && $c['used_on']['dayId'] == $day->dayId)
                return;
        }

        if (empty($coupon)) return;

        $day->coupons[] = $coupon->id;
        $coupon->use($day)->save();
    }
    public static function getDayCoupons(?Day $day = null): void
    {
        if (empty($day) || empty($day->coupons)) return;

        if (empty($ids)) return;

        $coupons = Coupons::getAll(['id' => $day->coupons]);
        $_coupons = [];
        foreach ($coupons as $c) {
            $_coupons[$c['owner']] = Coupon::fromArray($c);
        }
        $day->coupons = $_coupons;
    }
    public static function getAllCoupons(): array
    {
        $coupons = Coupons::getAll();
        if (empty($coupons)) return [];

        $result = [];
        foreach ($coupons as $c) {
            $result[] = Coupon::fromArray($c);
        }
        return $result;
    }
}

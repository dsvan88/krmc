<?php

namespace app\Services;

use app\core\Entities\Coupon;
use app\core\Entities\Day;
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

        $ids = [];
        foreach ($day->coupons as $c) {
            $ids[] = gmp_strval(gmp_init("0x$c"), 10);
        }

        if (empty($ids)) return;

        $coupons = Coupons::getAll(['id' => $ids]);
        $_coupons = [];
        foreach ($coupons as $c) {
            $_coupons[$c['owner']] = $c;
        }
        $day->coupons = $_coupons;
    }
}

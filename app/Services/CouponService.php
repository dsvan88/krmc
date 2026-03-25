<?php

namespace app\Services;

use app\core\Entities\Day;
use app\core\Tech;
use app\models\Coupons;

class CouponService
{
    public static function burn(?Day $day = null): void
    {
        if (empty($day) || empty($day->coupons)) return;
        $ids = [];
        foreach ($day->coupons as $c) {
            $ids[] = gmp_strval(gmp_init("0x$c"), 10);
        }
        foreach ($ids as $cId) {
            if ($day->status === 'finished')
                Coupons::setExpired($cId);
            else Coupons::recall($cId);
        }
    }
    public static function apply(?Day $day = null, int $userId = 0): void
    {
        if (empty($day)) return;

        $coupons = Coupons::findBy('owner', $userId);

        if (empty($coupons)) return;

        $coupon = '';
        foreach ($coupons as $c) {
            if (empty($c['used_on'])) {
                if (empty($coupon)) {
                    $coupon = $c['id'];
                }
                continue;
            }
            if ($c['used_on']['weekId'] == $day->weekId && $c['used_on']['dayId'] == $day->dayId)
                return;
        }

        if (empty($coupon)) return;

        $day->coupons[] = $coupon;
        $useOn = ['weekId' => $day->weekId, 'dayId' => $day->dayId];
        Coupons::use($coupon, $useOn);
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

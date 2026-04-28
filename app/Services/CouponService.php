<?php

namespace app\Services;

use app\core\Entities\Coupon;
use app\core\Entities\Day;
use app\core\Tech;
use app\mappers\Coupons;

class CouponService
{
    public static function burn(?Day $day = null): void
    {
        if (empty($day) || empty($day->coupons)) return;
        $coupons = [];
        foreach ($day->coupons as $c) {
            $coupons[] = Coupon::create($c);
        }

        $method = 'recall';
        if ($day->status === 'finished')
            $method = 'expire';

        $_SESSION['report'][] = "<b><u>$method</u></b> coupons for day {$day->dayId} of week {$day->weekId}.";

        foreach ($coupons as $coupon) {
            $coupon->$method($day);
            $_SESSION['report'][] = "Coupon {$coupon->id} for user {$coupon->owner->name} (id: {$coupon->owner->id}).";
            $coupon->save();
        }
    }
    public static function apply(?Day $day = null, int $userId = 0): void
    {
        if (empty($day) || empty($userId)) return;

        $coupons = Coupons::findBy('owner', $userId);
        if (empty($coupons)) return;

        usort($coupons, fn($a, $b) => strtotime($a['created_at']) > strtotime($b['created_at']) ? +1 : -1);

        $coupon = null;
        foreach ($coupons as $c) {
            if ($c['status'] === 'ready') {
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
        $coupon->apply($day)->save();
    }
    public static function getDayCoupons(?Day $day = null): void
    {
        if (empty($day) || empty($day->coupons)) return;

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
        usort($result, fn($a, $b) => $a->created_at > $b->created_at ? +1 : -1);
        return $result;
    }
}

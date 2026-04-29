<?php

namespace app\Services;

use app\core\Entities\Coupon;
use app\core\Entities\Day;
use app\core\Locale;
use app\core\Tech;
use app\core\Validator;
use app\mappers\Coupons;
use app\mappers\Settings;

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
    public static function getTypes(): array
    {
        $setting = Settings::findBy('type', 'coupons', 1);

        return empty($setting[0]['setting']) ? [] : $setting[0]['setting'];
    }
    public static function newType(array $post = []): array
    {
        if (empty($post))
            throw new \Exception(__METHOD__ . ': $post can’t be empty.');

        $coupon = Coupons::$blueprint;

        $cyrillics = Locale::$cyrillicPattern;
        $coupon['name'] = preg_replace(['/\s+/', "/[^a-z{$cyrillics}0-9 _!#@$%&*():+-]/ui"], [' ', ''], trim($post['name']));
        $coupon['price'] = Validator::validate('int', $post['price']) ?? $coupon['price'];
        $coupon['options']['discount'] = Validator::validate('int', $post['discount']) ?? $coupon['options']['discount'];
        $coupon['options']['discount_type'] = Validator::validate('discountType', $post['discount_type']) ?? $coupon['options']['discount_type'];

        if ($coupon['options']['discount_type'] !== '%')
            $coupon['icon'] = '🎟';

        $setting = Settings::findBy('type', 'coupons', 1);
        $coupons = empty($setting[0]['setting']) ? [] : $setting[0]['setting'];
        $coupons[] = $coupon;

        Settings::update(['setting' => json_encode($coupons, JSON_UNESCAPED_UNICODE)], ['type' => 'coupons']);

        return empty($coupons) ? [] : $coupons;
    }
}

<?php

namespace app\Controllers;

use app\core\Controller;
use app\core\Entities\Coupon;
use app\core\Validator;
use app\core\View;
use app\mappers\Coupons;
use app\Services\CouponService;

class CouponsController extends Controller
{
    public static function indexAction()
    {
        $vars = [
            'title' => 'Coupons List',
            'coupons' => CouponService::getAllCoupons(),
            'scripts' =>[
                'coupons.js',
            ],
            'styles' =>[
                'coupons',
            ]
        ];
        View::$route['vars'] = array_merge(View::$route['vars'], $vars);
        return View::render();
    }
    public static function setStatusAction()
    {
        $status = Validator::validate('couponStatus', $_POST['status'] ?? '');
        $couponId = Validator::validate('couponId', $_POST['couponId'] ?? '');

        if (empty($status) || empty($couponId))
            return View::notice(['message' => 'Something went wrong.', 'type' => 'error']);

        $coupon = Coupon::create($couponId);
        $coupon->status = $status;

        if ($status === 'ready' && $coupon->isExpired())
            $coupon->expired_at = $_SERVER['REQUEST_TIME'] + TIMESTAMP_WEEK;

        if ($status === 'expired')
            $coupon->expired_at = $_SERVER['REQUEST_TIME'];

        $coupon->save();

        return View::notice(['message' => 'Success', 'time' => 1500, 'location' => 'reload']);
    }
    public static function deleteAction()
    {
        if (!Validator::validate('rootpass', $_POST['verification']))
            return View::notice(['message' => 'Root password is not correct.', 'type' => 'error']);

        $couponId = Validator::validate('couponId', $_POST['couponId'] ?? '');
        if (empty($couponId))
            return View::notice(['message' => 'Something went wrong.', 'type' => 'error']);

        Coupons::delete($couponId);

        return View::notice(['message' => 'Success', 'time' => 1500, 'location' => 'reload']);
    }
}

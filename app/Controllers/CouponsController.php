<?php

namespace app\Controllers;

use app\core\Controller;
use app\core\Entities\Coupon;
use app\core\Entities\User;
use app\core\Validator;
use app\core\View;
use app\mappers\Coupons;
use app\mappers\Users;
use app\Services\CouponService;

class CouponsController extends Controller
{
    public static function indexAction()
    {

        $vars = [
            'title' => 'Coupons List',
            'subtitle' => 'List of all coupons',
            'coupons' => CouponService::getAllCoupons(),
            'scripts' => [
                'coupons.js',
            ],
            'tab' => $_GET['tab'] ?? 'index',
            'styles' => [
                'coupons',
            ]
        ];

        if ($_GET['tab'] === 'types') {
            $vars['title'] =  'Coupon’s types';
            $vars['subtitle'] =  'List of all coupon’s types';
            $vars['coupons'] =  Coupons::getTypes();
        }

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

        if ($status === 'ready') {
            $coupon->used_on = Coupon::$defaults['used_on'];
            if ($coupon->isExpired()) {
                $coupon->expired_at = $_SERVER['REQUEST_TIME'] + TIMESTAMP_WEEK;
            }
        }

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
    public static function deleteTypeAction()
    {
        if (!Validator::validate('rootpass', $_POST['verification']))
            return View::notice(['message' => 'Root password is not correct.', 'type' => 'error']);

        $num = Validator::validate('int', self::$route['vars']['typeNum'] ?? null);
        if (is_null($num))
            return View::notice(['message' => 'Something went wrong.', 'type' => 'error']);

        CouponService::deleteType($num);

        return View::notice(['message' => 'Success', 'time' => 1500, 'location' => 'reload']);
    }
    public static function addAction()
    {
        if (!Validator::csrfCheck()) {
            return View::notice(['error' => 403, 'message' => 'Try again later:)', 'time' => 2000]);
        }

        $num = Validator::validate('int', $_POST['type'] ?? null);
        if (is_null($num)) {
            return View::notice(['message' => 'Something went wrong.', 'type' => 'error']);
        }
        $name = Validator::validate('name', $_POST['name'] ?? '');
        $userData = Users::getDataByName($name);

        if (empty($userData))
            return View::notice(['message' => 'User not found.', 'type' => 'error']);

        if (is_null($num)) {
            return View::notice(['message' => 'Something went wrong.', 'type' => 'error']);
        }

        try {
            Coupons::create($userData['id'], $num);
        } catch (\Throwable $th) {
            return View::notice(['message' => $th->getMessage(), 'type' => 'error']);
        }
        return View::notice(['message' => 'Success', 'time' => 1500, 'location' => 'reload']);
    }
    public static function addTypeFormAction()
    {
        $vars = [
            'title' => 'Add a coupon’s type',
            'subtitle' => 'Set coupon’s type details',
            'texts' => [
                'SubmitLabel' => 'Add',
            ],
        ];
        View::$route['vars'] = array_merge(View::$route['vars'], $vars);
        return View::modal();
    }
    public static function addTypeAction()
    {
        try {
            CouponService::newType($_POST);
        } catch (\Throwable $th) {
            return View::notice(['message' => $th->getMessage(), 'type' => 'error']);
        }
        return View::notice(['message' => 'Success', 'time' => 1500, 'location' => 'reload']);
    }
    public static function addFormAction()
    {
        $vars = [
            'title' => 'Add a coupon',
            'subtitle' => 'Set coupon details',
            'texts' => [
                'SubmitLabel' => 'Add',
            ],
            'types' => Coupons::getTypes(),
            // 'coupons' => CouponService::getAllCoupons(),
            // 'scripts' => [
            //     'coupons.js',
            // ],
            // 'tab' => $_POST['type'] ?? 'index',
            //     'styles' => [
            //         'coupons',
            //     ]
        ];
        View::$route['vars'] = array_merge(View::$route['vars'], $vars);
        return View::modal();
    }
}

<?php

namespace app\Controllers;

use app\core\Controller;
use app\core\View;
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
}

<?php

namespace app\Controllers;

use app\core\Controller;
use app\core\View;
use app\models\Weeks;
use app\Services\WeekService;

class WeeksController extends Controller
{
    public function showAction()
    {
        $weekId = 0;
        extract(self::$route['vars']);

        $vars = WeekService::getShowData($weekId);

        $vars['texts'] = [
            'weeksBlockTitle' => 'Weekly schedule',
        ];
        $vars['title'] = 'Weekly schedule';

        View::$route['vars'] = array_merge(View::$route['vars'], $vars);
        View::$route['vars']['og'] = WeekService::formWeekOG($vars);
        View::$route['vars']['styles'] = ['weeks'];

        return View::render();
    }
    public function addAction()
    {
        return View::redirect('/weeks/' . Weeks::create());
    }
}

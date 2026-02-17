<?php

namespace app\Controllers;

use app\core\Controller;
use app\core\Locale;
use app\core\View;
use app\models\Weeks;
use app\Repositories\WeekRepository;

class WeeksController extends Controller
{
    public function showAction()
    {
        $weekId = 0;
        extract(self::$route['vars']);

        $vars = WeekRepository::getShowData($weekId);

        $vars['texts'] = [
            'weeksBlockTitle' => 'Weekly schedule',
            'days' => $vars['dayNames'],
        ];
        $vars['title'] = 'Weekly schedule';

        View::$route['vars'] = array_merge(View::$route['vars'], $vars);
        View::$route['vars']['og'] = WeekRepository::formWeekOG($vars);
        View::$route['vars']['styles'] = ['weeks'];

        return View::render();
    }
    public function addAction()
    {
        return View::redirect('/weeks/' . Weeks::create());
    }
}

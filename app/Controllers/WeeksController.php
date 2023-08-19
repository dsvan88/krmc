<?php

namespace app\Controllers;

use app\core\Controller;
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

        $texts = [
            'weeksBlockTitle' => 'Weekly schedule',
            'days' => $vars['dayNames'],
        ];

        $title = 'Weekly schedule';
        View::render(
            array_merge(
                $vars,
                compact(
                    'title',
                    'texts',
                )
            )
        );
    }
    public function addAction()
    {
        View::redirect('/weeks/' . Weeks::create());
    }
}

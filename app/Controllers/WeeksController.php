<?php

namespace app\Controllers;

use app\core\Controller;
use app\core\Locale;
use app\core\Paginator;
use app\core\View;
use app\models\Days;
use app\models\GameTypes;
use app\models\Users;
use app\models\Weeks;
use app\Repositories\WeekRepository;

class WeeksController extends Controller
{
    public function showAction()
    {
        extract(self::$route['vars']);

        if (empty($weekId))  $weekId = 0;
        
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

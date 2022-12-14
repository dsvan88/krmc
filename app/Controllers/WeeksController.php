<?php

namespace app\Controllers;

use app\core\Controller;
use app\core\Paginator;
use app\core\View;
use app\models\Days;
use app\models\Weeks;

class WeeksController extends Controller
{
    public function showAction()
    {
        $weekId = 0;
        if (isset(self::$route['vars']))
            extract(self::$route['vars']);

        [$weekCurrentId, $weeksIds, $weekCurrentIndexInList, $weekData] = Weeks::autoloadWeekData($weekId);

        $defaultDayData = Days::$dayDataDefault;

        $weeksCount = count($weeksIds);

        if ($weekId === 0) {
            $weekId = $weekCurrentId;
        }

        if ($weekId > 0) {
            $selectedWeekIndex = array_search($weekId, $weeksIds);
        } else {
            $selectedWeekIndex = $weekCurrentIndexInList;
        }

        $dayCurrentId = getdate()['wday'] - 1;

        if ($dayCurrentId === -1) {
            $dayCurrentId = 6;
        }
        $dayId = $dayCurrentId;

        if (isset($weekData['start'])) {
            $monday = $weekData['start'];
        } else {
            $monday = strtotime('last monday', strtotime('next sunday'));
        }

        $prevWeek = $nextWeek = false;

        if (isset($weeksIds[$selectedWeekIndex - 1]))
            $prevWeek = Weeks::weekDataById($weeksIds[$selectedWeekIndex - 1]);
        if (isset($weeksIds[$selectedWeekIndex + 1]))
            $nextWeek = Weeks::weekDataById($weeksIds[$selectedWeekIndex + 1]);

        $texts = [
            'weeksBlockTitle' => '{{ Weeks_Block_Title }}',
            'games' => [
                'mafia' => 'Mafia',
                'poker' => 'Poker',
                'board' => 'Board',
                'cash' => 'Cash',
                'etc' => 'Etc',
            ],
            'days' => [
                '{{ Monday }}',
                '{{ Tuesday }}',
                '{{ Wednesday }}',
                '{{ Thursday }}',
                '{{ Friday }}',
                '{{ Saturday }}',
                '{{ Sunday }}'
            ],
        ];
        $paginator = Paginator::weekly(['weeksIds' => $weeksIds, 'currentIndex' => $weekCurrentIndexInList, 'selectedIndex' => $selectedWeekIndex]);

        $title = '{{ Weeks_Show_Page_Title }}';
        View::render(compact(
            'title',
            'texts',
            'weekId',
            'weeksCount',
            'selectedWeekIndex',
            'weekCurrentId',
            'weeksIds',
            'weekCurrentIndexInList',
            'weekData',
            'monday',
            'dayId',
            'dayCurrentId',
            'defaultDayData',
            'prevWeek',
            'nextWeek',
            'paginator'
        ));
    }
    public function addAction()
    {
        $vars = [
            'title' => '{{ Week_Set_Page_Title }}',
            'texts' => [
                'weeksBlockTitle' => '{{ Week_Set_Block_Title }}'
            ]
        ];
        View::render($vars);
    }
}

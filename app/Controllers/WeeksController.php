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

        $dayNames = Locale::apply([
            '{{ Monday }}',
            '{{ Tuesday }}',
            '{{ Wednesday }}',
            '{{ Thursday }}',
            '{{ Friday }}',
            '{{ Saturday }}',
            '{{ Sunday }}'
        ]);

        $texts = [
            'weeksBlockTitle' => '{{ Weeks_Block_Title }}',
            'days' => $dayNames,
        ];

        $games = GameTypes::names();
        $days = [];

        for ($i = 0; $i < 7; $i++){
            if (!isset($weekData['data'][$i])) {
                $weekData['data'][$i] = $defaultDayData;
            } else {
                foreach ($defaultDayData as $key => $value) {
                    if (!isset($weekData['data'][$i][$key])) {
                        $weekData['data'][$i][$key] = $value;
                    }
                }
            }

            $days[$i] = $weekData['data'][$i];
            $days[$i]['timestamp'] = $monday + TIMESTAMP_DAY * $i;
            $days[$i]['date'] = date('d.m.Y', $days[$i]['timestamp']) . ' (<strong>' . $dayNames[$i] . '</strong>) ' . $days[$i]['time'];

            $days[$i]['game'] = $games[$days[$i]['game']];

            $days[$i]['class'] = 'day-future';
            if ($selectedWeekIndex < $weekCurrentIndexInList) {
                $days[$i]['class'] = 'day-expire';
            } elseif ($selectedWeekIndex === $weekCurrentIndexInList) {
                if ($dayCurrentId > $i) {
                    $days[$i]['class'] = 'day-expire';
                } elseif ($dayCurrentId === $i) {
                    $days[$i]['class'] = 'day-current';
                }
            }

            $days[$i]['participants'] = Users::addNames($days[$i]['participants']);
            $days[$i]['playersCount'] = min(count($days[$i]['participants']), 10);
            for($x=0; $x < $days[$i]['playersCount']; $x++){
                if (!empty($days[$i]['participants'][$x]) && empty($days[$i]['participants'][$x]['id'])) {
                    $days[$i]['participants'][$x]['name'] = '+1';
                }
            }
        }

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
            'weekData',
            'weekCurrentIndexInList',
            'monday',
            'days',
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

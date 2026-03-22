<?php

namespace app\Repositories;

use app\core\Entities\Week;
use app\core\Locale;
use app\core\Paginator;
use app\core\Tech;
use app\models\Days;
use app\models\GameTypes;
use app\models\Users;
use app\models\Weeks;

class WeekRepository
{
    public static function getShowData(int $weekId = 0): array
    {
        $weekCurrentId = Weeks::currentId();

        if (empty($weekId))
            $weekId = $weekCurrentId;

        $week = Week::create($weekId);

        $weeksIds = Weeks::getIds();

        $weeksCount = count($weeksIds);
        $weekCurrentIndexInList = array_search($weekCurrentId, $weeksIds);

        $prevWeek = $nextWeek = false;

        $selectedWeekIndex = array_search($weekId, $weeksIds);

        if (isset($weeksIds[$selectedWeekIndex - 1]))
            $prevWeek = Weeks::find($weeksIds[$selectedWeekIndex - 1]);
        if (isset($weeksIds[$selectedWeekIndex + 1]))
            $nextWeek = Weeks::find($weeksIds[$selectedWeekIndex + 1]);

        for ($i = 0; $i < 7; $i++) {
            if (empty($week->days[$i]->participantsCount)) continue;

            $week->days[$i]->participantsCount = min($week->days[$i]->participantsCount, 10);
            for ($x = 0; $x < $week->days[$i]->participantsCount; $x++) {
                if (empty($week->days[$i]->participants[$x]['id']) || $week->days[$i]->participants[$x]['name'][0] === '_')
                   $week->days[$i]->participants[$x]['name'] = '+1';
            }
        }
        $description = static::scheludeDescription($week->days);

        $paginator = Paginator::weekly(['weeksIds' => $weeksIds, 'currentIndex' => $weekCurrentIndexInList, 'selectedIndex' => $selectedWeekIndex]);

        $isManager = Users::checkAccess('manager');

        return compact(
            'weeksIds',
            'weeksCount',
            'weekCurrentIndexInList',
            'week',
            'prevWeek',
            'nextWeek',
            'selectedWeekIndex',
            'isManager',
            'paginator',
            'description',
        );
    }
    public static function scheludeDescription(array $days): string
    {
        if (empty($days)) return false;
        $result = Locale::phrase("Our schelude") . ':' . PHP_EOL;
        foreach ($days as $day) {
            $result .= $day->date . ' - ' .  $day->gameName . ';' . PHP_EOL;
        }
        $result .= Locale::phrase("Welcome to our club") . '!';
        return preg_replace('/<.*?>/', '', $result);
    }
    public static function formWeekOG(array $data = [])
    {
        $url = Tech::getRequestProtocol() . "://{$_SERVER['SERVER_NAME']}";
        $logo = empty($data['logo']) ? '/public/images/club-logo-w-city.jpg' : $data['logo'];
        $imageSize = getimagesize($_SERVER['DOCUMENT_ROOT'] . $logo);
        $image = "$url/$logo";
        $data['title'] = Locale::phrase($data['title']);
        $result = [
            'title' => $data['title'],
            'type' => 'article',
            'url' => "$url/weeks/".$data['week']->id,
            'image' => $image,
            'image:width' => $imageSize[0],
            'image:height' => $imageSize[1],
            'description' => $data['description'],
            'site_name' => $data['title'] . ' | ' . CLUB_NAME,
            'twitter' => [
                'card' => 'summary_large_image',
                'image' => $image,
            ],
        ];
        return $result;
    }
}

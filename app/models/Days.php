<?php

namespace app\models;

use app\core\Model;
use app\core\Locale;

class Days extends Model
{
    public static $currentDay = [];

    public static $days = [
        '{{ Monday }}',
        '{{ Tuesday }}',
        '{{ Wednesday }}',
        '{{ Thursday }}',
        '{{ Friday }}',
        '{{ Saturday }}',
        '{{ Sunday }}',
    ];
    public static $dayDataDefault = [
        'game' => 'mafia',
        'mods' => [],
        'time' => '14:00',
        'status' => '',
        'participants' => [],
        'day_prim' => ''
    ];
    public static function setDayData($weekId, $dayId, $data)
    {
        try {
            if ($weekId == 0) {
                if (Weeks::currentId()) {
                    $weekId = Weeks::$currentWeekId;
                }
            }
            if ($weekId > 0) {
                $weekData = Weeks::weekDataById($weekId);
            } else {
                $weekData = Weeks::weekDataDefault();
                $weekData['start'] = strtotime('last Monday');
                $weekData['finish'] = strtotime('next Sunday');
            }

            unset($weekData['id']);

            $weekData['data'][$dayId] = $data;
            $weekData['data'] = json_encode($weekData['data'], JSON_UNESCAPED_UNICODE);

            if ($weekId > 0) {
                self::update($weekData, ['id' => $weekId], SQL_TBL_WEEKS);
                return $weekId;
            } else {
                return self::insert($weekData, SQL_TBL_WEEKS);
            }
        } catch (\Throwable $th) {
            error_log(__METHOD__ . $th->__toString());
            return false;
        }
    }
    public static function weekDayData($weekId, $dayId)
    {
        $weekData = Weeks::weekDataById($weekId);

        if ($weekData) {
            $weekData['data'][$dayId]['weekStart'] = $weekData['start'];
            return $weekData['data'][$dayId];
        }
        return self::$dayDataDefault;
    }
    public static function getFullDescription($weekData, $day)
    {
        $format = "d.m.Y {$weekData['data'][$day]['time']}";
        $dayDate = strtotime(date($format, $weekData['start'] + TIMESTAMP_DAY * $day));

        if ($_SERVER['REQUEST_TIME'] > $dayDate + DATE_MARGE || in_array($weekData['data'][$day]['status'], ['', 'recalled'])) {
            return '';
        }

        $date = str_replace(
            ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'],
            ['<b>Понеділок</b>', '<b>Вівторок</b>', '<b>Середа</b>', '<b>Четвер</b>', '<b>П’ятница</b>', '<b>Субота</b>', '<b>Неділя</b>'],
            date('d.m.Y (l) H:i', $dayDate)
        );

        $gameNames = [
            'mafia' => '{{ Tg_Mafia }}',
            'poker' => '{{ Tg_Poker }}',
            'board' => '{{ Tg_Board }}',
            'cash' => '{{ Tg_Cash }}',
            'etc' => '{{ Tg_Etc }}',
        ];
        $gameNames = Locale::apply($gameNames);

        $result = '';

        $result .= "$date - {$gameNames[$weekData['data'][$day]['game']]}\n";

        if (isset($weekData['data'][$day]['mods'])) {
            if (in_array('fans', $weekData['data'][$day]['mods'], true))
                $result .= Locale::applySingle('{{ Tg_Game_Mod_Fan }}');
            if (in_array('tournament', $weekData['data'][$day]['mods'], true))
                $result .= Locale::applySingle('{{ Tg_Game_Mod_Tournament }}');
        }
        if (isset($weekData['data'][$day]['day_prim']) && $weekData['data'][$day]['day_prim'] !== '')
            $result .= "<u>{$weekData['data'][$day]['day_prim']}</u>\n";

        $result .= "\n";

        $participants = [];
        $participantsToEnd = [];
        $noNames = [];

        for ($x = 0; $x < count($weekData['data'][$day]['participants']); $x++) {
            if (!isset($weekData['data'][$day]['participants'][$x]['name']) || $weekData['data'][$day]['participants'][$x]['name'] === '')
                continue;
            if (strpos($weekData['data'][$day]['participants'][$x]['name'], 'tmp_user') !== false) {
                $noNames[] = $weekData['data'][$day]['participants'][$x];
                continue;
            }
            if ($weekData['data'][$day]['participants'][$x]['prim'] != '' || $weekData['data'][$day]['participants'][$x]['arrive'] != '') {
                $participantsToEnd[] = $weekData['data'][$day]['participants'][$x];
                continue;
            }
            $participants[] = $weekData['data'][$day]['participants'][$x];
        }
        $participants = array_merge($participants, $participantsToEnd, $noNames);

        for ($x = 0; $x < count($participants); $x++) {
            $modsData = '';
            $userName = '';
            if (!isset($participants[$x]['name']))
                continue;
            if (strpos($participants[$x]['name'], 'tmp_user') === false) {
                $userName = $participants[$x]['name'];
            } else {
                $userName = '+1';
            }
            if ($participants[$x]['arrive'] !== '' && $participants[$x]['arrive'] !== $weekData['data'][$day]['time']) {
                $modsData .= $participants[$x]['arrive'];
                if ($participants[$x]['prim'] != '') {
                    $modsData .= ', ';
                }
            }
            if ($participants[$x]['prim'] != '') {
                $modsData .= $participants[$x]['prim'];
            }
            if ($modsData !== '')
                $modsData = " (<i>$modsData</i>)";
            $result .= ($x + 1) . ". <b>$userName</b>{$modsData}\r\n";
        }
        return $result;
    }
    public static function addParticipantToDayData($dayData, $slot, &$data)
    {
        if ($slot === -1) {
            while (isset($dayData['participants'][++$slot])) {
            }
        }

        $dayData['participants'][$slot] = [
            'id'        =>    $data['userId'],
            'name'      =>    $data['userName'],
            'arrive'    =>    $data['arrive'],
            'prim'    =>    '',
        ];

        if ($data['prim'] != '') {
            $dayData['participants'][$slot]['prim'] = $data['prim'];
        }

        return $dayData;
    }
    public static function addNonamesToDayData($dayData, $slot, $count, $prim = '')
    {
        if ($slot === -1) {
            while (isset($dayData['participants'][++$slot])) {
            }
        }

        for ($x = 0; $x < $count; $x++) {
            $dayData['participants'][$slot + $x] = [
                'id'        =>    -1,
                'name'      =>    'tmp_user_' . ($slot + $x),
                'arrive'    =>    '',
                'prim'    =>     $prim,
            ];
        }
        return $dayData;
    }
    public static function removeNonamesFromDayData($dayData, $count)
    {
        for ($x = 0; $x < count($dayData['participants']); $x++) {
            if (strpos($dayData['participants'][$x]['name'], 'tmp_user') === false)
                continue;
            unset($dayData['participants'][$x]);
            --$count;
            if ($count === 0)
                break;
        }
        $dayData['participants'] = array_values($dayData['participants']);

        return $dayData;
    }
    public static function recall($weekId, $dayNum)
    {
        $weekData = Weeks::weekDataById($weekId);
        if (!isset($weekData['data'][$dayNum]) || $weekData['data'][$dayNum]['status'] === 'recalled') {
            return false;
        }
        $weekData['data'][$dayNum]['status'] = 'recalled';
        return self::setDayData($weekId, $dayNum, $weekData['data'][$dayNum]);
    }
}
<?php

namespace app\models;

use app\core\Model;
use app\core\Locale;

class Days extends Model
{
    public static $currentDay;

    public static $days = [
        'Monday',
        'Tuesday',
        'Wednesday',
        'Thursday',
        'Friday',
        'Saturday',
        'Sunday',
    ];
    public static $dayDataDefault = [
        'game' => 'mafia',
        'mods' => [],
        'time' => '14:00',
        'status' => '',
        'participants' => [],
        'day_prim' => ''
    ];
    public static function current()
    {
        if (!empty(self::$currentDay)) {
            return self::$currentDay;
        }

        self::$currentDay = getdate()['wday'] - 1;

        if (self::$currentDay === -1)
            self::$currentDay = 6;

        return self::$currentDay;
    }
    public static function isExpired(int $timestamp): bool{
        return $timestamp + TIMESTAMP_DAY < $_SERVER['REQUEST_TIME'];
    }
    public static function edit($weekId, $dayId, $data)
    {
        $newData = [
            'time' => trim($data['day_time']),
            'game' => trim($data['game']),
            'day_prim' => str_replace('  ', "\n", trim($data['day_prim'])),
            'status' => 'set'
        ];
        if (isset($data['mods'])) {
            $newData['mods'] = $data['mods'];
        }
        $newData['participants'] = [];
        $count = count($data['participant']);
        for ($i = 0; $i < $count; $i++) {
            if ($data['participant'][$i] === '+1') {
                $id = null;
            } else {
                $name = Users::formatName($data['participant'][$i]);

                if (empty($name)) continue;

                $id = Users::getId($name);
                if ($id < 2) {
                    $id = Users::add($name);
                }
            }
            $newData['participants'][] = [
                'id' => $id,
                'arrive' => trim($data['arrive'][$i]),
                'prim' => trim($data['prim'][$i]),
            ];
        }
        return self::setDayData($weekId, $dayId, $newData);
    }
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
                $weekData = Weeks::defaultWeekData();
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

        if (!$weekData) return self::$dayDataDefault;

        $weekData['data'][$dayId]['weekStart'] = $weekData['start'];

        if (empty($weekData['data'][$dayId]['participants'])) return $weekData['data'][$dayId];

        $weekData['data'][$dayId]['participants'] = Users::addNames($weekData['data'][$dayId]['participants']);
        $count = count($weekData['data'][$dayId]['participants']);
        for ($x = 0; $x < $count; $x++) {
            if (!empty($weekData['data'][$dayId]['participants'][$x]['id'])) continue;
            $weekData['data'][$dayId]['participants'][$x]['name'] = '+1';
        }

        return $weekData['data'][$dayId];
    }
    public static function getFullDescription($weekData, $day)
    {
        $dayTimestamp = $weekData['start'] + (TIMESTAMP_DAY * $day);
        $format = 'd.m.Y ' . $weekData['data'][$day]['time'];
        $dayDate = strtotime(date($format, $dayTimestamp));

        $game = $weekData['data'][$day]['game'];

        if ($_SERVER['REQUEST_TIME'] > $dayDate + DATE_MARGE || in_array($weekData['data'][$day]['status'], ['', 'recalled'])) {
            return '';
        }

        $date = date('d.m.Y', $dayDate) . ' (<b>' . Locale::phrase(self::$days[$day]) . '</b>) ' . $weekData['data'][$day]['time'];

        $gameNames = [
            'mafia' => '{{ Tg_Mafia }}',
            'board' => '{{ Tg_Board }}',
            'nlh' => '{{ Tg_NLH }}',
            'etc' => '{{ Tg_Etc }}',
        ];
        $gameNames = Locale::apply($gameNames);

        if (empty($gameNames[$game])) {
            $gameNames = GameTypes::names();
        }

        // $result = "$date - {$gameNames[$weekData['data'][$day]['game']]}\n";
        $result = "$date - <a href='{$_SERVER['HTTP_X_FORWARDED_PROTO']}://{$_SERVER['SERVER_NAME']}/game/{$weekData['data'][$day]['game']}/'>{$gameNames[$weekData['data'][$day]['game']]}</a>\n";

        if (isset($weekData['data'][$day]['mods'])) {
            if (in_array('fans', $weekData['data'][$day]['mods'], true))
                $result .= Locale::phrase("*<b>Fun game</b>!\nHave a good time and have fun!\n");
            if (in_array('tournament', $weekData['data'][$day]['mods'], true))
                $result .= Locale::phrase("<b>Tournament</b>!\nBecome a champion in a glorious and fair competition!\n");
        }
        if (!empty($weekData['data'][$day]['day_prim']))
            $result .= "<u>{$weekData['data'][$day]['day_prim']}</u>\n";

        $result .= "\n";

        $participants = [];
        $participantsToEnd = [];
        $noNames = [];

        $weekData['data'][$day]['participants'] = Users::addNames($weekData['data'][$day]['participants']);
        $count = count($weekData['data'][$day]['participants']);
        for ($x = 0; $x < $count; $x++) {
            if (empty($weekData['data'][$day]['participants'][$x]['id'])) {
                $noNames[] = $weekData['data'][$day]['participants'][$x];
                continue;
            }
            if (empty($weekData['data'][$day]['participants'][$x]['name']))
                continue;
            if (!empty($weekData['data'][$day]['participants'][$x]['prim']) || !empty($weekData['data'][$day]['participants'][$x]['arrive'])) {
                $participantsToEnd[] = $weekData['data'][$day]['participants'][$x];
                continue;
            }
            $participants[] = $weekData['data'][$day]['participants'][$x];
        }
        $participants = array_merge($participants, $participantsToEnd, $noNames);

        $count = count($participants);
        for ($x = 0; $x < $count; $x++) {
            $modsData = '';
            $userName = '+1';

            if (!empty($participants[$x]['name'])) {
                $userName = $participants[$x]['name'];
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
    public static function removeParticipant(int $weekId, int $dayId, int $userId): bool
    {
        $dayData = self::weekDayData($weekId, $dayId);
        $slot = -1;
        while (isset($dayData['participants'][++$slot])) {
            if ($dayData['participants'][$slot]['id'] != $userId) continue;

            unset($dayData['participants'][$slot]);
            $dayData['participants'] = array_values($dayData['participants']);

            return self::setDayData($weekId, $dayId, $dayData);
        }
        return false;
    }
    public static function addParticipant(int $weekId, int $dayId, int $userId): bool
    {
        $dayData = self::weekDayData($weekId, $dayId);
        $dayData = self::addParticipantToDayData($dayData, ['userId' => $userId]);
        return self::setDayData($weekId, $dayId, $dayData);
    }
    public static function addParticipantToDayData(array $dayData, array $userData, int $slot = -1): array
    {
        if ($slot === -1) {
            while (isset($dayData['participants'][++$slot])) {
            }
        }

        $dayData['participants'][$slot] = [
            'id'        =>    $userData['userId'],
            'arrive'    =>    !empty($userData['arrive']) ? $userData['arrive'] : '',
            'prim'        =>    !empty($userData['prim']) ? $userData['prim'] : '',
        ];

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
                'id'        =>    null,
                'arrive'    =>    '',
                'prim'    =>     $prim,
            ];
        }
        return $dayData;
    }
    public static function removeNonamesFromDayData($dayData, $count)
    {
        $count = (int) $count;
        $newParticipants = [];
        for ($x = 0; $x < count($dayData['participants']); $x++) {
            if (!empty($dayData['participants'][$x]['id']) || $count <= 0) {
                $newParticipants[] = $dayData['participants'][$x];
                continue;
            }
            --$count;
        }
        $dayData['participants'] = $newParticipants;

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
    public static function clear($weekId, $dayNum)
    {
        $weekData = Weeks::weekDataById($weekId);
        if (!isset($weekData['data'][$dayNum]) || $weekData['data'][$dayNum]['status'] !== 'recalled') {
            return false;
        }
        $weekData['data'][$dayNum]['day_prim'] = '';
        $weekData['data'][$dayNum]['participants'] = [];

        return self::setDayData($weekId, $dayNum, $weekData['data'][$dayNum]);
    }
}

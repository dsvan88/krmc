<?php

namespace app\Repositories;

use app\core\Locale;
use app\models\Days;
use app\models\Weeks;

class DayRepository
{
    public static $daysArray = [
        ['пн', 'пон', 'mon'],
        ['вт', 'вто', 'вів', 'tue'],
        ['ср', 'сре', 'сер', 'wed'],
        ['чт', 'чтв', 'чет', 'thu'],
        ['пт', 'пят', 'п’ят', 'fri'],
        ['сб', 'суб', 'sat'],
        ['вс', 'вос', 'нед', 'нд', 'sun']
    ];

    public static $dayDefaultModsArray = [
        'beginners' => '',
        'tournament' => '',
        'night' => '',
        'close' => '',
        'theme' => '',
        'funs' => '',
        'sales' => '',
    ];
    public static $techDaysArray = [
        'today' => ['tod', 'td', 'сг', 'сег', 'сьо'],
        'tomorrow' => ['tom', 'tm', 'зав'],
    ];


    public static function renamePlayer(int $userId, string $name): void
    {
        $weeks = Weeks::getAll();
        foreach ($weeks as $week) {
            foreach ($week['data'] as $dayNum => $day) {
                foreach ($day['participants'] as $participantNum => $participant) {
                    if ($participant['id'] !== $userId) continue;
                    $week['data'][$dayNum]['participants'][$participantNum]['name'] = $name;
                }
            }
            Weeks::setWeekData($week['id'], ['data' => $week['data']]);
        }
    }
    public static function dayDescription(array $day): string
    {
        if (empty($day)) return false;
        $result = $day['date'] . ' - ' .  $day['gameName'] . "\n" . Locale::phrase('Already registered players') . ': ' . count($day['participants']) . PHP_EOL;
        return preg_replace('/<.*?>/', '', $result);
    }
    public static function findNearSetDay(int $weekId, int $dayId)
    {
        $dayData = [];
        do {
            ++$dayId;
            if ($dayId > 6) {
                if (!Weeks::checkNextWeek($weekId, true)) return [$weekId, false];
                $dayId = 0;
                ++$weekId;
            }
            $dayData = Days::weekDayData($weekId, $dayId);
        } while ($dayData['status'] !== 'set');

        return [$weekId, $dayId];
    }
    public static function getDayNamesForCommand(): string
    {
        $days = [];
        foreach (static::$daysArray as $dayNames) {
            $days = array_merge($days, $dayNames);
        }
        foreach (static::$techDaysArray as $dayNames) {
            $days = array_merge($days, $dayNames);
        }
        return implode('|', $days);
    }
    public static function getModsTexts(array $mods = []): string
    {
        if (empty($mods)) return '';

        $result = '';
        if (in_array('funs', $mods, true))
            $result .= Locale::phrase("*<b>Fun game</b>!\nFewer rules, more emotions, additional roles and moves!\nHave a good time and have fun!\n");
        if (in_array('beginners', $mods, true))
            $result .= Locale::phrase("*<b>Begginers</b>!\nLess strict, more explanatory, friendly atmosphere!\nIt’s time to try something new in safest way!😉\n");
        if (in_array('night', $mods, true))
            $result .= Locale::phrase("*<b>Nights</b>!\nAll night long! Don’t stop!😉\n");
        if (in_array('theme', $mods, true))
            $result .= Locale::phrase("*<b>Themes</b>!\nPrepeare yourself and your image!\nIt’s time to dive into a different world!😁\n");
        if (in_array('close', $mods, true))
            $result .= Locale::phrase("*<b>Close</b>!\nOn invitation only!\n");
        if (in_array('sales', $mods, true))
            $result .= Locale::phrase("*<b>Sales</b>!\nThrow a dice!\nWin a dicount on evening's costs!\n");
        if (in_array('tournament', $mods, true))
            $result .= Locale::phrase("<b>Tournament</b>!\nBecome a champion in a glorious and fair competition!\n");
        return $result;
    }
    public static function findLastGameOfPlayer( $userId = 0)
    {
        if (empty($userId)) return 0;

        $weeks = Weeks::getAll();
        $weeks = array_reverse($weeks);
        $statuses = ['set', 'finished'];
        foreach ($weeks as $week) {
            foreach ($week['data'] as $num => $day) {
                if (!in_array($day['status'],  $statuses, true)) continue;
                foreach ($day['participants'] as $player) {
                    if ($player['id'] == $userId)
                        return $week['start'] + TIMESTAMP_DAY * $num;
                }
            }
        }

        return 0;
    }
    public static function findBookedDays( $userId = 0, int $limitWeeks = 0): array
    {
        if (empty($userId)) return [];

        $currentWeekId = Weeks::currentId();

        $weeks = Weeks::getAll();
        $weeks = array_reverse($weeks);
        $statuses = ['set', 'finished'];
        $result = [];
        foreach ($weeks as $week) {
            if (!empty($limitWeeks) && $week['id'] < $currentWeekId - $limitWeeks) break;
            foreach ($week['data'] as $num => $day) {
                if (!in_array($day['status'],  $statuses, true)) continue;
                foreach ($day['participants'] as $index => $player) {
                    if ($player['id'] == $userId){
                        $result[] = [
                            'week' => $week['id'],
                            'day' => $num,
                            'index' => $index,
                        ];
                        break;
                    }
                }
            }
        }

        return $result;
    }
    public static function changeParticipantId(array $data = [], int $userId = 0): void
    {
        if (empty($data) || empty($userId)) return;

        $count = count($data);
        for ($i=0; $i < $count; $i++) {
            $week = Weeks::find($data[$i]['week']);
            $week['data'][$data[$i]['day']]['participants'][$data[$i]['index']]['id'] = $userId;
            Weeks::update(['data' => json_encode($week['data'], JSON_UNESCAPED_UNICODE)], ['id' => $week['id']]);
        }
    }
    public static function getTimeEmoji(string $time = ''): string
    {
        if (empty($time)) return '';

        $offset = strpos($time, ':');
        $hour = (int) substr($time, 0, $offset);
        if ($hour > 12) $hour -= 12;
        $mins = (int) substr($time, $offset + 1);
        if (empty($mins) || $mins > 0 && $mins < 15) $mins = '';
        elseif ($mins > 15 && $mins <= 45) $mins = 30;
        elseif ($mins > 45 && $mins <= 59) {
            ++$hour;
            $mins = '';
        } elseif ($mins >= 60) {
            $hour += round($mins / 60);
            $mins = '';
        }
        $clocks = [
            '1' => '🕐',
            '130' => '🕜',
            '2' => '🕑',
            '230' => '🕝',
            '3' => '🕒',
            '330' => '🕞',
            '4' => '🕓',
            '430' => '🕟',
            '5' => '🕔',
            '530' => '🕠',
            '6' => '🕕',
            '630' => '🕡',
            '7' => '🕖',
            '730' => '🕢',
            '8' => '🕗',
            '830' => '🕣',
            '9' => '🕘',
            '930' => '🕤',
            '10' => '🕙',
            '1030' => '🕥',
            '11' => '🕚',
            '1130' => '🕦',
            '12' => '🕛',
            '1230' => '🕧',
        ];
        return isset($clocks[$hour . $mins]) ? $clocks[$hour . $mins] : '';
    }
}

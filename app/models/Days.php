<?php

namespace app\models;

use app\core\Model;
use app\core\Locale;
use app\core\Validator;
use app\Repositories\AccountRepository;
use app\Repositories\DayRepository;

class Days extends Model
{
    public static $table = SQL_TBL_WEEKS;
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
    public static $daysLocaled = [];
    public static $dayDataDefault = [
        'game' => 'mafia',
        'mods' => [],
        'time' => '14:00',
        'status' => '',
        'starter' => 0,
        'participants' => [],
        'day_prim' => '',
        'cost' => ''
    ];

    public static function current(): int
    {
        if (!empty(self::$currentDay)) {
            return self::$currentDay;
        }

        self::$currentDay = getdate()['wday'] - 1;

        if (self::$currentDay === -1)
            self::$currentDay = 6;

        return self::$currentDay;
    }
    public static function daysNames()
    {
        if (empty(static::$daysLocaled))
            static::$daysLocaled = Locale::apply(static::$days);
        return static::$daysLocaled;
    }
    public static function near()
    {
        $weekId = Weeks::currentId();
        $dayId = self::current();
        $dayData = self::weekDayData($weekId, $dayId);

        if ($dayData['status'] === 'set') return [$weekId, $dayId];

        return DayRepository::findNearSetDay($weekId, $dayId);
    }
    public static function isExpired(int $timestamp): bool
    {
        return $timestamp + TIMESTAMP_DAY < $_SERVER['REQUEST_TIME'] - 3600;
    }
    public static function edit($weekId, $dayId, $data)
    {
        $newData = [
            'game' => trim($data['game']),
            'mods' => $data['mods'] ?? [],
            'time' => trim($data['day_time']),
            'status' => 'set',
            'participants' => [],
            'day_prim' => str_replace('  ', "\n", trim($data['day_prim'])),
            'cost' => trim($data['day_cost']),
        ];
        $count = count($data['participant']);
        for ($i = 0; $i < $count; $i++) {
            if (empty($data['participant'][$i])) continue;
            if ($data['participant'][$i] === '+1') {
                $id = null;
            } elseif ($data['participant'][$i][0] === '@') {
                $tgName = substr($data['participant'][$i], 1);
                $chatData = TelegramChats::findByUserName($tgName);

                if (empty($chatData)) continue;

                $id = '_' . $chatData['id'];
            } elseif ($data['participant'][$i][0] === '_') {
                $tgChatId = substr($data['participant'][$i], 1);
                $chatData = TelegramChats::find($tgChatId);

                if (empty($chatData)) continue;

                $id = '_' . $chatData['id'];
            } else {
                $name = $data['participant'][$i];
                $id = Users::getId($name);
                if ($id < 2) {
                    $name = Validator::validate('name', $name);

                    if (empty($name)) continue;

                    $id = Users::getId($name);
                    if ($id < 2) {
                        $id = Users::add($name);
                    }
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
    public static function setStatus(int $weekId, int $dayNum, string $status = 'set')
    {
        $weekData = Weeks::weekDataById($weekId);
        if (!isset($weekData['data'][$dayNum]) || $weekData['data'][$dayNum]['status'] === $status) {
            return false;
        }
        $weekData['data'][$dayNum]['status'] = $status;
        return self::update(['data' => json_encode($weekData['data'], JSON_UNESCAPED_UNICODE)], ['id' => $weekId]);
    }
    public static function setDayData($weekId, $dayId, $data)
    {
        try {
            if (empty($weekId))
                $weekId = Weeks::currentId();

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
                self::update($weekData, ['id' => $weekId]);
                return $weekId;
            } else {
                return self::insert($weekData);
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

        AccountRepository::addNames($weekData['data'][$dayId]['participants']);
        $count = count($weekData['data'][$dayId]['participants']);
        for ($x = 0; $x < $count; $x++) {
            if (!empty($weekData['data'][$dayId]['participants'][$x]['id'])) continue;
            $weekData['data'][$dayId]['participants'][$x]['name'] = '+1';
        }

        return $weekData['data'][$dayId];
    }
}

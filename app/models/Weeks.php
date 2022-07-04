<?php

namespace app\models;

use app\core\Model;

class Weeks extends Model
{
    public static $currentWeek;
    public static $currentWeekId = -1;

    // Получить настройки недели по времени
    public static function weekDataByTime($time = 0)
    {
        if ($time === 0)
            $time = $_SERVER['REQUEST_TIME'];
        $result = self::query('SELECT id,data,start,finish FROM ' . SQL_TBL_WEEKS . ' WHERE start < :time AND finish > :time LIMIT 1', ['time' => $time], 'Assoc');
        if (!empty($result)) {
            $result = $result[0];
            $result['data'] = json_decode($result['data'], true);
            return $result;
        }
        return false;
    }
    // Отримати й зберегти id поточного тижня
    public static function currentId()
    {
        if (self::$currentWeekId !== -1)
            return self::$currentWeekId;

        $time = $_SERVER['REQUEST_TIME'];
        self::$currentWeekId = self::query('SELECT id FROM ' . SQL_TBL_WEEKS . ' WHERE start < :time AND finish > :time LIMIT 1', ['time' => $time], 'Column');
        return self::$currentWeekId;
    }
    public static function getIds()
    {
        return self::getRawArray('SELECT id FROM ' . SQL_TBL_WEEKS . ' ORDER BY id', []);
    }
    // Получить настройки недели по id недели
    public static function weekDataById($id)
    {
        $result = self::query('SELECT id,data,start,finish FROM ' . SQL_TBL_WEEKS . ' WHERE id = ? LIMIT 1', [$id], 'Assoc');
        if (!empty($result)) {
            $result = $result[0];
            $result['data'] = json_decode($result['data'], true);
            return $result;
        }
        return false;
    }
    // Получить настройки последней зарегистрированной недели
    public static function lastWeekData()
    {
        $result = self::query('SELECT id,data FROM ' . SQL_TBL_WEEKS . ' ORDER BY id DESC LIMIT 1', [], 'Assoc');
        if (!empty($result)) {
            $result = $result[0];
            $result['data'] = json_decode($result['data'], true);
            return $result;
        }
        return false;
    }
    // Получить настройки будущих зарегистрированных недель
    public static function nearWeeksDataByTime()
    {
        $time = $_SERVER['REQUEST_TIME'];
        $result = self::query('SELECT id,data,start,finish FROM ' . SQL_TBL_WEEKS . ' WHERE finish > ? ORDER BY id ASC', [$time], 'Assoc');
        if (!empty($result)) {
            for ($i = 0; $i < count($result); $i++) {
                $result[$i]['data'] = json_decode($result[$i]['data'], true);
            }
            return $result;
        }
        return false;
    }
    public static function weekDataDefault($sunday = 0)
    {
        if ($sunday === 0) {
            $result = self::lastWeekData();
        } else {
            $time = $sunday - TIMESTAMP_WEEK;
            $result = self::weekDataByTime($time);
        }

        if ($result) {
            $weekData = $result;
            $weekData['id'] = 0;
            if (is_string($weekData['data'])) {
                $weekData['data'] = json_decode($weekData['data'], true);
            }
            for ($i = 0; $i < 7; $i++) {
                $weekData['data'][$i]['participants'] = [];
            }
        } else {
            $weekData = [
                'id' => 0,
                'data' => []
            ];
            for ($i = 0; $i < 7; $i++) {
                $weekData['data'][] = Days::$dayDataDefault;
            }
        }
        return $weekData;
    }
    public static function autoloadWeekData($weekId)
    {
        $currentId = self::currentId();
        $weeksIds = self::getIds();
        $isWeekIdInList = -1;
        if ($currentId) {
            $isWeekIdInList = array_search($currentId, $weeksIds);
            $countWeeks = count($weeksIds);
            if ($isWeekIdInList > $countWeeks - 4) {
                self::initNextWeeks($weeksIds[$countWeeks - 1]);
            }
        } else
            $currentId = 0;

        if (is_numeric($weekId) && $weekId > 0) {
            return [$currentId, $weeksIds, $isWeekIdInList, self::weekDataById($weekId)];
        }
        if ($weekId === 0) {
            if ($currentId) {
                return [$currentId, $weeksIds, $isWeekIdInList, self::weekDataById($currentId)];
            }
            return [$currentId, $weeksIds, $isWeekIdInList, self::weekDataDefault()];
        }
    }
    public static function initNextWeeks($lastWeekId)
    {
        $weekData = self::weekDataById($lastWeekId);
        unset($weekData['id']);
        for ($x = 0; $x < count($weekData['data']); $x++) {
            $weekData['data'][$x]['participants'] = [];
        }
        $weekData['data'] = json_encode($weekData['data'], JSON_UNESCAPED_UNICODE);
        $weekData['start'] += TIMESTAMP_WEEK;
        $weekData['finish'] = $weekData['start'] + TIMESTAMP_WEEK - 2;
        self::insert($weekData, SQL_TBL_WEEKS);
        return true;
    }
}

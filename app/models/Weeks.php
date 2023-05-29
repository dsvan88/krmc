<?php

namespace app\models;

use app\core\Model;

class Weeks extends Model
{
    public static $currentWeek;
    public static $currentWeekId = -1;
    public static $table = SQL_TBL_WEEKS;

    // Получить настройки недели по времени
    public static function weekDataByTime($time = 0)
    {
        $table = self::$table;
        if ($time === 0)
            $time = $_SERVER['REQUEST_TIME'];
        $result = self::query("SELECT id,data,start,finish FROM $table WHERE start < :time AND finish > :time LIMIT 1", ['time' => $time], 'Assoc');
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
        $table = self::$table;
        if (self::$currentWeekId !== -1)
            return self::$currentWeekId;

        $time = $_SERVER['REQUEST_TIME'];
        self::$currentWeekId = self::query("SELECT id FROM $table WHERE start < :time AND finish > :time LIMIT 1", ['time' => $time], 'Column');
        return self::$currentWeekId;
    }
    public static function getIds()
    {
        $table = self::$table;
        return self::getRawArray("SELECT id FROM $table ORDER BY id", []);
    }
    // Получить настройки недели по id недели
    public static function weekDataById(int $id): mixed
    {
        $table = self::$table;
        $result = self::query("SELECT id,data,start,finish FROM $table WHERE id = ? LIMIT 1", [$id], 'Assoc');
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
        $table = self::$table;
        $result = self::query("SELECT id,data FROM $table ORDER BY id DESC LIMIT 1", [], 'Assoc');
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
        $table = self::$table;
        $time = $_SERVER['REQUEST_TIME'];
        $result = self::query("SELECT id,data,start,finish FROM $table WHERE finish > ? ORDER BY id ASC", [$time], 'Assoc');
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
                $weekData['data'][$i]['status'] = '';
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
            $weekData['data'][$x]['status'] = '';
        }
        $weekData['data'] = json_encode($weekData['data'], JSON_UNESCAPED_UNICODE);
        $weekData['start'] += TIMESTAMP_WEEK;
        $weekData['finish'] = $weekData['start'] + TIMESTAMP_WEEK - 2;
        self::insert($weekData, SQL_TBL_WEEKS);
        return true;
    }
    public static function setWeekData(int $weekId, array $weekData): mixed
    {
        try {
            if (is_array($weekData['data'])) {
                $weekData['data'] = json_encode($weekData['data'], JSON_UNESCAPED_UNICODE);
            }

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
    public static function init()
    {
        $table = self::$table;
        self::query(
            "CREATE TABLE IF NOT EXISTS $table (
                id INT GENERATED BY DEFAULT AS IDENTITY PRIMARY KEY,
                data JSON DEFAULT NULL,
                start INT NOT NULL DEFAULT '0',
                finish INT NOT NULL DEFAULT '0',
                created_at TIMESTAMP DEFAULT NOW(),
                updated_at TIMESTAMP DEFAULT NOW(),
                date_delete TIMESTAMP DEFAULT NULL
            );"
        );
    }
}

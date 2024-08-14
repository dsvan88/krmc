<?php

namespace app\models;

use app\core\Model;

class Weeks extends Model
{
    public static $currentWeek;
    public static $currentWeekId = -1;
    public static $table = SQL_TBL_WEEKS;
    public static $jsonFields = ['data'];

    // Получить настройки недели по времени
    public static function weekDataByTime($time = 0)
    {
        $table = self::$table;
        if ($time === 0)
            $time = $_SERVER['REQUEST_TIME'];
        $result = self::query("SELECT id,data,start,finish FROM $table WHERE start < :time AND finish > :time LIMIT 1", ['time' => $time], 'Assoc');
        if (!empty($result)) {
            return self::decodeJson($result[0]);
        }
        return false;
    }
    // Отримати й зберегти id поточного тижня
    public static function currentId()
    {
        if (self::$currentWeekId !== -1)
            return self::$currentWeekId;

        $table = self::$table;
        self::$currentWeekId = self::query("SELECT id FROM $table WHERE start < :time AND finish > :time LIMIT 1", ['time' => $_SERVER['REQUEST_TIME']], 'Column');

        if (empty(self::$currentWeekId))
            self::$currentWeekId = self::create();

        return self::$currentWeekId;
    }
    // Получить настройки недели по id недели
    public static function weekDataById(int $id = 0)
    {
        if ($id < 1) return false;

        $result = self::find($id);
        if (!empty($result)) {
            return $result;
        }
        $id = self::create();

        return self::weekDataById($id);
    }
    // Получить настройки последней зарегистрированной недели
    public static function lastWeekData()
    {
        $table = self::$table;
        $result = self::query("SELECT * FROM $table ORDER BY id DESC LIMIT 1", [], 'Assoc');
        if (!empty($result)) {
            return self::decodeJson($result[0]);
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
                $result[$i] = self::decodeJson($result[$i]);
            }
            return $result;
        }
        return false;
    }
    public static function defaultWeekData(int $sunday = 0)
    {
        if ($sunday === 0) {
            $result = self::lastWeekData();
        } else {
            $result = self::weekDataByTime($sunday - TIMESTAMP_WEEK);
        }

        if (empty($result)) {
            $weekData = [
                'id' => 0,
                'data' => [],
                'start' => strtotime('last monday', strtotime('next sunday')),
            ];
            $weekData['finish'] = $weekData['start'] + TIMESTAMP_WEEK - 2;
            for ($i = 0; $i < 7; $i++) {
                $weekData['data'][] = Days::$dayDataDefault;
            }
            return $weekData;
        }

        $weekData = $result;
        $weekData['id'] = 0;
        if (is_string($weekData['data'])) {
            $weekData = self::decodeJson($weekData);
        }
        for ($i = 0; $i < 7; $i++) {
            $weekData['data'][$i]['participants'] = [];
            $weekData['data'][$i]['status'] = '';
        }

        return $weekData;
    }
    public static function create()
    {
        $weekData = self::lastWeekData();

        if (empty($weekData)) {
            $weekData = self::defaultWeekData();
        } else {
            if ($weekData['start'] > $_SERVER['REQUEST_TIME'] + TIMESTAMP_WEEK * MAX_WEEKS_AHEAD) return false;

            for ($i = 0; $i < 7; $i++) {
                $weekData['data'][$i]['participants'] = [];
                $weekData['data'][$i]['status'] = '';
            }
            $weekData['start'] += TIMESTAMP_WEEK;
            $weekData['finish'] = $weekData['start'] + TIMESTAMP_WEEK - 1;
        }
        unset($weekData['id']);

        $weekData['data'] = json_encode($weekData['data'], JSON_UNESCAPED_UNICODE);

        return self::insert($weekData);
    }
    public static function setWeekData(int $weekId, array $weekData): mixed
    {
        try {
            if (is_array($weekData['data'])) {
                $weekData['data'] = json_encode($weekData['data'], JSON_UNESCAPED_UNICODE);
            }

            self::update($weekData, ['id' => $weekId]);
            return $weekId;
        } catch (\Throwable $th) {
            error_log(__METHOD__ . $th->__toString());
            return false;
        }
    }
    public static function checkNextWeek(int $weekId, bool $strict = false)
    {
        if (Users::checkAccess('manager')) return $strict ? false : true;

        return self::isExists(['id' => $weekId + 1]);
    }
    public static function checkPrevWeek(int $weekId)
    {
        return self::isExists(['id' => $weekId - 1]);
    }
    public static function init()
    {
        $table = self::$table;
        self::query(
            "CREATE TABLE IF NOT EXISTS $table (
                id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
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

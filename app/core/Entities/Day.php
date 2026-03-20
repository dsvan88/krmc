<?php

namespace  app\core\Entities;

use app\core\Tech;
use app\models\Days;
use app\models\GameTypes;
use app\models\Weeks;
use app\Repositories\AccountRepository;
use Exception;

class Day
{
    public int $dayId = 0;
    public int $weekId = 0;
    public array $participants = [];
    public array $coupons = [];
    public string $game = '';
    public array $mods = [];
    public string $time = '';
    public string $status = '';
    public string $day_prim = '';
    
    public bool $current = false;
    public int $timestamp = 0;
    public int $start = 0;
    public int $finish = 0;
    public string $dayName = '';
    public string $type = 'future';
    public string $gameName = '';

    public static array $instances = [];
    public static array $week = [];

    private function __construct(int $dayId = 0, int $weekId = 0)
    {

        if ($this->init($dayId, $weekId)) {
            $classId = get_class($this) . "_$dayId.$weekId";
            static::$instances[$classId] = $this;
        } else
            throw new Exception(__METHOD__ . ' New instance of ' . static::class . " with id:  $dayId.$weekId - cant be create!");
    }
    public function init(int $dayId = 0, int $weekId = 0): bool
    {
        $props = get_object_vars($this);

        unset($props['dayId'],
            $props['weekId'],
            $props['current'],
            $props['timestamp'],
            $props['start'],
            $props['finish'],
            $props['dayName'],
            $props['gameName'],
            $props['type'],
        );
        $props = array_keys($props);

        $this->dayId = $dayId;
        $this->weekId = $weekId;
        $this->timestamp = static::$week['start']+ TIMESTAMP_DAY * $dayId;

        $dayNames = Days::daysNames();
        $this->dayName = $dayNames[$dayId];

        $games = GameTypes::names();
        $this->gameName = $games[static::$week['data'][$dayId]['game']];

        $currWeekId = Weeks::currentId();
        $currDayId = Days::current();
        if ($weekId === $currWeekId && $dayId === $currDayId){
            $this->current = true;
            $this->type = 'current';
        }
        elseif ($weekId < $currWeekId || $weekId === $currWeekId && $dayId < $currDayId) {
            $this->type = 'expire';
        }

        foreach ($props as $v) {
            if (empty(static::$week['data'][$dayId][$v])) continue;
            if ($v === 'participants') {
                AccountRepository::addNames(static::$week['data'][$dayId]['participants']);
            }
            $this->$v = static::$week['data'][$dayId][$v];
        }

        return true;
    }
    public static function create(int $dayId = 0, int $weekId = 0)
    {
        $validated = static::validate($dayId, $weekId);

        if (!$validated) return null;

        [$dayId, $weekId] = $validated;
        $classId = get_called_class() . "_$dayId.$weekId";
        if (!empty(static::$instances[$classId]))
            return static::$instances[$classId];

        if (!static::find($weekId)) return null;

        for ($x = 0; $x < 7; $x++) {
            new static($x, $weekId);
        }
        return static::$instances[get_called_class() . "_$dayId.$weekId"];
    }
    public static function find(int $weekId): bool
    {
        $week = Weeks::find($weekId);

        if (empty($week)) return false;

        static::$week = $week;
        return true;
    }
    public static function validate(int $dayId = 0, int $weekId = 0): ?array
    {
        if (0 > $dayId || $dayId > 6) return null;

        return [$dayId, empty($weekId) ? Weeks::currentId() : $weekId];
    }
    public function __toString()
    {
        return "Week: {$this->weekId}, day: {$this->dayId}, game: {$this->game}";
    }
    public function save(): bool
    {

        return true;
    }
}

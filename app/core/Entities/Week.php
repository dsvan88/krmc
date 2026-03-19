<?php

namespace  app\core\Entities;

use app\core\Tech;
use app\models\Weeks;
use app\Repositories\AccountRepository;
use Exception;

class Week
{
    public int $id = 0;
    public int $start = 0;
    public int $finish = 0;
    public array $days = [];

    public static array $instances = [];

    private function __construct(int $weekId = 0)
    {

        if ($this->init($weekId)) {
            $classId = get_class($this) . "_$weekId";
            static::$instances[$classId] = $this;
        } else
            throw new Exception(__METHOD__ . ' New instance of ' . static::class . " with id: $weekId - cant be create!");
    }
    public function init(int $weekId = 0): bool
    {
        $props = get_object_vars($this);

        unset($props['id']);
        $props = array_keys($props);
        $this->id = $weekId;

        Day::create(0, $weekId);
        $week = Day::$week;

        foreach ($props as $v) {
            if ($v === 'days'){
                for($x = 0; $x<7;$x++)
                    $this->days[] = Day::create($x, $weekId);
                continue;
            }
            if (empty($week[$v])) continue;
            $this->$v = $week[$v];
        }

        return true;
    }
    public static function create(int $weekId = 0)
    {
        $weekId = static::validate($weekId);

        if (!$weekId) return null;

        $classId = get_called_class() . "_$weekId";
        if (!empty(static::$instances[$classId]))
            return static::$instances[$classId];

        return new static($weekId);
    }
    public static function validate(int $weekId = 0): ?int
    {
        if (!empty($weekId) && !Weeks::isExists(['id' => $weekId]))
            return null;
        return empty($weekId) ? Weeks::currentId() : $weekId;
    }
    public function __toString()
    {
        $start = date('d.m.Y H:i:s', $this->start);
        $finish = date('d.m.Y H:i:s', $this->finish);
        return "Week: {$this->id}, start: $start, end: $finish";
    }
    public function save(): bool
    {
        return true;
    }
}
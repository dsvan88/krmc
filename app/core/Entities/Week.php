<?php

namespace  app\core\Entities;

use app\models\Weeks;
use Exception;

class Week
{
    public int $id = 0;
    public int $start = 0;
    public int $finish = 0;
    public array $days = [];
    public bool $current = false;

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
        unset($props['days']);
        unset($props['current']);
        $props = array_keys($props);
        $this->id = $weekId;
        if ($weekId === Weeks::currentId()) {
            $this->current = true;
        }

        for ($x = 0; $x < 7; $x++)
            $this->days[] = Day::create($x, $weekId);

        $week = Day::$week;

        foreach ($props as $v) {
            if (empty($week[$v])) continue;
            $this->$v = $week[$v];
        }

        return true;
    }
    public static function create(int $weekId = 0): ?Week
    {
        $weekId = static::validate($weekId);

        if (!$weekId) return null;

        $classId = get_called_class() . "_$weekId";
        if (!empty(static::$instances[$classId]))
            return static::$instances[$classId];

        return new static($weekId);
    }
    public static function fromArray(array $week = []): ?Week
    {
        if (empty($week) || empty($week['id'])) return null;

        $classId = get_called_class() . "_{$week['id']}";
        if (!empty(static::$instances[$classId]))
            return static::$instances[$classId];

        Day::fromWeekArray($week);

        return new static($week['id']);
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
    public function save()
    {
        $data = [];
        for ($x = 0; $x < 7; $x++) {
            $data[] = $this->days[$x]->save(1);
        }
        return (bool) Weeks::update(['data' => $data], $this->id);
    }
}

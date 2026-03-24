<?php

namespace  app\core\Entities;

use app\models\Days;
use app\models\GameTypes;
use app\models\Weeks;
use app\Repositories\AccountRepository;
use Exception;

class Day
{
    public int $dayId = 0;
    public int $weekId = 0;
    public int $starter = 0;
    public array $participants = [];
    public array $coupons = [];
    public string $game = '';
    public array $mods = [];
    public string $time = '';
    public string $status = '';
    public string $day_prim = '';
    public string $cost = '';

    public bool $current = false;
    public int $timestamp = 0;
    public int $start = 0;
    public int $finish = 0;
    public string $date = '';
    public string $datetime = '';
    public string $dayName = '';
    public string $type = 'future';
    public string $gameName = '';
    public int $participantsCount = 0;

    public static array $instances = [];
    public static array $week = [];
    public static array $games = [];
    public static bool $once = false;

    private function __construct(int $dayId = 0, int $weekId = 0)
    {

        if ($this->init($dayId, $weekId)) {
            $classId = get_class($this) . "_$dayId.$weekId";
            static::$instances[$classId] = $this;
        } else
            throw new Exception(__METHOD__ . ' New instance of ' . static::class . " with id:  $dayId.$weekId - cant be create!");
    }
    private function init(int $dayId = 0, int $weekId = 0): bool
    {
        $props = get_object_vars($this);

        unset(
            $props['dayId'],
            $props['weekId'],
            $props['current'],
            $props['timestamp'],
            $props['start'],
            $props['finish'],
            $props['date'],
            $props['datetime'],
            $props['dayName'],
            $props['type'],
            $props['gameName'],
            $props['participantsCount'],
        );
        $props = array_keys($props);

        $this->dayId = $dayId;
        $this->weekId = $weekId;
        $this->timestamp = static::$week['start'] + TIMESTAMP_DAY * $dayId;

        $dayNames = Days::daysNames();
        $this->dayName = $dayNames[$dayId];

        $currWeekId = Weeks::currentId();
        $currDayId = Days::current();
        if ($weekId === $currWeekId && $dayId === $currDayId) {
            $this->current = true;
            $this->type = 'current';
        } elseif ($weekId < $currWeekId || $weekId === $currWeekId && $dayId < $currDayId) {
            $this->type = 'expire';
        }

        foreach ($props as $v) {
            if (empty(static::$week['data'][$dayId][$v])) continue;
            if ($v === 'participants') {
                AccountRepository::addNames(static::$week['data'][$dayId]['participants']);
                $this->participantsCount = count(static::$week['data'][$dayId]['participants']);
            }
            $this->$v = static::$week['data'][$dayId][$v];
        }


        static::$games = GameTypes::names();
        $this->gameName = static::$games[$this->game];
        $this->datetime = date('d.m.Y', $this->timestamp) . " {$this->time}";
        $this->date = date('d.m.Y', $this->timestamp) . " (<b>{$this->dayName}</b>) {$this->time}";

        return true;
    }
    public static function create(int $dayId = 0, int $weekId = 0): ?Day
    {
        $validated = static::validate($dayId, $weekId);

        if (!$validated) return null;

        [$dayId, $weekId] = $validated;
        $classId = get_called_class() . "_$dayId.$weekId";
        if (!empty(static::$instances[$classId]))
            return static::$instances[$classId];

        if (!static::find($weekId)) return null;

        if (static::$once) {
            new static($dayId, $weekId);
        } else {
            for ($x = 0; $x < 7; $x++) {
                new static($x, $weekId);
            }
        }
        return static::$instances[get_called_class() . "_$dayId.$weekId"];
    }
    public static function fromWeekArray(array $week = [], int $dayId = 0): ?Day
    {
        if (empty($week) || empty($week['id'])) return null;

        $weekId = $week['id'];
        $classId = get_called_class() . "_$dayId.$weekId";
        if (!empty(static::$instances[$classId]))
            return static::$instances[$classId];

        static::$week = $week;

        if (static::$once) {
            new static($dayId, $weekId);
        } else {
            for ($x = 0; $x < 7; $x++) {
                new static($x, $weekId);
            }
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
    public function isExpired(): bool
    {
        return $this->timestamp + TIMESTAMP_DAY < $_SERVER['REQUEST_TIME'] - 3600;
    }
    public function clear()
    {
        $this->starter = 0;
        $this->participants = [];
        $this->day_prim = '';
        $this->mods = [];
        $this->status = 'recalled';
    }
    public function addParticipant(array $participant, int $slot = -1)
    {
        if ($slot === -1) {
            while (isset($this->participants[++$slot])) {
            }
        }

        $this->participants[$slot] = [
            'id'        =>    $participant['userId'],
            'arrive'    =>    $participant['arrive'] ?? '',
            'prim'      =>    $participant['prim'] ?? '',
        ];
        AccountRepository::addNames($this->participants[$slot]);
    }
    public function removeParticipant(int $index)
    {
        unset($this->participants[$index]);
        $this->participants = array_values($this->participants);
    }
    public function addNonames(int $count = 0, ?string $prim = ''): void
    {
        if ($count === 0) return;

        for ($x = 0; $x < $count; $x++) {
            $this->participants[] = [
                'id'        =>    null,
                'arrive'    =>    '',
                'prim'    =>     $prim ?? '',
            ];
        }
    }
    public function removeNonames(int $count = 0): void
    {
        if ($count === 0) return;

        $_participants = [];
        foreach ($this->participants as $p) {
            if (!isset($p['id']) && $count-- > 0) continue;
            $_participants[] = $p;
        }
        $this->participants = $_participants;
    }
    public function addMod(string $mod = ''): void
    {
        if (empty($mod)) return;

        if (empty($this->mods) || !in_array($mod, $this->mods)) {
            $this->mods[] = $mod;
        }
    }
    public function removeMod(string $mod = ''): void
    {
        if (empty($mod)) return;

        $i = array_search($mod, $this->mods, true);
        if ($i !== false) {
            unset($day->mods[$i]);
            $this->mods = array_values($this->mods);
        }
    }
    public function save(bool $return = false)
    {
        $defs = Days::$dayDataDefault;

        $day = [];
        foreach ($defs as $k => $v) {
            $day[$k] = $this->$k ?? $v;
        }

        foreach ($day['participants'] as $i => $p) {
            $day['participants'][$i] = [
                'id' => $p['id'],
                'arrive' => $p['arrive'],
                'prim' => $p['prim'],
            ];
        }
        return $return ? $day : Days::setDayData($this->weekId, $this->dayId, $day);
    }
}

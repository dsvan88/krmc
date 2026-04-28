<?php

namespace  app\core\Entities;

use app\core\Locale;
use app\core\Tech;
use app\mappers\Coupons;
use app\mappers\Days;
use app\mappers\GameTypes;
use app\mappers\Weeks;
use app\Services\AccountService;
use app\Services\CouponService;
use Exception;

class Day
{
    public int $dayId = 0;
    public int $weekId = 0;
    public string $status = '';
    public ?int $starter = null;
    public array $participants = [];
    public array $coupons = [];
    public string $game = '';
    public array $mods = [];
    public string $time = '';
    public string $day_prim = '';
    public array $cost = [];
    public string $costText = '';

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
    public static bool $all = false;
    public static ?int $currentDay = null;
    public static $defaults = [
        'game' => 'mafia',
        'mods' => [],
        'coupons' => [],
        'time' => '14:00',
        'status' => '',
        'starter' => null,
        'participants' => [],
        'day_prim' => '',
        'cost' => [
            'amount' => 100,
            'currency' => '₴',
            'type' => 'day',
        ],
        'costText' => '',
    ];

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

        $this->dayId = $dayId;
        $this->weekId = $weekId;
        $this->timestamp = static::$week['start'] + TIMESTAMP_DAY * $dayId;

        $dayNames = Days::daysNames();
        $this->dayName = $dayNames[$dayId];

        $currWeekId = Weeks::currentId();
        $currDayId = static::current();
        if ($weekId === $currWeekId && $dayId === $currDayId) {
            $this->current = true;
            $this->type = 'current';
        } elseif ($weekId < $currWeekId || $weekId === $currWeekId && $dayId < $currDayId) {
            $this->type = 'expire';
        }

        foreach (static::$defaults as $field => $v) {
            if ($field === 'participants') {
                AccountService::addNames(static::$week['data'][$dayId]['participants']);
                $this->participantsCount = count(static::$week['data'][$dayId]['participants']);
            }
            $this->$field = static::$week['data'][$dayId][$field] ?? $v;
        }


        static::$games = GameTypes::names();
        $this->gameName = static::$games[$this->game];
        $this->datetime = date('d.m.Y', $this->timestamp) . " {$this->time}";
        $this->date = date('d.m.Y', $this->timestamp) . " (<b>{$this->dayName}</b>) {$this->time}";

        $_type = $this->cost['type'] === 'day' ? Locale::phrase('evening') : Locale::phrase('game');
        $this->costText = "{$this->cost['amount']} {$this->cost['currency']} for $_type";

        return true;
    }
    public static function create(int $dayId = -1, int $weekId = 0): ?Day
    {
        $validated = static::validate($dayId, $weekId);

        if (!$validated) return null;

        [$dayId, $weekId] = $validated;
        $classId = get_called_class() . "_$dayId.$weekId";
        if (!empty(static::$instances[$classId]))
            return static::$instances[$classId];

        if (!static::find($weekId)) return null;

        if (static::$all)
            for ($x = 0; $x < 7; $x++)
                new static($x, $weekId);
        else
            new static($dayId, $weekId);

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

        if (static::$all) {
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
    public static function validate(int $dayId = -1, int $weekId = 0): ?array
    {
        if ($dayId === -1)
            $dayId = static::current();

        if (0 > $dayId || $dayId > 6)
            return null;

        return [$dayId, empty($weekId) ? Weeks::currentId() : $weekId];
    }
    public static function current()
    {
        if (!is_null(static::$currentDay)) {
            return static::$currentDay;
        }

        static::$currentDay = getdate()['wday'] - 1;

        if (static::$currentDay === -1)
            static::$currentDay = 6;

        return static::$currentDay;
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
        $this->starter = null;
        $this->participants = [];
        $this->day_prim = '';
        $this->mods = [];
        $this->status = 'recalled';
        return $this;
    }
    public function addParticipant(array $participant, int $slot = -1): Day
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

        AccountService::addNames($this->participants[$slot]);

        if (is_numeric($participant['userId'])) {
            return $this->applyCoupons($participant['userId']);
        }
        return $this;
    }
    public function applyCoupons(int $userId = 0): Day
    {
        if (empty($userId)) return $this;

        CouponService::apply($this, $userId);

        return $this;
    }
    public function removeParticipant(int $index): Day
    {
        unset($this->participants[$index]);
        $this->participants = array_values($this->participants);
        return $this;
    }
    public function addNonames(int $count = 0, ?string $prim = ''): Day
    {
        if ($count === 0) return $this;

        for ($x = 0; $x < $count; $x++) {
            $this->participants[] = [
                'id'        =>    null,
                'arrive'    =>    '',
                'prim'    =>     $prim ?? '',
            ];
        }
        return $this;
    }
    public function removeNonames(int $count = 0): Day
    {
        if ($count === 0) return $this;

        $_participants = [];
        foreach ($this->participants as $p) {
            if (!isset($p['id']) && $count-- > 0) continue;
            $_participants[] = $p;
        }
        $this->participants = $_participants;
        return $this;
    }
    public function addMod(string $mod = ''): Day
    {
        if (empty($mod))  return $this;

        if (empty($this->mods) || !in_array($mod, $this->mods)) {
            $this->mods[] = $mod;
        }
        return $this;
    }
    public function removeMod(string $mod = ''): Day
    {
        if (empty($mod)) return $this;

        $i = array_search($mod, $this->mods, true);
        if ($i !== false) {
            unset($this->mods[$i]);
            $this->mods = array_values($this->mods);
        }
        return $this;
    }
    public function save(bool $return = false)
    {
        $day = [];
        foreach (static::$defaults as $k => $v) {
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

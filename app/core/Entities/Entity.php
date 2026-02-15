<?php

namespace  app\core\Entities;

use app\core\Tech;
use Exception;

abstract class Entity
{
    public int $id = 0;
    public static array $cache = [];
    public static array $instances = [];
    public static $model = null;

    private function __construct(int $id)
    {

        if ($this->init($id)) {
            $classId = get_class($this) . "_$id";
            static::$instances[$classId] = $this;
        } else
            throw new Exception(__METHOD__ . ' New instance of ' . static::class . ' with id: ' . $id . ' - cant be create!');
    }
    public function init(int $id): bool
    {
        $props = get_object_vars($this);
        unset($props['id']);
        $props = array_keys($props);

        $this->id = $id;

        if (count($props) === 1)
            return (bool) $this->{$props[0]} = static::$cache;

        foreach ($props as $v) {
            if (empty(static::$cache[$v])) continue;
            $this->$v = static::$cache[$v];
        }

        return true;
    }
    public static function create(int $id = 0)
    {

        $id = static::validate($id);

        if (empty($id)) return null;

        $classId = get_called_class() . "_$id";
        if (!empty(static::$instances[$classId]))
            return static::$instances[$classId];

        return static::find($id)
            ? new static($id)
            : null;
    }
    public static function validate(int $id)
    {
        return empty($id) ? false : $id;
    }
    public static function find(int $id): bool
    {
        $data =  static::$model::find($id);

        if (empty($data)) return false;

        static::$cache = $data;
        return true;
    }
    public function __get($name)
    {
        return $this->$name ?? null;
    }
}

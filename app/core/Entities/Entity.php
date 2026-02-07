<?php

namespace  app\core\Entities;

use app\core\Tech;
use Exception;

class Entity
{
    public int $id = 0;
    public static array $cache = [];
    public static array $instances = [];

    private function __construct(int $id)
    {
        $this->init($id);
        static::$instances[$id] = $this;
    }
    public function init(int $id)
    {
        foreach(static::$cache[$id] as $k=>$v){
            if (!property_exists($this, $k)) continue;
            $this->$k = $v;
        }
        $this->id = $id;
        return true;
    }
    public static function create(int $id = 0){
        $id = static::validate($id);

        if (empty($id)) return null;

        if (!empty(static::$instances[$id]))
            return static::$instances[$id];

        static::find($id);

        return empty(static::$cache[$id])
            ? null
            : new static($id);
    }
    public static function validate(int $id){
        return empty($id) ? false : $id;
    }
    public static function find(int $id){
        return empty($id) ? null : ['id' => $id];
    }
    public function __get($name)
    {
        return $this->$name ?? null;
    }
}

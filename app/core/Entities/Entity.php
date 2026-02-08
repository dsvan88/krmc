<?php

namespace  app\core\Entities;

use app\core\Model;
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
        if ($this->init($id))
            static::$instances[$id] = $this;
        else 
            throw new Exception(__METHOD__ . ' New instance of ' . static::class . ' with id: ' . $id . ' - cant be create!');
    }
    public function init(int $id)
    {
        $props = array_keys(
            array_filter(
                get_object_vars($this),
                fn($k) => $k !== 'id',
                ARRAY_FILTER_USE_KEY
            )
        );
        $this->{$props[0]} = static::$cache;
        $this->id = $id;
        return true;
    }
    public static function create(int $id = 0){
        $id = static::validate($id);

        if (empty($id)) return null;

        if (!empty(static::$instances[$id]))
            return static::$instances[$id];

        return static::find($id)
            ? new static($id)
            : null;
    }
    public static function validate(int $id){
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

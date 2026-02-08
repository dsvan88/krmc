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
    private static $classId = '';

    private function __construct(int $id)
    {
        static::$classId = get_class($this) . "_$id";
        if ($this->init($id))
            static::$instances[static::$classId] = $this;
        else 
            throw new Exception(__METHOD__ . ' New instance of ' . static::class . ' with id: ' . $id . ' - cant be create!');
    }
    public function init(int $id):bool
    {
        $props = array_keys(
            array_filter(
                get_object_vars($this),
                fn($k) => $k !== 'id',
                ARRAY_FILTER_USE_KEY
            )
        );
        
        $this->id = $id;

        if (count($props) === 1)
            return (bool) $this->{$props[0]} = static::$cache;
            
        foreach($props as $v){
            $this->$v = static::$cache[$v];
        }
        
        return true;
    }
    public static function create(int $id = 0){
        $id = static::validate($id);

        if (empty($id)) return null;

        if (!empty(static::$instances[static::$classId]))
            return static::$instances[static::$classId];

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

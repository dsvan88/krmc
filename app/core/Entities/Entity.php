<?php

namespace  app\core\Entities;

use Exception;

class Entity
{
    public static $id = 0;

    public function init(int $id)
    {
        if (empty($id))
            throw new Exception(__METHOD__ . ' ID can’t be empty.');

        if ($id === static::$id)
            return $this->applyProps();

        $this->getProps($id);

        return $this->applyProps();
    }
    public function getProps(int $id)
    {
        static::$id = $id;
        $this->id = static::$id;
    }
    public function applyProps()
    {
        $vars = get_class_vars(static::class);
        foreach ($vars as $name => $value) {
            if (empty(static::$$name)) continue;
            $this->$name = static::$$name;
        }
    }
    public function __get($name)
    {
        return $this->$name ?? null;
    }
}

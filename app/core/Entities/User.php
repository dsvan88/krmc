<?php

namespace  app\core\Entities;

use app\models\Users;
use Exception;

class User extends Entity
{
    public static $profile = [];

    public function __construct(int $id = 0)
    {
        if (empty($id) && empty($_SESSION['id']))
            throw new Exception(__METHOD__ . ': UserID can’t be empty');

        return $this->init(empty($id) ? $_SESSION['id'] : $id);
    }
    public function getProps(int $id)
    {
        static::$profile =  Users::find($id);

        if (empty(static::$profile))
            throw new Exception(__METHOD__ . ": Can’t find a user with the userId $id.");

        static::$id = $id;
    }
    public function __toString()
    {
        return $this->name ?? '';
    }
    public function __get($name)
    {
        if (isset($this->$name))
            return $this->$name;

        if (isset($this->profile['personal'][$name]))
            return $this->profile['personal'][$name];

        if (isset($this->profile['contacts'][$name]))
            return $this->profile['contacts'][$name];

        return null;
    }
}

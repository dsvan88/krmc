<?php

namespace  app\core\Entities;

use app\models\Users;

class User extends Entity
{
    public array $profile = [];
    public static $model = Users::class;

    public static function validate(int $id)
    {
        if (empty($id)) {
            return $_SESSION['id'] ?? false;
        }
        return $id;
    }
    public function __toString()
    {
        return $this->name ?? '';
    }
    public function __get($name)
    {
        if (isset($this->$name))
            return $this->$name;

        if (isset($this->profile[$name]))
            return $this->profile[$name];

        if (isset($this->profile['privilege'][$name]))
            return $this->profile['privilege'][$name];

        if (isset($this->profile['personal'][$name]))
            return $this->profile['personal'][$name];

        if (isset($this->profile['contacts'][$name]))
            return $this->profile['contacts'][$name];

        return null;
    }
}

<?php

namespace  app\core\Entities;

use app\models\TelegramChats;

class Chat extends Entity
{
    public array $chat = [];
    public static $model = TelegramChats::class;

    public static function validate(int $id){
        if (empty($id)){
            return empty($_SESSION['id']) ? false : $_SESSION['id'];
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

        if (isset($this->profile['personal'][$name]))
            return $this->profile['personal'][$name];

        if (isset($this->profile['contacts'][$name]))
            return $this->profile['contacts'][$name];

        return null;
    }
}

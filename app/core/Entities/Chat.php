<?php

namespace  app\core\Entities;

use app\models\TelegramChats;
use app\Repositories\TelegramChatsRepository;

class Chat extends Entity
{
    public array $chat = [];
    public static $model = TelegramChats::class;

    public static function validate(int $id)
    {
        if (!empty($id)) return $id;

        if (empty($_SESSION['id'])) return false;

        $chat = static::$model::findByUserId($_SESSION['id']);
        return empty($chat) ? false : $chat['id'];
    }
    public static function find(int $id): bool
    {
        $data =  static::$model::find($id);

        if (empty($data)) return false;

        static::$cache = $data;
        return true;
    }
    public function __toString()
    {
        return $this->username ?? '';
    }
    public function __get($name)
    {
        if (isset($this->$name))
            return $this->$name;

        if ($name === 'title') {
            return TelegramChatsRepository::chatTitle($this->chat);
        }

        if (isset($this->chat[$name]))
            return $this->chat[$name];

        if (isset($this->chat['personal'][$name]))
            return $this->chat['personal'][$name];

        if (isset($this->chat['data'][$name]))
            return $this->chat['data'][$name];

        return null;
    }
}

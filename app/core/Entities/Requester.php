<?php

namespace  app\core\Entities;

use app\models\TelegramChats;
use app\Repositories\TelegramBotRepository;
use app\Repositories\TelegramChatsRepository;
use Exception;

/**
 * @property User|null $profile
 */
class Requester extends Entity
{
    public $chat = [];
    public $userId = 0;
    public ?User $profile = null;

    public static function find(int $id)
    {
        $_chat = TelegramChats::getChat($id);

        if (empty($_chat)) return null;
        
        static::$cache[$id]['chat'] = $_chat;

        if (empty($_chat['user_id'])) return true;

        $_profile = User::create($_chat['user_id']);
        if (!empty($_profile)){
            static::$cache[$id]['userId'] = $_profile->id;
            static::$cache[$id]['profile'] = $_profile;
        }
        return true;
    }
    public static function validate(int $id){
        if (empty($id)){
            $id = TelegramBotRepository::getUserTelegramId();
            if (empty($id)) return false;
        }
        return $id;
    }
    public function __toString()
    {
        $title = TelegramChatsRepository::chatTitle($this->chat);
        return $title ?? '';
    }
    public function __get($name)
    {
        if (property_exists($this, $name)){
            return $this->$name;
        }
        if (isset($this->chat['personal'][$name]))
            return $this->chat['personal'][$name];

        if (isset($this->chat['data'][$name]))
            return $this->chat['data'][$name];

        return null;
    }
}

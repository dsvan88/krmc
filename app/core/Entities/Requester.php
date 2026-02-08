<?php

namespace  app\core\Entities;

use app\core\Tech;
use app\models\TelegramChats;
use app\Repositories\TelegramBotRepository;
use app\Repositories\TelegramChatsRepository;

/**
 * @property Chat|null $chat
 * @property User|null $profile
 */
class Requester extends Entity
{
    public ?Chat $chat = null;
    public $userId = 0;
    public ?User $profile = null;

    public static function find(int $id):bool
    {
        $data = Chat::create($id);

        if (empty($data)) return false;
        
        $_cache['chat'] = $data;
        
        Tech::dump($data->user_id);

        if (empty($data->user_id))
            return (bool) static::$cache = $_cache;

        Tech::dump($data->user_id);
        
        $data = User::create($data->user_id);
        if (!empty($data)){
            $_cache['userId'] = $data->id;
            $_cache['profile'] = $data;
        }
        return (bool) static::$cache = $_cache;
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
        return $this->chat->title ?? '';
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

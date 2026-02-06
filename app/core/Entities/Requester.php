<?php

namespace  app\core\Entities;

use app\core\Tech;
use app\models\TelegramChats;
use app\Repositories\TelegramBotRepository;
use Exception;

class Requester extends Entity
{
    public static $id = 0;
    public static $chat = null;
    public static $userId = 0;
    public static $profile = null;

    public function __construct(int $id = 0)
    {
        if (empty($id) && empty($_SESSION['id']))
            throw new Exception(__METHOD__ . ': UserID can’t be empty');

        $this->init(empty($id) ? TelegramBotRepository::getUserTelegramId() : $id);
    }
    public function getProps(int $id)
    {
        static::$chat = TelegramChats::getChat($id);

        if (empty(static::$chat))
            throw new Exception(__METHOD__ . ": Can’t find a chat with the chatId $id.");

        static::$id = $id;

        if (empty(static::$chat['user_id'])) return;

        static::$profile = new User(static::$chat['user_id']);
        if (!empty(static::$profile))
            static::$userId = static::$chat['user_id'];
    }
    public function __toString()
    {
        return $this->name ?? '';
    }
    public function __get($name)
    {
        if (isset($this->$name))
            return $this->$name;

        if (isset($this->chat['personal'][$name]))
            return $this->chat['personal'][$name];

        if (isset($this->chat['data'][$name]))
            return $this->chat['data'][$name];

        return null;
    }
}

<?php

namespace  app\core\Entities;

use app\core\Tech;
use app\Repositories\TelegramBotRepository;

/**
 * @property Chat|null $chat
 * @property User|null $profile
 */
class Requester extends Entity
{
    public ?Chat $chat = null;
    public $userId = 0;
    public ?User $profile = null;

    public static function find(int $id): bool
    {
        $_chat = Chat::create($id);

        if (empty($_chat)) return false;

        $_cache['chat'] = $_chat;

        $uId = $_chat->user_id;

        if (empty($uId))
            return (bool) static::$cache = $_cache;

        $_profile = User::create($uId);
        if (!empty($_profile)) {
            $_cache['userId'] = $uId;
            $_cache['profile'] = $_profile;
        }
        return (bool) static::$cache = $_cache;
    }
    public static function validate(int $id)
    {
        if (empty($id)) {
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
        return $this->$name ?? null;
    }
}

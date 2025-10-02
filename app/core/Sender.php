<?php

namespace  app\core;

class Sender
{
    public static $operator = null;
    public static function init(string $method = 'telegram')
    {
        if ($method === 'telegram') {
            self::$operator = new TelegramBot();
        } elseif ($method === 'email') {
            self::$operator = new Mailer();
        }
        return self::$operator;
    }
    public static function message($chatId, string $message, int $replyOn = 0)
    {
        if (empty($chatId)) return false;
        if (empty(self::$operator)) self::init();

        return self::$operator->sendMessage($chatId, $message, $replyOn);
    }
    public static function delete(int $chatId, int $messageId)
    {
        if (empty($chatId)) return false;
        if (empty(self::$operator)) self::init();

        return self::$operator->deleteMessage($chatId, $messageId);
    }
    public static function photo($chatId, string $message, $image)
    {
        if (empty($chatId)) return false;
        if (empty(self::$operator)) self::init();

        return self::$operator->sendPhoto($chatId, $message);
    }
    public static function edit(int $chatId, int $messageId, string $message)
    {
        if (empty($chatId)) return false;
        if (empty(self::$operator)) self::init();

        return self::$operator->editMessage($chatId, $messageId, $message);
    }
    public static function pin($chatId, $messageId)
    {
        if (empty($chatId)) return false;
        if (empty(self::$operator)) self::init();

        return self::$operator->pinMessage($chatId, $messageId);
    }
    public static function unpin($chatId, $messageId)
    {
        if (empty($chatId)) return false;
        if (empty(self::$operator)) self::init();

        return self::$operator->unpinMessage($chatId, $messageId);
    }
    public static function getUserProfilePhotos($userId = 0, $offset = 0, $limit = 1)
    {
        if (empty($userId)) return false;
        if (empty(self::$operator)) self::init();

        return self::$operator->getUserProfilePhotos($userId, $offset, $limit);
    }
    public static function getUserProfileAvatar(int $userId = 0)
    {
        if (empty($userId)) return false;
        if (empty(self::$operator)) self::init();

        return self::$operator->getUserProfileAvatar($userId);
    }
    public static function getFile(string $file_id = '')
    {
        if (empty($file_id)) return false;
        if (empty(self::$operator)) self::init();

        return self::$operator->getFile($file_id);
    }
    public static function setMessageReaction($chatId, $messageId, $reaction)
    {
        if (empty($chatId)) return false;
        if (empty(self::$operator)) self::init();

        return self::$operator->setMessageReaction($chatId, $messageId, $reaction);
    }
    public static function getMe()
    {
        if (empty(self::$operator)) self::init();

        return self::$operator->getMe();
    }
}

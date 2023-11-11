<?php

namespace  app\core;

class Sender
{
    public static $bot = null;
    public static function init(string $method = 'telegram'){
        $class = ucfirst($method).'Bot.class';
        self::$bot = new $class();
        return self::$bot;
    }
    public static function message($chatId, string $message, int $replyOn = null){
        if (empty(self::$bot)) self::init();
        
        return self::$bot->sendMessage($chatId, $message, $replyOn);
    }
    public static function delete(int $chatId, int $messageId){
        if (empty(self::$bot)) self::init();

        return self::$bot->deleteMessage($chatId, $messageId);
    }
    public static function photo($chatId, string $message, $image){
        if (empty(self::$bot)) self::init();

        return self::$bot->sendPhoto($chatId, $message);
    }
    public static function edit(int $chatId, int $messageId, string $message){
        if (empty(self::$bot)) self::init();

        return self::$bot->editMessage($chatId, $messageId, $message);
    }
    public static function pin($chatId, $messageId){
        if (empty(self::$bot)) self::init();

        return self::$bot->pinMessage($chatId, $messageId);
    }
    public static function getMe(){
        if (empty(self::$bot)) self::init();

        return self::$bot->getMe();
    }
}
<?php

namespace  app\core;

use app\Interfaces\Command;

class ChatCommand implements Command
{
    public static $requester = [];
    public static $message = [];
    public static $operatorClass;
    public static $accessLevel = 'guest';

    public static function set($data)
    {
        foreach ($data as $k => $v) {
            static::$$k = $v;
        }
        return true;
    }
    public static function getAccessLevel()
    {
        return static::$accessLevel;
    }
    public static function description()
    {
        return static::class . ' - ' . self::locale('Here isn’t description yet');
    }
    public static function execute(array $arguments = [])
    {
        return static::class . ' - ' . self::locale('Action is done!');
    }
    public static function locale($phrase)
    {
        return Locale::phrase($phrase);
    }
}

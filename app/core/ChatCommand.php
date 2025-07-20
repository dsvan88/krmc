<?php

namespace  app\core;

use app\Interfaces\Command;

class ChatCommand implements Command
{
    public static $requester = [];
    public static $message = [];
    public static $operatorClass;
    public static $accessLevel = 'guest';

    public static function set(array $arguments = []): bool
    {
        foreach ($arguments as $k => $v) {
            static::$$k = $v;
        }
        return true;
    }
    public static function getAccessLevel(): string
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
        if (is_array($phrase))
            return Locale::apply($phrase);
        return Locale::phrase($phrase);
    }
}

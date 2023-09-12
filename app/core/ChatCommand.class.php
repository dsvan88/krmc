<?php

namespace  app\core;

use app\Interfaces\Command;

class ChatCommand implements Command
{
    public static $requester = [];
    public static $message = [];
    public static $operatorClass;
    public static $accessLevel = 'guest';

    public static function description()
    {
        return ClassName::class . ' - ' . self::locale('Here isn’t description yet');
    }
    public static function execute(array $arguments = [])
    {
        return ClassName::class . ' - ' . self::locale('Action is done!');
    }
    public static function locale($phrase)
    {
        return Locale::phrase($phrase);
    }
}

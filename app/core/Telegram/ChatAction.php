<?php

namespace  app\core\Telegram;

use app\core\Locale;
use app\Interfaces\Command;

class ChatAction implements Command
{
    public static $accessLevel = 'all';
    // public static $status = '';
    public static $requester = [];
    public static $message = [];
    public static $arguments = [];
    public static $report = '';

    public static function getAccessLevel(): string
    {
        return static::$accessLevel;
    }
    public static function execute()
    {
        return [
            'result' => true,
            'send' => [
                ['message' => static::class . ' - ' . self::locale('Action is done!')]
            ]
        ];
    }
    public static function locale($phrase)
    {
        if (is_array($phrase))
            return Locale::apply($phrase);
        return Locale::phrase($phrase);
    }
}

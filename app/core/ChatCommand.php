<?php

namespace  app\core;

use app\Interfaces\Command;

class ChatCommand implements Command
{
    public static $requester = [];
    public static $message = [];
    public static $accessLevel = 'all';
    public static $arguments = [];
    public static $status = '';

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
        return [
            'result' => true,
            'send' => [
                ['message' => static::class . ' - ' . self::locale('Here isn’t description yet')],
            ]
        ];
    }
    public static function execute()
    {
        static::$status = static::class . ' - ' . self::locale('Action is done!');
        return [
            'result' => true,
            'send' => [
                ['message' => static::$status]
            ]
        ];
    }
    public static function locale($phrase)
    {
        if (is_array($phrase))
            return Locale::apply($phrase);
        return Locale::phrase($phrase);
    }
    public static function result($message, string $reaction = '', bool $ok = false): array
    {
        return [
            'result' => $ok,
            'reaction' => $reaction,
            'send' => [
                [
                    'message' => static::locale($message),
                ],
            ]
        ];
    }
}

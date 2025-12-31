<?php

namespace  app\core\Telegram;

class ChatCommand extends ChatAction
{
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

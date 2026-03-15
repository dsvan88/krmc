<?php

namespace  app\core\Telegram;

class ChatCommand extends ChatAction
{
    public static function description()
    {
        return static::class . ' - ' . self::locale('Here isn’t description yet');
    }
    public static function result($message, string $reaction = '', bool $ok = false, int $replyOn = 0): array
    {
        $result =  [
            'result' => $ok,
            'reaction' => $reaction,
            'send' => [
                ['message' => static::locale($message)],
            ]
        ];
        
        if (!empty($replyOn)) $result['send'][0]['replyOn'] = $replyOn;

        return $result;
    }
}

<?php

namespace  app\core\Telegram;

class ChatAnswer extends ChatAction
{
    public static function result($answer, bool $ok = false): array
    {
        return [
            'result' => $ok,
            'answer' => static::locale($answer),
        ];
    }
}

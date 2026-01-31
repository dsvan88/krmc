<?php

namespace  app\core\Telegram;

class ChatAnswer extends ChatAction
{
    public static function result($answer, bool $ok = false, bool $alert = false): array
    {
        return [
            'result' => $ok,
            'alert' => $alert,
            'answer' => static::locale($answer),
        ];
    }
}

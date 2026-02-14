<?php

namespace app\Repositories\TelegramCommands;

use app\core\Telegram\ChatCommand;
use app\models\TelegramEmojis;
use app\Repositories\TelegramBotRepository;

class EmojiCommand extends ChatCommand
{
    public static $accessLevel = 'user';
    public static function description()
    {
        return self::locale('<u>/emoji</u> {emoji} <i>// Choose a emoji for your nickname.</i>');
    }
    public static function execute()
    {
        $message = 'Get your emoji from a list for free:';
        $collection = isset(static::$arguments[0]) && is_numeric(static::$arguments[0]) ? static::$arguments[0] : 0;
        $list = TelegramEmojis::get($collection);
        $replyMarkup['inline_keyboard'] = [];
        $row = $i = 0;
        foreach($list as $key=>$item){
            $replyMarkup['inline_keyboard'][$row][] = ['text' => $item, 'callback_data' => ['c' => 'emoji', 'col' => $collection, 'k' => $key, 'u' => static::$requester->profile->id]];
            if (++$i > 0 && $i%7 === 0) ++$row;
        }
        $replyMarkup['inline_keyboard'][] = [
                // ['text' => $item, 'callback_data' => ['c' => 'emoji', 'col' => $collection, 'd' => $key, 'u' => static::$requester->profile->id, 'o' => 0, 'p' => 1]],
                ['text' => '->', 'callback_data' => ['c' => 'emoji', 'col' => $collection, 'd' => $key, 'u' => static::$requester->profile->id, 'o' => TelegramEmojis::$limit]]
            ];


        $result = [
            'result' => true,
            'reaction' => '👌',
            'send' => [
                [
                    'message' => $message,
                    'replyMarkup' => $replyMarkup,
                ]
            ]
        ];
        return $result;
    }
}

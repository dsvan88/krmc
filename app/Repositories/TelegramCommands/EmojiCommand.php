<?php

namespace app\Repositories\TelegramCommands;

use app\core\Telegram\ChatCommand;
use app\models\SocialPoints;
use app\models\TelegramEmojis;
use app\Repositories\TelegramBotRepository;
use app\Repositories\TelegramChatsRepository;

class EmojiCommand extends ChatCommand
{
    public static $accessLevel = 'user';
    public static function description()
    {
        return self::locale('<u>/emoji</u> {emoji} <i>// Choose a emoji for your nickname.</i>');
    }
    public static function execute()
    {
        $arg = trim(static::$arguments[0]);
        if (!is_numeric($arg)){
            $emojiData = TelegramEmojis::findEmojiInCollection($arg);
            if (!$emojiData){
                return static::result('Can’t find this emoji in our collections.', '🤷‍♂️', false, TelegramBotRepository::getMessageId());
            }

            $sp = SocialPoints::get(static::$requester->profile->id);
            if ( $sp < $emojiData['collectId']){
                $emoji = TelegramEmojis::getEmoji($emojiData['collectId'], $emojiData['key']);
                return static::result(['string' => "Sorry! I can not set an emoji '%s', as your custom emoji:(\t It’s costs %s SPs and you’re have %s", 'vars' => [$emoji, $emojiData['collectId'], $sp]], '🤷‍♂️', false, TelegramBotRepository::getMessageId());
            }

            $emoji = TelegramEmojis::set(static::$requester->profile->id, $emojiData['collectId'], $emojiData['key']);
            
            if (empty($emoji)){
                return static::result(['string' => "Sorry! I can not set an emoji '%s', as your custom emoji:(\t:Lets try again!", 'vars' => [$emoji]], '🤷‍♂️', false, TelegramBotRepository::getMessageId());
            }
            
            SocialPoints::minus(static::$requester->profile->id, $emojiData['collectId']);

            return static::result(['string' => 'Okay! We are set an emoji %s, as your custom emoji:)', 'vars' => [$emoji]], '👌', true, TelegramBotRepository::getMessageId());
        }
        $message = 'Get your emoji from a list for free:';
        $collection = isset($arg) && is_numeric($arg) ? $arg : 0;
        $list = TelegramEmojis::get($collection);
        $replyMarkup['inline_keyboard'] = [];
        $row = $i = 0;
        foreach($list as $key=>$item){
            $replyMarkup['inline_keyboard'][$row][] = ['text' => $item, 'callback_data' => ['c' => 'emoji', 'col' => $collection, 'k' => $key, 'u' => static::$requester->profile->id]];
            if (++$i > 0 && $i%7 === 0) ++$row;
        }
        $replyMarkup['inline_keyboard'][] = [
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

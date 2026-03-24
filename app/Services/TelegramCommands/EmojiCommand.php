<?php

namespace app\Services\TelegramCommands;

use app\core\Telegram\ChatCommand;
use app\models\SocialPoints;
use app\models\TelegramEmojis;
use app\Services\TelegramBotService;
use app\Services\TelegramChatsService;

class EmojiCommand extends ChatCommand
{
    public static $accessLevel = 'user';
    public static function description()
    {
        return self::locale('<u>/emoji</u> {emoji} <i>// Choose a emoji for your nickname.</i>');
    }
    public static function execute()
    {
        $emoji = TelegramBotService::findEmoji();
        if ($emoji){
            $emojiData = TelegramEmojis::findEmojiInCollection($emoji);
            if (!$emojiData){
                return static::result(['string' => 'Can’t find emoji "%s" in our collections.', 'vars' => [$emoji]], '🤷‍♂️', false);
            }

            $sp = SocialPoints::get(static::$requester->profile->id);
            if ( $sp < $emojiData['collectId']){
                $emoji = TelegramEmojis::getEmoji($emojiData['collectId'], $emojiData['key']);
                return static::result(['string' => "Sorry! I can not set an emoji '%s', as your custom emoji:(\t It’s costs %s SPs and you’re have %s", 'vars' => [$emoji, $emojiData['collectId'], $sp]], '🤷‍♂️', false);
            }

            $emoji = TelegramEmojis::set(static::$requester->profile->id, $emojiData['collectId'], $emojiData['key']);
            
            if (empty($emoji)){
                return static::result(['string' => "Sorry! I can not set an emoji '%s', as your custom emoji:(\t:Lets try again!", 'vars' => [$emoji]], '🤷‍♂️', false);
            }
            
            SocialPoints::minus(static::$requester->profile->id, $emojiData['collectId']);

            return static::result(['string' => 'Okay! We are set an emoji \'%s\', as your custom emoji:)', 'vars' => [$emoji]], '👌', true);
        }
        $collId = trim(static::$arguments[0] ?? '');

        $collection = isset($collId) && is_numeric($collId) ? $collId : 0;
        $message = 'Get your emoji from a list for '.($collection > 0 ? $collection.' SP' : 'free').':';
        $list = TelegramEmojis::get($collection);
        if (empty($list)){
            return static::result(['string' => 'Can’t find %s as an emoji or collection amoung our collections😔', 'vars' => [$collId]], '👌', true);
        }
        $replyMarkup['inline_keyboard'] = [];
        $row = $i = 0;
        foreach($list as $key=>$item){
            $replyMarkup['inline_keyboard'][$row][] = ['text' => $item, 'callback_data' => ['c' => 'emoji', 'col' => $collection, 'k' => $key, 'u' => static::$requester->profile->id]];
            if (++$i > 0 && $i%7 === 0) ++$row;
        }
        $replyMarkup['inline_keyboard'][$row] = [
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

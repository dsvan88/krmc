<?php

namespace app\Repositories\TelegramCbAnswers;

use app\core\Telegram\ChatAnswer;
use app\models\TelegramEmojis;
use Exception;

class EmojiAnswer extends ChatAnswer
{
    public static function execute():array
    {
        if (empty(static::$arguments))
            throw new Exception(__METHOD__ . ': Arguments is empty');

        if (empty(static::$requester)) {
            return static::result('You don’t have enough rights to change information about other users!');
        }

        $uId = (int) trim(static::$arguments['u']);

        if (empty($uId))
            throw new Exception(__METHOD__ . ': UserID can’t be empty!');

        if (static::$requester->profile->id != $uId && !in_array(static::$requester->profile->status, ['manager', 'admin', 'root'], true))
            return static::result('You don’t have enough rights to change information about other users!');

        $collection = (int) static::$arguments['col'];
        
        if (isset(static::$arguments['k'])){
            
            $key = (int) static::$arguments['k'];
            $emoji = TelegramEmojis::set(static::$requester->profile->id, $collection, $key);

            if (empty($emoji)){
                return static::result(['string' => "Sorry! I can not set an emoji '%s', as your custom emoji:(\t:Lets try again!", 'vars' => [$emoji]], false, true);
            }

            $update = [
                'message' => static::locale(['string' => 'Okay! We are set an emoji %s, as your custom emoji:)', 'vars' => [$emoji]])
            ];

            return array_merge(static::result('Success', true), ['update' => [$update]]);
        }

        $update = ['message' => 'Get your emoji from a list for '. ($collection > 0 ? $collection.' SP' : 'free') . ':'];
        $offset = (int) static::$arguments['o'];
        
        if ($offset < 0) $offset = 0;
        
        $list = TelegramEmojis::get($collection, $offset);
        $replyMarkup['inline_keyboard'] = [];
        $row = $i = 0;
        foreach($list as $key=>$item){
            $replyMarkup['inline_keyboard'][$row][] = ['text' => $item, 'callback_data' => ['c' => 'emoji', 'col' => $collection, 'k' => $key, 'u' => static::$requester->profile->id]];
            if (++$i > 0 && $i%7 === 0) ++$row;
        }
        
        $inlineKeyboard = [];
        if ($offset-TelegramEmojis::$limit > 0)
            $inlineKeyboard[0][] = ['text' => '<-', 'callback_data' => ['c' => 'emoji', 'col' => $collection, 'u' => static::$requester->profile->id, 'o' => $offset-TelegramEmojis::$limit]];

        if ($offset+TelegramEmojis::$limit < TelegramEmojis::$count)
            $inlineKeyboard[0][] = ['text' => '->', 'callback_data' => ['c' => 'emoji', 'col' => $collection, 'u' => static::$requester->profile->id, 'o' => $offset+TelegramEmojis::$limit]];
            
        $replyMarkup['inline_keyboard'][] = $inlineKeyboard;

        return array_merge(static::result('Okay', true), ['update' => [$update]]);
    }
}
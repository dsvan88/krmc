<?php

namespace app\Repositories\TelegramCommands;

use app\core\Telegram\ChatCommand;
use app\models\Days;
use app\models\Weeks;
use app\Repositories\DayRepository;
use app\Repositories\TelegramBotRepository;

class UnregCommand extends ChatCommand
{
    public static $accessLevel = 'manager';
    public static function description()
    {
        return self::locale('<u>/unreg</u> <i>// Unbooking players for a specific day. Examples:</i>
    /unreg mon
    /unreg mon
');
    }
    public static function execute()
    {
        if (empty(static::$arguments)) {
            return static::daysMenu();
        }

        TelegramBotRepository::parseDayNum(static::$arguments[0]);
        $requestData = static::$arguments;

        $weekId = Weeks::currentId();
        if ($requestData['dayNum'] < 0) {
            $requestData['dayNum'] = $requestData['currentDay'];
        } else {
            if ($requestData['currentDay'] > $requestData['dayNum']) {
                ++$weekId;
            }
        }
        return static::participantsMenu($weekId, $requestData['dayNum']);
    }
    public static function daysMenu(){
        $message = 'Choose a day:';
        $replyMarkup = TelegramBotRepository::getForwardDaysListMarkup('unreg', false);
        $replyMarkup['inline_keyboard'][] = [['text' => self::locale('Done'), 'callback_data' => ['c' => 'unreg', 'd' => 1]]];
        return [
            'result' => true,
            'reaction' => '👌',
            'send' => [
                [
                    'message' => $message,
                    'replyMarkup' => $replyMarkup,
                    'replyOn' => TelegramBotRepository::getMessageId()
                ]
            ]
        ];
    }
    public static function participantsMenu(int $weekId, int $dayId){
        $message = 'Choose a participant to UnReg:';
        $replyMarkup = TelegramBotRepository::getPaticipantsListMarkup('unreg', $weekId, $dayId);
        $replyMarkup['inline_keyboard'][] = [['text' => self::locale('Done'), 'callback_data' => ['c' => 'unreg', 'd' => 1]]];
        return [
            'result' => true,
            'reaction' => '👌',
            'send' => [
                [
                    'message' => $message,
                    'replyMarkup' => $replyMarkup,
                    'replyOn' => TelegramBotRepository::getMessageId()
                ]
            ]
        ];
    }
}

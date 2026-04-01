<?php

namespace app\Services\TelegramCommands;

use app\core\Tech;
use app\core\Telegram\ChatCommand;
use app\Formatters\TelegramBotFormatter;
use app\mappers\Weeks;
use app\Services\TelegramBotService;

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

        TelegramBotService::parseDayNum(static::$arguments[0]);
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
    private static function daysMenu()
    {
        $message = 'Choose a day:';
        $replyMarkup = TelegramBotFormatter::getForwardDaysListMarkup('unreg');
        $replyMarkup['inline_keyboard'][] = [['text' => self::locale('Done'), 'callback_data' => ['c' => 'close', 'u' => static::$requester->profile->id]]];
        return [
            'result' => true,
            'reaction' => '👌',
            'send' => [
                [
                    'message' => $message,
                    'replyMarkup' => $replyMarkup,
                ]
            ]
        ];
    }
    private static function participantsMenu(int $weekId, int $dayId)
    {
        $message = 'Choose a participant to UnReg:';
        $replyMarkup = TelegramBotFormatter::getPaticipantsListMarkup('unreg', $weekId, $dayId);
        $replyMarkup['inline_keyboard'][] = [['text' => self::locale('Done'), 'callback_data' => ['c' => 'close', 'u' => static::$requester->profile->id]]];
        return [
            'result' => true,
            'reaction' => '👌',
            'send' => [
                [
                    'message' => $message,
                    'replyMarkup' => $replyMarkup,
                ]
            ]
        ];
    }
}

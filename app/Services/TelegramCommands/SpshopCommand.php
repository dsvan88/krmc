<?php

namespace app\Services\TelegramCommands;

use app\core\Telegram\ChatCommand;
use app\models\Weeks;
use app\Services\TelegramBotService;

class SpshopCommand extends ChatCommand
{
    public static $accessLevel = 'manager';
    public static function description()
    {
        return self::locale('<u>/spshop</u> <i>// Spend your Social Points, gain our gratitude!</i>');
    }
    public static function execute()
    {
        // if (empty(static::$arguments)) {
        return static::couponsMenu();
        // }

        // TelegramBotService::parseDayNum(static::$arguments[0]);
        // $requestData = static::$arguments;

        // $weekId = Weeks::currentId();
        // if ($requestData['dayNum'] < 0) {
        //     $requestData['dayNum'] = $requestData['currentDay'];
        // } else {
        //     if ($requestData['currentDay'] > $requestData['dayNum']) {
        //         ++$weekId;
        //     }
        // }
        // return static::participantsMenu($weekId, $requestData['dayNum']);
    }
    public static function shopMenu()
    {
        $message = static::locale(['string' => 'Your amount of Social Points is: <b>%s</b>SP', 'vars' => [static::$requester->profile->points]]) . PHP_EOL;
        $message .= static::locale('Choose a coupons:');
        $replyMarkup = TelegramBotService::getCouponsListMarkup(false);
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
    public static function couponsMenu()
    {
        $message = static::locale(['string' => 'Your amount of Social Points is: <b>%s</b>SP', 'vars' => [static::$requester->profile->points]]) . PHP_EOL;
        $message .= static::locale('Choose a coupons:');
        $replyMarkup = TelegramBotService::getCouponsListMarkup(false);
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

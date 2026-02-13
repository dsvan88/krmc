<?php

namespace app\Repositories\TelegramCommands;

use app\core\Telegram\ChatCommand;
use app\models\Days;
use app\models\Weeks;
use app\Repositories\TelegramBotRepository;

class DayCommand extends ChatCommand
{
    public static function description()
    {
        return self::locale('<u>/day (week day)</u> <i>// Booking information for a specific day. Without specifying the day - for today</i>');
    }
    public static function execute()
    {
        $weekId = Weeks::currentId();

        $daySlug = isset(static::$arguments[0]) ? static::$arguments[0] : 'tod';
        TelegramBotRepository::parseDayNum($daySlug);

        $dayNum = static::$arguments['dayNum'];
        if (static::$arguments['dayNum'] < static::$arguments['currentDay'])
            $weekId++;

        $weekData = Weeks::weekDataById($weekId);
        $message = Days::getFullDescription($weekData, static::$arguments['dayNum']);

        if (empty($message)) {
            return static::result('{{ Tg_Command_Games_Not_Set }}');
        }

        $replyMarkup = TelegramBotRepository::getBookingMarkup($weekId, $dayNum, array_column($weekData['data'][$dayNum]['participants'], 'id'));

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

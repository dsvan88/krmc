<?php

namespace app\Repositories\TelegramCommands;

use app\core\Telegram\ChatCommand;
use app\models\Days;
use app\models\Weeks;
use app\Repositories\TelegramBotRepository;

class TodayCommand extends ChatCommand
{
    public static function description()
    {
        return self::locale('<u>/today</u> <i>// Booking information for today.</i>');
    }
    public static function execute()
    {
        $weekData = Weeks::weekDataByTime();
        $weekId = $weekData['id'];

        $currentDayNum = Days::current();

        $message = Days::getFullDescription($weekData, $currentDayNum);

        if (empty($message)) {
            return static::result('{{ Tg_Command_Games_Not_Set }}');
        }

        $booked = in_array(static::$requester->profile->id, array_column($weekData['data'][$currentDayNum]['participants'], 'id'));
        $replyMarkup = TelegramBotRepository::getBookingMarkup($weekId, $currentDayNum, $booked);

        $result = [
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

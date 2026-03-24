<?php

namespace app\Services\TelegramCommands;

use app\core\Entities\Day;
use app\core\Telegram\ChatCommand;
use app\Formatters\DayFormatter;
use app\Formatters\TelegramBotFormatter;
use app\models\Days;

class TodayCommand extends ChatCommand
{
    public static function description()
    {
        return self::locale('<u>/today</u> <i>// Booking information for today.</i>');
    }
    public static function execute()
    {
        $day = Day::create();

        $message = DayFormatter::forMessengers($day);

        if (empty($message)) {
            return static::result('{{ Tg_Command_Games_Not_Set }}');
        }

        $booked = in_array(static::$requester->profile->id, array_column($day->participants, 'id'));
        $replyMarkup = TelegramBotFormatter::getBookingMarkup($day->weekId, $day->dayId, $booked);

        $result = [
            'reaction' => '👌',
            'send' => [
                [
                    'message' => $message,
                    'replyMarkup' => $replyMarkup,
                    'replyOn' => 0,
                ]
            ]
        ];
        return $result;
    }
}

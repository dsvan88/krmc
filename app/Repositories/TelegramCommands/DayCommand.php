<?php

namespace app\Repositories\TelegramCommands;

use app\core\Entities\Day;
use app\core\Telegram\ChatCommand;
use app\Formatters\DayFormatter;
use app\Formatters\TelegramBotFormatter;
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

        $daySlug = static::$arguments[0] ?? 'tod';
        TelegramBotRepository::parseDayNum($daySlug);

        $dayNum = static::$arguments['dayNum'];
        if (static::$arguments['dayNum'] < static::$arguments['currentDay'])
            $weekId++;

        $day = Day::create($dayNum, $weekId);
        $message = DayFormatter::forMessengers($day);

        if (empty($message)) {
            return static::result('{{ Tg_Command_Games_Not_Set }}');
        }

        $booked = in_array(static::$requester->profile->id, array_column($day->participants, 'id'));
        $replyMarkup = TelegramBotFormatter::getBookingMarkup($weekId, $dayNum, $booked);

        $result = [
            'result' => true,
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

<?php

namespace app\Services\TelegramCommands;

use app\core\Entities\Day;
use app\core\Entities\Week;
use app\core\Telegram\ChatCommand;
use app\mappers\Days;
use app\mappers\Weeks;
use app\Services\DayService;
use app\Services\TelegramBotService;
use Exception;

class ClearCommand extends ChatCommand
{
    public static $accessLevel = 'manager';
    public static function description()
    {
        return self::locale("<u>/clear (week day)</u> <i>// Clear patricipant’s list of a specific day.\n\tWithout specifying the day - for today.\n\tWorking on recalled day only!</i>");
    }
    public static function execute()
    {

        $dayName = '';
        $days = DayService::getDayNamesForCommand();
        if (!empty(static::$arguments)) {
            if (preg_match("/^($days)/ui", mb_strtolower(static::$arguments[0], 'UTF-8'), $daysPattern) === 1) {
                $dayName = $daysPattern[0];
            }
        }
        if ($dayName === '')
            $dayName = 'tod';

        TelegramBotService::parseDayNum($dayName);
        
        $weekId = Weeks::currentId();
        $dayId = static::$arguments['dayNum'];
        if ($dayId < Day::current())
            $weekId++;

        $day = Day::create($dayId, $weekId);

        if (!$day)
            throw new Exception(__METHOD__.' $day can’t be null');
        
        if ($day->status !== 'recalled')
            return  static::approve($day->weekId, $day->dayId);

        $day->clear()->save();
        
        return static::result('This day’s settings have been cleared.', '👌', true);
    }
    public static function approve(int $weekId, int $dayId){

        $message = static::locale("Can’t clear this day.😥\nIt’s still \"set\". I can only clear \"recalled\"!").PHP_EOL;
        $message .= static::locale("Do you wanna to recall and clear this day?");

        $replyMarkup = [
            'inline_keyboard' => [
                [
                    ['text' => '✔' . self::locale('Agree'), 'callback_data' => ['c' => 'clear', 'u' => static::$requester->profile->id, 'w' => $weekId, 'd' => $dayId ]],
                    ['text' => '❌' . self::locale('Cancel'), 'callback_data' => ['c' => 'cancel', 'u' => static::$requester->profile->id]],
                ],
            ],
        ];
        return [
            'result' => false,
            'reaction' => '🤷‍♂️',
            'send' => [
                [
                    'message' => $message,
                    'replyMarkup' => $replyMarkup,
                ]
            ]
        ];
    }
}

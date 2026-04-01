<?php

namespace app\Services\TelegramCommands;

use app\core\Entities\Day;
use app\core\Telegram\ChatCommand;
use app\mappers\Days;
use app\mappers\Weeks;
use app\Services\DayService;
use app\Services\TelegramBotService;

class RecallCommand extends ChatCommand
{
    public static $accessLevel = 'manager';
    public static function description()
    {
        return self::locale("<u>/recall (week day)</u> <i>// Recall day settings for a specific day.\nRestored by a new registration from the admin.\nWithout specifying the day - for today</i>\n");
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

        if (static::$arguments['dayNum'] < static::$arguments['currentDay']) {
            ++$weekId;
        }

        $day = Day::create(static::$arguments['dayNum'], $weekId);
        $day->status = 'recalled';
        $day->save();

        return static::result('{{ Tg_Command_Successfully_Canceled }}', '👌', true);
    }
}

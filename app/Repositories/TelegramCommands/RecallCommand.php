<?php

namespace app\Repositories\TelegramCommands;

use app\core\Entities\Day;
use app\core\Telegram\ChatCommand;
use app\models\Days;
use app\models\Weeks;
use app\Repositories\DayRepository;
use app\Repositories\TelegramBotRepository;

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
        $days = DayRepository::getDayNamesForCommand();
        if (!empty(static::$arguments)) {
            if (preg_match("/^($days)/ui", mb_strtolower(static::$arguments[0], 'UTF-8'), $daysPattern) === 1) {
                $dayName = $daysPattern[0];
            }
        }
        if ($dayName === '')
            $dayName = 'tod';

        TelegramBotRepository::parseDayNum($dayName);

        $weekId = Weeks::currentId();

        if (static::$arguments['dayNum'] < static::$arguments['currentDay']) {
            ++$weekId;
        }

        Day::$once = true;
        $day = Day::create(static::$arguments['dayNum'], $weekId);
        $day->status = 'recalled';
        $day->save();

        return static::result('{{ Tg_Command_Successfully_Canceled }}', '👌', true);
    }
}

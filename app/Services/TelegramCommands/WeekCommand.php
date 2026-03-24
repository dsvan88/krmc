<?php

namespace app\Services\TelegramCommands;

use app\core\Entities\Week;
use app\core\Telegram\ChatCommand;
use app\Formatters\DayFormatter;
use app\models\Days;
use app\models\News;
use app\models\Weeks;

class WeekCommand extends ChatCommand
{
    public static function description()
    {
        return self::locale('<u>/week</u> <i>// Schedule of upcoming games</i>');
    }
    public static function execute()
    {
        $weeksData = Weeks::nearWeeksDataByTime();

        if (empty($weeksData)) {
            return static::result('{{ Tg_Command_Games_Not_Set }}');
        }

        $message = static::locale('Our schedule for the near future').":\n\n";
        foreach ($weeksData as $weekData) {
            $week = Week::fromArray($weekData);
            for ($i = 0; $i < 7; $i++) {
                $day = $week->days[$i] ?? null;
                if (!isset($day) || in_array($day->status, ['', 'recalled'])) {
                    continue;
                }
                $dayDescription = DayFormatter::forMessengers($day);
                if ($dayDescription !== '')
                    $message .=  $dayDescription .
                        "___________________________\n";
            }
        }

        $promoData = News::getBySlug('promo');
        if ($promoData) {
            if ($promoData['title'] !== '') {
                $message .= "<u><b>$promoData[title]</b></u>\n<i>$promoData[subtitle]</i>\n\n";
                $message .= preg_replace('/(<((?!b|u|s|strong|em|i|\/b|\/u|\/s|\/strong|\/em|\/i)[^>]+)>)/i', '', str_replace(['<br />', '<br/>', '<br>', '</p>'], "\n", trim($promoData['html'])));
            }
        }

        return static::result($message, '👌', true, 0);
    }
}

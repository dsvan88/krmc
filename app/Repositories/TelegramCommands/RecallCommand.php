<?

namespace app\Repositories\TelegramCommands;

use app\core\ChatCommand;
use app\models\Days;
use app\models\Weeks;

class RecallCommand extends ChatCommand
{
    public static $accessLevel = 'manager';
    public static function description()
    {
        return self::locale("<u>/recall (week day)</u> <i>// Recall day settings for a specific day.\nRestored by a new registration from the admin.\nWithout specifying the day - for today</i>\n");
    }
    public static function execute(array $arguments = [])
    {
        $dayName = '';
        $dayNum = -1;
        $currentDayNum = Days::current();

        if (!empty($arguments)) {
            if (preg_match('/^(пн|пон|вт|ср|чт|чет|пт|пят|сб|суб|вс|вос|сг|сег|зав)/', mb_strtolower($arguments[0], 'UTF-8'), $daysPattern) === 1) {
                $dayName = $daysPattern[0];
            }
        }

        if ($dayName === '')
            $dayName = 'сг';

        $dayNum = self::$operatorClass::parseDayNum($dayName, $currentDayNum);

        $weekId = Weeks::currentId();

        if ($dayNum < $currentDayNum) {
            ++$weekId;
        }

        $result = Days::recall($weekId, $dayNum);
        self::$operatorClass::$resultMessage = $result ? '{{ Tg_Command_Successfully_Canceled }}' : '{{ Tg_Command_Set_Day_Not_Found }}';;
        return $result;
    }
}

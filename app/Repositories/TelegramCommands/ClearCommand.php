<?

namespace app\Repositories\TelegramCommands;

use app\core\ChatCommand;
use app\models\Days;
use app\models\Weeks;

class ClearCommand extends ChatCommand
{
    public static $accessLevel = 'manager';
    public static function description()
    {
        return self::locale("<u>/clear (week day)</u> <i>// Clear patricipant’s list of a specific day.\n\tWithout specifying the day - for today.\n\tWorking on recalled day only!</i>");
    }
    public static function execute(array $arguments = [])
    {
        $dayName = '';
        $dayNum = -1;
        $currentDayNum = Days::current();
        // $message = "Не можу очистити цей день.😥\nВін й досі запланований! Я можу очистити лише дні, по яким стався \"відбій\"";
        self::$operatorClass::$resultMessage = self::locale("Can't clear this day.\nIt's still \"set\". I can only clear \"recalled\"!");

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

        $result = Days::clear($weekId, $dayNum);

        if (!$result)
            return false;

        self::$operatorClass::$resultMessage = self::locale('This day’s settings have been cleared.');
        return true;
    }
}

<?

namespace app\Repositories\TelegramCommands;

use app\core\ChatCommand;
use app\models\Days;
use app\models\Weeks;
use app\Repositories\DayRepository;

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
        $requestData = $arguments;
        $days = DayRepository::getDayNamesForCommand();
        if (!empty($requestData)) {
            if (preg_match("/^($days)/ui", mb_strtolower($requestData[0], 'UTF-8'), $daysPattern) === 1) {
                $dayName = $daysPattern[0];
            }
        }
        if ($dayName === '')
            $dayName = 'сг';

        self::$operatorClass::parseDayNum($dayName, $requestData);

        $weekId = Weeks::currentId();

        if ($requestData['dayNum'] < $requestData['currentDay']) {
            ++$weekId;
        }

        $result = Days::recall($weekId, $requestData['dayNum']);
        self::$operatorClass::$resultMessage = $result ? '{{ Tg_Command_Successfully_Canceled }}' : '{{ Tg_Command_Set_Day_Not_Found }}';;
        return $result;
    }
}

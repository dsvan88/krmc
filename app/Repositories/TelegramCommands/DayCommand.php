<?

namespace app\Repositories\TelegramCommands;

use app\core\ChatCommand;
use app\models\Days;
use app\models\Weeks;

class DayCommand extends ChatCommand
{
    public static function description()
    {
        return self::locale('<u>/day (week day)</u> <i>// Booking information for a specific day. Without specifying the day - for today</i>');
    }
    public static function execute(array $arguments = [])
    {
        $weekId = Weeks::currentId();
        $requestData = $arguments;

        if (isset($requestData[0])) {
            self::$operatorClass::parseDayNum($requestData[0], $requestData);
            if ($requestData['dayNum'] < $requestData['currentDay'])
                $weekId++;
        } else {
            self::$operatorClass::parseDayNum('сг', $requestData);
            $requestData['dayNum'] = $requestData['currentDay'];
        }

        $weekData = Weeks::weekDataById($weekId);
        $message = Days::getFullDescription($weekData, $requestData['dayNum']);

        if (empty($message)) {
            self::$operatorClass::$resultMessage = self::locale('{{ Tg_Command_Games_Not_Set }}');
            return false;
        }

        self::$operatorClass::$resultMessage = $message;
        return true;
    }
}

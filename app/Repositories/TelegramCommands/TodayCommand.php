<?

namespace app\Repositories\TelegramCommands;

use app\core\ChatCommand;
use app\models\Days;
use app\models\Weeks;

class TodayCommand extends ChatCommand
{
    public static function description()
    {
        return self::locale('<u>/today</u> <i>// Booking information for today.</i>');
    }
    public static function execute(array $arguments = [])
    {
        $weekData = Weeks::weekDataByTime();

        $currentDayNum = Days::current();

        $message = Days::getFullDescription($weekData, $currentDayNum);

        if (empty($message)) {
            self::$operatorClass::$resultMessage = self::locale('{{ Tg_Command_Games_Not_Set }}');
            return false;
        }

        self::$operatorClass::$resultMessage = $message;
        return true;
    }
}

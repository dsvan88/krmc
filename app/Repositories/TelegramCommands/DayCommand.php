<?
namespace app\Repositories\TelegramCommands;

use app\core\ChatCommand;
use app\models\Days;
use app\models\Weeks;

class DayCommand extends ChatCommand {
    public static function description(){
        return self::locale('<u>/day (week day)</u> <i>// Booking information for a specific day. Without specifying the day - for today</i>');
    }
    public static function execute(array $arguments=[]){
        $weekId = Weeks::currentId();
        $currentDayNum = Days::current();

        if (isset($arguments[0])) {
            $dayNum = self::$operatorClass::parseDayNum($arguments[0], $currentDayNum);
            if ($dayNum < $currentDayNum)
                $weekId++;
        } else {
            $dayNum = $currentDayNum;
        }

        $weekData = Weeks::weekDataById($weekId);
        $message = Days::getFullDescription($weekData, $dayNum);

        if ($message === '')
            return [false, self::locale('{{ Tg_Command_Games_Not_Set }}')];
        
        return [false, $message];
    }
}
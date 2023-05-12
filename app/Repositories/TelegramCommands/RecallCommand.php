<?
namespace app\Repositories\TelegramCommands;

use app\core\ChatCommand;
use app\models\Days;
use app\models\Weeks;

class RecallCommand extends ChatCommand {
    public static function description(){
        return self::locale('<u>/day (week day)</u> <i>// Booking information for a specific day. Without specifying the day - for today</i>');
    }
    public static function execute(array $arguments=[]){
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
        $message = $result ? '{{ Tg_Command_Successfully_Canceled }}' : '{{ Tg_Command_Set_Day_Not_Found }}';
        return [$result, self::locale($message)];
    }
}
<?
namespace app\Repositories\TelegramCommands;

use app\core\ChatCommand;

class WeekCommand extends ChatCommand {
    public static function description(){
        return self::locale('<u>/day (week day)</u> <i>// Booking information for a specific day. Without specifying the day - for today</i>');
    }
    public static function execute(array $arguments=[]){
        return [true, json_encode(self::$message, JSON_UNESCAPED_UNICODE)];
    }
}
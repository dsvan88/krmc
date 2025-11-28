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
    public static function execute(array $arguments = [], string &$message = '', string &$reaction = '', array &$replyMarkup = [])
    {
        $weekId = Weeks::currentId();
        $requestData = $arguments;

        $daySlug = isset($requestData[0]) ? $requestData[0] : 'tod';
        self::$operatorClass::parseDayNum($daySlug, $requestData);
        if ($requestData['dayNum'] < $requestData['currentDay'])
        $weekId++;
        
        $weekData = Weeks::weekDataById($weekId);
        $message = Days::getFullDescription($weekData, $requestData['dayNum']);

        if (empty($message)) {
            $message = self::locale('{{ Tg_Command_Games_Not_Set }}');
            return false;
        }
        $reaction = '👌';
        $replyMarkup = [
            'inline_keyboard' => [ 
                    [
                        ['text' => self::locale('I will too!'), 'callback_data' => base64_encode(json_encode(['cmd' => 'booking', 'wId'=> $weekId, 'dNum' => $requestData['dayNum']]))],
                        ['text' => self::locale('I will too! I hope...'), 'callback_data' => base64_encode(json_encode(['cmd' => 'booking', 'wId'=> $weekId, 'dNum' => $requestData['dayNum'], 'prim' => '?']))],
                    ],
                ],
            ];
        return true;
    }
}

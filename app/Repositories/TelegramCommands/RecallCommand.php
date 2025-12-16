<?

namespace app\Repositories\TelegramCommands;

use app\core\ChatCommand;
use app\models\Days;
use app\models\Weeks;
use app\Repositories\DayRepository;
use app\Repositories\TelegramBotRepository;

class RecallCommand extends ChatCommand
{
    public static $accessLevel = 'manager';
    public static function description()
    {
        return self::locale("<u>/recall (week day)</u> <i>// Recall day settings for a specific day.\nRestored by a new registration from the admin.\nWithout specifying the day - for today</i>\n");
    }
    public static function execute(array $arguments = [], string &$message = '', string &$reaction = '', array &$replyMarkup = [])
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

        TelegramBotRepository::parseDayNum($dayName, $requestData);

        $weekId = Weeks::currentId();

        if ($requestData['dayNum'] < $requestData['currentDay']) {
            ++$weekId;
        }

        $result = Days::recall($weekId, $requestData['dayNum']);
        $reaction = '😢';
        $message = '{{ Tg_Command_Set_Day_Not_Found }}';

        if (!empty($result)){
            $reaction = '👌';
            $message = '{{ Tg_Command_Successfully_Canceled }}';
        }
        return $result;
    }
}

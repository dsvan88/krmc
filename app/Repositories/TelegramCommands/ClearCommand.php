<?

namespace app\Repositories\TelegramCommands;

use app\core\ChatCommand;
use app\models\Days;
use app\models\Weeks;
use app\Repositories\DayRepository;
use app\Repositories\TelegramBotRepository;

class ClearCommand extends ChatCommand
{
    public static $accessLevel = 'manager';
    public static function description()
    {
        return self::locale("<u>/clear (week day)</u> <i>// Clear patricipant’s list of a specific day.\n\tWithout specifying the day - for today.\n\tWorking on recalled day only!</i>");
    }
    public static function execute()
    {
        // $message = "Не можу очистити цей день.😥\nВін й досі запланований! Я можу очистити лише дні, по яким стався \"відбій\"";
        $message = self::locale("Can’t clear this day.\nIt’s still \"set\". I can only clear \"recalled\"!");

        $dayName = '';
        $requestData = $arguments;
        $days = DayRepository::getDayNamesForCommand();
        if (!empty($requestData)) {
            if (preg_match("/^($days)/ui", mb_strtolower($requestData[0], 'UTF-8'), $daysPattern) === 1) {
                $dayName = $daysPattern[0];
            }
        }
        if ($dayName === '')
            $dayName = 'tod';

        TelegramBotRepository::parseDayNum($dayName, $requestData);

        $weekId = Weeks::currentId();

        if ($requestData['dayNum'] < $requestData['currentDay']) {
            ++$weekId;
        }

        $result = Days::clear($weekId, $requestData['dayNum']);

        if (!$result)
            return false;

        $reaction = '👌';
        $message = self::locale('This day’s settings have been cleared.');
        return true;
    }
}

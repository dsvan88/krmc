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

        $dayName = '';
        $days = DayRepository::getDayNamesForCommand();
        if (!empty(static::$arguments)) {
            if (preg_match("/^($days)/ui", mb_strtolower(static::$arguments[0], 'UTF-8'), $daysPattern) === 1) {
                $dayName = $daysPattern[0];
            }
        }
        if ($dayName === '')
            $dayName = 'tod';

        TelegramBotRepository::parseDayNum($dayName, static::$arguments);

        $weekId = Weeks::currentId();

        if (static::$arguments['dayNum'] < static::$arguments['currentDay']) {
            ++$weekId;
        }

        // $message = "Не можу очистити цей день.😥\nВін й досі запланований! Я можу очистити лише дні, по яким стався \"відбій\"";
        if (!Days::clear($weekId, static::$arguments['dayNum']))
            return static::result("Can’t clear this day.\nIt’s still \"set\". I can only clear \"recalled\"!");

        return static::result('This day’s settings have been cleared.', '👌', true);
    }
}

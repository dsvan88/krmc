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
    public static function execute(array $arguments = [], string &$message = '', string &$reaction = '', array &$replyMarkup = [])
    {
        $weekData = Weeks::weekDataByTime();

        $currentDayNum = Days::current();

        $message = Days::getFullDescription($weekData, $currentDayNum);

        if (empty($message)) {
            $message = self::locale('{{ Tg_Command_Games_Not_Set }}');
            return false;
        }

        $reaction = '👌';
        $replyMarkup = [
            'inline_keyboard' => [
                [
                    ['text' => '🙋' . self::locale('I will too!'), 'callback_data' => ['c' => 'booking', 'w' => $weekData['id'], 'd' => $currentDayNum]],
                    ['text' => self::locale('I want too!') . '🥹', 'callback_data' => ['c' => 'booking', 'w' => $weekData['id'], 'd' => $currentDayNum, 'p' => '?']],
                ],
            ],
        ];
        return true;
    }
}

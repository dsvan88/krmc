<?

namespace app\Repositories\TelegramCommands;

use app\core\ChatCommand;
use app\models\Days;
use app\models\Weeks;
use app\Repositories\TelegramBotRepository;

class DayCommand extends ChatCommand
{
    public static function description()
    {
        return self::locale('<u>/day (week day)</u> <i>// Booking information for a specific day. Without specifying the day - for today</i>');
    }
    public static function execute()
    {
        $weekId = Weeks::currentId();

        $daySlug = isset(static::$arguments[0]) ? static::$arguments[0] : 'tod';
        TelegramBotRepository::parseDayNum($daySlug, static::$arguments);
        if (static::$arguments['dayNum'] < static::$arguments['currentDay'])
            $weekId++;

        $weekData = Weeks::weekDataById($weekId);
        $message = Days::getFullDescription($weekData, static::$arguments['dayNum']);

        if (empty($message)) {
            return static::result('{{ Tg_Command_Games_Not_Set }}');
        }


        return [
            'reaction' => '👌',
            'send' => [
                [
                    'message' => self::locale('This day’s settings have been cleared.'),
                    'replyMarkup' => [
                        'inline_keyboard' => [
                            [
                                ['text' => '🙋' . self::locale('I will too!'), 'callback_data' => ['c' => 'booking', 'w' => $weekId, 'd' => static::$arguments['dayNum']]],
                                ['text' => self::locale('I want too!') . '🥹', 'callback_data' => ['c' => 'booking', 'w' => $weekId, 'd' => static::$arguments['dayNum'], 'p' => '?']],
                            ],
                        ],
                    ]
                ]
            ]
        ];
        return true;
    }
}

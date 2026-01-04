<?

namespace app\Repositories\TelegramCommands;

use app\core\Telegram\ChatCommand;
use app\models\Days;
use app\models\Weeks;
use app\Repositories\TelegramBotRepository;

class TodayCommand extends ChatCommand
{
    public static function description()
    {
        return self::locale('<u>/today</u> <i>// Booking information for today.</i>');
    }
    public static function execute()
    {
        $weekData = Weeks::weekDataByTime();
        $weekId = $weekData['id'];

        $currentDayNum = Days::current();

        $message = Days::getFullDescription($weekData, $currentDayNum);

        if (empty($message)) {
            return static::result('{{ Tg_Command_Games_Not_Set }}');
        }

        $replyMarkup = [
            'inline_keyboard' => [
                [
                    ['text' => '🙋' . self::locale('I will!'), 'callback_data' => ['c' => 'booking', 'w' => $weekId, 'd' => static::$arguments['dayNum']]],
                    ['text' => self::locale('I want!') .'(?)'. '🥹', 'callback_data' => ['c' => 'booking', 'w' => $weekId, 'd' => static::$arguments['dayNum'], 'p' => '?']],
                ],
            ],
        ];

        if (!TelegramBotRepository::isDirect()) {
            $replyMarkup['inline_keyboard'][0][] = ['text' => '❌', 'callback_data' => ['c' => 'booking', 'w' => $weekId, 'd' => static::$arguments['dayNum'], 'r' => 1]];
        }

        $result = [
            'reaction' => '👌',
            'send' => [
                [
                    'message' => $message,
                    'replyMarkup' => $replyMarkup,
                ]
            ]
        ];

        if (empty(self::$requester['id'])) return $result;

        if (TelegramBotRepository::isDirect() && in_array(self::$requester['id'], array_column($weekData['data'][static::$arguments['dayNum']]['participants'], 'id'))) {
            $result['send'][0]['replyMarkup']['inline_keyboard'] = [
                [
                    ['text' => '❌' . self::locale('Opt-out'), 'callback_data' => ['c' => 'booking', 'w' => $weekId, 'd' => static::$arguments['dayNum'], 'r' => 1]]
                ]
            ];
        }
        return $result;
    }
}

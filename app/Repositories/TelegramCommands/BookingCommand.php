<?

namespace app\Repositories\TelegramCommands;

use app\core\Telegram\ChatCommand;
use app\models\Days;
use app\models\Weeks;
use app\Repositories\TelegramBotRepository;

class BookingCommand extends ChatCommand
{
    public static function description()
    {
        return self::locale("<u>+ (week day)</u> <i>// Booking for the scheduled games of the current week, examples:</i>\n\t\t+вс\n\t\t+ на сегодня, на 19:30 (отсижу 1-2 игры, под ?)\n<u>- (week day)</u> <i>// Unsubscribe from games on a specific day that you previously signed up for, examples:</i>\n\t\t-вс\n\t\t- завтра\n");
    }
    public static function execute(): array
    {
        TelegramBotRepository::parseDayNum(static::$arguments['dayName']);

        static::$arguments['userId'] = self::$requester['id'];
        static::$arguments['userName'] = self::$requester['name'];
        static::$arguments['userStatus'] = empty(self::$requester['privilege']['status']) ? 'user' : self::$requester['privilege']['status'];

        $weekId = Weeks::currentId();
        if (static::$arguments['currentDay'] > static::$arguments['dayNum']) {
            ++$weekId;
        }

        $weekData = Weeks::weekDataById($weekId);

        $participantId = $slot = -1;
        if ($weekData['data'][static::$arguments['dayNum']]['status'] !== 'set') {
            if (!in_array(static::$arguments['userStatus'], ['trusted', 'activist', 'manager', 'admin'])) {
                return static::result('{{ Tg_Gameday_Not_Set }}');
            }
            if (!isset($weekData['data'][static::$arguments['dayNum']]['game']))
                $weekData['data'][static::$arguments['dayNum']] = Days::$dayDataDefault;

            if (!empty(static::$arguments['arrive']))
                $weekData['data'][static::$arguments['dayNum']]['time'] = static::$arguments['arrive'];

            static::$arguments['arrive'] = '';

            // For social points of day started non-admin
            if (in_array(static::$arguments['userStatus'], ['trusted', 'activist']) && empty($weekData['data'][static::$arguments['dayNum']]['status'])){
                $weekData['data'][static::$arguments['dayNum']]['starter'] = self::$requester['id'];
            }
            
            $weekData['data'][static::$arguments['dayNum']]['status'] = 'set';
        }

        foreach ($weekData['data'][static::$arguments['dayNum']]['participants'] as $index => $userData) {
            if ($userData['id'] !== self::$requester['id']) continue;

            if (!empty(static::$arguments['arrive']) && static::$arguments['arrive'] !== $userData['arrive']) {
                $slot = $index;
                break;
            }

            $participantId = $index;
            break;
        }
        $result = ['result' => true];
        $newDayData = $weekData['data'][static::$arguments['dayNum']];
        if (static::$arguments['method'] === '+') {
            if ($participantId !== -1) {
                return static::result('{{ Tg_Command_Requester_Already_Booked }}');
            }
            $newDayData = Days::addParticipantToDayData($newDayData, static::$arguments, $slot);
            $reactions = [
                '👍',
                '🤩',
                '🔥',
                '❤',
                '🔥',
                '🥰',
                '🎉',
                '👏',
                '⚡',
                '🤝',
                '👌',
            ];
            //👍👎❤🔥🥰👏😁🤔🤯😱🤬😢🎉🤩🤮🤣💔💯⚡🤷‍♂🤝👌
        } else {
            if ($participantId === -1) {
                return static::result('{{ Tg_Command_Requester_Not_Booked }}');
            }
            unset($newDayData['participants'][$participantId]);
            $newDayData['participants'] = array_values($newDayData['participants']);
            //👍👎❤🔥🥰👏😁🤔🤯😱🤬😢🎉🤩🤮🤣💔💯⚡🤷‍♂🤝👌
            $reactions = [
                '👎',
                '🤔',
                '😢',
                '💔',
                '😱',
                '🤯',
                '🤬',
                '🤷‍♂',
            ];
        }

        Days::setDayData($weekId, static::$arguments['dayNum'], $newDayData);

        if (!empty($reactions)) {
            $result['reaction'] = $reactions[mt_rand(0, count($reactions) - 1)];
        }

        $weekData['data'][static::$arguments['dayNum']] = $newDayData;

        $replyMarkup = [
            'inline_keyboard' => [
                [
                    ['text' => '🙋' . self::locale('I will!'), 'callback_data' => ['c' => 'booking', 'w' => $weekId, 'd' => static::$arguments['dayNum']]],
                    ['text' => self::locale('I want!') . '🥹', 'callback_data' => ['c' => 'booking', 'w' => $weekId, 'd' => static::$arguments['dayNum'], 'p' => '?']],
                ],
            ],
        ];

        if (!TelegramBotRepository::isDirect()){
            if (count($weekData['data'][static::$arguments['dayNum']]['participants']) > 0) {
                $replyMarkup['inline_keyboard'][0][] = ['text' => '❌' . static::locale('Opt-out'), 'callback_data' => ['c' => 'booking', 'w' => $weekId, 'd' => static::$arguments['dayNum'], 'r' => '1']];
            }
        }
        elseif (in_array(self::$requester['id'], array_column($newDayData['participants'], 'id'))) {
            $replyMarkup['inline_keyboard'] = [
                [
                    ['text' => '❌' . self::locale('Opt-out'), 'callback_data' => ['c' => 'booking', 'w' => $weekId, 'd' => static::$arguments['dayNum'], 'r' => 1]]
                ]
            ];
        }

        $result['send'][] = [
            'message' => Days::getFullDescription($weekData, static::$arguments['dayNum']),
            'replyMarkup' => $replyMarkup,
        ];

        return $result;
    }
}

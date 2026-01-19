<?

namespace app\Repositories\TelegramCbAnswers;

use app\core\Telegram\ChatAnswer;
use app\models\Days;
use app\models\Settings;
use app\models\TelegramChats;
use app\models\Weeks;
use app\Repositories\TelegramBotRepository;
use Exception;

class BookingAnswer extends ChatAnswer
{
    public static $timestamp = 0;
    public static function execute(): array
    {
        if (empty(static::$arguments))
            throw new Exception(__METHOD__ . ': arguments is empty');

        if (empty(self::$requester['id'])) {
            $chatId = TelegramBotRepository::getUserTelegramId();
            $tgChat = TelegramChats::getChat($chatId);
            if (empty($tgChat) || empty($tgChat['personal']['username'])) {
                return static::result("I can’t to recognize you!\nPlease, register in our system!");
            }
            static::$arguments['userId'] = 't' . $chatId;
            static::$arguments['userName'] = '@' . $tgChat['personal']['username'];
            static::$arguments['userStatus'] = 'all';
        } else {
            static::$arguments['userId'] = self::$requester['id'];
            static::$arguments['userName'] = self::$requester['name'];
            static::$arguments['userStatus'] = empty(self::$requester['privilege']['status']) ? 'user' : self::$requester['privilege']['status'];
        }

        $weekId = (int) trim(static::$arguments['w']);
        $dayNum = (int) trim(static::$arguments['d']);

        $weekData = Weeks::weekDataById($weekId);

        static::$timestamp = $weekData['start'] + (TIMESTAMP_DAY * $dayNum) - 7200;

        if (Days::isExpired(static::$timestamp) || in_array($weekData['data'][$dayNum]['status'], ['', 'recalled'])) {
            return static::result('This day is over🤷‍♂️');
        }

        if ($weekData['data'][$dayNum]['status'] !== 'set') {
            if (!TelegramBotRepository::hasAccess(static::$arguments['userStatus'], 'trusted')) {
                return static::result('{{ Tg_Gameday_Not_Set }}');
            }
            if (!isset($weekData['data'][$dayNum]['game']))
                $weekData['data'][$dayNum] = Days::$dayDataDefault;

            $weekData['data'][$dayNum]['status'] = 'set';
        }

        $pIndex = -1;
        foreach ($weekData['data'][$dayNum]['participants'] as $index => $participant) {
            if ($participant['id'] != static::$arguments['userId']) continue;

            $pIndex = $index;
            break;
        }

        if ($pIndex === -1) {
            static::addParticipant($weekData['data'][$dayNum]);
        } else {
            if (empty(static::$arguments['r'])) {
                static::changePrim($weekData['data'][$dayNum]['participants'], $pIndex);
            } else {
                static::removeParticipant($weekData['data'][$dayNum]['participants'], $pIndex);
            }
        }

        Days::setDayData($weekId, $dayNum, $weekData['data'][$dayNum]);

        $update = [
            'message' => Days::getFullDescription($weekData, $dayNum),
            'replyMarkup' => [
                'inline_keyboard' => [
                    [
                        ['text' => '🙋' . static::locale('I will!'), 'callback_data' => ['c' => 'booking', 'w' => $weekId, 'd' => $dayNum]],
                        ['text' => static::locale('I want!') . '🥹', 'callback_data' => ['c' => 'booking', 'w' => $weekId, 'd' => $dayNum, 'p' => '?']],
                    ],
                ],
            ],
        ];

        if (count($weekData['data'][$dayNum]['participants']) > 0) {
            $update['replyMarkup']['inline_keyboard'][0][] = ['text' => '❌' . static::locale('Opt-out'), 'callback_data' => ['c' => 'booking', 'w' => $weekId, 'd' => $dayNum, 'r' => '1']];
        }
        if (TelegramBotRepository::isDirect() && in_array(static::$arguments['userId'], array_column($weekData['data'][$dayNum]['participants'], 'id'))) {
            $update['replyMarkup']['inline_keyboard'] = [
                [
                    ['text' => '❌' . static::locale('Opt-out'), 'callback_data' => ['c' => 'booking', 'w' => $weekId, 'd' => $dayNum, 'r' => 1]]
                ]
            ];
        }

        return array_merge(static::result('Success', true), ['update' => [$update]]);
    }
    public static function addParticipant(array &$day = []): void
    {
        if (empty($day)) {
            throw new Exception(__METHOD__ . ': $day, cant be empty!');
        }
        $data = [
            'userId' => static::$arguments['userId'],
            'prim' => empty(static::$arguments['p']) ? '' : static::$arguments['p'],
        ];
        Days::addParticipantToDayData($day, $data);
        self::$report = static::locale(['string' => 'User <b>%s</b> is opted-in on <b>%s</b>.', 'vars' => [static::$arguments['userName'], date('d.m.Y', static::$timestamp)]]);
    }
    public static function changePrim(array &$participants = [], int $index = 0): void
    {
        if ($index < 0 || empty($participants)) {
            throw new Exception(__METHOD__ . ': $index or $participants, cant be empty!');
        }
        $participants[$index]['prim'] = static::$arguments['p'];
        self::$report = static::locale(['string' => 'User <b>%s</b> is changed prim on <b>%s</b>.', 'vars' => [static::$arguments['userName'], date('d.m.Y', static::$timestamp)]]);
    }
    public static function removeParticipant(array &$participants = [], int $index = 0): void
    {
        if ($index < 0 || empty($participants)) {
            throw new Exception(__METHOD__ . ': $index or $participants, cant be empty!');
        }
        unset($participants[$index]);
        $participants = array_values($participants);
        self::$report = static::locale(['string' => 'User <b>%s</b> is opted-out from <b>%s</b>.', 'vars' => [static::$arguments['userName'], date('d.m.Y', static::$timestamp)]]);
    }
}

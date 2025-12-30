<?

namespace app\Repositories\TelegramCommands;

use app\core\ChatCommand;
use Exception;

class BookingCommand extends ChatCommand
{
    public static function execute():array
    {
        if (empty(static::$arguments))
            throw new Exception(__METHOD__ . ': UserData or arguments is empty');

        if (empty(static::$userData)) {
            return static::answer("I can’t to recognize you!\nPlease, register in our system!");
        }

        static::$userData['status'] = empty($userData['privilege']['status']) ? 'user' : static::$userData['privilege']['status'];

        $weekId = (int) trim(static::$arguments['w']);
        $dayNum = (int) trim(static::$arguments['d']);
        $chatId = static::getChatId();

        $weekData = Weeks::weekDataById($weekId);
        $dayEnd = $weekData['start'] + (TIMESTAMP_DAY * ($dayNum + 1));
        if ($dayEnd < $_SERVER['REQUEST_TIME'])
            return static::answer('This day is over🤷‍♂️');

        if ($weekData['data'][$dayNum]['status'] !== 'set') {
            if (!static::hasAccess(static::$userData['status'], 'trusted')) {
                return static::answer('{{ Tg_Gameday_Not_Set }}');
            }
            if (!isset($weekData['data'][$dayNum]['game']))
                $weekData['data'][$dayNum] = Days::$dayDataDefault;

            $weekData['data'][$dayNum]['status'] = 'set';
        }
        $send = [];

        foreach ($weekData['data'][$dayNum]['participants'] as $index => $participant) {
            if ($participant['id'] != static::$userData['id']) continue;
            if (empty(static::$arguments['r']))
                return static::answer('{{ Tg_Command_Requester_Already_Booked }}');

            unset($weekData['data'][$dayNum]['participants'][$index]);
            $weekData['data'][$dayNum]['participants'] = array_values($weekData['data'][$dayNum]['participants']);

            $send = [
                'chatId' => Settings::getTechTelegramId(),
                'message' => Locale::phrase(['string' => 'User <b>%s</b> is opted-out from <b>%s</b>.', 'vars' => [static::$userData['name'], date('d.m.Y', $dayEnd - TIMESTAMP_DAY)]]),
            ];
            break;
        }

        $newDayData = $weekData['data'][$dayNum];

        if (empty(static::$arguments['r'])) {
            $data = [
                'userId' => static::$userData['id'],
                'prim' => empty(static::$arguments['p']) ? '' : static::$arguments['p'],
            ];
            $newDayData = Days::addParticipantToDayData($newDayData, $data);
            $send = [
                'chatId' => Settings::getTechTelegramId(),
                'message' => Locale::phrase(['string' => 'User <b>%s</b> is opted-in on <b>%s</b>.', 'vars' => [static::$userData['name'], date('d.m.Y', $dayEnd - TIMESTAMP_DAY)]]),
            ];
        }

        Days::setDayData($weekId, $dayNum, $newDayData);

        $weekData['data'][$dayNum] = $newDayData;

        $update = [
            'message' => Days::getFullDescription($weekData, $dayNum),
            'replyMarkup' => [
                'inline_keyboard' => [
                    [
                        ['text' => '🙋' . Locale::phrase('I will!'), 'callback_data' => ['c' => 'booking', 'w' => $weekId, 'd' => $dayNum]],
                        ['text' => Locale::phrase('I want!') . '🥹', 'callback_data' => ['c' => 'booking', 'w' => $weekId, 'd' => $dayNum, 'p' => '?']],
                    ],
                ],
            ],
        ];

        if (count($weekData['data'][$dayNum]['participants']) > 0) {
            $update['replyMarkup']['inline_keyboard'][0][] = ['text' => '❌', 'callback_data' => ['c' => 'booking', 'w' => $weekId, 'd' => $dayNum, 'r' => '1']];
        }
        if (static::isDirect() && in_array(static::$userData['id'], array_column($weekData['data'][$dayNum]['participants'], 'id'))) {
            $update['replyMarkup']['inline_keyboard'] = [
                [
                    ['text' => '❌' . Locale::phrase('Opt-out'), 'callback_data' => ['c' => 'booking', 'w' => $weekId, 'd' => $dayNum, 'r' => 1]]
                ]
            ];
        }

        return array_merge(static::answer('Success', true), ['update' => [$update]], ['send' => [$send]]);
    }
}
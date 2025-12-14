<?

namespace app\Repositories;

use app\core\Locale;
use app\core\TelegramBot;
use app\models\Contacts;
use app\models\Days;
use app\models\Users;
use app\models\Weeks;
use app\models\Settings;
use app\models\TelegramChats;
use Exception;

class TelegramBotRepository
{
    public static $message = '';
    public static $userData = [];
    public static $arguments = [];
    public static function nickRelink(array &$update = [])
    {
        if (empty(static::$arguments))
            throw new Exception(__METHOD__ . ': Arguments is empty!');

        $uId = (int) trim(static::$arguments['u']);
        $tId = (int) trim(static::$arguments['t']);

        if (empty($uId) || empty($tId))
            throw new Exception(__METHOD__ . ': UserID or TelegramID can’t be empty!');

        if (!empty(static::$userData)) {
            if (empty(static::$userData['privilege']['status']) || !in_array(static::$userData['privilege']['status'], ['manager', 'admin', 'root'], true))
                return 'You don’t have enough rights to change information about other users!';
            static::$arguments['ci'] = static::$message['callback_query']['message']['chat']['id'];
            static::$arguments['mi'] = static::$message['callback_query']['message']['message_id'];
            return static::nickApprove($update);
        }

        $userData = Users::find($uId);

        if (static::$message['callback_query']['from']['id'] == $tId) {
            if (empty(static::$arguments['y'])) {
                $message = Locale::phrase(['string' => 'The nickname <b>%s</b> is already registered by another member of the group!', 'vars' => [$userData['name']]]);
                $message .= PHP_EOL;
                $message .= Locale::phrase('Just come up with a new nickname for yourself!');
                $update = [
                    'message' => $message
                ];
                return 'Success';
            }
            $message = Locale::phrase(['string' => 'The nickname <b>%s</b> is already registered by another member of the group!', 'vars' => [$userData['name']]]) . PHP_EOL;
            $message .= Locale::phrase('But... I can’t find his TelegramID🤷‍♂️') . PHP_EOL;
            $message .= Locale::phrase('Is it your?*') . PHP_EOL;
            $message .= PHP_EOL . '⏳<i>' . Locale::phrase('*Just wait a little for Administrators’s approve.') . '</i>';
            $replyMarkup = [
                'inline_keyboard' => [
                    [
                        ['text' => '✅' . Locale::phrase('Yes'), 'callback_data' => ['c' => 'nickRelink', 'u' => $uId, 't' => $tId, 'y' => 1]],
                        ['text' => '❌' . Locale::phrase('No'), 'callback_data' => ['c' => 'nickRelink', 'u' => $uId, 't' => $tId ]],
                    ],
                ],
            ];
            $update = [
                'message' => $message,
                'replyMarkup' =>  $replyMarkup,
            ];

            $cId = static::$message['callback_query']['message']['chat']['id'];
            $mId = static::$message['callback_query']['message']['id'];
            if ($cId !== Settings::getMainTelegramId()) {
                $message = Locale::phrase(['string' => 'Telegram user with ID <b>%s</b> trying to register the nickname <b>%s</b>.', 'vars' => [$tId, $userData['name']]]);
                $message .= PHP_EOL;
                $message .= Locale::phrase('It’s already registered in our system with another TelegramID, but his TelegramID doesn’t exists anymore or owner didn’t play for quite time.');
                $message .= PHP_EOL;
                $message .= Locale::phrase('Do you agree to pass an ownership of the nickname to a new user?');
                $replyMarkup = [
                    'inline_keyboard' => [
                        [
                            ['text' => '✅' . Locale::phrase('Yes'), 'callback_data' => ['c' => 'nickApprove', 'u' => $uId, 't' => $tId, 'ci' => $cId, 'mi' => $mId]],
                            ['text' => '❌' . Locale::phrase('No'), 'callback_data' => ['c' => 'nickApprove', 'ci' => $cId, 'mi' => $mId]],
                        ],
                    ],
                ];
                $tbBot = new TelegramBot;
                $tbBot->sendMessage(Settings::getTechTelegramId(), $message, -1, $replyMarkup);
            }
            return 'Success';
        }
        return 'You don’t have enough rights to change information about other users!';
    }
    public static function nick(array &$update = [])
    {

        if (empty(static::$arguments))
            throw new Exception(__METHOD__ . ': Arguments is empty');

        if (empty(static::$userData)) {
            return 'You don’t have enough rights to change information about other users!';
        }

        $uId = (int) trim(static::$arguments['u']);

        if (empty($uId))
            throw new Exception(__METHOD__ . ': UserID can’t be empty!');

        if (static::$userData['id'] != $uId && (empty(static::$userData['privilege']['status']) || !in_array(static::$userData['privilege']['status'], ['manager', 'admin', 'root'], true)))
            return 'You don’t have enough rights to change information about other users!';

        if (empty(static::$arguments['y'])) {
            Users::delete($uId);

            $update = [
                'message' => Locale::phrase(['string' => "Okay! Let’s try again!\nUse the next command to register your nickname:\n/nick <b>%s</b>\n\nTry to avoid characters of different languages.", 'vars' => [static::$userData['name']]]),
            ];
            return 'Okay!';
        }

        $update = [
            'message' => 
                Locale::phrase(['string' => "<b>%s</b>, nice to meet you!\nYou successfully registered in our system!", 'vars' =>[static::$userData['name']]]).
                PHP_EOL . PHP_EOL . 
                Locale::phrase('If you made a mistake - don’t worry! Just tell the Administrator about it and he will quickly fix it😏'),
        ];

        return 'Success';
    }
    public static function booking(array &$update = [])
    {
        if (empty(static::$arguments))
            throw new Exception(__METHOD__ . ': UserData or arguments is empty');

        if (empty(static::$userData)) {
            return "I can’t to recognize you!\nPlease, register in our system!";
        }

        static::$userData['status'] = empty($userData['privilege']['status']) ? 'user' : static::$userData['privilege']['status'];

        $weekId = (int) trim(static::$arguments['w']);
        $dayNum = (int) trim(static::$arguments['d']);

        $weekData = Weeks::weekDataById($weekId);
        $dayEnd = $weekData['start'] + (TIMESTAMP_DAY * ($dayNum + 1));
        if ($dayEnd < $_SERVER['REQUEST_TIME'])
            return 'This day is over🤷‍♂️';

        if ($weekData['data'][$dayNum]['status'] !== 'set') {
            if (!in_array(static::$userData['status'], ['trusted', 'activist', 'manager', 'admin'])) {
                return '{{ Tg_Gameday_Not_Set }}';
            }
            if (!isset($weekData['data'][$dayNum]['game']))
                $weekData['data'][$dayNum] = Days::$dayDataDefault;

            $weekData['data'][$dayNum]['status'] = 'set';
        }

        foreach ($weekData['data'][$dayNum]['participants'] as $participant) {
            if ($participant['id'] != static::$userData['id']) continue;
            return '{{ Tg_Command_Requester_Already_Booked }}';
        }

        $newDayData = $weekData['data'][$dayNum];
        $data = [
            'userId' => static::$userData['id'],
            'prim' => empty(static::$arguments['p']) ? '' : static::$arguments['p'],
        ];
        $newDayData = Days::addParticipantToDayData($newDayData, $data);

        Days::setDayData($weekId, $dayNum, $newDayData);

        $weekData['data'][$dayNum] = $newDayData;

        $update = [
            'message' => Days::getFullDescription($weekData, $dayNum),
            'replyMarkup' => [
                'inline_keyboard' => [
                    [
                        ['text' => '🙋' . Locale::phrase('I will too!'), 'callback_data' => ['c' => 'booking', 'w' => $weekId, 'd' => $dayNum]],
                        ['text' => Locale::phrase('I want too!') . '🥹', 'callback_data' => ['c' => 'booking', 'w' => $weekId, 'd' => $dayNum, 'p' => '?']],
                    ],
                ],
            ],
        ];
        
        if (static::$message['callback_query']['message']['chat']['id'] !== Settings::getMainTelegramId() && in_array(static::$userData['id'], array_column($weekData['data'][$dayNum]['participants'], 'id'))){
            $update['replyMarkup']['inline_keyboard'][] = ['text' => '❌' . Locale::phrase('Opt-out'), 'callback_data' => ['c' => 'booking', 'w' => $weekId, 'd' => $dayNum, 'r' => 1]];
        }
        return 'Success';
    }


    /**
     * Admins block
     */


    public static function regSend(array &$update = []): string
    {
        if (empty(static::$userData) || empty(static::$arguments))
            throw new Exception(__METHOD__ . ': UserData or Arguments is empty!');

        if (empty(static::$userData['privilege']['status']) || !in_array(static::$userData['privilege']['status'], ['manager', 'admin', 'root'], true))
            return 'You don’t have enough rights!';

        $wId = (int) trim(static::$arguments['w']);
        $dId = (int) trim(static::$arguments['d']);

        $message = Days::getFullDescription(Weeks::weekDataById($wId), $dId);

        $replyMarkup = [
            'inline_keyboard' => [
                [
                    ['text' => '🙋' . Locale::phrase('I will too!'), 'callback_data' => ['c' => 'booking', 'w' => $wId, 'd' => $dId]],
                    ['text' => Locale::phrase('I want too!') . '🥹', 'callback_data' => ['c' => 'booking', 'w' => $wId, 'd' => $dId, 'p' => '?']],
                ],
            ],
        ];
        TelegramBotRepository::encodeInlineKeyboard($replyMarkup['inline_keyboard']);

        $tbBot = new TelegramBot;
        $tbBot->sendMessage(Settings::getMainTelegramId(), $message, -1, $replyMarkup);

        return 'Success';
    }

    public static function nickApprove(array &$update = [])
    {
        if (empty(static::$userData) || empty(static::$arguments))
            throw new Exception(__METHOD__ . ': UserData or Arguments is empty!');

        if (empty(static::$userData['privilege']['status']) || !in_array(static::$userData['privilege']['status'], ['admin', 'root'], true))
            return 'You don’t have enough rights to change information about other users!';

        if (empty(static::$arguments['u']) || empty(static::$arguments['t'])) {

            if (static::$arguments['ci'] != Settings::getMainTelegramId()){
                $message = Locale::phrase('Okay! I get it.');
                $message .= PHP_EOL;
                $message .= Locale::phrase('I’ll inform the user about your decision😔');

                $update = [
                    'message' => $message,
                ];
            }

            $message = Locale::phrase('I offer my deepest apologies, but the Administrator has rejected your request.');
            $message .= PHP_EOL;
            $message .= Locale::phrase('Just come up with a new nickname for yourself!');

            $tbBot = new TelegramBot;
            $tbBot->editMessage((int) static::$arguments['ci'], (int) static::$arguments['mi'], $message);
            return 'Success';
        }

        $uId = (int) trim(static::$arguments['u']);
        $tId = (int) trim(static::$arguments['t']);

        if (empty($uId) || empty($tId))
            throw new Exception(__METHOD__ . ': UserID or TelegramID can’t be empty!');

        $userData = Users::find($uId);
        $thChat = TelegramChats::getChat($tId);
        $contacts = [['telegramid' => $tId, 'telegram' => $thChat['personal']['username']]];
        Contacts::reLink($contacts, $uId);
        TelegramChatsRepository::getAndSaveTgAvatar($uId, true);

        if (static::$arguments['ci'] != Settings::getMainTelegramId()){
            $message = Locale::phrase('Okay! I get it.');
            $message .= PHP_EOL;
            $message .= Locale::phrase('I’ll inform the user about your decision😊');

            $update = [
                'message' => $message,
            ];
        }

        $message = Locale::phrase('The administrator has approved your request!');
        $message .= PHP_EOL;
        $message .= Locale::phrase(['string' => 'I’m remember you under nickname <b>%s</b>', 'vars' => [$userData['name']]]);
        $message .= PHP_EOL;
        $message .= Locale::phrase('Nice to meet you!');

        $tbBot = new TelegramBot;
        $tbBot->editMessage((int) static::$arguments['ci'], (int) static::$arguments['mi'], $message);

        return 'Success';
    }

    /**
     * Tech block
     */


    public static function encodeInlineKeyboard(array &$data): void
    {
        foreach ($data as $i => $row) {
            foreach ($row as $k => $v) {
                $data[$i][$k]['text'] = Locale::phrase($v['text']);
                $data[$i][$k]['callback_data'] = static::replyButtonEncode($v['callback_data']);
            }
        }
    }
    public static function replyButtonEncode(array $data): string
    {
        return base64_encode(http_build_query($data));
    }
    public static function replyButtonDecode(string $data): array
    {
        parse_str(base64_decode($data), $result);
        return $result;
    }
}

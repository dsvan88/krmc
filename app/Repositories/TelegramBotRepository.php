<?

namespace app\Repositories;

use app\core\ChatCommand;
use app\core\Locale;
use app\core\Router;
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

    public static function nickRelink(): array
    {
        if (empty(static::$arguments))
            throw new Exception(__METHOD__ . ': Arguments is empty!');

        $uId = (int) trim(static::$arguments['u']);
        $tId = (int) trim(static::$arguments['t']);

        if (empty($uId) || empty($tId))
            throw new Exception(__METHOD__ . ': UserID or TelegramID can’t be empty!');

        if (!empty(static::$userData)) {
            if (empty(static::$userData['privilege']['status']) || !in_array(static::$userData['privilege']['status'], ['manager', 'admin', 'root'], true))
                return ['message' => 'You don’t have enough rights to change information about other users!'];
            static::$arguments['ci'] = static::$message['callback_query']['message']['chat']['id'];
            static::$arguments['mi'] = static::$message['callback_query']['message']['message_id'];
            return static::nickApprove();
        }

        $userData = Users::find($uId);

        if (static::$message['callback_query']['from']['id'] == $tId) {
            if (empty(static::$arguments['y'])) {
                $update['message'] = Locale::phrase(['string' => 'The nickname <b>%s</b> is already registered by another member of the group!', 'vars' => [$userData['name']]]);
                $update['message'] .= PHP_EOL;
                $update['message'] .= Locale::phrase('Just come up with a new nickname for yourself!');
                return [
                    'message' => 'Success',
                    'update' => [$update],
                ];
            }
            $update['message'] = Locale::phrase(['string' => 'The nickname <b>%s</b> is already registered by another member of the group!', 'vars' => [$userData['name']]]) . PHP_EOL;
            $update['message'] .= Locale::phrase('But... I can’t find his TelegramID🤷‍♂️') . PHP_EOL;
            $update['message'] .= Locale::phrase('Is it your?*') . PHP_EOL;
            $update['message'] .= PHP_EOL . '⏳<i>' . Locale::phrase('*Just wait a little for Administrators’s approve.') . '</i>';
            $update['replyMarkup'] = [
                'inline_keyboard' => [
                    [
                        ['text' => '✅' . Locale::phrase('Yes'), 'callback_data' => ['c' => 'nickRelink', 'u' => $uId, 't' => $tId, 'y' => 1]],
                        ['text' => '❌' . Locale::phrase('No'), 'callback_data' => ['c' => 'nickRelink', 'u' => $uId, 't' => $tId]],
                    ],
                ],
            ];

            $result = [
                'message' => 'Success',
                'update' => [$update],
            ];

            $cId = static::$message['callback_query']['message']['chat']['id'];
            $mId = static::$message['callback_query']['message']['id'];
            if ($cId !== Settings::getMainTelegramId()) {
                $send['message'] = Locale::phrase(['string' => 'Telegram user with ID <b>%s</b> trying to register the nickname <b>%s</b>.', 'vars' => [$tId, $userData['name']]]) . PHP_EOL;
                $send['message'] .= Locale::phrase('It’s already registered in our system with another TelegramID, but his TelegramID doesn’t exists anymore or owner didn’t play for quite time.') . PHP_EOL;
                $send['message'] .= Locale::phrase('Do you agree to pass an ownership of the nickname to a new user?');
                $send['replyMarkup'] = [
                    'inline_keyboard' => [
                        [
                            ['text' => '✅' . Locale::phrase('Yes'), 'callback_data' => ['c' => 'nickApprove', 'u' => $uId, 't' => $tId, 'ci' => $cId, 'mi' => $mId]],
                            ['text' => '❌' . Locale::phrase('No'), 'callback_data' => ['c' => 'nickApprove', 'ci' => $cId, 'mi' => $mId]],
                        ],
                    ],
                ];
                $result['send'][] = $send;
            }
            return $result;
        }
        return ['message' => 'You don’t have enough rights to change information about other users!'];
    }
    public static function nick(): array
    {

        if (empty(static::$arguments))
            throw new Exception(__METHOD__ . ': Arguments is empty');

        if (empty(static::$userData)) {
            return ['message' => 'You don’t have enough rights to change information about other users!'];
        }

        $uId = (int) trim(static::$arguments['u']);

        if (empty($uId))
            throw new Exception(__METHOD__ . ': UserID can’t be empty!');

        if (static::$userData['id'] != $uId && (empty(static::$userData['privilege']['status']) || !in_array(static::$userData['privilege']['status'], ['manager', 'admin', 'root'], true)))
            return ['message' => 'You don’t have enough rights to change information about other users!'];

        if (empty(static::$arguments['y'])) {

            Users::delete($uId);

            return [
                'message' => 'Okay!',
                'update' => [
                    'message' => Locale::phrase(['string' => "Okay! Let’s try again!\nUse the next command to register your nickname:\n/nick <b>%s</b>\n\nTry to avoid characters of different languages.", 'vars' => [static::$userData['name']]])
                ],
            ];
        }

        return [
            'message' => 'Okay!',
            'update' => [
                [
                    'message' =>
                    Locale::phrase(['string' => "<b>%s</b>, nice to meet you!\nYou successfully registered in our system!", 'vars' => [static::$userData['name']]]) .
                        PHP_EOL . PHP_EOL .
                        Locale::phrase('If you made a mistake - don’t worry! Just tell the Administrator about it and he will quickly fix it😏'),
                ],
            ],
        ];
    }
    public static function booking(): array
    {
        if (empty(static::$arguments))
            throw new Exception(__METHOD__ . ': UserData or arguments is empty');

        if (empty(static::$userData)) {
            return ['message' => "I can’t to recognize you!\nPlease, register in our system!"];
        }

        static::$userData['status'] = empty($userData['privilege']['status']) ? 'user' : static::$userData['privilege']['status'];

        $weekId = (int) trim(static::$arguments['w']);
        $dayNum = (int) trim(static::$arguments['d']);
        $chatId = static::getChatId();

        $weekData = Weeks::weekDataById($weekId);
        $dayEnd = $weekData['start'] + (TIMESTAMP_DAY * ($dayNum + 1));
        if ($dayEnd < $_SERVER['REQUEST_TIME'])
            return ['message' => 'This day is over🤷‍♂️'];

        if ($weekData['data'][$dayNum]['status'] !== 'set') {
            if (!static::hasAccess(static::$userData['status'], 'trusted')) {
                return ['message' => '{{ Tg_Gameday_Not_Set }}'];
            }
            if (!isset($weekData['data'][$dayNum]['game']))
                $weekData['data'][$dayNum] = Days::$dayDataDefault;

            $weekData['data'][$dayNum]['status'] = 'set';
        }
        $send = [];

        foreach ($weekData['data'][$dayNum]['participants'] as $index => $participant) {
            if ($participant['id'] != static::$userData['id']) continue;
            if (empty(static::$arguments['r']))
                return ['message' => '{{ Tg_Command_Requester_Already_Booked }}'];

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
                        ['text' => '🙋' . Locale::phrase('I will too!'), 'callback_data' => ['c' => 'booking', 'w' => $weekId, 'd' => $dayNum]],
                        ['text' => Locale::phrase('I want too!') . '🥹', 'callback_data' => ['c' => 'booking', 'w' => $weekId, 'd' => $dayNum, 'p' => '?']],
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

        return [
            'message' => 'Success',
            'update' => [$update],
            'send' => [$send],
        ];
    }


    /**
     * Admins block
     */


    public static function regSend(): array
    {
        if (empty(static::$userData) || empty(static::$arguments))
            throw new Exception(__METHOD__ . ': UserData or Arguments is empty!');

        if (empty(static::$userData['privilege']['status']) || !in_array(static::$userData['privilege']['status'], ['manager', 'admin', 'root'], true))
            return ['message' => 'You don’t have enough rights!'];

        $weekId = (int) trim(static::$arguments['w']);
        $dayNum = (int) trim(static::$arguments['d']);

        $message = Days::getFullDescription(Weeks::weekDataById($weekId), $dayNum);

        $replyMarkup = [
            'inline_keyboard' => [
                [
                    ['text' => '🙋' . Locale::phrase('I will too!'), 'callback_data' => ['c' => 'booking', 'w' => $weekId, 'd' => $dayNum]],
                    ['text' => Locale::phrase('I want too!') . '🥹', 'callback_data' => ['c' => 'booking', 'w' => $weekId, 'd' => $dayNum, 'p' => '?']],
                    ['text' => '⛔️', 'callback_data' => ['c' => 'booking', 'w' => $weekId, 'd' => $dayNum, 'r' => '1']],
                ],
            ],
        ];

        return [
            'message' => 'Success',
            'send' => [
                [
                    'chatId' => Settings::getMainTelegramId(),
                    'message' => $message,
                    'replyMarkup' => $replyMarkup,
                ],
            ]
        ];
    }

    public static function nickApprove(): array
    {
        if (empty(static::$userData) || empty(static::$arguments))
            throw new Exception(__METHOD__ . ': UserData or Arguments is empty!');

        if (empty(static::$userData['privilege']['status']) || !in_array(static::$userData['privilege']['status'], ['admin', 'root'], true))
            return ['message' => 'You don’t have enough rights to change information about other users!'];

        if (empty(static::$arguments['u']) || empty(static::$arguments['t'])) {

            if (static::$arguments['ci'] != Settings::getMainTelegramId()) {
                $update['message'] = Locale::phrase('Okay! I get it.');
                $update['message'] .= PHP_EOL;
                $update['message'] .= Locale::phrase('I’ll inform the user about your decision😔');
            }

            $message = Locale::phrase('I offer my deepest apologies, but the Administrator has rejected your request.');
            $message .= PHP_EOL;
            $message .= Locale::phrase('Just come up with a new nickname for yourself!');

            return [
                'message' => 'Success',
                'update' => [
                    $update,
                    [
                        'chatId' => (int) static::$arguments['ci'],
                        'messageId' => (int) static::$arguments['mi'],
                        'message' => $message,
                    ]
                ],
            ];
        }

        $uId = (int) trim(static::$arguments['u']);
        $tId = (int) trim(static::$arguments['t']);

        if (empty($uId) || empty($tId))
            throw new Exception(__METHOD__ . ': UserID or TelegramID can’t be empty!');

        $userData = Users::find($uId);
        $thChat = TelegramChats::getChat($tId);
        $contacts = ['telegramid' => $tId, 'telegram' => $thChat['personal']['username']];
        Contacts::reLink($contacts, $uId);
        TelegramChatsRepository::getAndSaveTgAvatar($uId, true);

        if (static::$arguments['ci'] != Settings::getMainTelegramId()) {
            $update['message'] = Locale::phrase('Okay! I get it.');
            $update['message'] .= PHP_EOL;
            $update['message'] .= Locale::phrase('I’ll inform the user about your decision😊');
        }

        $message = Locale::phrase('The administrator has approved your request!');
        $message .= PHP_EOL;
        $message .= Locale::phrase(['string' => 'I’m remember you under nickname <b>%s</b>', 'vars' => [$userData['name']]]);
        $message .= PHP_EOL;
        $message .= Locale::phrase('Nice to meet you!');

        return [
            'message' => 'Success',
            'update' => [
                $update,
                [
                    'chatId' => (int) static::$arguments['ci'],
                    'messageId' => (int) static::$arguments['mi'],
                    'message' => $message,
                ]
            ],
        ];
    }

    /**
     * Init chat callbackQuery command
     */
    public static function init(array $arguments): bool
    {
        foreach ($arguments as $k => $v) {
            static::$$k = $v;
        }
        return true;
    }

    /**
     * Tech block
     */

    public static function parseChatCommand(string $text): string
    {
        $_text = mb_strtolower(str_replace('на ', '', $text), 'UTF-8');
        $days = DayRepository::getDayNamesForCommand();
        if (preg_match("/^([+-])\s{0,3}($days)/ui", $_text, $match) === 1) {
            $arguments['method'] = $match[1];
            $arguments['dayName'] = $match[2];
            if (preg_match('/([0-2][0-9])(:[0-5][0-9]){0,1}/', str_replace('.', ':', $_text), $match) === 1) {
                $arguments['arrive'] = $match[0];
                if (strlen($arguments['arrive']) < 3) {
                    $arguments['arrive'] .= ':00';
                }
            }
            if (preg_match('/\([^)]+\)/', $text, $prim) === 1) {
                $arguments['prim'] = mb_substr($prim[0], 1, -1, 'UTF-8');
            } elseif (preg_match('/\?/', $_text) === 1) {
                $arguments['prim'] = '?';
            }
            ChatCommand::$arguments = $arguments;
            return 'booking';
        }

        if (preg_match('/^[+]\s{0,3}[0-2]{0,1}[0-9]/', $_text) === 1) {
            preg_match('/^(\+)\s{0,3}([0-2]{0,1}[0-9])(:[0-5][0-9]){0,1}/i', mb_strtolower(str_replace('.', ':', $_text), 'UTF-8'), $matches);

            if ($matches[2] < 8 || $matches[2] > 23) return false;
            if (empty($matches[3])) $matches[3] = ':00';

            $arguments['method'] = '+';
            $arguments['dayName'] = 'tod';
            $arguments['arrive'] = $matches[2] . $matches[3];

            if (preg_match('/\([^)]+\)/', $text, $prim) === 1) {
                $arguments['prim'] = mb_substr($prim[0], 1, -1, 'UTF-8');
            }
            ChatCommand::$arguments = $arguments;
            return 'booking';
        }

        if ($text[0] === '/') {
            $command = mb_substr($text, 1, NULL, 'UTF-8');

            $spacePos = mb_strpos($command, ' ', 0, 'UTF-8');
            if ($spacePos !== false) {
                $command = mb_substr($command, 0, $spacePos, 'UTF-8');
            }
            $commandLen = mb_strlen($command);
            $atPos = mb_strpos($command, '@', 0, 'UTF-8'); // at = @ in English context
            if ($atPos !== false) {
                $command = mb_substr($command, 0, $atPos, 'UTF-8');
                $commandLen = $atPos;
            }
            $command = strtolower($command);

            if (in_array($command, ['?', 'help', 'start'])) {
                return 'help';
            }

            if (in_array($command, ['reg', 'set'], true)) {
                $_text = mb_substr($_text, $commandLen + 1, NULL, 'UTF-8');
                $arguments = explode(',', $_text);
                if (preg_match('/\([^)]+\)/', $text, $prim) === 1) {
                    $arguments['prim'] = mb_substr($prim[0], 1, -1, 'UTF-8');
                }
                ChatCommand::$arguments = $arguments;
                return $command;
            }
            $symbols = Locale::$cyrillicPattern;
            preg_match_all("/([a-z$symbols.0-9#-]+)/ui", trim(mb_substr($text, $commandLen + 1, NULL, 'UTF-8')), $matches);

            ChatCommand::$arguments = $matches[0];
            return $command;
        }
        return '';
    }
    public static function parseArguments($arguments): void
    {
        if (isset($arguments['prim'])) {
            ChatCommand::$arguments['prim'] = $arguments['prim'];
            unset($arguments['prim']);
        }
        foreach ($arguments as $value) {
            $value = trim($value);
            if (preg_match('/^[+-][^0-9]/', $value)) {

                ChatCommand::$arguments['method'] = $value[0];
                $withoutMethod = trim(mb_substr($value, 1, 6, 'UTF-8'));
                $dayName = mb_strtolower(mb_substr($withoutMethod, 0, 3, 'UTF-8'), 'UTF-8');

                static::parseDayNum($dayName, ChatCommand::$arguments);
            } elseif (preg_match('/^\d{2}:\d{2}$/', $value) === 1 && empty(ChatCommand::$arguments['arrive'])) {
                ChatCommand::$arguments['arrive'] = $value;
            } elseif (preg_match('/\#(\d)*$/', $value, $match) === 1) {
                $userRegData = Users::find($match[0]);
                if ($userRegData) {
                    ChatCommand::$arguments['userId'] = $userRegData['id'];
                    ChatCommand::$arguments['userName'] = $userRegData['name'];
                }
            } elseif (preg_match('/^(\+|-)\d{1,2}/', $value, $match) === 1) {
                ChatCommand::$arguments['nonames'] = substr($match[0], 1);
            } elseif (ChatCommand::$arguments['userId'] < 2) {
                // $value = str_ireplace(['m', 'c', 'o', 'p', 'x', 'a'], ['м', 'с', 'о', 'р', 'х', 'а'], $value);
                $value = Users::formatName($value);

                if (empty($value)) continue;

                $userRegData = Users::getDataByName($value);
                ChatCommand::$arguments['probableUserName'] = $value;
                if (!empty($userRegData)) {
                    ChatCommand::$arguments['userId'] = $userRegData['id'];
                    ChatCommand::$arguments['userName'] = $userRegData['name'];
                }
            }
        }

        if (empty(ChatCommand::$arguments['currentDay']))
            static::parseDayNum('tod', ChatCommand::$arguments);
    }
    public static function parseDayNum(string $daySlug): bool
    {
        ChatCommand::$arguments['currentDay'] = Days::current();

        $daySlug = mb_strtolower($daySlug, 'UTF-8');
        if (mb_strlen($daySlug, 'UTF-8') > 3) {
            $daySlug = mb_substr($daySlug, 0, 3);
        }
        if (in_array($daySlug, DayRepository::$techDaysArray['today'], true)) {
            ChatCommand::$arguments['dayNum'] = ChatCommand::$arguments['currentDay'];
            return true;
        } elseif (in_array($daySlug, DayRepository::$techDaysArray['tomorrow'], true)) {
            ChatCommand::$arguments['dayNum'] = ChatCommand::$arguments['currentDay'] + 1;

            if (ChatCommand::$arguments['dayNum'] === 7) ChatCommand::$arguments['dayNum'] = 0;

            return true;
        } else {
            foreach (DayRepository::$daysArray as $num => $daysNames) {
                if (in_array($daySlug, $daysNames, true)) {
                    ChatCommand::$arguments['dayNum'] = $num;
                    return true;
                }
            }
        }
        return false;
    }

    public static function hasAccess(string $status = '', string $level = 'all'): bool
    {
        $levels = array_flip(Router::$accessLevels);

        if (empty($status)) $status = 'user';

        if (!static::isDirect() && static::getChatId() != Settings::getMainTelegramId()) {
            $status = $levels[$status] > 1 ? 'trusted' : 'user';
        }
        return $levels[$level] <= $levels[$status];
    }
    public static function getChatId(): int
    {
        return empty(static::$message['callback_query']) ?
            static::$message['message']['chat']['id'] :
            static::$message['callback_query']['message']['chat']['id'];
    }
    public static function isDirect()
    {
        return empty(static::$message['callback_query']) ?
            static::$message['message']['chat']['type'] === 'private' :
            static::$message['callback_query']['message']['chat']['type'] === 'private';
    }
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

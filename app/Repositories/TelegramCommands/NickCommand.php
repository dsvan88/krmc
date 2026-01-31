<?

namespace app\Repositories\TelegramCommands;

use app\core\Telegram\ChatCommand;
use app\core\Locale;
use app\core\Telegram\ChatAction;
use app\core\Validator;
use app\models\Contacts;
use app\models\TelegramChats;
use app\models\Users;
use app\Repositories\AccountRepository;
use app\Repositories\ContactRepository;
use app\Repositories\TelegramBotRepository;
use app\Repositories\TelegramChatsRepository;

class NickCommand extends ChatCommand
{
    public static function description()
    {
        return self::locale('<u>/nick Your nickname</u> <i>// Register your nickname</i>');
    }
    public static function execute()
    {
        if (!empty(self::$requester)) {
            return static::result(['string' => '{{ Tg_Command_Name_Already_Set }}', 'vars' => [self::$requester['name']]]);
        }

        $_username = implode(' ', static::$arguments);

        if (empty(trim($_username))) {
            // return static::result("Your nickname can’t be empty!\nPlease, use that format:\n/nick <b>Your nickname</b>");

            TelegramChatsRepository::setPendingState('nick');

            $message = self::locale('Okay, Im ready to get your beautiful nickname!'). PHP_EOL;
            $message .= self::locale('Your next message - will be your nickname!');
            $replyMarkup = [
                'inline_keyboard' => [
                    [
                        ['text' => '❌' . self::locale('Cancel'), 'callback_data' => ['c' => 'pending', 'p' => 'nick', 'ci' => TelegramBotRepository::getUserTelegramId()]],
                    ],
                ],
            ];
            return [
                'result' => true,
                'reaction' => '👌',
                'send' => [
                    [
                        'message' => $message,
                        'replyMarkup' => $replyMarkup,
                    ]
                ]
            ];
        }

        TelegramChatsRepository::setPendingState();
        
        $username = Validator::validate('name', $_username);

        if (empty($username)) {
            return static::result("Invalid nickname format!\nPlease use <b>Cyrillic</b> or <b>Latin</b> alphabet, <b>spaces</b> and <b>digits</b> in the nickname!");
        }

        if (mb_strlen($username, 'UTF-8') < 2) {
            return static::result("Your nickname is too short!\nPlease use at least <b>2</b> symbols, so people can recognize you!");
        }

        $symbols = Locale::$cyrillicPattern;
        if (preg_match_all("/[^a-z$symbols .0-9]/ui", $_username, $matches)) {
            $wrong = implode('</i>", "<i>', $matches[0]);
            return static::result(['string' => "Invalid nickname format!\nPlease use <b>Cyrillic</b> or <b>Latin</b> alphabet, <b>spaces</b> and <b>digits</b>!\nWrong simbols: %s", 'vars' => ["\"<i>$wrong</i>\""]]);
        }

        $telegramId = ChatAction::$message['message']['from']['id'];
        $telegram = '';
        if (empty(ChatAction::$message['message']['from']['username']))
            $telegram = ChatAction::$message['message']['from']['username'];

        $userExistsData = Users::getDataByName($username);

        if (empty($userExistsData['id'])) {
            $userId = Users::add($username);

            Contacts::new(['telegramid' => $telegramId, 'telegram' => $telegram], $userId);
            TelegramChats::save(ChatAction::$message);
            TelegramChatsRepository::getAndSaveTgAvatar($userId, true);

            $message = self::locale(['string' => "So... we remember you under the nickname <b>%s</b>. Right?\nNice to meet you!", 'vars' => [$username]]) . PHP_EOL;
            $message .= PHP_EOL . self::locale('If you made a mistake, don’t worry, tell the administrator about it and he will quickly fix it😏');

            $replyMarkup = [
                'inline_keyboard' => [
                    [
                        ['text' => '✅' . self::locale('Yes'), 'callback_data' => ['c' => 'nick', 'u' => $userId, 'y' => 1]],
                        ['text' => '❌' . self::locale('No'), 'callback_data' => ['c' => 'nick', 'u' => $userId]],
                    ],
                ],
            ];
            return [
                'result' => true,
                'reaction' => '👌',
                'send' => [
                    [
                        'message' => $message,
                        'replyMarkup' => $replyMarkup,
                    ]
                ]
            ];
        }

        $userContacts = Contacts::getByUserId($userExistsData['id']);

        if (empty($userContacts['telegramid'])) {
            Contacts::new(['telegramid' => $telegramId, 'telegram' => $telegram], $userExistsData['id']);
            TelegramChatsRepository::getAndSaveTgAvatar($userExistsData['id'], true);
            $message = self::locale(['string' => "So... we remember you under the nickname <b>%s</b>. Right?\nNice to meet you!", 'vars' => [$username]]);
            $message .= PHP_EOL . PHP_EOL;
            $message .= self::locale('If you made a mistake, don’t worry, tell the administrator about it and he will quickly fix it😏');
            return static::result($message, '👌', true);
        }

        $userContacts = ContactRepository::formatUserContacts($userContacts);

        $message = self::locale(['string' => '{{ Tg_Command_Name_Already_Set_By_Other }}', 'vars' => [$username]]);

        $isChatExists = TelegramChatsRepository::isChatExists($userContacts['telegramid']);
        $isAvailable = AccountRepository::checkAvailable($userExistsData['id']);

        if (!$isAvailable || $isChatExists) {
            $message .= PHP_EOL;
            $message .= self::locale('If it is your, then contact the Administrators to make changes!');
            return [
                'result' => false,
                'reaction' => '🤷‍♂️',
                'send' => [
                    [
                        'message' => $message,
                    ]
                ]
            ];
        }

        if (!$isChatExists) {
            // $message = self::locale(['string' => 'The nickname <b>%s</b> is already registered by another member of the group!', 'vars' => [$username]]);
            $message .= PHP_EOL;
            $message .= self::locale('But... I can’t find his TelegramID🤷‍♂️');
            $message .= PHP_EOL;
            $message .= self::locale('Is it your?*');
            $message .= PHP_EOL . PHP_EOL;
            $message .= '<i>' . self::locale('*This nickname will be your, after Administrators’s approve.') . '</i>';
            $replyMarkup = [
                'inline_keyboard' => [
                    [
                        ['text' => '✅' . self::locale('Yes'), 'callback_data' => ['c' => 'nickRelink', 'u' => $userExistsData['id'], 't' => $telegramId, 'y' => 1]],
                        ['text' => '❌' . self::locale('No'), 'callback_data' => ['c' => 'nickRelink', 'u' => $userExistsData['id'], 't' => $telegramId]],
                    ],
                ],
            ];
            return [
                'result' => true,
                'reaction' => '🤔',
                'send' => [
                    [
                        'message' => $message,
                        'replyMarkup' => $replyMarkup,
                    ]
                ]
            ];
        }

        // $message = self::locale(['string' => 'The nickname <b>%s</b> is already registered by another member of the group!', 'vars' => [$username]]);
        $message .= PHP_EOL;
        $message .= self::locale('But... We didn’t saw him for quite time🤷‍♂️');
        $message .= PHP_EOL;
        $message .= self::locale('Do you wanna to make it your?*');
        $message .= PHP_EOL . PHP_EOL;
        $message .= '<i>' . self::locale('*This nickname will be your, after Administrators’s approve.') . '</i>';
        $replyMarkup = [
            'inline_keyboard' => [
                [
                    ['text' => '✅' . self::locale('Yes'), 'callback_data' => ['c' => 'nickRelink', 'u' => $userExistsData['id'], 't' => $telegramId, 'y' => 1]],
                    ['text' => '❌' . self::locale('No'), 'callback_data' => ['c' => 'nickRelink', 'u' => $userExistsData['id'], 't' => $telegramId]],
                ],
            ],
        ];
        return [
            'result' => true,
            'reaction' => '🤔',
            'send' => [
                [
                    'message' => $message,
                    'replyMarkup' => $replyMarkup,
                ]
            ]
        ];
    }
}

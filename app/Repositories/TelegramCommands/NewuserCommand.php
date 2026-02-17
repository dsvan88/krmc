<?php

namespace app\Repositories\TelegramCommands;

use app\core\Telegram\ChatCommand;
use app\core\Locale;
use app\core\Validator;
use app\models\Users;
use app\Repositories\TelegramBotRepository;
use app\Repositories\TelegramChatsRepository;

class NewuserCommand extends ChatCommand
{
    public static $accessLevel = 'manager';
    public static function description()
    {
        return self::locale('<u>/newuser Player’s nickname</u> (in Cyrillic) <i>// Register a new nickname in the system.</i>');
    }
    public static function execute()
    {
        if (empty(static::$arguments)){

            TelegramChatsRepository::setPendingState('newuser');

            $message = self::locale('Okay, Im ready to get a nickname of a new user!'). PHP_EOL;
            $message .= self::locale('Your next message - will be his nickname!');
            $replyMarkup = [
                'inline_keyboard' => [
                    [
                        ['text' => '❌' . self::locale('Cancel'), 'callback_data' => ['c' => 'pending', 'p' => 'newuser', 'ci' => TelegramBotRepository::getChatId()]],
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

        $username = '';
        foreach (static::$arguments as $string) {
            $username .= Locale::mb_ucfirst($string) . ' ';
        }

        $username = Validator::validate('name', mb_substr($username, 0, -1, 'UTF-8'));

        if (mb_strlen(trim($username), 'UTF-8') < 2) {
            return static::result('{{ Tg_Command_Name_Too_Short }}');
        }

        if (Users::getId($username) > 0) {
            return static::result(['string' => '{{ Tg_Command_New_User_Already_Set }}', 'vars' => [$username]]);
        }

        $userId = Users::add($username);

        $message = self::locale(['string' => 'The player under the nickname <b>%s</b> is successfully registered in the system!', 'vars' => [$username]]) . PHP_EOL . PHP_EOL;
        $message .= self::locale('Is this correct?');

        $replyMarkup = [
            'inline_keyboard' => [
                [
                    ['text' => '✅' . self::locale('Yes'), 'callback_data' => ['c' => 'newuser', 'u' => $userId, 'y' => 1]],
                    ['text' => '❌' . self::locale('No'), 'callback_data' => ['c' => 'newuser', 'u' => $userId]],
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
}

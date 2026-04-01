<?php

namespace app\Services\TelegramCommands;

use app\core\Telegram\ChatCommand;
use app\core\Locale;
use app\core\Validator;
use app\mappers\SocialPoints;
use app\mappers\Users;
use app\Services\TelegramBotService;
use app\Services\TelegramChatsService;

class RenickCommand extends ChatCommand
{
    public static $accessLevel = 'user';
    public static $costs = 50;

    public static function description()
    {
        return self::locale('<u>/renick Your nickname</u> <i>// Change your nickname</i>');
    }
    public static function execute()
    {
        if (static::$requester->profile->points < static::$costs) {
            return static::result(['string' => 'I’m deeply sorry, but you can’t do this command yet. Social Points isn’t enough. Need <b>%s</b>.', 'vars' => [static::$costs]]);
        }

        $_username = implode(' ', static::$arguments);

        if (empty(trim($_username))) {

            TelegramChatsService::setPendingState('renick');

            $message = self::locale('Okay, Im ready to get your beautiful nickname!') . PHP_EOL;
            $message .= self::locale('Your next message - will be your nickname!');
            $replyMarkup = [
                'inline_keyboard' => [
                    [
                        ['text' => '❌' . self::locale('Cancel'), 'callback_data' => ['c' => 'pending', 'p' => 'renick', 'ci' => TelegramBotService::getChatId()]],
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

        TelegramChatsService::setPendingState();

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

        $userExistsData = Users::getDataByName($username);

        if (empty($userExistsData['id'])) {

            $personal = static::$requester->profile->personal;
            $personal['newName'] = $username;
            Users::edit(['personal' => $personal], ['id' => static::$requester->profile->id]);

            $message = self::locale(['string' => "So... you wanna to change your nickname <b>%s</b> to a nickname <b>%s</b>. Right?", 'vars' => [static::$requester->profile->name, $username]]);

            $replyMarkup = [
                'inline_keyboard' => [
                    [
                        ['text' => '✅' . self::locale('Yes'), 'callback_data' => ['c' => 'renick', 'u' => static::$requester->profile->id, 'y' => 1]],
                        ['text' => '❌' . self::locale('No'), 'callback_data' => ['c' => 'renick', 'u' => static::$requester->profile->id]],
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

        $message = self::locale(['string' => '{{ Tg_Command_New_User_Already_Set }}', 'vars' => [$username]]) . PHP_EOL;
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
}

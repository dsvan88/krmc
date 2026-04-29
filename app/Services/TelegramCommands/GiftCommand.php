<?php

namespace app\Services\TelegramCommands;

use app\core\Entities\User;
use app\core\Telegram\ChatCommand;
use app\core\Locale;
use app\core\Telegram\ChatAction;
use app\core\Validator;
use app\mappers\Contacts;
use app\mappers\Coupons;
use app\mappers\Users;
use app\Services\AccountService;
use app\Services\ContactService;
use app\Services\TelegramBotService;
use app\Services\TelegramChatsService;

class NickCommand extends ChatCommand
{
    public static $accessLevel = 'admin';
    public static ?User $target = null;
    public static string $text = 'User isn’t found';

    public static function description()
    {
        return self::locale('<u>/gift User’s nickname</u> <i>// Present a gift to a user</i>');
    }
    public static function execute()
    {
        if (empty(static::$arguments)) {
            return static::result('User’s nickname can’t be empty');
        }

        if (static::$arguments[0][0] === '#') {
            static::findById(substr(static::$arguments[0], 1));
        } else {
            static::findByName();
        }
        if (empty(static::$target)) {
            return static::result(static::$text);
        }

        Coupons::create();







        // return [
        //     'result' => true,
        //     'reaction' => '🤔',
        //     'send' => [
        //         [
        //             'message' => $message,
        //             'replyMarkup' => $replyMarkup,
        //         ]
        //     ]
        // ];
    }
    public static function findById($id): void
    {
        $user = Users::find($id, true);
        if ($user) {
            self::$target = User::create($user);
        }
        static::$text = static::locale(['string' => 'User #%s is not found', 'vars' => [$id]]);
    }
    public static function findByName():void
    {
        $_username = implode(' ', static::$arguments);
        $username = Validator::validate('name', $_username);

        if (!$username){
            static::$text = static::locale('User’s nickname can’t be empty');
            return;
        }
        static::$target = User::fromArray(Users::getDataByName($username)); 
    }


                
}

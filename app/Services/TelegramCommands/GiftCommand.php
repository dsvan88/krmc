<?php

namespace app\Services\TelegramCommands;

use app\core\Entities\User;
use app\core\Telegram\ChatCommand;
use app\core\Validator;
use app\Formatters\TelegramBotFormatter;
use app\mappers\Users;

class NickCommand extends ChatCommand
{
    public static $accessLevel = 'manager';
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
        $message = static::locale(['string' => 'Your amount of Social Points is: <b>%s</b>SP', 'vars' => [static::$requester->profile->points]]) . PHP_EOL;
        $message .= static::locale('Choose a coupons:');
        $replyMarkup = TelegramBotFormatter::getCouponsListGiftMarkup(static::$target->id);
        $replyMarkup['inline_keyboard'][] = [['text' => self::locale('Done'), 'callback_data' => ['c' => 'close', 'u' => static::$requester->profile->id]]];

        $update = [
            'message' => $message,
            'replyMarkup' => $replyMarkup,
        ];
        return array_merge(static::result('Success', true, true), ['update' => [$update]]);
    }
    public static function findById(int $id = 0): void
    {
        if (empty($id))
            static::$text = static::locale(['string' => 'User #%s is not found', 'vars' => [$id]]);

        self::$target = User::create($id);

        if (empty(static::$target))
            static::$text = static::locale(['string' => 'User #%s is not found', 'vars' => [$id]]);
    }
    public static function findByName(): void
    {
        $username = implode(' ', static::$arguments);
        $username = Validator::validate('name', $username);

        if (!$username) {
            static::$text = static::locale('User’s nickname can’t be empty');
            return;
        }

        static::$target = User::fromArray(Users::getDataByName($username));

        if (empty(static::$target))
            static::$text = static::locale(['string' => 'User "%s" is not found', 'vars' => [$username]]);
    }
}

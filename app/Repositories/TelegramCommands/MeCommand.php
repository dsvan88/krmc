<?php

namespace app\Repositories\TelegramCommands;

use app\core\Telegram\ChatCommand;
use app\models\SocialPoints;
use app\Repositories\TelegramBotRepository;

class MeCommand extends ChatCommand
{
    public static $accessLevel = 'user';

    public static function description()
    {
        return self::locale('<u>/me</u> <i>// Get information about your profile.</i>');
    }
    public static function execute()
    {
        $gender = static::$requester->profile->gender ?? '';
        // Mr./Ms./Mrs.
        if (empty($gender) || $gender === 'secret') {
            $gender = 'Mr.(Ms.|Mrs.)';
        }
        $message = self::locale(ucfirst($gender)) . ' <b>' . static::$requester->profile->name . (static::$requester->profile->emoji ?? '') . '</b>!' . PHP_EOL;
        $message .= self::locale('Here is your profile’s info:') . PHP_EOL;
        $message .= self::locale(['string' => 'Your summ of social points is: <b>%s</b>', 'vars' => [static::$requester->profile->points ?? 0]]);

        return static::result($message, '👌', true, TelegramBotRepository::getMessageId());
    }
}

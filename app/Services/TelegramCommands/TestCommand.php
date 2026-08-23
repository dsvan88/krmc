<?php

namespace app\Services\TelegramCommands;

use app\core\Telegram\ChatCommand;

class TestCommand extends ChatCommand
{
    public static $accessLevel = 'admin';
    public static function description()
    {
        return self::locale('<u>/test</u> //<i>Command for testing new functions.</i>');
    }
    public static function execute()
    {
        return static::result('Done!', '🤔', true);
    }
}

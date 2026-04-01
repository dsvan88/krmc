<?php

namespace app\Services\TelegramCommands;

use app\core\Telegram\ChatCommand;
use app\mappers\Contacts;
use app\mappers\TelegramChats;
use app\mappers\Users;
use app\Services\AccountService;
use app\Services\ContactService;
use app\Services\TelegramChatsService;

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

<?

namespace app\Repositories\TelegramCommands;

use app\core\Telegram\ChatCommand;
use app\models\Contacts;
use app\models\TelegramChats;
use app\models\Users;
use app\Repositories\AccountRepository;
use app\Repositories\ContactRepository;
use app\Repositories\TelegramChatsRepository;

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

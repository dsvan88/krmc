<?

namespace app\Repositories\TelegramCommands;

use app\core\ChatCommand;
use app\models\Contacts;
use app\models\Users;
use app\Repositories\ContactRepository;

class TestCommand extends ChatCommand
{
    public static $accessLevel = 'admin';
    public static function description()
    {
        return self::locale('<u>/test</u> //<i>Command for testing new functions.</i>');
    }
    public static function execute(array $arguments = [], string &$message = '', string &$reaction = '', array &$replyMarkup = [])
    {
        
    }
}

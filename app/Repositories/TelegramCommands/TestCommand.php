<?

namespace app\Repositories\TelegramCommands;

use app\core\ChatCommand;

class TestCommand extends ChatCommand
{
    public static $accessLevel = 'root';
    public static function description()
    {
        return self::locale('<u>/test</u> //<i>Command for testing new functions.</i>');
    }
    public static function execute(array $arguments = [])
    {
        self::$operatorClass::$resultMessage = json_encode(self::$message, JSON_UNESCAPED_UNICODE);
        return true;
    }
}

<?

namespace app\Repositories\TelegramCommands;

use app\core\ChatCommand;

class HelpCommand extends ChatCommand
{
    public static $accessLevel = 'user';
    public static function description()
    {
        return self::locale('<u>/?</u> or <u>/start</u> or <u>/help</u> <i>// This help menu</i>');
    }
    public static function execute(array $arguments = [])
    {
        $message = self::locale('{{ Tg_Command_Help }}');

        if (self::$message['message']['chat']['type'] === 'private' && in_array(self::$requester['privilege']['status'], ['manager', 'admin'])) {
            $message .= self::locale('{{ Tg_Command_Help_Admin }}');
        }
        self::$operatorClass::$resultMessage = $message;
        return true;
    }
}

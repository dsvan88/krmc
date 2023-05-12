<?
namespace app\Repositories\TelegramCommands;

use app\core\ChatCommand;

class HelpCommand extends ChatCommand {
    public static function description(){
        return self::locale('<u>/nick Your nickname</u> (Cyrillic) <i>// Register your nickname</i>');
    }
    public static function execute(array $arguments=[]){
        $message = self::locale('{{ Tg_Command_Help }}');

        if (self::$message['message']['chat']['type'] === 'private' && in_array(self::$requester['privilege']['status'], ['manager', 'admin'])) {
            $message .= self::locale('{{ Tg_Command_Help_Admin }}');
        }
        return [true, $message];
    }
}
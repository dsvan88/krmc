<?

namespace app\Repositories\TelegramCommands;

use app\core\ChatCommand;

class TestCommand extends ChatCommand
{
    public static $accessLevel = 'admin';
    public static function description()
    {
        return self::locale('<u>/test</u> //<i>Command for testing new functions.</i>');
    }
    public static function execute(array $arguments = [], string &$message = '', string &$reaction = '', array &$replyMarkup = [])
    {
        $message = json_encode(self::$message, JSON_UNESCAPED_UNICODE);
        $weekId = 123;
        $dayNum = 5;
        $replyMarkup = [
            'inline_keyboard' => [ 
                    [
                        ['text' => self::locale('I will too!'), 'callback_data' => base64_encode(json_encode(['cmd' => 'booking', 'wId'=> $weekId, 'dNum' => $dayNum]))],
                        ['text' => self::locale('I will too! I hope...'), 'callback_data' => base64_encode(json_encode(['cmd' => 'booking', 'wId'=> $weekId, 'dNum' => $dayNum, 'prim' => '?']))],
                    ],
                ],
            ];
        $reaction = '👌';
        return true;
    }
}

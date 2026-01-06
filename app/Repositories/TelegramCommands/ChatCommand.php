<?

namespace app\Repositories\TelegramCommands;

use app\core\Telegram\ChatCommand as TgChatCommand;
use app\Repositories\TelegramBotRepository;

class ChatCommand extends TgChatCommand
{
    public static $accessLevel = 'admin';
    public static function description()
    {
        return self::locale("<u>/chat (type: main | admin | log)</u> <i>// Mark current chat as Main chat, Admin chat or Tech Log chat. Leave clear if you wanna to get options</i>");
    }
    public static function execute()
    {
        if (empty(static::$arguments)){
            return [
                'result' => true,
                'reaction' => '👌',
                'send'  => [
                    'message' => self::locale('Choose chats type:'),
                    'inline_keyboard' => [
                        [['text' => 'Main - main group chat', 'callback_data' => ['c'=> 'chat', 't' => 'main']]],
                        [['text' => 'Admin - admin group chat', 'callback_data' => ['c'=> 'chat', 't' => 'admin']]],
                        [['text' => 'Log - tech log chat', 'callback_data' => ['c'=> 'chat', 't' => 'log']]],
                    ]
                ]
            ];
        }
        $type = trim(static::$arguments[0]);

        if (!in_array($type, ['main', 'admin', 'log', 'tech'], true)){
            return static::result('Please, use one of next types: main, admin or log. Or leave field empty.');
        }
        // TelegramBotRepository

        return static::result('Success', '👌', true);
    }
}

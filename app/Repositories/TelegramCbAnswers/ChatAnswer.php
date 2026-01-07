<?

namespace app\Repositories\TelegramCommands;

use app\core\Telegram\ChatAnswer as TgChatAnswer;
use app\models\Days;
use app\models\Settings;
use app\models\Weeks;
use app\Repositories\TelegramBotRepository;
use app\Repositories\TelegramChatsRepository;
use Exception;

class ChatAnswer extends TgChatAnswer
{
    public static function execute(): array
    {
        if (empty(static::$requester) || empty(static::$arguments))
            throw new Exception(__METHOD__ . ': UserData or Arguments is empty!');

        if (empty(static::$requester['privilege']['status']) || !in_array(static::$requester['privilege']['status'], ['admin', 'root'], true))
            return static::result('You don’t have enough rights!');

        $type = trim(static::$arguments['t']);

        if (!in_array($type, ['main', 'admin', 'log', 'tech'], true)) {
            return static::result('Please, use one of next types: main, admin or log.');
        }

        TelegramChatsRepository::setChatsType(TelegramBotRepository::getChatId(), $type);
        $update = [
            'message' => static::locale(['string' => 'Current chat is successfully marked as <b>%s</b>.', 'vars' => [ucfirst($type)]]),
        ];
        return array_merge(static::result('Success', true), ['update' => $update]);
    }
}

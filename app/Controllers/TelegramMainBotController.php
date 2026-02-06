<?php

namespace app\Controllers;

use app\core\Controller;
use app\core\Locale;
use app\core\Sender;
use app\core\Telegram\ChatAction;
use app\core\TelegramBot;
use app\core\Validator;
use app\models\Contacts;
use app\models\Settings;
use app\models\TelegramChats;
use app\models\Users;
use app\Repositories\TelegramBotRepository;
use app\Repositories\TelegramChatsRepository;
use Exception;

class TelegramMainBotController extends TelegramBotController
{
    public static $guestCommands = ['help', 'booking', 'nick', 'nickRelink', 'week', 'day', 'today', 'pending'];
    public static $CommandNamespace = '\\app\\Repositories\\TelegramCommands';
    public static $AnswerNamespace = '\\app\\Repositories\\TelegramCbAnswers';

    public static function resolveResult(array $result = []): int
    {
        $messageId = parent::resolveResult($result);

        if (empty($messageId)) return 0;

        if (static::$command === 'week') {
            static::unpinWeekMessage();
            static::pinMessage($messageId);
        } elseif (in_array(static::$command, ['booking', 'reg', 'set', 'recall', 'promo'], true)) {
            static::updateWeekMessages();
        }

        return $messageId;
    }
    public static function updateWeekMessages(): bool
    {
        static::$type = 'message';
        $result = static::execute('week');

        if (!$result['result']) return false;

        $message = $result['send'][0]['message'];
        $chatData = TelegramChats::getChatsWithPinned();
        foreach ($chatData as $chatId => $pinned) {
            Sender::edit($chatId, $pinned, $message);

            if (Sender::$operator::$result['ok'] || Sender::$operator::$result['error_code'] != 400) continue;

            // Clear saved pinned message if not found in the chat.
            TelegramChats::clearPinned($chatId);
        }

        return true;
    }
    public static function pinMessage(int $messageId = 0)
    {
        if (empty($messageId)) return false;

        Sender::pin(static::$chatId, $messageId);
        TelegramChats::savePinned(ChatAction::$message, $messageId);

        return true;
    }
    public static function unpinWeekMessage()
    {
        $pinned = TelegramChats::getPinnedMessage(static::$chatId);

        if (empty($pinned)) return false;

        Sender::unpin(static::$chatId, $pinned);

        return true;
    }
}

<?php

namespace app\Services\TelegramCbAnswers;

use app\core\Telegram\ChatAnswer as TgChatAnswer;
use app\Services\TelegramBotService;
use app\Services\TelegramChatsService;
use Exception;

class ChatAnswer extends TgChatAnswer
{

    public static $accessLevel = 'admin';
    public static function execute(): array
    {
        if (empty(static::$requester) || empty(static::$arguments))
            throw new Exception(__METHOD__ . ': UserData or Arguments is empty!');

        $type = trim(static::$arguments['t']);

        if (!in_array($type, ['main', 'admin', 'log', 'tech'], true)) {
            return static::result('Please, use one of next types: main, admin or log.');
        }

        TelegramChatsService::setChatsType(TelegramBotService::getChatId(), $type);
        $update = [
            'message' => static::locale(['string' => 'Current chat is successfully marked as <b>%s</b>.', 'vars' => [ucfirst($type)]]),
        ];
        return array_merge(static::result('Success', true), ['update' => $update]);
    }
}

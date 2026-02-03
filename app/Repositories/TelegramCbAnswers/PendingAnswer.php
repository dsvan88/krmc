<?php

namespace app\Repositories\TelegramCbAnswers;

use app\core\Telegram\ChatAnswer;
use app\Repositories\TelegramBotRepository;
use app\Repositories\TelegramChatsRepository;
use Exception;

class PendingAnswer extends ChatAnswer
{
    public static function execute():array
    {
        if (empty(static::$arguments['ci']))
            throw new Exception(__METHOD__ . ': ChatId is empty');

        $cId = (int) trim(static::$arguments['ci']);

        $status = empty(static::$requester['privilege']['status']) ? '' : static::$requester['privilege']['status'];
        if ($cId !== TelegramBotRepository::getUserTelegramId() && !in_array($status, ['admin', 'root'], true)){
            return static::result('You don’t have enough rights to change information about other users!');
        }

        $param = trim(static::$arguments['p']);

        if ($param !== TelegramChatsRepository::isPendingState($cId)){
            $update = [
                'message' => 'This command is expired.',
            ];
            return array_merge(static::result('This command is expired.'), ['update' => [$update]]);
        }

        TelegramChatsRepository::clearUserPendingState($cId);

        $update = [
                'message' => 'This command is canceled.',
            ];
        return array_merge(static::result('Okay', true, true), ['update' => [$update]]);
    }
}
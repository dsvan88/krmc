<?php

namespace app\Repositories\TelegramCbAnswers;

use app\core\Telegram\ChatAnswer;

class CancelAnswer extends ChatAnswer
{
    public static $accessLevel = 'user';

    public static function execute(): array
    {
        $userId = (int) trim(static::$arguments['u']);
        if ($userId != static::$requester->profile->id || !in_array(static::$requester->profile->status, ['manager', 'admin', 'root'], true))
            return static::result('You don’t have enough rights to change information about other users!', false, true);
        
        $update = ['message' => '<i>'.static::locale('This action is canceled.').'</i>'];
        return array_merge(static::result('Success', true), ['update' => [$update]]);
    }
}

<?php

namespace app\Repositories\TelegramCbAnswers;

use app\core\Telegram\ChatAnswer;
use app\models\Days;
use app\Repositories\TelegramBotRepository;
use Exception;

class UnregAnswer extends ChatAnswer
{
    public static $accessLevel = 'manager';

    public static function execute(): array
    {
        if (empty(static::$arguments))
            throw new Exception(__METHOD__ . ': Arguments is empty.');

        $weekId = (int) trim(static::$arguments['w']);
        $dayId = (int) trim(static::$arguments['d']);

        $userId = 0;
        if (static::$arguments['u'])
            $userId = (int) trim(static::$arguments['u']);

        if (empty($userId))
            return static::participantsMenu($weekId, $dayId);

        Days::removeParticipant($weekId, $dayId, $userId);

        return static::participantsMenu($weekId, $dayId);
    }
    public static function participantsMenu(int $weekId, int $dayId)
    {
        $message = 'Choose a participant to UnReg:';
        $replyMarkup = TelegramBotRepository::getPaticipantsListMarkup('unreg', $weekId, $dayId);
        $replyMarkup['inline_keyboard'][] = [['text' => self::locale('Done'), 'callback_data' => ['c' => 'close', 'u' => static::$requester->profile->id]]];
        $update = [
            'message' => $message,
            'replyMarkup' => $replyMarkup,
        ];
        return array_merge(static::result('Success', true), ['update' => [$update]]);
    }
}

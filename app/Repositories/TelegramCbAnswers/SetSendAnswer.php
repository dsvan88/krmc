<?php

namespace app\Repositories\TelegramCbAnswers;

use app\core\Telegram\ChatAnswer;
use app\models\Days;
use app\models\Settings;
use app\models\Weeks;
use app\Repositories\TelegramBotRepository;
use Exception;

class SetSendAnswer extends ChatAnswer
{
    public static function execute():array
    {
        if (empty(static::$requester) || empty(static::$arguments))
            throw new Exception(__METHOD__ . ': UserData or Arguments is empty!');

        if (!in_array(static::$requester->profile->status, ['manager', 'admin', 'root'], true))
            return static::result('You don’t have enough rights!');

        $weekId = (int) trim(static::$arguments['w']);
        $dayNum = (int) trim(static::$arguments['d']);

        $message = Days::getFullDescription(Weeks::weekDataById($weekId), $dayNum);

        $replyMarkup = TelegramBotRepository::getBookingMarkup($weekId, $dayNum);

        $send = [
            'chatId' => Settings::getMainTelegramId(),
            'message' => $message,
            'replyMarkup' => $replyMarkup,
        ];
        return array_merge(static::result('Success', true), ['send' => [$send]]);
    }
}
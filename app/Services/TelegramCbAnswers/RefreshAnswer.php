<?php

namespace app\Services\TelegramCbAnswers;

use app\core\Entities\Day;
use app\core\Telegram\ChatAnswer;
use app\Formatters\DayFormatter;
use app\Formatters\TelegramBotFormatter;
use Exception;

class RefreshAnswer extends ChatAnswer
{
    public static function execute(): array
    {
        if (empty(static::$requester) || empty(static::$arguments))
            throw new Exception(__METHOD__ . ': UserData or Arguments is empty!');

        $weekId = (int) trim(static::$arguments['w']);
        $dayNum = (int) trim(static::$arguments['d']);

        $message = DayFormatter::forMessengers(Day::create($dayNum, $weekId));

        $replyMarkup = TelegramBotFormatter::getBookingMarkup($weekId, $dayNum, false, true);

        $update = [
            'message' => $message,
            'replyMarkup' => $replyMarkup,
        ];
        return array_merge(static::result('Success', true), ['update' => [$update]]);
    }
}

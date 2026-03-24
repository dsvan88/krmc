<?php

namespace app\Services\TelegramCbAnswers;

use app\core\Entities\Day;
use app\core\Telegram\ChatAnswer;
use app\Formatters\DayFormatter;
use app\Formatters\TelegramBotFormatter;
use app\models\Settings;
use Exception;

class ResendAnswer extends ChatAnswer
{
    public static $accessLevel = 'manager';

    public static function execute(): array
    {
        if (empty(static::$arguments))
            throw new Exception(__METHOD__ . ': Arguments is empty!');

        $weekId = (int) trim(static::$arguments['w']);
        $dayNum = (int) trim(static::$arguments['d']);

        $message = DayFormatter::forMessengers(Day::create($dayNum, $weekId));

        $replyMarkup = TelegramBotFormatter::getBookingMarkup($weekId, $dayNum, false, true);

        $send = [
            'chatId' => Settings::getMainTelegramId(),
            'message' => $message,
            'replyMarkup' => $replyMarkup,
            'replyOn' => 0,
        ];
        return array_merge(static::result('Success', true), ['send' => [$send]]);
    }
}

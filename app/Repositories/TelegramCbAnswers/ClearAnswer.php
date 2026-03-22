<?php

namespace app\Repositories\TelegramCbAnswers;

use app\core\Entities\Day;
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

        Day::$once = true;
        $day = Day::create($dayId, $weekId);

        if (empty($day))
            throw new Exception(__METHOD__.' $day can’t be empty.');

        $update = [
            'message' => static::locale('This day’s settings have been cleared.'),
        ];

        $day->clear();
        $day->save();

        return array_merge(static::result('Success', true), ['update' => [$update]]);
    }
}

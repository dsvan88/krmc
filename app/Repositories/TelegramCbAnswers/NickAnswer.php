<?php

namespace app\Repositories\TelegramCbAnswers;

use app\core\Telegram\ChatAnswer;
use app\models\Users;
use app\Repositories\DayRepository;
use app\Repositories\TelegramBotRepository;
use app\Repositories\WeekRepository;
use Exception;

class NickAnswer extends ChatAnswer
{
    public static function execute():array
    {
        if (empty(static::$arguments))
            throw new Exception(__METHOD__ . ': Arguments is empty');

        if (empty(static::$requester)) {
            return static::result('You don’t have enough rights to change information about other users!');
        }

        $uId = (int) trim(static::$arguments['u']);

        if (empty($uId))
            throw new Exception(__METHOD__ . ': UserID can’t be empty!');

        if (static::$requester->profile->id != $uId && !in_array(static::$requester->profile->status, ['manager', 'admin', 'root'], true))
            return static::result('You don’t have enough rights to change information about other users!');

        if (empty(static::$arguments['y'])) {

            Users::delete($uId);

            $update = [
                'message' => static::locale(['string' => "Okay! Let’s try again!\nUse the next command to register your nickname:\n/nick <b>%s</b>\n\nTry to avoid characters of different languages.", 'vars' => [static::$requester->profile->name]])
            ];
        } else {
            $update = [
                'message' =>
                static::locale(['string' => "<b>%s</b>, nice to meet you!\nYou successfully registered in our system!", 'vars' => [static::$requester->profile->name]]) .
                    PHP_EOL . PHP_EOL .
                    static::locale('If you made a mistake, don’t worry, tell the administrator about it and he will quickly fix it😏'),
            ];
            $records = DayRepository::findBookedDays('_'.TelegramBotRepository::getUserTelegramId(), 5);
            if (!empty($records)){
                DayRepository::changeParticipantId($records, $uId);
            }
        }

        return array_merge(static::result('Okay', true), ['update' => [$update]]);
    }
}
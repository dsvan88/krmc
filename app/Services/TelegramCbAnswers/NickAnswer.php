<?php

namespace app\Services\TelegramCbAnswers;

use app\core\Telegram\ChatAnswer;
use app\models\Users;
use app\Services\DayService;
use app\Services\TelegramBotService;
use app\Services\WeekService;
use Exception;

class NickAnswer extends ChatAnswer
{
    public static $accessLevel = 'user';
    public static function execute():array
    {
        if (empty(static::$arguments))
            throw new Exception(__METHOD__ . ': Arguments is empty');

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
            if (empty($user)){
                $update = [
                    'message' => static::locale('Can’t find a user with such criteria in our system.'),
                ];
                return array_merge(static::result('Fail', true), ['update' => [$update]]);
            }
            $update = [
                'message' =>
                static::locale(['string' => "<b>%s</b>, nice to meet you!\nYou successfully registered in our system!", 'vars' => [static::$requester->profile->name]]) .
                    PHP_EOL . PHP_EOL .
                    static::locale('If you made a mistake, don’t worry, tell the administrator about it and he will quickly fix it😏'),
            ];

            $records = DayService::findBookedDays('_'.TelegramBotService::getUserTelegramId(), 5);
            if (!empty($records)){
                DayService::changeParticipantId($records, $uId);
            }
        }

        return array_merge(static::result('Okay', true), ['update' => [$update]]);
    }
}
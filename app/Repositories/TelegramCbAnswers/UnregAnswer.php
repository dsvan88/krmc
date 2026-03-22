<?php

namespace app\Repositories\TelegramCbAnswers;

use app\core\Entities\Day;
use app\core\Telegram\ChatAnswer;
use app\Formatters\TelegramBotFormatter;
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

        Day::$once = true;
        $day = Day::create($dayId, $weekId);

        if (empty($day))
            throw new Exception(__METHOD__.' $day can’t be empty.');

        $index = -1;
        foreach($day->participants as $i=>$p){
            if ($p['id'] != $userId) continue;
            $index = $i;
            break;
        }

        if ($index === -1)
            return static::participantsMenu($weekId, $dayId);;

        $day->removeParticipant($index);
        $day->save();

        return static::participantsMenu($weekId, $dayId);
    }
    public static function participantsMenu(int $weekId, int $dayId)
    {
        $message = 'Choose a participant to UnReg:';
        $replyMarkup = TelegramBotFormatter::getPaticipantsListMarkup('unreg', $weekId, $dayId);
        $replyMarkup['inline_keyboard'][] = [['text' => self::locale('Done'), 'callback_data' => ['c' => 'close', 'u' => static::$requester->profile->id]]];
        $update = [
            'message' => $message,
            'replyMarkup' => $replyMarkup,
        ];
        return array_merge(static::result('Success', true), ['update' => [$update]]);
    }
}

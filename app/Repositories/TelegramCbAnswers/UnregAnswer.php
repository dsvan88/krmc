<?php

namespace app\Repositories\TelegramCbAnswers;

use app\core\Telegram\ChatAnswer;
use app\models\Days;
use app\models\Settings;
use app\models\Weeks;
use app\Repositories\TelegramBotRepository;
use Exception;

class ResendAnswer extends ChatAnswer
{
    public static $accessLevel = 'manager';

    public static function execute(): array
    {
        if (empty(static::$arguments))
            throw new Exception(__METHOD__ . ': UserData or Arguments is empty!');

        if (!empty(static::$arguments['d'])){
            $update = ['message' => 'Done'];
            return array_merge(static::result('Success', true), ['update' => [$update]]);
        }

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
    public static function participantsMenu(int $weekId, int $dayId){
        $message = 'Choose a participant to UnReg:';
        $replyMarkup = TelegramBotRepository::getPaticipantsListMarkup('unreg', $weekId, $dayId);
        $replyMarkup['inline_keyboard'][] = [['text' => self::locale('Done'), 'callback_data' => ['c' => 'unreg', 'd' => 1]]];
        $update = [
            'message' => $message,
            'replyMarkup' => $replyMarkup,
        ];
        return array_merge(static::result('Success', true), ['update' => [$update]]);
    }
}

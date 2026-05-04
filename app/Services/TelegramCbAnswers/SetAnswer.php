<?php

namespace app\Services\TelegramCbAnswers;

use app\core\Entities\Day;
use app\core\Telegram\ChatAnswer;
use app\Formatters\TelegramBotFormatter;
use Exception;

class SetAnswer extends ChatAnswer
{
    public static $accessLevel = 'manager';

    public static function execute(): array
    {
        if (empty(static::$arguments))
            throw new Exception(__METHOD__ . ': Arguments is empty.');

        $weekId = (int) trim(static::$arguments['w']);
        $dayId = (int) trim(static::$arguments['d']);
        $requesterId = (int) trim(static::$arguments['r']);

        if (static::$requester->profile->id != $requesterId)
            return static::result('You can’t to use commands of others!');

        $pName = trim(static::$arguments['p'] ?? '');
        $pValue = trim(static::$arguments['v'] ?? '');

        if (!empty($pName) && !empty($pValue)) {
            $day = Day::create($dayId, $weekId);

            if (empty($day))
                throw new Exception(__METHOD__ . ' $day can’t be empty.');

            $day->$pName = $pValue;
            $day->save();

            return static::result('Success', true);
        }

        if (empty($pValue)) {
            $menu = $pName . 'Menu';
            return static::$menu($weekId, $dayId, $requesterId);
        }

        return static::result('Success', true);
    }
    public static function gameMenu(int $weekId, int $dayId)
    {
        // $message = 'Choose a participant to UnReg:';
        // $replyMarkup = TelegramBotFormatter::getPaticipantsListMarkup('unreg', $weekId, $dayId);
        // $replyMarkup['inline_keyboard'][] = [['text' => self::locale('Done'), 'callback_data' => ['c' => 'close', 'u' => static::$requester->profile->id]]];
        // $update = [
        //     'message' => $message,
        //     'replyMarkup' => $replyMarkup,
        // ];
        // return array_merge(static::result('Success', true), ['update' => [$update]]);
    }
    public static function modsMenu(int $weekId, int $dayId)
    {
        // $message = 'Choose a participant to UnReg:';
        // $replyMarkup = TelegramBotFormatter::getPaticipantsListMarkup('unreg', $weekId, $dayId);
        // $replyMarkup['inline_keyboard'][] = [['text' => self::locale('Done'), 'callback_data' => ['c' => 'close', 'u' => static::$requester->profile->id]]];
        // $update = [
        //     'message' => $message,
        //     'replyMarkup' => $replyMarkup,
        // ];
        // return array_merge(static::result('Success', true), ['update' => [$update]]);
    }
    public static function timeMenu(int $weekId, int $dayId)
    {
        // $message = 'Choose a participant to UnReg:';
        // $replyMarkup = TelegramBotFormatter::getPaticipantsListMarkup('unreg', $weekId, $dayId);
        // $replyMarkup['inline_keyboard'][] = [['text' => self::locale('Done'), 'callback_data' => ['c' => 'close', 'u' => static::$requester->profile->id]]];
        // $update = [
        //     'message' => $message,
        //     'replyMarkup' => $replyMarkup,
        // ];
        // return array_merge(static::result('Success', true), ['update' => [$update]]);
    }
}

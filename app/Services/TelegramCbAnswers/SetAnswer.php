<?php

namespace app\Services\TelegramCbAnswers;

use app\core\Entities\Day;
use app\core\Telegram\ChatAnswer;
use app\Formatters\TelegramBotFormatter;
use Exception;

class SetAnswer extends ChatAnswer
{
    public static $accessLevel = 'manager';
    public static $dayId = -1;
    public static $weekId = 0;

    public static function execute(): array
    {
        if (empty(static::$arguments))
            throw new Exception(__METHOD__ . ': Arguments is empty.');
        
        $requesterId = (int) trim(static::$arguments['r']);

        if (static::$requester->profile->id != $requesterId)
            return static::result('You can’t to use commands of others!');

        if (empty(static::$arguments['w'])){
            return static::daysMenu();
        }

        static::$weekId = (int) trim(static::$arguments['w']);
        static::$dayId = (int) trim(static::$arguments['d']);

        $pName = trim(static::$arguments['p'] ?? '');
        $pValue = trim(static::$arguments['v'] ?? '');

        if (!empty($pName) && !empty($pValue)) {
            $day = Day::create(static::$dayId, static::$weekId);

            if (empty($day))
                throw new Exception(__METHOD__ . ' $day can’t be empty.');

            if (!property_exists($day, $pName))
                throw new Exception(__METHOD__ . " property $pName doesn't exist in Day class.");

            if ($pName === 'mods') {
                $day->toggleMod($pValue);
            } else {
                $day->$pName = $pValue;
            }

            $day->save();

            $menu = $pName . 'Menu';
            return static::$menu();
        }

        if (empty($pName)) {
            return static::dayParamsMenu();
        }

        if (empty($pValue)) {
            $menu = $pName . 'Menu';
            return static::$menu();
        }

        return static::result('Fail', true);
    }
    private static function dayParamsMenu()
    {
        $message = 'Choose a parameter for a day to set:';
        $replyMarkup = TelegramBotFormatter::getDayParamsMarkup(static::$weekId, static::$dayId);
        $replyMarkup['inline_keyboard'][] = [['text' => self::locale('Done'), 'callback_data' => ['c' => 'close', 'u' => static::$requester->profile->id]]];
        $replyMarkup['inline_keyboard'][] = [['text' => self::locale('<< Back'), 'callback_data' => ['c' => 'set', 'r' => static::$requester->profile->id]]];
        $update = [
            'message' => $message,
            'replyMarkup' => $replyMarkup,
        ];
        return array_merge(static::result('Success', true), ['update' => [$update]]);
    }
    private static function gameMenu()
    {
        $message = 'Choose a game for a day:';
        $replyMarkup = TelegramBotFormatter::getGamesListMarkup(static::$weekId, static::$dayId);
        $replyMarkup['inline_keyboard'][] = [['text' => self::locale('Done'), 'callback_data' => ['c' => 'close', 'u' => static::$requester->profile->id]]];
        $replyMarkup['inline_keyboard'][] = [['text' => self::locale('<< Back'), 'callback_data' => ['c' => 'set', 'w' => static::$weekId, 'd' => static::$dayId, 'r' => static::$requester->profile->id]]];
        $update = [
            'message' => $message,
            'replyMarkup' => $replyMarkup,
        ];
        return array_merge(static::result('Success', true), ['update' => [$update]]);
    }
    private static function modsMenu()
    {
        $message = 'Choose a mods for a game:';
        $replyMarkup = TelegramBotFormatter::getModsListMarkup(static::$weekId, static::$dayId);
        $replyMarkup['inline_keyboard'][] = [['text' => self::locale('Done'), 'callback_data' => ['c' => 'close', 'u' => static::$requester->profile->id]]];
        $replyMarkup['inline_keyboard'][] = [['text' => self::locale('<< Back'), 'callback_data' => ['c' => 'set', 'w' => static::$weekId, 'd' => static::$dayId, 'r' => static::$requester->profile->id]]];
        $update = [
            'message' => $message,
            'replyMarkup' => $replyMarkup,
        ];
        return array_merge(static::result('Success', true), ['update' => [$update]]);
    }
    private static function timeMenu()
    {
        $message = 'Choose a time for a day’s start:';
        $replyMarkup = TelegramBotFormatter::getDayTimesListMarkup(static::$weekId, static::$dayId);
        $replyMarkup['inline_keyboard'][] = [['text' => self::locale('Done'), 'callback_data' => ['c' => 'close', 'u' => static::$requester->profile->id]]];
        $replyMarkup['inline_keyboard'][] = [['text' => self::locale('<< Back'), 'callback_data' => ['c' => 'set', 'w' => static::$weekId, 'd' => static::$dayId, 'r' => static::$requester->profile->id]]];
        $update = [
            'message' => $message,
            'replyMarkup' => $replyMarkup,
        ];
        return array_merge(static::result('Success', true), ['update' => [$update]]);
    }
    private static function daysMenu()
    {
        $message = 'Choose a day:';
        $replyMarkup = TelegramBotFormatter::getForwardDaysListMarkup('set', true);
        $replyMarkup['inline_keyboard'][] = [['text' => self::locale('Done'), 'callback_data' => ['c' => 'close', 'u' => static::$requester->profile->id]]];
        $update = [
            'message' => $message,
            'replyMarkup' => $replyMarkup,
        ];
        return array_merge(static::result('Success', true), ['update' => [$update]]);
    }
}

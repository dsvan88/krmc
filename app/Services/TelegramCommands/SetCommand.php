<?php

namespace app\Services\TelegramCommands;

use app\core\Entities\Day;
use app\core\Telegram\ChatCommand;
use app\Formatters\DayFormatter;
use app\Formatters\TelegramBotFormatter;
use app\models\GameTypes;
use app\models\Weeks;
use app\Services\DayService;
use app\Services\TelegramBotService;

class SetCommand extends ChatCommand
{
    public static $accessLevel = 'manager';
    public static function description()
    {
        return self::locale("<u>/set</u> <i>// Set data for a specific day. Example:</i>\n\t\t/set sun, mafia, 18:00, (Good luck, have fun!)\n");
    }
    public static function execute()
    {
        if (empty(static::$arguments[0])) {
            return static::daysMenu();
        }

        static::parseArguments();

        $weekId = Weeks::currentId();
        $dayNum = static::$arguments['dayNum'];

        if (static::$arguments['dayNum'] < static::$arguments['currentDay']) {
            ++$weekId;
        }

        $day = Day::create($dayNum, $weekId);
        $day->status = 'set';

        if (static::$arguments['method'] === '-') {
            static::$arguments['method'] = 'recalled';
        }

        if (!empty(static::$arguments['game'])) {
            $day->game = static::$arguments['game'];
            $day->gameName = Day::$games[static::$arguments['game']] ?? '';
        }

        if (!empty(static::$arguments['time'])) {
            $day->time = static::$arguments['time'];
            $day->date = date('d.m.Y', $day->timestamp) . ' (<b>' . $day->dayName . '</b>) ' . $day->time;
        }

        if (!empty(static::$arguments['prim'])) {
            $day->day_prim = static::$arguments['prim'];
        }

        if (!empty(static::$arguments['tournament'])) {
            $day->addMod('tournament');
        } elseif (!empty($day->mods)) {
            $day->removeMod('tournament');
        }

        $day->save();

        $message = static::$arguments['method'] === '-'
            ? self::locale('{{ Tg_Command_Successfully_Canceled }}')
            : DayFormatter::forMessengers($day);

        $replyMarkup = [
            'inline_keyboard' => [
                [
                    ['text' => 'Send to the group?', 'callback_data' => ['c' => 'resend', 'w' => $weekId, 'd' => $dayNum]],
                ],
            ]
        ];

        return [
            'result' => true,
            'reaction' => '👌',
            'send' => [
                [
                    'message' => $message,
                    'replyMarkup' => $replyMarkup,
                ]
            ]
        ];
    }
    private static function parseArguments()
    {
        $days = DayService::getDayNamesForCommand();

        $game = $dayName = $time = '';
        $tournament = false;

        $pattern = 'maf|маф|наст|board|table|пок|pok|nlh|інш|другое|etc';
        $gamesArray = [
            'mafia' => ['maf', 'маф'],
            'board' => ['наст', 'board', 'table'],
            'nlh' => ['пок', 'pok', 'nlh'],
            'etc' => ['інш', 'другое', 'etc'],
        ];

        $gamesKeywords = GameTypes::getKeywords();

        if (!empty($gamesKeywords)) {
            $_gamesArray = [];
            $_pattern = [];
            foreach ($gamesKeywords as $slug => $keywords) {

                if (empty($keywords)) continue;

                $_gamesArray[$slug] = array_slice($keywords, 0, 3);

                foreach ($_gamesArray[$slug] as $index => $keyword) {
                    $_gamesArray[$slug][$index] = mb_strtolower(trim($keyword), 'UTF-8');
                    $_pattern[] = $_gamesArray[$slug][$index];
                }
            }
            if (!empty($_pattern)) {
                $pattern = implode('|', $_pattern);
                $gamesArray = $_gamesArray;
            }
        }

        foreach (static::$arguments as $value) {
            $value = trim($value);
            if ($game === '' && preg_match("/^($pattern)/ui", mb_strtolower($value, 'UTF-8'), $gamesPattern) > 0) {
                $game = $gamesPattern[0];
                if (!$tournament && preg_match('/(тур|tour)/ui', mb_strtolower($value, 'UTF-8')) === 1) {
                    $tournament = true;
                }
                continue;
            }
            if ($time === '' && preg_match('/^([0-2]{0,1}[0-9]\:[0-5][0-9])/', mb_strtolower($value, 'UTF-8'), $timesPattern) > 0) {
                $time = $timesPattern[0];
                continue;
            }
            if ($dayName === '' && preg_match("/^[+-]{0,1}($days)/ui", mb_strtolower($value, 'UTF-8'), $daysPattern) > 0) {
                $dayName = $daysPattern[0];
                continue;
            }
        }

        $method = '+';
        if ($dayName[0] === '+' || $dayName[0] === '-') {
            $method = $dayName[0];
            $dayName = mb_substr($dayName, 1, null, 'UTF-8');
        }

        if ($dayName === '')
            $dayName = 'tod';

        if ($game !== '') {
            foreach ($gamesArray as $name => $gameNames) {
                if (in_array($game, $gameNames, true)) {
                    $game = $name;
                    break;
                }
            }
        }

        TelegramBotService::parseDayNum($dayName);
        static::$arguments = array_merge(static::$arguments, compact('game', 'dayName', 'time', 'method', 'tournament'));
    }
    private static function daysMenu()
    {
        $message = 'Choose a day:';
        $replyMarkup = TelegramBotFormatter::getForwardDaysListMarkup('set', true);
        $replyMarkup['inline_keyboard'][] = [['text' => self::locale('Done'), 'callback_data' => ['c' => 'close', 'u' => static::$requester->profile->id]]];
        return [
            'result' => true,
            'reaction' => '👌',
            'send' => [
                [
                    'message' => $message,
                    'replyMarkup' => $replyMarkup,
                ]
            ]
        ];
    }
}

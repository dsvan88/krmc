<?php

namespace app\Repositories\TelegramCommands;

use app\core\Telegram\ChatCommand;
use app\models\Days;
use app\models\GameTypes;
use app\models\Weeks;
use app\Repositories\DayRepository;
use app\Repositories\TelegramBotRepository;

class SetCommand extends ChatCommand
{
    public static $accessLevel = 'manager';
    public static function description()
    {
        return self::locale("<u>/set</u> <i>// Set data for a specific day. Example:</i>\n\t\t/set sun, mafia, 18:00, (Good luck, have fun!)\n");
    }
    public static function execute()
    {
        if (empty(static::$arguments)) {
            $message = self::locale('{{ Tg_Command_Without_Arguments }}');
            return static::result($message);
        }

        $days = DayRepository::getDayNamesForCommand();

        $gameName = $dayName = $time = '';
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
                    $_gamesArray[$slug][$index] = trim($keyword);
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
            if ($gameName === '' && preg_match("/^($pattern)/ui", mb_strtolower($value, 'UTF-8'), $gamesPattern) > 0) {
                $gameName = $gamesPattern[0];
                if ($tournament === false && preg_match('/(тур|tour)/ui', mb_strtolower($value, 'UTF-8')) > 0) {
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

        if ($dayName === '')
            $dayName = 'сг';

        $method = '+';
        if ($dayName[0] === '+' || $dayName[0] === '-') {
            $method = $dayName[0];
            $dayName = mb_substr($dayName, 1, null, 'UTF-8');
        }

        TelegramBotRepository::parseDayNum($dayName);

        if ($gameName !== '') {
            foreach ($gamesArray as $name => $gameNames) {
                if (in_array($gameName, $gameNames, true)) {
                    $gameName = $name;
                    break;
                }
            }
        }

        $weekId = Weeks::currentId();
        $dayNum = static::$arguments['dayNum'];

        if (static::$arguments['dayNum'] < static::$arguments['currentDay']) {
            ++$weekId;
        }
        $weekData = Weeks::weekDataById($weekId);

        $weekData['data'][$dayNum]['status'] = 'set';

        if ($method === '-') {
            $weekData['data'][$dayNum]['status'] = 'recalled';
        }

        if ($gameName !== '') {
            $weekData['data'][$dayNum]['game'] = $gameName;
        }

        if ($time !== '') {
            $weekData['data'][$dayNum]['time'] = $time;
        }

        if (isset($arguments['prim'])) {
            $weekData['data'][$dayNum]['day_prim'] = $arguments['prim'];
        }

        if ($tournament) {
            if (empty($weekData['data'][$dayNum]['mods']) || !in_array('tournament', $weekData['data'][$dayNum]['mods'])) {
                $weekData['data'][$dayNum]['mods'][] = 'tournament';
            }
        } elseif (!empty($weekData['data'][$dayNum]['mods'])) {
            $index = array_search('tournament', $weekData['data'][$dayNum]['mods'], true);
            if ($index !== false) {
                unset($weekData['data'][$dayNum]['mods'][$index]);
                $weekData['data'][$dayNum]['mods'][$index] = array_values($weekData['data'][$dayNum]['mods']);
            }
        }

        $result = Days::setDayData($weekId, $dayNum, $weekData['data'][$dayNum]);

        if (!$result) {
            $message = json_encode($weekData['data'][$dayNum], JSON_UNESCAPED_UNICODE);
            static::$report = $message;
        }

        $message = $method === '-' ? self::locale('{{ Tg_Command_Successfully_Canceled }}') : Days::getFullDescription($weekData, $dayNum);
        $replyMarkup = [
            'inline_keyboard' => [
                [
                    ['text' => 'Send to the group?', 'callback_data' => ['c' => 'setSend', 'w' => $weekId, 'd' => $dayNum]],
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
        // return static::result($message, '👌', true);
    }
}

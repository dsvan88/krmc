<?php

namespace app\Services\TelegramCommands;

use app\core\Entities\Day;
use app\core\Telegram\ChatCommand;
use app\Formatters\DayFormatter;
use app\mappers\Weeks;
use app\Services\TelegramBotService;
use Exception;

class RegCommand extends ChatCommand
{
    public static $accessLevel = 'manager';
    public static function description()
    {
        return self::locale('<u>/reg</u> <i>// Booking/unbooking players for a specific day. Examples:</i>
    /reg +mon, nickname, 18:00, (with ?)
    /reg -mon, nickname
');
    }
    public static function execute()
    {
        if (empty(static::$arguments)) {
            return static::result('{{ Tg_Command_Without_Arguments }}');
        }

        TelegramBotService::parseArguments(static::$arguments);
        $requestData = static::$arguments;

        if (!isset($requestData['nonames']) && $requestData['userId'] < 2) {
            $message = self::locale(['string' => 'No users found with nickname: <b>%s</b>!', 'vars' => [$requestData['probableUserName']]]);
            return static::result($message);
        }

        $weekId = Weeks::currentId();
        $daySlug = static::$arguments['dayNum'] ?? 'tod';
        TelegramBotService::parseDayNum($daySlug);

        $dayNum = static::$arguments['dayNum'];
        if (static::$arguments['dayNum'] < static::$arguments['currentDay'])
            $weekId++;

        $day = Day::create($dayNum, $weekId);

        if (!$day)
            throw new Exception(__METHOD__ . ' $day can’t be null');

        $participantId = $slot = -1;

        if ($day->status !== 'set') {
            if (!empty($requestData['arrive']))
                $day->time = $requestData['arrive'];
            $requestData['arrive'] = '';
            $day->status = 'set';
        }

        if (!isset($requestData['nonames'])) {
            foreach ($day->participants as $index => $participant) {
                if ($participant['id'] !== $requestData['userId']) continue;

                if (!empty($requestData['arrive']) && $requestData['arrive'] !== $participant['arrive']) {
                    $slot = $index;
                    break;
                }
                $participantId = $index;
                break;
            }
        }

        if ($requestData['method'] === '+') {
            if ($participantId !== -1) {
                return static::result('{{ Tg_Command_User_Already_Booked }}');
            }
            if (isset($requestData['nonames'])) {
                $day->addNonames($requestData['nonames'], $requestData['prim']);
            } else {
                $day->addParticipant($requestData, $slot);
            }
        } else {
            if (isset($requestData['nonames'])) {
                $day->removeNonames($requestData['nonames']);
            } else {
                if ($participantId === -1) {
                    return static::result('{{ Tg_Command_User_Not_Booked }}');
                }
                $day->removeParticipant($participantId);
            }
        }

        $day->save();

        $message = DayFormatter::forMessengers($day);

        $replyMarkup = [
            'inline_keyboard' => [
                [
                    ['text' => 'Send to the group?', 'callback_data' => ['c' => 'resend', 'w' => $weekId, 'd' => $day->dayId]],
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
}

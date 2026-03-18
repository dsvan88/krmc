<?php

namespace app\Repositories\TelegramCommands;

use app\core\Telegram\ChatCommand;
use app\models\Days;
use app\models\Weeks;
use app\Repositories\TelegramBotRepository;

class BookingCommand extends ChatCommand
{
    public static function description()
    {
        return static::locale("<u>+ (week day)</u> <i>// Booking for the scheduled games of the current week, examples:</i>\n\t\t+вс\n\t\t+ на сегодня, на 19:30 (отсижу 1-2 игры, под ?)\n<u>- (week day)</u> <i>// Unsubscribe from games on a specific day that you previously signed up for, examples:</i>\n\t\t-вс\n\t\t- завтра\n");
    }
    public static function execute(): array
    {
        TelegramBotRepository::parseDayNum(static::$arguments['dayName']);
        $dayNum = static::$arguments['dayNum'];

        if (empty(static::$requester->profile)) {
            if (empty(static::$requester->chat))
                return static::result('{{ Tg_Unknown_Requester }}', '🤷‍♂');

            static::$arguments['userId'] = '_' . static::$requester->chat->id;
            static::$arguments['userName'] = empty(static::$requester->chat->username) ? '+1' : '@' . static::$requester->chat->username;
            static::$arguments['userStatus'] = 'all';
        } else {
            static::$arguments['userId'] = static::$requester->profile->id;
            static::$arguments['userName'] = static::$requester->profile->name;
            static::$arguments['userStatus'] = static::$requester->profile->status ?? 'user';
        }

        $weekId = Weeks::currentId();
        if ($dayNum < static::$arguments['currentDay']) {
            ++$weekId;
        }

        $weekData = Weeks::weekDataById($weekId);

        $participantId = $slot = -1;
        if ($weekData['data'][$dayNum]['status'] !== 'set') {
            if (!in_array(static::$arguments['userStatus'], ['trusted', 'activist', 'manager', 'admin'])) {
                return static::result('{{ Tg_Gameday_Not_Set }}');
            }
            if (!isset($weekData['data'][$dayNum]['game']))
                $weekData['data'][$dayNum] = Days::$dayDataDefault;

            if (!empty(static::$arguments['arrive']))
                $weekData['data'][$dayNum]['time'] = static::$arguments['arrive'];

            static::$arguments['arrive'] = '';

            // For social points of day started non-admin
            if (in_array(static::$arguments['userStatus'], ['trusted', 'activist']) && empty($weekData['data'][$dayNum]['status'])) {
                $weekData['data'][$dayNum]['starter'] = static::$arguments['userId'];
            }

            $weekData['data'][$dayNum]['status'] = 'set';
        }

        foreach ($weekData['data'][$dayNum]['participants'] as $index => $userData) {
            if ($userData['id'] !== static::$arguments['userId']) continue;

            if (!empty(static::$arguments['arrive']) && static::$arguments['arrive'] !== $userData['arrive']) {
                $slot = $index;
                break;
            }

            $participantId = $index;
            break;
        }
        $result = ['result' => true];
        $newDayData = $weekData['data'][$dayNum];
        if (static::$arguments['method'] === '+') {
            if ($participantId !== -1) {
                return static::result('{{ Tg_Command_Requester_Already_Booked }}');
            }
            Days::addParticipantToDayData($newDayData, static::$arguments, $slot);
            $reactions = [
                '👍',
                '🤩',
                '🔥',
                '❤',
                '🔥',
                '🥰',
                '🎉',
                '👏',
                '⚡',
                '🤝',
                '👌',
            ];
            //👍👎❤🔥🥰👏😁🤔🤯😱🤬😢🎉🤩🤮🤣💔💯⚡🤷‍♂🤝👌
        } else {
            if ($participantId === -1) {
                return static::result('{{ Tg_Command_Requester_Not_Booked }}');
            }
            unset($newDayData['participants'][$participantId]);
            $newDayData['participants'] = array_values($newDayData['participants']);
            //👍👎❤🔥🥰👏😁🤔🤯😱🤬😢🎉🤩🤮🤣💔💯⚡🤷‍♂🤝👌
            $reactions = [
                '👎',
                '🤔',
                '😢',
                '💔',
                '😱',
                '🤯',
                '🤬',
                '🤷‍♂',
            ];
        }

        Days::setDayData($weekId, $dayNum, $newDayData);

        if (!empty($reactions)) {
            $result['reaction'] = $reactions[mt_rand(0, count($reactions) - 1)];
        }

        $weekData['data'][$dayNum] = $newDayData;

        $booked = in_array(static::$requester->profile->id, array_column($weekData['data'][$dayNum]['participants'], 'id'));
        $replyMarkup = TelegramBotRepository::getBookingMarkup($weekId, $dayNum, $booked);

        $result['send'][] = [
            'message' => Days::getFullDescription($weekData, $dayNum),
            'replyMarkup' => $replyMarkup,
            'replyOn' => 0,
        ];

        return $result;
    }
}

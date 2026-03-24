<?php

namespace app\Services\TelegramCommands;

use app\core\Entities\Day;
use app\core\Telegram\ChatCommand;
use app\Formatters\DayFormatter;
use app\Formatters\TelegramBotFormatter;
use app\models\Weeks;
use app\Services\TelegramBotService;
use Exception;

class BookingCommand extends ChatCommand
{
    public static function description()
    {
        return static::locale("<u>+ (week day)</u> <i>// Booking for the scheduled games of the current week, examples:</i>\n\t\t+вс\n\t\t+ на сегодня, на 19:30 (отсижу 1-2 игры, под ?)\n<u>- (week day)</u> <i>// Unsubscribe from games on a specific day that you previously signed up for, examples:</i>\n\t\t-вс\n\t\t- завтра\n");
    }
    public static function execute(): array
    {
        TelegramBotService::parseDayNum(static::$arguments['dayName']);
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
        $day = Day::create($dayNum, $weekId);

        if (empty($day))
            throw new Exception(__METHOD__.' $day can’t be empty.');

        $participantId = $slot = -1;
        if ($day->status !== 'set') {
            if (!in_array(static::$arguments['userStatus'], ['trusted', 'activist', 'manager', 'admin'])) {
                return static::result('{{ Tg_Gameday_Not_Set }}');
            }

            if (!empty(static::$arguments['arrive']))
                $day->time = static::$arguments['arrive'];

            static::$arguments['arrive'] = '';

            // For social points of day started non-admin
            if (in_array(static::$arguments['userStatus'], ['trusted', 'activist']) && empty($day->status)) {
                $day->starter = static::$arguments['userId'];
            }

            $day->status = 'set';
        }

        foreach ($day->participants as $index => $participant) {
            if ($participant['id'] !== static::$arguments['userId']) continue;

            if (!empty(static::$arguments['arrive']) && static::$arguments['arrive'] !== $participant['arrive']) {
                $slot = $index;
                break;
            }

            $participantId = $index;
            break;
        }
        $result = ['result' => true];
        if (static::$arguments['method'] === '+') {
            if ($participantId !== -1) {
                return static::result('{{ Tg_Command_Requester_Already_Booked }}', '🤷‍♂');
            }
            $day->addParticipant(static::$arguments, $slot);
            $reactions = [ '👍','🤩','🔥','❤','🔥','🥰','🎉','👏','⚡','🤝','👌',];
        } else {
            if ($participantId === -1) {
                return static::result('{{ Tg_Command_Requester_Not_Booked }}', '🤷‍♂');
            }
            $day->removeParticipant($participantId);
            $reactions = ['👎','🤔','😢','💔','😱','🤯','🤬','🤷‍♂',];
        }

        $day->save();

        if (!empty($reactions)) {
            $result['reaction'] = $reactions[mt_rand(0, count($reactions) - 1)];
        }

        $booked = in_array(static::$requester->profile->id, array_column($day->participants, 'id'));
        $replyMarkup = TelegramBotFormatter::getBookingMarkup($weekId, $dayNum, $booked);

        $result['send'][] = [
            'message' => DayFormatter::forMessengers($day),
            'replyMarkup' => $replyMarkup,
            'replyOn' => 0,
        ];

        return $result;
    }
}

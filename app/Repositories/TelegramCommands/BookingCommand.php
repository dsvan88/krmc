<?

namespace app\Repositories\TelegramCommands;

use app\core\ChatCommand;
use app\models\Days;
use app\models\Weeks;

class BookingCommand extends ChatCommand
{
    public static $accessLevel = 'user';
    public static function description()
    {
        return self::locale("<u>+ (week day)</u> <i>// Booking for the scheduled games of the current week, examples:</i>\n\t\t+вс\n\t\t+ на сегодня, на 19:30 (отсижу 1-2 игры, под ?)\n<u>- (week day)</u> <i>// Unsubscribe from games on a specific day that you previously signed up for, examples:</i>\n\t\t-вс\n\t\t- завтра\n");
    }
    public static function execute(array $arguments = [])
    {
        $requestData = $arguments;
        self::$operatorClass::parseDayNum($requestData['dayName'], $requestData);

        $requestData['userId'] = self::$requester['id'];
        $requestData['userName'] = self::$requester['name'];
        $requestData['userStatus'] = empty(self::$requester['privilege']['status']) ? 'user' : self::$requester['privilege']['status'];

        $weekId = Weeks::currentId();
        if ($requestData['currentDay'] > $requestData['dayNum']) {
            ++$weekId;
        }

        $weekData = Weeks::weekDataById($weekId);

        $participantId = $slot = -1;
        if ($weekData['data'][$requestData['dayNum']]['status'] !== 'set') {
            if (!in_array($requestData['userStatus'], ['trusted', 'manager', 'admin'])) {
                self::$operatorClass::$resultMessage = self::locale('{{ Tg_Gameday_Not_Set }}');
                return false;
            }
            if (!isset($weekData['data'][$requestData['dayNum']]['game']))
                $weekData['data'][$requestData['dayNum']] = Days::$dayDataDefault;

            if (!empty($requestData['arrive']))
                $weekData['data'][$requestData['dayNum']]['time'] = $requestData['arrive'];

            $requestData['arrive'] = '';
            $weekData['data'][$requestData['dayNum']]['status'] = 'set';
        }

        foreach ($weekData['data'][$requestData['dayNum']]['participants'] as $index => $userData) {
            if ($userData['id'] !== $requestData['userId']) continue;

            if (!empty($requestData['arrive']) && $requestData['arrive'] !== $userData['arrive']) {
                $slot = $index;
                break;
            }

            $participantId = $index;
            break;
        }

        $newDayData = $weekData['data'][$requestData['dayNum']];
        if ($requestData['method'] === '+') {
            if ($participantId !== -1) {
                self::$operatorClass::$resultMessage = self::locale('{{ Tg_Command_Requester_Already_Booked }}');
                return false;
            }
            $newDayData = Days::addParticipantToDayData($newDayData, $requestData, $slot);
            $reactions = [
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
            // $reactions = [
            //     '🤩',
            //     '🔥',
            //     '❤',
            //     '❤‍🔥',
            //     '💘',
            //     '🆒',
            //     '🎉',
            //     '👏',
            //     '🥰',
            //     '😍',
            //     '🤗',
            //     '🤩',
            //     '😘',
            // ];
        } else {
            if ($participantId === -1) {
                self::$operatorClass::$resultMessage = self::locale('{{ Tg_Command_Requester_Not_Booked }}');
                return false;
            }
            unset($newDayData['participants'][$participantId]);
            $newDayData['participants'] = array_values($newDayData['participants']);
            // $reactions = [
            //     '🤨',
            //     '😐',
            //     '😢',
            //     '👎',
            //     '😭',
            //     '😱',
            //     '😨',
            //     '🤯',
            //     '🤬',
            //     '😡',
            // ];
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

        Days::setDayData($weekId, $requestData['dayNum'], $newDayData);

        $botReaction = '';
        if (!empty($reactions)) {
            $botReaction = $reactions[mt_rand(0, count($reactions) - 1)];
        }

        $weekData['data'][$requestData['dayNum']] = $newDayData;

        self::$operatorClass::$resultMessage = Days::getFullDescription($weekData, $requestData['dayNum']);
        self::$operatorClass::$reaction = $botReaction;
        return true;
    }
}

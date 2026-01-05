<?

namespace app\Repositories\TelegramCommands;

use app\core\Telegram\ChatCommand;
use app\models\Days;
use app\models\Weeks;
use app\Repositories\TelegramBotRepository;

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

        TelegramBotRepository::parseArguments(static::$arguments);
        $requestData = static::$arguments;

        if (!isset($requestData['nonames']) && $requestData['userId'] < 2) {
            $message = self::locale(['string' => 'No users found with nickname: <b>%s</b>!', 'vars' => [$requestData['probableUserName']]]);
            return static::result($message);
        }

        $weekId = Weeks::currentId();
        if ($requestData['dayNum'] < 0) {
            $requestData['dayNum'] = $requestData['currentDay'];
        } else {
            if ($requestData['currentDay'] > $requestData['dayNum']) {
                ++$weekId;
            }
        }
        $weekData = Weeks::weekDataById($weekId);

        $participantId = $slot = -1;

        if ($weekData['data'][$requestData['dayNum']]['status'] !== 'set') {
            if (!isset($weekData['data'][$requestData['dayNum']]['game']))
                $weekData['data'][$requestData['dayNum']] = Days::$dayDataDefault;

            if ($requestData['arrive'] !== '')
                $weekData['data'][$requestData['dayNum']]['time'] = $requestData['arrive'];
            $requestData['arrive'] = '';
            $weekData['data'][$requestData['dayNum']]['status'] = 'set';
        }

        if (isset($requestData['nonames'])) {
            $slot = count($weekData['data'][$requestData['dayNum']]['participants']);
        } else {
            foreach ($weekData['data'][$requestData['dayNum']]['participants'] as $index => $userData) {
                if ($userData['id'] !== $requestData['userId']) continue;

                if ($requestData['arrive'] !== '' && $requestData['arrive'] !== $userData['arrive']) {
                    $slot = $index;
                    break;
                }
                $participantId = $index;
                break;
            }
        }
        $newDayData = $weekData['data'][$requestData['dayNum']];
        if ($requestData['method'] === '+') {
            if ($participantId !== -1) {
                return static::result('{{ Tg_Command_User_Already_Booked }}');
            }
            if (isset($requestData['nonames'])) {
                $newDayData = Days::addNonamesToDayData($newDayData, $slot, $requestData['nonames'], $requestData['prim']);
            } else {
                $newDayData = Days::addParticipantToDayData($newDayData, $requestData, $slot);
            }
        } else {
            if (isset($requestData['nonames'])) {
                $newDayData = Days::removeNonamesFromDayData($newDayData, $requestData['nonames']);
            } else {
                if ($participantId === -1) {
                    $message = self::locale('{{ Tg_Command_User_Not_Booked }}');
                    return static::result($message);
                }
                unset($newDayData['participants'][$participantId]);
                $newDayData['participants'] = array_values($newDayData['participants']);
            }
        }

        $result = Days::setDayData($weekId, $requestData['dayNum'], $newDayData);

        $weekData['data'][$requestData['dayNum']] = $newDayData;
        $message = Days::getFullDescription($weekData, $requestData['dayNum']);

        error_log('$message' . $message);
        error_log('dayNum' . $requestData['dayNum']);

        $replyMarkup = [
            'inline_keyboard' => [
                [
                    ['text' => 'Send to the group?', 'callback_data' => ['c' => 'regSend', 'w' => $weekId, 'd' => $requestData['dayNum']]],
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

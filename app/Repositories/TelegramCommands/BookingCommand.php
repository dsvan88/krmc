<?
namespace app\Repositories\TelegramCommands;

use app\core\ChatCommand;
use app\models\Days;
use app\models\Weeks;

class BookingCommand extends ChatCommand {
    public static function description(){
        return self::locale('+ (week day) <i>// Booking for the scheduled games of the current week, examples:</i>
        +вс
        + на сегодня, на 19:30 (отсижу 1-2 игры, под ?)
    - (week day) <i>// Unsubscribe from games on a specific day that you previously signed up for, examples:</i>
        -вс
        - завтра');
    }
    public static function execute(array $arguments=[]){
        $requestData = self::$operatorClass::parseArguments($arguments);
        $requestData['userId'] = self::$requester['id'];
        $requestData['userName'] = self::$requester['name'];
        $requestData['userStatus'] = self::$requester['privilege']['status'];

        $weekId = Weeks::currentId();
        if ($requestData['currentDay'] > $requestData['dayNum']) {
            ++$weekId;
        }

        $weekData = Weeks::weekDataById($weekId);

        $participantId = $slot = -1;
        if ($weekData['data'][$requestData['dayNum']]['status'] !== 'set') {
            if (!in_array($requestData['userStatus'], ['manager', 'admin'])){
                return [false, self::locale('{{ Tg_Gameday_Not_Set }}')];
            }
            if (!isset($weekData['data'][$requestData['dayNum']]['game']))
                $weekData['data'][$requestData['dayNum']] = Days::$dayDataDefault;

            if ($requestData['arrive'] !== '')
                $weekData['data'][$requestData['dayNum']]['time'] = $requestData['arrive'];
                
            $requestData['arrive'] = '';
            $weekData['data'][$requestData['dayNum']]['status'] = 'set';
        }

        foreach ($weekData['data'][$requestData['dayNum']]['participants'] as $index => $userData) {
            if ($userData['id'] === $requestData['userId']) {
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
                return [false, self::locale('{{ Tg_Command_Requester_Already_Booked }}')];
            }
            $newDayData = Days::addParticipantToDayData($newDayData, $requestData, $slot);
            $reactions = [
                '🤩',
                '🥰',
                '🥳',
                '😻',
            ];
        } else {
            if ($participantId === -1) {
                return [false, self::locale('{{ Tg_Command_Requester_Not_Booked }}')];
            }
            unset($newDayData['participants'][$participantId]);
            $newDayData['participants'] = array_values($newDayData['participants']);
            $reactions = [
                '😥',
                '😭',
                '😱',
                '😿',
            ];
        }

        $result = Days::setDayData($weekId, $requestData['dayNum'], $newDayData);

        $botReaction = '';
        if (isset($reactions)) {
            $botReaction = $reactions[mt_rand(0, count($reactions) - 1)];
        }

        $weekData['data'][$requestData['dayNum']] = $newDayData;
        return [$result, Days::getFullDescription($weekData, $requestData['dayNum']), $botReaction];
    }
}
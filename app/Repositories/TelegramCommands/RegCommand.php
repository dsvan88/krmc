<?
namespace app\Repositories\TelegramCommands;

use app\core\ChatCommand;
use app\models\Days;
use app\models\Weeks;

class RegCommand extends ChatCommand {
    public static function description(){
        return self::locale('<u>/day (week day)</u> <i>// Booking information for a specific day. Without specifying the day - for today</i>');
    }
    public static function execute(array $arguments=[]){
        if (empty($arguments)) {
            return [false, self::locale('{{ Tg_Command_Without_Arguments }}')];
        }
        
        $requestData = self::$operatorClass::parseArguments($arguments);
        
        if (!isset($requestData['nonames']) && $requestData['userId'] < 2) {
            return [false, self::locale('{{ Tg_Command_User_Not_Found }}')];
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
                if ($userData['id'] === $requestData['userId']) {
                    if ($requestData['arrive'] !== '' && $requestData['arrive'] !== $userData['arrive']) {
                        $slot = $index;
                        break;
                    }
                    $participantId = $index;
                    break;
                }
            }
        }
        $newDayData = $weekData['data'][$requestData['dayNum']];
        if ($requestData['method'] === '+') {
            if ($participantId !== -1) {
                return [false, self::locale('{{ Tg_Command_User_Already_Booked }}')];
            }
            if (isset($requestData['nonames'])) {
                $newDayData = Days::addNonamesToDayData($newDayData, $slot, $requestData['nonames'], $requestData['prim']);
            } else {
                $newDayData = Days::addParticipantToDayData($newDayData, $slot, $requestData);
            }
        } else {
            if (isset($requestData['nonames'])) {
                $newDayData = Days::removeNonamesFromDayData($newDayData, $requestData['nonames']);
            } else {
                if ($participantId === -1) {
                    return [false, self::locale('{{ Tg_Command_User_Not_Booked }}')];
                }
                unset($newDayData['participants'][$participantId]);
                $newDayData['participants'] = array_values($newDayData['participants']);
            }
        }
        
        $result = Days::setDayData($weekId, $requestData['dayNum'], $newDayData);
        
        $weekData['data'][$requestData['dayNum']] = $newDayData;

        return [$result, Days::getFullDescription($weekData, $requestData['dayNum'])];
    }
}
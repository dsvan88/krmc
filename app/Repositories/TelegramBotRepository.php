<?

namespace app\Repositories;

use app\models\Days;
use app\models\Weeks;
use Exception;

class TelegramBotRepository
{
    public static function booking(array $userData = [], array $arguments = [], string &$update = ''){
        if (empty($userData) || empty($arguments))
            throw new Exception('UserData or arguments is empty');

        $userData['status'] = empty($userData['privilege']['status']) ? 'user' : $userData['privilege']['status'];

        $weekId = (int) $arguments['wId'];
        $dayNum = (int) $arguments['dNum'];
        
        $weekData = Weeks::weekDataById($weekId);
        $participantId = -1;
        if ($weekData['data'][$dayNum]['status'] !== 'set') {
            if (!in_array($userData['status'], ['trusted', 'activist', 'manager', 'admin'])) {
                return '{{ Tg_Gameday_Not_Set }}';
            }
            if (!isset($weekData['data'][$dayNum]['game']))
                $weekData['data'][$dayNum] = Days::$dayDataDefault;

            $weekData['data'][$dayNum]['status'] = 'set';
        }

        foreach ($weekData['data'][$dayNum]['participants'] as $index => $participant) {
            if ($participant['id'] !== $userData['userId']) continue;
            $participantId = $index;
            break;
        }
        if ($participantId !== -1) {
            return '{{ Tg_Command_Requester_Already_Booked }}';
        }
        $newDayData = $weekData['data'][$dayNum];
        $data = [
            'userId' => $userData['id'],
            'prim' => empty($arguments['prim']) ? '' : $arguments['prim'],
        ];
        $newDayData = Days::addParticipantToDayData($newDayData, $data);

        Days::setDayData($weekId, $dayNum, $newDayData);

        $weekData['data'][$dayNum] = $newDayData;

        $update = ['message' => Days::getFullDescription($weekData, $dayNum)];

        return 'Success';
    }
}
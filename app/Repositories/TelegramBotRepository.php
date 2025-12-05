<?

namespace app\Repositories;

use app\core\Locale;
use app\models\Days;
use app\models\Weeks;
use Exception;

class TelegramBotRepository
{
    public static function nick (array $userData = [], array $arguments = [], array &$update = []){
        $tId = (int) trim($arguments['uid']);
        if (empty($tId))
            throw new Exception('UserID can’t be empty!');

        if ($userData['id'] != $tId && (empty($userData['privilege']['status']) || !in_array($userData['privilege']['status'], ['manager', 'admin', 'root'], true)))
            return 'You don’t have enough rights to change information about other users!';

        if ($arguments['save'])
            return "<b>%s</b>, nice to meet you!\nYou successfully registered in our system!\n\nIf you make a mistake, don't worry, tell the administrator about it and he will quickly fix it😏";
        
        return "Okay! Let's try again!\nUse the next command to register your nickname:\n/nick Here Is Your Nickname\n\nTry to avoid characters of different languages";
        
        Users::delete($tId);

        $update = [
            'message' => Locale::phrase(['text' => "<b>%s</b>, nice to meet you!\nYou successfully registered in our system!\n\nIf you make a mistake, don't worry, tell the administrator about it and he will quickly fix it😏", 'vars' => $userData['name']]),
        ];

        return 'Success';
    }
    public static function booking(array $userData = [], array $arguments = [], array &$update = []){
        if (empty($userData) || empty($arguments))
            throw new Exception('UserData or arguments is empty');

        $userData['status'] = empty($userData['privilege']['status']) ? 'user' : $userData['privilege']['status'];

        $weekId = (int) trim($arguments['wId']);
        $dayNum = (int) trim($arguments['dNum']);
        
        $weekData = Weeks::weekDataById($weekId);
        $dayEnd = $weekData['start'] + (TIMESTAMP_DAY * ($dayNum + 1));
        if ($dayEnd < $_SERVER['REQUEST_TIME'])
            return 'This day is over🤷‍♂️';

        if ($weekData['data'][$dayNum]['status'] !== 'set') {
            if (!in_array($userData['status'], ['trusted', 'activist', 'manager', 'admin'])) {
                return '{{ Tg_Gameday_Not_Set }}';
            }
            if (!isset($weekData['data'][$dayNum]['game']))
                $weekData['data'][$dayNum] = Days::$dayDataDefault;

            $weekData['data'][$dayNum]['status'] = 'set';
        }

        foreach ($weekData['data'][$dayNum]['participants'] as $participant) {
            if ($participant['id'] != $userData['id']) continue;
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

        $update = [
            'message' => Days::getFullDescription($weekData, $dayNum),
            'replyMarkup' => [
                'inline_keyboard' => [ 
                        [
                            ['text' => Locale::phrase('I will too!'), 'callback_data' => base64_encode(json_encode(['cmd' => 'booking', 'wId'=> $weekId, 'dNum' => $dayNum]))],
                            ['text' => Locale::phrase('I will too! I hope...'), 'callback_data' => base64_encode(json_encode(['cmd' => 'booking', 'wId'=> $weekId, 'dNum' => $dayNum, 'prim' => '?']))],
                        ],
                    ],
                ],
        ];

        return 'Success';
    }
}
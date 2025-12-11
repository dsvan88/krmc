<?

namespace app\Repositories;

use app\core\ChatCommand;
use app\core\Locale;
use app\models\Days;
use app\models\Users;
use app\models\Weeks;
use Exception;

class TelegramBotRepository
{
    public static $message = '';
    public static $userData = [];
    public static $arguments = [];
    public static function nickRelink(array $userData = [], array $arguments = [], array &$update = [])
    {

        // $userData IS EMPTY! Need to resolve add Relink To guestCommands;
        if (empty($arguments))
            throw new Exception('Arguments is empty!');

        $uId = (int) trim($arguments['u']);
        $tId = (int) trim($arguments['t']);

        if (empty($uId) || empty($tId))
            throw new Exception('UserID or TelegramID can’t be empty!');


        if (empty($userData['privilege']['status']) || !in_array($userData['privilege']['status'], ['manager', 'admin', 'root'], true))
            return 'You don’t have enough rights to change information about other users!';
    }
    public static function nick(array $userData = [], array $arguments = [], array &$update = [])
    {

        if (empty($userData) || empty($arguments))
            throw new Exception('UserData or arguments is empty');

        $uId = (int) trim($arguments['u']);

        if (empty($uId))
            throw new Exception('UserID can’t be empty!');

        if ($userData['id'] != $uId && (empty($userData['privilege']['status']) || !in_array($userData['privilege']['status'], ['manager', 'admin', 'root'], true)))
            return 'You don’t have enough rights to change information about other users!';

        if ($arguments['s']) {
            $update = [
                'message' => Locale::phrase(['text' => "<b>%s</b>, nice to meet you!\nYou successfully registered in our system!", 'vars' => $userData['name']]),
                PHP_EOL . PHP_EOL . Locale::phrase('If you made a mistake - don’t worry! Just tell the Administrator about it and he will quickly fix it😏'),
            ];

            return 'Success';
        }

        Users::delete($uId);

        $update = [
            'message' => Locale::phrase(['text' => "Okay! Let’s try again!\nUse the next command to register your nickname:\n/nick <b>%s</b>\n\nTry to avoid characters of different languages.", 'vars' => $userData['name']]),
        ];

        return 'Okay!';
    }
    public static function booking(array $userData = [], array $arguments = [], array &$update = [])
    {
        if (empty($userData) || empty($arguments))
            throw new Exception('UserData or arguments is empty');

        $userData['status'] = empty($userData['privilege']['status']) ? 'user' : $userData['privilege']['status'];

        $weekId = (int) trim($arguments['w']);
        $dayNum = (int) trim($arguments['d']);

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
            'prim' => empty($arguments['p']) ? '' : $arguments['p'],
        ];
        $newDayData = Days::addParticipantToDayData($newDayData, $data);

        Days::setDayData($weekId, $dayNum, $newDayData);

        $weekData['data'][$dayNum] = $newDayData;

        $update = [
            'message' => Days::getFullDescription($weekData, $dayNum),
            'replyMarkup' => [
                'inline_keyboard' => [
                    [
                        ['text' => Locale::phrase('I will too!'), 'callback_data' => ChatCommand::replyButton(['c' => 'booking', 'w' => $weekId, 'd' => $dayNum])],
                        ['text' => Locale::phrase('I will too! I hope...'), 'callback_data' => ChatCommand::replyButton(['c' => 'booking', 'w' => $weekId, 'd' => $dayNum, 'p' => '?'])],
                    ],
                ],
            ],
        ];

        return 'Success';
    }
}

<?

namespace app\Repositories;

use app\core\Locale;
use app\core\Tech;
use app\core\Validator;
use app\models\Days;
use app\models\Users;
use app\models\Weeks;

class AccountRepository
{
    public static function getFields(int $userId): array
    {
        $data = Users::getDataById($userId);
        $data['personal']['genderName'] = Locale::phrase(ucfirst($data['personal']['gender']));
        return $data;
    }
    public static function edit(int $userId, array $data)
    {
        $userData = Users::getDataById($userId);
        unset($userData['id']);

        if (isset($data['fio']))
            $userData['personal']['fio'] = $data['fio'];

        if (isset($data['birthday']) && !empty($data['birthday'])) {
            $birthday = strtotime(trim($data['birthday']));

            if ($birthday > $_SERVER['REQUEST_TIME'] - 31536000) // 31536000 = 60 * 60 * 24 * 365
                $birthday = 0;
            $userData['personal']['birthday'] = $birthday;
        }
        if (isset($data['gender'])) {
            $gender = Validator::validate('gender', $data['gender']);
            if ($gender !== false) {
                $userData['personal']['gender'] = $data['gender'];
            }
        }
        $data = Users::edit($userData, ['id' => $userId]);
        return true;
    }
    public static function rename(int $userId, string $name)
    {
        if (Users::getDataByName($name) !== false)
            return ['result' => false, 'message' => "This new name already exists in the base.\nPlease, select another!"];

        DayRepository::renamePlayer($userId, $name);
        Users::edit(['name' => $name], ['id' => $userId]);
        return ['result' => true, 'message' => 'Success!'];
    }
    public static function addParticipantToDay(string $name, int $day = null)
    {
        $userData = Users::getDataByName($name);
        if (empty($userData)) {
            $userId = Users::add($name);
            $userData = Users::getDataById($userId);
        }
        $weekId = Weeks::currentId();
        $weekData = Weeks::weekDataById($weekId);

        if (is_null($day)) {
            $day = getdate()['wday'] - 1;

            if ($day === -1)
                $day = 6;
        }

        foreach ($weekData['data'][$day]['participants'] as $index => $participant) {
            if ($participant['name'] === $userData['name']) {
                return ['result' => false, 'message' => 'Already in the list.'];
            }
        }

        $userData = [
            'userId' => $userData['id'],
            'userName' => $userData['name'],
        ];

        $weekData['data'][$day] = Days::addParticipantToDayData($weekData['data'][$day], $userData);
        $weekData['data'] = json_encode($weekData['data'], JSON_UNESCAPED_UNICODE);
        Weeks::update(['data' => $weekData['data']], ['id' => $weekId]);

        return ['result' => true, 'name' => $userData['userName']];
    }
    public static function saveTelegramApproveCode(array $userData, string $code)
    {
        $userData['personal']['tg-code'] = $code;
        Users::edit(['personal' => $userData['personal']], ['id' => $userData['id']]);
        return true;
    }
}

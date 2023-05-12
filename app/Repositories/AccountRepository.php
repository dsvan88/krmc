<?
namespace app\Repositories;

use app\core\Locale;
use app\core\Validator;
use app\models\Users;

class AccountRepository
{
    public static function getFields(int $userId) : array{
        $data = Users::getDataById($userId);
        $data['personal']['gender'] = Locale::phrase(ucfirst($data['personal']['gender']));
        return $data;
    }
    public static function edit(int $userId, array $data){
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
        if (isset($data['gender'])){
            $gender = Validator::validate('gender', $data['gender']);
            if ($gender !== false){
                $userData['personal']['gender'] = $data['gender'];
            }
        }
        $data = Users::edit($userData, ['id'=>$userId]);
        return true;
    }
}
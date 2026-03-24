<?php

namespace app\Services;

use app\core\Entities\Day;
use app\core\Locale;
use app\core\Tech;
use app\core\Validator;
use app\models\Contacts;
use app\models\Days;
use app\models\TelegramChats;
use app\models\Users;
use app\models\Weeks;

class AccountService
{
    public static function getFields(int $userId): array
    {
        $data = Users::find($userId);
        $data['personal']['genderName'] = '';

        if (!empty($data['personal']['gender'])) {
            $data['personal']['genderName'] = Locale::phrase(ucfirst($data['personal']['gender']));
        }

        $data['status'] = '';
        if (!empty($data['privilege']['status']) && array_search($data['privilege']['status'], Users::$usersAccessLevels) > 2) {
            $data['status'] = $data['privilege']['status'];
        }

        $contacts = ContactService::getFields($userId, 'No data');
        $contacts = ContactService::wrapLinks($contacts);

        return array_merge($data, $contacts);
    }
    public static function edit(int $userId, array $data)
    {
        $userData = Users::find($userId);
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

        // DayService::renamePlayer($userId, $name);
        Users::edit(['name' => $name], ['id' => $userId]);
        return ['result' => true, 'message' => 'Success'];
    }
    public static function addNames(array &$source): array
    {
        if (!empty($source['id'])) {
            if (!is_numeric($source['id']) && $source['id'][0] === '_') {
                $chatData = TelegramChats::find(substr($source['id'], 1));
                $source['name'] = empty($chatData['personal']['username']) ? $source['id'] : '@' . $chatData['personal']['username'];
                $source['status'] = 'all';
                $source['gender'] = '';
                $source['emoji'] = '';
                return $source;
            }
            try {
                $userData = Users::find($source['id']);
            } catch (\Throwable $error) {
                error_log($source['id']);
            }
            $source['name'] = empty($userData) ? '&lt; Deleted &gt;' : $userData['name'];

            $source['status'] = $userData['privilege']['status']  ?? '';
            $source['gender'] = $userData['personal']['gender'] ?? '';
            $source['emoji'] = $userData['personal']['emoji'] ?? '';

            return $source;
        }

        $count = count($source);
        for ($x = 0; $x < $count; $x++) {
            if (is_null($source[$x]['id'])) continue;
            static::addNames($source[$x]);
        }
        return $source;
    }
    public static function addParticipantToDay(string $name, int $dayId = -1)
    {
        $userData = Users::getDataByName($name);
        if (empty($userData)) {
            $userId = Users::add($name);
            $userData = Users::find($userId);
        }

        $day = Day::create($dayId);

        foreach ($day->participants as $participant) {
            if ($participant['name'] !== $userData['name']) continue;
            return ['message' => 'Already in the list.'];
        }
        $day->addParticipant(['userId' => $userData['id']])->save();

        return ['name' => $userData['userName']];
    }
    public static function unlinkTelegram(int $chatId)
    {
        $chatData =  TelegramChats::find($chatId);
        $chatId = (int) $chatData['id'];
        unset($chatData['id']);

        if (empty($chatData['user_id']))
            return false;

        $userData = Users::find($chatData['user_id']);

        if (!empty($userData['contacts']['telegram'])) {
            unset($userData['contacts']['telegram']);
        }
        if (!empty($userData['personal']['fio']) && $userData['personal']['fio'] === self::formFioFromChatData($chatData)) {
            unset($userData['personal']['fio']);
        }

        $userId = (int) $userData['id'];
        unset($userData['id']);

        Users::edit($userData, ['id' => $userId]);
        Contacts::deleteByUserId($userId, ['telegram', 'telegramid']);

        $chatData['user_id'] = null;
        TelegramChats::edit($chatData, $chatId);
        return true;
    }
    public static function linkTelegram(int $chatId, string $name)
    {

        $chatData =  TelegramChats::find($chatId);
        $chatId = (int) $chatData['id'];
        unset($chatData['id']);

        $userData = Users::getDataByName($name);
        if (!$userData) {
            $userId = Users::add($name);
            $userData = Users::find($userId);
        } else {
            $userId = (int) $userData['id'];
        }

        unset($userData['id']);

        $userData['personal']['fio'] = self::formFioFromChatData($chatData);
        $chatData['user_id'] = $userId;

        $telegram = empty($chatData['personal']['username']) ? null : $chatData['personal']['username'];

        $chatData['personal'] = json_encode($chatData['personal'], JSON_UNESCAPED_UNICODE);
        Users::edit($userData, ['id' => $userId]);
        TelegramChats::edit($chatData, $chatId);

        $contacts = ['telegramid' => $chatData['id']];
        if (!empty($telegram)) {
            $contacts['telegram'] = $telegram;
        }
        Contacts::reLink($contacts, $userId);

        return true;
    }
    public static function formFioFromChatData(array $chatData): string
    {
        $fio = '';
        if (!empty($chatData['personal']['first_name'])) {
            $fio .= $chatData['personal']['first_name'];
        }
        if (!empty($chatData['personal']['last_name'])) {
            $fio .= ' ' . $chatData['personal']['last_name'];
        }
        return trim($fio);
    }
    public static function mergeUsersData($main, $merged)
    {
        $new = [];
        foreach ($main as $key => $value) {
            if (in_array($key, ['created_at', 'updated_at', 'date_delete'])) continue;

            if (in_array($key, ['id', 'name'])) {
                $new[$key] = $value;
                continue;
            }
            if (is_array($value) && !empty($merged[$key])) {
                $new[$key] = self::mergeUsersData($value, $merged[$key]);
                continue;
            }
            $new[$key] = empty($value) && !empty($merged[$key]) ? $merged[$key] : $value;
        }
        return $new;
    }
    public static function mergeAccounts($userId, $name)
    {
        $mainUserData = Users::decodeJson(Users::find($userId));
        $mergedUserData = Users::decodeJson(Users::findBy('name', $name)[0]);

        if (empty($mainUserData) || empty($mergedUserData)) {
            return false;
        }

        $mainUserData = Users::contacts($mainUserData);
        $mergedUserData = Users::contacts($mergedUserData);

        $newUserData = self::mergeUsersData($mainUserData, $mergedUserData);
        // DayService::renamePlayer($mergedUserData['id'], $newUserData['name']);

        Users::edit($newUserData, ['id' => $newUserData['id']]);
    }
    public static function renameDummy(string $name): bool
    {
        $weekData = Weeks::weekDataByTime();
        $dayId = Day::current();

        $id = Users::getId($name);
        if ($id < 2) {
            $id = Users::add($name);
        }

        $countParticipants = count($weekData['data'][$dayId]['participants']);
        $firstSlot = null;

        for ($x = 0; $x < $countParticipants; $x++) {
            if ($weekData['data'][$dayId]['participants'][$x]['id'] === $id) return false;
            if (!empty($weekData['data'][$dayId]['participants'][$x]['id'])) continue;
            if (!empty($firstSlot)) continue;
            $firstSlot = $x;
        }
        $weekData['data'][$dayId]['participants'][$firstSlot]['id'] = $id;

        $weekId = $weekData['id'];
        unset($weekData['id']);

        return Weeks::setWeekData($weekId, $weekData);
    }

    public static function telegramAuth(string $string): bool
    {
        $string = urldecode($_POST['data']);
        parse_str($string, $array);

        $tgUserData = json_decode($array['user'], true);
        $userId = Contacts::getUserIdByContact('telegramid', $tgUserData['id']);

        if (!$userId) return false;

        return Users::auth($userId);
    }
    public static function checkAvailable(int $userId = 0): bool
    {
        if (empty($userId)) return false;

        $userData = Users::find($userId);

        if (!empty($userData['login'])) return false;

        $tgChat = TelegramChats::findBy('user_id', $userId)[0];

        if ($tgChat['data']['last_seems'] > $_SERVER['REQUEST_TIME'] - TIMESTAMP_DAY * 365) return false;

        $lastGameDay = DayService::findLastGameOfPlayer($userId);

        if ($lastGameDay > $_SERVER['REQUEST_TIME'] - TIMESTAMP_DAY * 600) return false;

        return true;
    }
}

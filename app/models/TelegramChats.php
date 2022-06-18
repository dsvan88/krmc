<?

namespace app\models;

use app\core\Model;

class TelegramChats extends Model
{
    public static function save($messageArray)
    {
        $table = SQL_TBL_TG_CHATS;

        $uid = $messageArray['message']['from']['id'];

        $result = self::getChat($uid);
        if (!$result) {
            $chatData = ['uid' => $uid, 'personal' => ['id' => $uid], 'data' => ['last_seems' => $messageArray['message']['date']]];

            if (isset($messageArray['message']['from']['first_name']) && !empty($messageArray['message']['from']['first_name'])) {
                $chatData['personal']['first_name'] = $messageArray['message']['from']['first_name'];
            }
            if (isset($messageArray['message']['from']['last_name']) && !empty($messageArray['message']['from']['last_name'])) {
                $chatData['personal']['last_name'] = $messageArray['message']['from']['last_name'];
            }
            if (isset($messageArray['message']['from']['username']) && !empty($messageArray['message']['from']['username'])) {
                $chatData['personal']['username'] = $messageArray['message']['from']['username'];
            }
            if ($messageArray['message']['chat']['type'] === 'private') {
                $chatData['data']['direct'] = true;
            }
            $userData = Users::getDataByTelegramId($uid);

            if ($userData) {
                $chatData['personal']['nickname'] = $userData['name'];
            }

            $chatData['personal'] = json_encode($chatData['personal'], JSON_UNESCAPED_UNICODE);
            $chatData['data'] = json_encode($chatData['data'], JSON_UNESCAPED_UNICODE);

            return self::insert($chatData, $table);
        }
        $savedChatId = $result['id'];

        $chatData = $result;
        if (!isset($chatData['personal']['first_name']) && isset($messageArray['message']['from']['first_name']) && !empty($messageArray['message']['from']['first_name'])) {
            $chatData['personal']['first_name'] = $messageArray['message']['from']['first_name'];
        }
        if (!isset($chatData['personal']['last_name']) && isset($messageArray['message']['from']['last_name']) && !empty($messageArray['message']['from']['last_name'])) {
            $chatData['personal']['last_name'] = $messageArray['message']['from']['last_name'];
        }
        if (!isset($chatData['personal']['username']) && isset($messageArray['message']['from']['username']) && !empty($messageArray['message']['from']['username'])) {
            $chatData['personal']['username'] = $messageArray['message']['from']['username'];
        }
        if ($messageArray['message']['chat']['type'] === 'private') {
            $chatData['data']['direct'] = true;
        }

        if (!isset($chatData['personal']['nickname'])) {
            $userData = Users::getDataByTelegramId($uid);
            if ($userData) {
                $chatData['personal']['nickname'] = $userData['name'];
            }
        }

        $chatData['data']['last_seems'] = $messageArray['message']['date'];

        $chatData['personal'] = json_encode($chatData['personal'], JSON_UNESCAPED_UNICODE);
        $chatData['data'] = json_encode($chatData['data'], JSON_UNESCAPED_UNICODE);

        self::update(['personal' => $chatData['personal'], 'data' => $chatData['data']], ['id' => $savedChatId], $table);
        return true;
    }
    public static function savePinned($messageArray, $messageId)
    {
        $chatId = $messageArray['message']['chat']['id'];
        $chatData = self::getChat($chatId);
        if (!$chatData) {
            self::createPinned($chatId, $messageArray, $messageId);
            return true;
        }
        $id = $chatData['id'];
        $data = $chatData['data'];
        $data['last_seems'] = $messageArray['message']['date'];
        $data['pinned'] = $messageId;
        self::edit(['data' => json_encode($data, JSON_UNESCAPED_UNICODE)], $id);
        return true;
    }
    public static function getChat($uid)
    {
        $table = SQL_TBL_TG_CHATS;
        $result = self::query("SELECT * FROM $table WHERE uid = ?", [$uid], 'Assoc');
        if (empty($result)) {
            return false;
        }
        $result = self::jsonDecodeData($result[0]);
        return $result;
    }
    public static function getChatsList()
    {
        $table = SQL_TBL_TG_CHATS;
        $result = self::query("SELECT * FROM $table ORDER BY id", [], 'Assoc');
        if (empty($result)) {
            return false;
        }
        for ($i = 0; $i < count($result); $i++) {
            $result[$i] = self::jsonDecodeData($result[$i]);
        }
        return $result;
    }
    public static function getGroupChatsList()
    {
        $table = SQL_TBL_TG_CHATS;
        $result = self::query("SELECT * FROM $table WHERE uid LIKE '-%' ORDER BY id", [], 'Assoc');
        if (empty($result)) {
            return [];
        }
        for ($i = 0; $i < count($result); $i++) {
            $result[$i] = self::jsonDecodeData($result[$i]);
        }
        return $result;
    }
    public static function getDirectChats()
    {
        $table = SQL_TBL_TG_CHATS;
        $result = self::query("SELECT * FROM $table WHERE data->>'direct' = ? ORDER BY id", ['true'], 'Assoc');
        if (empty($result)) {
            return [];
        }
        for ($i = 0; $i < count($result); $i++) {
            $result[$i] = self::jsonDecodeData($result[$i]);
        }
        return $result;
    }
    public static function getChatsWithPinned()
    {
        $chats = self::getChatsList();
        $result = [];
        for ($i = 0; $i < count($chats); $i++) {
            if (isset($chats['data']['pinned'])) {
                $result[$chats['uid']] = $chats['data']['pinned'];
            }
        }
        return $result;
    }
    public static function createPinned($chatId, $messageArray, $messageId)
    {
        $chatData = [
            'uid' => $chatId,
            'personal' => json_encode([
                'id' => $messageArray['message']['chat']['id'],
                'title' => $messageArray['message']['chat']['title'],
            ], JSON_UNESCAPED_UNICODE),
            'data' => json_encode([
                'last_seems' =>  $messageArray['message']['date'],
                'pinned' => $messageId,
            ], JSON_UNESCAPED_UNICODE)
        ];
        $result = self::create($chatData);
        return $result;
    }
    public static function create($chatData)
    {
        $table = SQL_TBL_TG_CHATS;
        $result = self::insert($chatData, $table);
        return $result;
    }
    public static function edit($data, $id)
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = json_encode($value, JSON_UNESCAPED_UNICODE);
            }
        }
        self::update($data, ['id' => $id], SQL_TBL_TG_CHATS);
        return true;
    }
    public static function jsonDecodeData($chatData)
    {
        $chatData['personal'] = json_decode($chatData['personal'], true);
        $chatData['data'] = json_decode($chatData['data'], true);
        return $chatData;
    }
}

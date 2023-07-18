<?

namespace app\models;

use app\core\Model;

class TelegramChats extends Model
{
    public static $table = SQL_TBL_TG_CHATS;
    public static function save($messageArray)
    {
        $table = self::$table;

        $uid = $messageArray['message']['from']['id'];

        $result = self::getChat($uid);
        if (!$result) {
            $chatData = ['uid' => $uid, 'personal' => ['id' => $uid], 'data' => ['last_seems' => $messageArray['message']['date']]];

            if (!empty($messageArray['message']['from']['first_name'])) {
                $chatData['personal']['first_name'] = $messageArray['message']['from']['first_name'];
            }
            if (!empty($messageArray['message']['from']['last_name'])) {
                $chatData['personal']['last_name'] = $messageArray['message']['from']['last_name'];
            }
            if (!empty($messageArray['message']['from']['username'])) {
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
        if (empty($chatData['personal']['first_name']) && !empty($messageArray['message']['from']['first_name'])) {
            $chatData['personal']['first_name'] = $messageArray['message']['from']['first_name'];
        }
        if (empty($chatData['personal']['last_name']) && !empty($messageArray['message']['from']['last_name'])) {
            $chatData['personal']['last_name'] = $messageArray['message']['from']['last_name'];
        }
        if (empty($chatData['personal']['username']) && !empty($messageArray['message']['from']['username'])) {
            $chatData['personal']['username'] = $messageArray['message']['from']['username'];
        }
        if ($messageArray['message']['chat']['type'] === 'private') {
            $chatData['data']['direct'] = true;
        }

        if (empty($chatData['personal']['nickname'])) {
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
        $table = self::$table;
        $result = self::query("SELECT * FROM $table WHERE uid = ?", [$uid], 'Assoc');
        if (empty($result)) {
            return false;
        }
        $result = self::jsonDecodeData($result[0]);
        return $result;
    }
    public static function getChatsList()
    {
        $table = self::$table;
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
        $table = self::$table;
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
        $table = self::$table;
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
            if (isset($chats[$i]['data']['pinned'])) {
                $result[$chats[$i]['uid']] = $chats[$i]['data']['pinned'];
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
        $table = self::$table;
        $result = self::insert($chatData, $table);
        return $result;
    }
    public static function edit($data, $id)
    {
        $table = self::$table;
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = json_encode($value, JSON_UNESCAPED_UNICODE);
            }
        }
        self::update($data, ['id' => $id], $table);
        return true;
    }
    public static function jsonDecodeData($chatData)
    {
        $chatData['personal'] = json_decode($chatData['personal'], true);
        $chatData['data'] = json_decode($chatData['data'], true);
        return $chatData;
    }
    public static function init()
    {
        $table = self::$table;
        self::query(
            "CREATE TABLE IF NOT EXISTS $table (
                id INT GENERATED BY DEFAULT AS IDENTITY PRIMARY KEY,
                user_id INT DEFAULT NULL,
                uid CHARACTER VARYING(250) NOT NULL DEFAULT '',
                personal JSON DEFAULT NULL,
                data JSON DEFAULT NULL,
                CONSTRAINT fk_user
                    FOREIGN KEY(user_id) 
                    REFERENCES users(id)
                    ON DELETE CASCADE
            );"
        );
    }
}

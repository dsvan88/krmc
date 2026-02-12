<?php

namespace app\models;

use app\core\Model;
use app\core\Sender;

class TelegramChats extends Model
{
    public static $table = SQL_TBL_TG_CHATS;
    public static $foreign = ['users' => Users::class];
    public static $jsonFields = ['personal', 'data'];

    public static function getPinnedMessage(int $chatId = 0): int
    {
        if (empty($chatId)) return false;

        $chatData = self::find($chatId);

        return empty($chatData['data']['pinned']) ? false : $chatData['data']['pinned'];
    }
    public static function savePinned(array $incomeMessage, int $messageId = 0): void
    {
        $chatId = $incomeMessage['message']['chat']['id'];
        $chatData = self::find($chatId);
        if (!$chatData) {
            self::createPinned($chatId, $incomeMessage, $messageId);
            return;
        }
        $data = $chatData['data'];

        $data['last_seems'] = $incomeMessage['message']['date'];
        $data['pinned'] = $messageId;

        self::edit(['data' => $data], $chatData['id']);
        return;
    }
    public static function clearPinned(int $chatId): void
    {
        $chatData = self::find($chatId);
        unset($chatData['data']['pinned']);
        self::edit(['data' => $chatData['data']], $chatData['id']);
        return;
    }
    public static function getChatsList(int $limit = 0, int $offset = 0)
    {
        $table = self::$table;
        $query = "SELECT * FROM $table ORDER BY id";

        if (!empty($limit))
            $query .= " LIMIT $limit OFFSET $offset";

        $result = self::query($query, [], 'Assoc');
        if (empty($result)) {
            return false;
        }
        for ($i = 0; $i < count($result); $i++) {
            $result[$i] = self::decodeJson($result[$i]);
        }
        return $result;
    }
    public static function getGroupChatsList()
    {
        $table = self::$table;
        $result = self::query("SELECT * FROM $table WHERE id LIKE '-%' ORDER BY id", [], 'Assoc');
        if (empty($result)) {
            return [];
        }
        for ($i = 0; $i < count($result); $i++) {
            $result[$i] = self::decodeJson($result[$i]);
        }
        return $result;
    }
    public static function getListUserNames(string $name = ''): array
    {
        if (empty($name)) {
            return [];
        }
        $table = self::$table;
        $results = self::query("SELECT personal->'$.username' FROM $table WHERE LOWER( personal->'$.username' ) LIKE ? ORDER BY id", ["%$name%"], 'Num');
        if (empty($results)) return [];
        $names = [];
        foreach ($results as $result) {
            $names[] = '@' . $result[0];
        };
        return $names;
    }
    public static function findByUserId(int $id = 0)
    {
        if (empty($id)) {
            return [];
        }

        $result = static::findBy('user_id', $id, 1);

        return empty($result) ? [] : self::decodeJson($result[0]);
    }
    public static function findByUserName(string $username = '')
    {
        if (empty($username)) {
            return [];
        }
        $table = self::$table;
        $result = self::query("SELECT * FROM $table WHERE LOWER ( personal->'$.username' ) = ? LIMIT 1", [strtolower($username)], 'Assoc');

        return empty($result) ? [] : self::decodeJson($result[0]);
    }
    public static function getDirectChats()
    {
        $table = self::$table;
        $result = self::query("SELECT * FROM $table WHERE data->'$.direct' = ?", ['true'], 'Assoc');
        if (empty($result)) {
            return [];
        }
        for ($i = 0; $i < count($result); $i++) {
            $result[$i] = self::decodeJson($result[$i]);
        }
        return $result;
    }
    public static function getChatsWithPinned()
    {
        $chats = self::getChatsList();
        $result = [];
        for ($i = 0; $i < count($chats); $i++) {
            if (empty($chats[$i]['data']['pinned'])) continue;
            $result[$chats[$i]['id']] = $chats[$i]['data']['pinned'];
        }
        return $result;
    }
    public static function nicknames(array $chatsData)
    {
        if (!empty($chatsData['user_id'])) {
            $userData = static::findByUserId();
            $chatsData['nickname'] = $userData['name'];
            return $chatsData;
        }

        $countChats = count($chatsData);
        $ids = [];
        for ($x = 0; $x < $countChats; $x++) {
            if (empty($chatsData[$x]['user_id'])) continue;
            $ids[] = $chatsData[$x]['user_id'];
        }

        $usersData = Users::findGroup('id', $ids);
        $countUsers = count($usersData);
        for ($x = 0; $x < $countUsers; $x++) {
            for ($y = 0; $y < $countChats; $y++) {
                if ($usersData[$x]['id'] != $chatsData[$y]['user_id']) continue;
                $chatsData[$y]['nickname'] = $usersData[$x]['name'];
            }
        }
        return $chatsData;
    }
    public static function avatars(array $chatsData)
    {
        if (!empty($chatsData['id'])) {
            $chatsData['avatar'] = Sender::getUserProfileAvatar($chatsData['id']);
        }

        $countChats = count($chatsData);
        for ($x = 0; $x < $countChats; $x++) {
            if (empty($chatsData[$x]['id']) || $chatsData[$x]['id'] < 0) continue;
            $chatsData[$x]['avatar'] = Sender::getUserProfileAvatar($chatsData[$x]['id']);
        }

        return $chatsData;
    }
    public static function createPinned(int $chatId, array $message = [], int $messageId = 0)
    {
        $chatData = [
            'id' => $chatId,
            'personal' => json_encode([
                'id' => $message['message']['chat']['id'],
                'title' => $message['message']['chat']['title'],
            ], JSON_UNESCAPED_UNICODE),
            'data' => json_encode([
                'last_seems' =>  $message['message']['date'],
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
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = json_encode($value, JSON_UNESCAPED_UNICODE);
            }
        }
        self::update($data, ['id' => $id]);
        return true;
    }
    public static function init()
    {
        $table = self::$table;
        foreach (self::$foreign as $key => $class) {
            $$key = $class::$table;
        }

        self::query(
            "CREATE TABLE IF NOT EXISTS $table (
                id INT NOT NULL PRIMARY KEY,
                user_id INT DEFAULT NULL,
                personal JSON DEFAULT NULL,
                data JSON DEFAULT NULL,
                CONSTRAINT fk_user_chat
                    FOREIGN KEY(user_id) 
                    REFERENCES $users(id)
                    ON DELETE CASCADE
            );"
        );
    }
}

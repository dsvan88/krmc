<?

namespace app\models;

use app\core\Model;
use app\core\Sender;
use app\core\Telegram\ChatAction;
use app\Repositories\SocialPointsRepository;
use app\Repositories\TelegramBotRepository;
use app\Repositories\TelegramChatsRepository;

class TelegramChats extends Model
{
    public static $table = SQL_TBL_TG_CHATS;
    public static $foreign = ['users' => Users::class];
    public static $jsonFields = ['personal', 'data'];

    public static function save()
    {
        $chatId = TelegramBotRepository::getChatId();
        $message = ChatAction::$message;

        $result = self::getChat($chatId);
        if (empty($result)) {
            $chatData = ['uid' => $chatId, 'personal' => ['id' => $chatId], 'data' => ['last_seems' => $message['message']['date']]];

            if (!empty($message['message']['from']['first_name'])) {
                $chatData['personal']['first_name'] = $message['message']['from']['first_name'];
            }
            if (!empty($message['message']['from']['last_name'])) {
                $chatData['personal']['last_name'] = $message['message']['from']['last_name'];
            }
            if (!empty($message['message']['from']['username'])) {
                $chatData['personal']['username'] = $message['message']['from']['username'];
            }
            if ($message['message']['chat']['type'] === 'private') {
                $chatData['data']['direct'] = true;
            }

            $userId = Contacts::getUserIdByContact('telegramid', $chatId);
            if (!empty($userId)) {
                $chatData['user_id'] = $userId;
                if (TelegramBotRepository::getChatId() === Settings::getMainTelegramId()) {
                    SocialPointsRepository::evaluateMessage($userId, $message['message']['text']);
                }
            }

            $chatData['personal'] = json_encode($chatData['personal'], JSON_UNESCAPED_UNICODE);
            $chatData['data'] = json_encode($chatData['data'], JSON_UNESCAPED_UNICODE);

            return self::insert($chatData);
        }
        $savedChatId = $result['id'];

        $chatData = $result;
        if (empty($chatData['personal']['first_name']) && !empty($message['message']['from']['first_name'])) {
            $chatData['personal']['first_name'] = $message['message']['from']['first_name'];
        }
        if (empty($chatData['personal']['last_name']) && !empty($message['message']['from']['last_name'])) {
            $chatData['personal']['last_name'] = $message['message']['from']['last_name'];
        }
        if ($message['message']['chat']['type'] === 'private') {
            $chatData['data']['direct'] = true;
        }
        $chatData['personal']['username'] = empty($message['message']['from']['username']) ? '' : $message['message']['from']['username'];

        TelegramChatsRepository::$pending = empty($chatData['personal']['pending']) ? '' : $chatData['personal']['pending'];

        $userId = Contacts::getUserIdByContact('telegramid', $chatId);
        if (!empty($userId)) {
            $chatData['user_id'] = $userId;

            if (!empty($chatData['personal']['username'])) {
                $contact = Contacts::getUserContact($userId, 'telegram');
                if (empty($contact['contact'])) {
                    Contacts::new(['telegram' => $chatData['personal']['username']], $userId);
                } elseif ($contact['contact'] !== $chatData['personal']['username']) {
                    Contacts::update(['contact' => $chatData['personal']['username']], ['id' => $contact['id']]);
                }
            }
        }
        $chatData['data']['last_seems'] = $message['message']['date'];

        $chatData['personal'] = json_encode($chatData['personal'], JSON_UNESCAPED_UNICODE);
        $chatData['data'] = json_encode($chatData['data'], JSON_UNESCAPED_UNICODE);

        $saveData = ['personal' => $chatData['personal'], 'data' => $chatData['data']];
        if (!empty($chatData['user_id'])) {
            $saveData['user_id'] = $chatData['user_id'];
        }
        self::update($saveData, ['id' => $savedChatId]);

        return true;
    }
    public static function getPinnedMessage(int $chatId = 0): int
    {
        if (empty($chatId)) return false;

        $chatData = self::getChat($chatId);

        return empty($chatData['data']['pinned']) ? false : $chatData['data']['pinned'];
    }
    public static function savePinned(array $incomeMessage, int $messageId = 0): void
    {
        $chatId = $incomeMessage['message']['chat']['id'];
        $chatData = self::getChat($chatId);
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
        $chatData = self::getChat($chatId);
        unset($chatData['data']['pinned']);
        self::edit(['data' => $chatData['data']], $chatData['id']);
        return;
    }
    public static function getChat($uid)
    {
        $result = self::findBy('uid', $uid);
        return empty($result) ? false : $result[0];
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
        $result = self::query("SELECT * FROM $table WHERE uid LIKE '-%' ORDER BY id", [], 'Assoc');
        if (empty($result)) {
            return [];
        }
        for ($i = 0; $i < count($result); $i++) {
            $result[$i] = self::decodeJson($result[$i]);
        }
        return $result;
    }
    public static function findByUserName(string $username = '')
    {
        if (empty($username)) {
            return [];
        }
        $table = self::$table;
        $result = self::query("SELECT * FROM $table WHERE personal->'$.username' = ? LIMIT 1", [$username], 'Assoc');

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
            $result[$chats[$i]['uid']] = $chats[$i]['data']['pinned'];
        }
        return $result;
    }
    public static function nicknames(array $chatsData)
    {
        if (!empty($chatsData['user_id'])) {
            $userData = Users::findBy('id', $chatsData['user_id']);
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
        if (!empty($chatsData['uid'])) {
            $chatsData['avatar'] = Sender::getUserProfileAvatar($chatsData['uid']);
        }

        $countChats = count($chatsData);
        for ($x = 0; $x < $countChats; $x++) {
            if (empty($chatsData[$x]['uid']) || $chatsData[$x]['uid'] < 0) continue;
            $chatsData[$x]['avatar'] = Sender::getUserProfileAvatar($chatsData[$x]['uid']);
        }

        return $chatsData;
    }
    public static function createPinned(int $chatId, array $message = [], int $messageId = 0)
    {
        $chatData = [
            'uid' => $chatId,
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
                id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
                user_id INT DEFAULT NULL,
                uid CHARACTER VARYING(250) NOT NULL DEFAULT '',
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

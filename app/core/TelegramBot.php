<?php

namespace app\core;

use app\mappers\Settings;
use Exception;

class TelegramBot
{
    public static $message = '';
    private static $botToken = '';
    private static $options = [];
    private static $params = [];
    public static $result = [];
    public static $curl = null;
    public static $close = true;
    public static $webhookLink = 'api/telegram/webhook';

    public function __construct(string $token = '')
    {
        static::set($token);
    }
    public static function set(?string $token = null)
    {
        static::$botToken = empty($token) ? static::getAuthData() : $token;
        if (empty(static::$options)) {
            static::$options = [
                CURLOPT_RETURNTRANSFER => true,
                // CURLOPT_RETURNTRANSFER => false,
                CURLOPT_POST => true,       // отправка данных методом POST
                CURLOPT_TIMEOUT => 10,      // максимальное время выполнения запроса
            ];
        }
        static::$curl = curl_init();
    }
    private static function getAuthData()
    {
        if (!empty(static::$botToken))
            return static::$botToken;
        return Settings::getBotToken();
    }

    public static function sendDice(int $chatId, string $emoji = '🎲')
    {
        $params['chat_id'] = $chatId; // id получателя сообщения
        $params['emoji'] = $emoji; // emoji: 🎲, 🎯, 🎳, 🎰, 🏀, ⚽

        return static::send('sendDice', $params);
    }

    public static function getChat(int $chatId)
    {
        $params['chat_id'] = $chatId; // id получателя сообщения

        return static::send('getChat', $params);
    }
    public static function sendMessage($userId, string $message = '', int $messageId = -1, array $replyMarkup = [])
    {
        $botToken = static::$botToken;
        $params = static::$params;
        $params['chat_id'] = is_array($userId) ? $userId[0] : $userId; // id получателя сообщения
        if ($message !== '') {
            $params['text'] = $message;
            $params['parse_mode'] = 'HTML';
        }
        if ($messageId > 0) {
            $params['reply_to_message_id'] = $messageId;
        }
        if (!empty($replyMarkup)) {
            $params['reply_markup'] = json_encode($replyMarkup);
        }

        $options = static::$options;
        $options[CURLOPT_URL] = "https://api.telegram.org/bot$botToken/sendMessage"; // адрес api телеграмм-бота
        $options[CURLOPT_POSTFIELDS] = $params;

        $curl = curl_init();

        curl_setopt_array($curl, $options);

        if (is_array($userId) && !empty($userId)) {
            for ($x = 1; $x < count($userId); $x++) {
                usleep(750000);
                $params['chat_id'] = $userId[$x];
                curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
                static::$result[] = json_decode(curl_exec($curl), true);
            }
            return static::$result;
        }
        static::$result = json_decode(curl_exec($curl), true);

        static::end();

        if (static::$result['ok']) {
            return [static::$result];
        }
        throw new Exception(json_encode(static::$result, JSON_UNESCAPED_UNICODE));
    }
    public static function sendMessageWithImage($userId, $message = '', $image = '', $messageId = -1)
    {
        $botToken = static::$botToken;
        $params = [
            'chat_id' => is_array($userId) ? $userId[0] : $userId, // id получателя сообщения
        ];
        if (!empty($message)) {
            $params['text'] = $message;
            $params['parse_mode'] = 'HTML';
        }
        if ($messageId !== -1) {
            $params['reply_to_message_id'] = $messageId;
        }
        if ($image !== '') {
            $image = Tech::getRequestProtocol() . "://{$_SERVER['SERVER_NAME']}$image";
            $params['text'] = "<a href='$image'>&#8205;</a>" . $params['text'];
            $params['disable_web_page_preview'] = false;
        }

        $options = static::$options;
        $options[CURLOPT_URL] = "https://api.telegram.org/bot$botToken/sendMessage"; // адрес api телеграмм-бота
        $options[CURLOPT_POSTFIELDS] = $params; // адрес api телеграмм-бота

        $curl = curl_init();

        curl_setopt_array($curl, $options);

        if (is_array($userId) && isset($userId[1])) {
            for ($x = 1; $x < count($userId); $x++) {
                usleep(750000);
                $params['chat_id'] = $userId[$x];
                curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
                static::$result[] = json_decode(curl_exec($curl), true);
            }
            return static::$result;
        }
        static::$result = json_decode(curl_exec($curl), true);

        static::end();

        if (static::$result['ok']) {
            return [static::$result];
        }
        throw new Exception(json_encode(static::$result, JSON_UNESCAPED_UNICODE));
    }
    public static function sendPhoto($userId, string $caption = '', string $image = '', $type = 'image/jpeg', $messageId = -1)
    {
        $botToken = static::$botToken;
        $params = [
            'chat_id' => is_array($userId) ? $userId[0] : $userId, // id получателя сообщения
        ];
        if (!empty($caption)) {
            $params['caption'] = $caption;
            $params['parse_mode'] = 'HTML';
        }
        if ($messageId !== -1) {
            $params['reply_to_message_id'] = $messageId;
        }

        if (!empty($image)) {
            $params['photo'] = strpos($image, 'https://') === false ?
                curl_file_create($image, $type, 'image') :
                $image;
        }
        $options = static::$options;
        $options[CURLOPT_URL] = "https://api.telegram.org/bot$botToken/sendPhoto"; // адрес api телеграмм-бота
        $options[CURLOPT_POSTFIELDS] = $params; // адрес api телеграмм-бота

        $curl = curl_init();


        curl_setopt_array($curl, $options);

        if (is_array($userId) && isset($userId[1])) {
            for ($x = 1; $x < count($userId); $x++) {
                usleep(750000);
                $params['chat_id'] = $userId[$x];
                curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
                static::$result[] = json_decode(curl_exec($curl), true);
            }
            return static::$result;
        }
        static::$result = json_decode(curl_exec($curl), true);

        static::end();

        if (static::$result['ok']) {
            return [static::$result];
        }
        throw new Exception(json_encode(static::$result, JSON_UNESCAPED_UNICODE));
    }
    public static function deleteMessage($chatId, $messageId)
    {
        $params['chat_id'] = $chatId; // id получателя сообщения
        $params['message_id'] = $messageId;

        return static::send('deleteMessage', $params);
    }
    public static function getMe()
    {
        static::send('getMe', []);
        return static::$result;
    }
    public static function webhookDelete()
    {
        return static::send('deleteWebhook');
    }
    public static function webhookSet(string $botToken)
    {
        if (strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) !== 'https')
            return false;

        $options = static::$options;
        $options[CURLOPT_URL] = "https://api.telegram.org/bot$botToken/setWebhook?url=https://$_SERVER[HTTP_HOST]/" . static::$webhookLink . '&secret_token=' . $_ENV['TG_SECRET_TOKEN'] ?? '';

        $curl = curl_init();
        curl_setopt_array($curl, $options);
        static::$result = json_decode(curl_exec($curl), true);

        static::end();

        if (static::$result['ok']) {
            static::$botToken = $botToken;
            return true;
        }
        return false;
    }
    public static function pinMessage($chatId, $messageId)
    {
        $params = [
            'chat_id' => $chatId, // id чата
            'message_id' => $messageId, // id закрепляемого сообщения
            'disable_notification' => true, // "Тихий" метод закрепления, без оповещения
        ];

        return static::send('pinChatMessage', $params);
    }
    public static function unpinMessage($chatId, $messageId)
    {
        $params = [
            'chat_id' => $chatId, // id чата
            'message_id' => $messageId
        ];

        return static::send('unpinChatMessage', $params);
    }
    public static function editMessage($chatId, $messageId, $message, array $replyMarkup = [])
    {
        $params = [
            'chat_id' => $chatId, // id чата
            'message_id' => $messageId, // id сообщения
            'text' => $message, // текст сообщения
            'parse_mode' => 'HTML', // режим отображения сообщения, не обязательный параметр
        ];
        if (!empty($replyMarkup)) {
            $params['reply_markup'] = json_encode($replyMarkup);
        }
        // error_log($params['reply_markup']);
        return static::send('editMessageText', $params);
    }
    public static function getUserProfilePhotos($userId = 0, $offset = 0, $limit = 1)
    {
        if (empty($userId)) {
            throw new Exception('UserID can’t be empty.');
        }
        $params = [
            'user_id' => $userId, // id користувача
            'offset' => $offset, // зміщення від початку, скільки треба пропустити фотографій користувача
            'limit' => $limit, // кількість фото користувача
        ];

        static::send('getUserProfilePhotos', $params);

        return static::$result;
    }
    public static function getFile(string $file_id = '')
    {
        if (empty($file_id)) {
            throw new Exception('UserID can’t be empty.');
        }
        $params = [
            'file_id' => $file_id, // id файлу
        ];

        static::send('getFile', $params);

        return static::$result;
    }

    public static function getUserProfileAvatar(int $userId = 0)
    {
        if (empty($userId)) {
            throw new Exception(__METHOD__ . ': UserID can’t be empty.');
        }

        static::$close = false;
        $profilePhotos = static::getUserProfilePhotos($userId);

        if (!$profilePhotos['ok'] || $profilePhotos['result']['total_count'] < 1) return false;

        $mainPhotoData = static::getFile($profilePhotos['result']['photos'][0][0]['file_id']);

        if (empty($mainPhotoData['result']['file_path'])) return false;

        $botToken = static::$botToken;
        $file_path = $mainPhotoData['result']['file_path'];
        $url = "https://api.telegram.org/file/bot$botToken/$file_path";

        static::$curl = curl_init();

        $options = [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true
        ];
        curl_setopt_array(static::$curl, $options);

        $fileContent = curl_exec(static::$curl);

        static::$close = true;
        static::end();

        return empty($fileContent) ? false : $fileContent;
    }
    /** 
     * ReactionTypeEmoji:
     *  type 	String 	Type of the reaction, always “emoji”
     *  emoji     String Reaction emoji. Currently, it can be one of "👍", "👎", "❤", "🔥", "🥰", "👏", "😁", "🤔", "🤯", "😱", "🤬", "😢", "🎉", "🤩", "🤮", "💩", "🙏", "👌", "🕊", "🤡", "🥱", "🥴", "😍", "🐳", "❤‍🔥", "🌚", "🌭", "💯", "🤣", "⚡", "🍌", "🏆", "💔", "🤨", "😐", "🍓", "🍾", "💋", "🖕", "😈", "😴", "😭", "🤓", "👻", "👨‍💻", "👀", "🎃", "🙈", "😇", "😨", "🤝", "✍", "🤗", "🫡", "🎅", "🎄", "☃", "💅", "🤪", "🗿", "🆒", "💘", "🙉", "🦄", "😘", "💊", "🙊", "😎", "👾", "🤷‍♂", "🤷", "🤷‍♀", "😡"
     *  */
    public static function setMessageReaction($chatId, $messageId, $reaction)
    {
        $params = [
            'chat_id' => $chatId, // id чата
            'message_id' => $messageId, // id сообщения
            'reaction' => json_encode([
                [
                    'type' => 'emoji',
                    'emoji' => $reaction
                ]
            ]),
            'is_big' => true,
        ];
        return static::send('setMessageReaction', $params);
    }
    public static function answerCallbackQuery(int $cqId = 0, string $text = '', bool $alert = false)
    {
        $params = [
            'callback_query_id' => $cqId, // id колл-бек події
            'text' => $text, // Текст повідомлення
            'show_alert' => $alert, // Показати як alert повідомлення, замість зникаючого.
        ];
        return static::send('answerCallbackQuery', $params);
    }

    public static function send(string $method = '', $params = [])
    {
        if (empty($method)) return false;

        $botToken = static::$botToken;
        $options = static::$options;

        if (!empty($params)) {
            $options[CURLOPT_POSTFIELDS] = $params;
        }
        $options[CURLOPT_URL] = "https://api.telegram.org/bot$botToken/$method";

        if (empty(static::$curl)) {
            static::$curl = curl_init();
        }
        curl_setopt_array(static::$curl, $options);
        static::$result = json_decode(curl_exec(static::$curl), true);
        // error_log(json_encode(static::$result,JSON_UNESCAPED_UNICODE));

        static::end();

        return !empty(static::$result['ok']);
    }

    public static function end()
    {
        if (static::$close) static::$curl = null;
    }
}

<?php

namespace app\core;

use app\models\Settings;
use Exception;
use LDAP\Result;

class TelegramBot
{
    public static $message = '';
    private static $botToken = '';
    private static $options = [];
    private static $params = [];
    private static $result = [];

    public function __construct($text = '')
    {
        self::set($text);
    }
    public static function set($text = '')
    {
        self::$botToken = self::getAuthData();
        if (empty(self::$options)) {
            self::$options = [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,       // отправка данных методом POST
                CURLOPT_TIMEOUT => 10,      // максимальное время выполнения запроса
            ];
        }
        if ($text !== '') {
            self::$params['text'] = $text;
        }
    }
    private static function getAuthData()
    {
        if (!empty(self::$botToken))
            return self::$botToken;
        return Settings::getBotToken();
    }

    public static function sendMessage($userId, $message = '', $messageId = -1)
    {
        $botToken = self::$botToken;
        $params = self::$params;
        $params['chat_id'] = is_array($userId) ? $userId[0] : $userId; // id получателя сообщения
        if ($message !== '') {
            $params['text'] = $message;
            $params['parse_mode'] = 'HTML';
        }
        if ($messageId !== -1) {
            $params['reply_to_message_id'] = $messageId;
        }
        $options = self::$options;
        $options[CURLOPT_URL] = "https://api.telegram.org/bot$botToken/sendMessage"; // адрес api телеграмм-бота
        $options[CURLOPT_POSTFIELDS] = $params; // адрес api телеграмм-бота

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
        if (static::$result['ok']) {
            return [static::$result];
        }
        throw new Exception(json_encode(static::$result, JSON_UNESCAPED_UNICODE));
    }
    public static function sendMessageWithImage($userId, $message = '', $image = '', $messageId = -1)
    {
        $botToken = self::$botToken;
        $params = self::$params;
        $params['chat_id'] = is_array($userId) ? $userId[0] : $userId; // id получателя сообщения
        if ($message !== '') {
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

        $options = self::$options;
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
        if (static::$result['ok']) {
            return [static::$result];
        }
        throw new Exception(json_encode(static::$result, JSON_UNESCAPED_UNICODE));
    }
    public static function sendPhoto($userId, string $caption = '', string $image = '', $type = 'image/jpeg', $messageId = -1)
    {
        $botToken = self::$botToken;
        $params = self::$params;
        $params['chat_id'] = is_array($userId) ? $userId[0] : $userId; // id получателя сообщения
        if (empty($caption)) {
            $params['caption'] = $caption;
            $params['parse_mode'] = 'HTML';
        }
        if ($messageId !== -1) {
            $params['reply_to_message_id'] = $messageId;
        }
        if (empty($image)) {
            $params['photo'] = curl_file_create($image, $type, 'image');
        }
        $options = self::$options;
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
        if (static::$result['ok']) {
            return [static::$result];
        }
        throw new Exception(json_encode(static::$result, JSON_UNESCAPED_UNICODE));
    }
    public static function deleteMessage($chatId, $messageId)
    {
        $params['chat_id'] = $chatId; // id получателя сообщения
        $params['message_id'] = $messageId;

        return self::send('deleteMessage', $params);
    }
    public static function getMe()
    {
        self::send('getMe', []);
        return static::$result;
    }
    public static function webhookDelete()
    {
        return self::send('deleteWebhook');
    }
    public static function webhookSet($botToken)
    {
        if (strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) !== 'https')
            return false;

        $options = self::$options;
        $options[CURLOPT_URL] = "https://api.telegram.org/bot$botToken/setWebhook?url=https://$_SERVER[HTTP_HOST]/api/telegram/webhook";

        $curl = curl_init();
        curl_setopt_array($curl, $options);
        static::$result = json_decode(curl_exec($curl), true);
        if (static::$result['ok']) {
            self::$botToken = $botToken;
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

        return self::send('pinChatMessage', $params);
    }
    public static function unpinMessage($chatId, $messageId)
    {
        $params = [
            'chat_id' => $chatId, // id чата
            'message_id' => $messageId
        ];

        return self::send('unpinChatMessage', $params);
    }
    public static function editMessage($chatId, $messageId, $message)
    {
        $params = [
            'chat_id' => $chatId, // id чата
            'message_id' => $messageId, // id сообщения
            'text' => $message, // текст сообщения
            'parse_mode' => 'HTML', // режим отображения сообщения, не обязательный параметр
        ];

        return self::send('editMessageText', $params);
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
            'reaction' => [
                [
                    'type' => 'emoji',
                    'emoji' => $reaction
                ]
            ],
        ];

        return self::send('setMessageReaction', $params);
    }

    public static function send(string $method = '', $params = [])
    {
        if (empty($method)) return false;

        $botToken = self::$botToken;
        $options = self::$options;

        if (!empty($params)) {
            $options[CURLOPT_POSTFIELDS] = $params;
        }
        $options[CURLOPT_URL] = "https://api.telegram.org/bot$botToken/$method";

        $curl = curl_init();
        curl_setopt_array($curl, $options);
        static::$result = json_decode(curl_exec($curl), true);

        error_log($method.': '.json_encode(static::$result, JSON_UNESCAPED_UNICODE));
        return !empty(static::$result['ok']);
    }
}

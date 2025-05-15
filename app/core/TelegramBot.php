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
    public static function sendMessage($userId, $message = '', $messageId = -1)
    {
        $botToken = self::$botToken;
        $params = self::$params;
        $params['chat_id'] = is_array($userId) ? $userId[0] : $userId; // id получателя сообщения
        if ($message !== '') {
            $params['text'] = $message;
            $params['parse_mode'] = 'HTML';
            // $params['disable_web_page_preview'] = false;
        }
        if ($messageId !== -1) {
            $params['reply_to_message_id'] = $messageId;
        }
        $options = self::$options;
        $options[CURLOPT_URL] = "https://api.telegram.org/bot$botToken/sendMessage"; // адрес api телеграмм-бота
        $options[CURLOPT_POSTFIELDS] = $params; // адрес api телеграмм-бота

        $result = [];
        $curl = curl_init();

        curl_setopt_array($curl, $options);

        if (is_array($userId) && !empty($userId)) {
            for ($x = 1; $x < count($userId); $x++) {
                usleep(750000);
                $params['chat_id'] = $userId[$x];
                curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
                $result[] = json_decode(curl_exec($curl), true);
            }
            return $result;
        }
        $result = json_decode(curl_exec($curl), true);
        if ($result['ok']) {
            return [$result];
        }
        throw new Exception(json_encode($result, JSON_UNESCAPED_UNICODE));
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

        $result = [];
        $curl = curl_init();

        curl_setopt_array($curl, $options);

        if (is_array($userId) && isset($userId[1])) {
            for ($x = 1; $x < count($userId); $x++) {
                usleep(750000);
                $params['chat_id'] = $userId[$x];
                curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
                $result[] = json_decode(curl_exec($curl), true);
            }
            return $result;
        } else
            return [json_decode(curl_exec($curl), true)];
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

        $result = [];
        $curl = curl_init();

        curl_setopt_array($curl, $options);

        if (is_array($userId) && isset($userId[1])) {
            for ($x = 1; $x < count($userId); $x++) {
                usleep(750000);
                $params['chat_id'] = $userId[$x];
                curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
                $result[] = json_decode(curl_exec($curl), true);
            }
            return $result;
        } else
            return [json_decode(curl_exec($curl), true)];
    }
    public static function deleteMessage($chatId, $messageId)
    {
        // $botToken = self::$botToken;

        $params['chat_id'] = $chatId; // id получателя сообщения
        $params['message_id'] = $messageId;

        return self::send('deleteMessage', $params);

        // $options = self::$options;
        // $options[CURLOPT_URL] = "https://api.telegram.org/bot$botToken/deleteMessage"; // адрес api телеграмм-бота
        // $options[CURLOPT_POSTFIELDS] = $params; // адрес api телеграмм-бота

        // $curl = curl_init();

        // curl_setopt_array($curl, $options);
        // return [json_decode(curl_exec($curl), true)];
    }
    public static function getMe()
    {
        self::send('getMe', [], $data);
        return $data;

        // $botToken = self::$botToken;

        // $options = self::$options;
        // $options[CURLOPT_URL] = "https://api.telegram.org/bot$botToken/getMe"; // адрес api телеграмм-бота
        // $curl = curl_init();
        // curl_setopt_array($curl, $options);
        // return json_decode(curl_exec($curl), true);
    }
    private static function getAuthData()
    {
        if (!empty(self::$botToken))
            return self::$botToken;
        return Settings::getBotToken();
    }
    public static function webhookDelete()
    {
        return self::send('deleteWebhook');

        // $botToken = self::$botToken;
        // $options = self::$options;
        // $options[CURLOPT_URL] = "https://api.telegram.org/bot$botToken/deleteWebhook";

        // $curl = curl_init();
        // curl_setopt_array($curl, $options);
        // $result = json_decode(curl_exec($curl), true);

        // return !empty($result['ok']);
    }
    public static function webhookSet($botToken)
    {
        if (strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) !== 'https')
            return false;

        $options = self::$options;
        $options[CURLOPT_URL] = "https://api.telegram.org/bot$botToken/setWebhook?url=https://$_SERVER[HTTP_HOST]/api/telegram/webhook";

        $curl = curl_init();
        curl_setopt_array($curl, $options);
        $result = json_decode(curl_exec($curl), true);
        if ($result['ok']) {
            self::$botToken = $botToken;
            return true;
        }
        return false;
    }
    public static function pinMessage($chatId, $messageId)
    {
        // $botToken = self::$botToken;
        $params = [
            'chat_id' => $chatId, // id чата
            'message_id' => $messageId, // id закрепляемого сообщения
            'disable_notification' => true, // "Тихий" метод закрепления, без оповещения
        ];

        return self::send('pinChatMessage', $params);

        // $options = self::$options;
        // $options[CURLOPT_URL] = "https://api.telegram.org/bot$botToken/pinChatMessage";
        // $options[CURLOPT_POSTFIELDS] = $params;

        // $curl = curl_init();
        // curl_setopt_array($curl, $options);
        // $result = json_decode(curl_exec($curl), true);

        // return !empty($result['ok']);
    }
    public static function unpinMessage($chatId, $messageId)
    {
        // $botToken = self::$botToken;
        $params = [
            'chat_id' => $chatId, // id чата
            'message_id' => $messageId
        ];

        return self::send('unpinChatMessage', $params);
        // $options = self::$options;
        // $options[CURLOPT_URL] = "https://api.telegram.org/bot$botToken/unpinChatMessage";
        // $options[CURLOPT_POSTFIELDS] = $params;

        // $curl = curl_init();
        // curl_setopt_array($curl, $options);
        // $result = json_decode(curl_exec($curl), true);

        // return !empty($result['ok']);
    }
    public static function editMessage($chatId, $messageId, $message)
    {
        // $botToken = self::$botToken;
        $params = [
            'chat_id' => $chatId, // id чата
            'message_id' => $messageId, // id сообщения
            'text' => $message, // текст сообщения
            'parse_mode' => 'HTML', // режим отображения сообщения, не обязательный параметр
        ];

        return self::send('editMessageText', $params);
        // $options = self::$options;
        // $options[CURLOPT_URL] = "https://api.telegram.org/bot$botToken/editMessageText";
        // $options[CURLOPT_POSTFIELDS] = $params;

        // $curl = curl_init();
        // curl_setopt_array($curl, $options);
        // $result = json_decode(curl_exec($curl), true);

        // return !empty($result['ok']);
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
                'type' => 'emoji',
                'emoji' => $reaction
            ],
        ];

        return self::send('setMessageReaction', $params);
    }

    public static function send(string $method = '', $params = [], array &$result = [])
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
        $result = json_decode(curl_exec($curl), true);

        error_log($method.': '.json_encode($result, JSON_UNESCAPED_UNICODE));
        return !empty($result['ok']);
    }
}

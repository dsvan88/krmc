<?php

namespace app\core;

use app\models\Settings;

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
        } else
            return [json_decode(curl_exec($curl), true)];
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
            $image = "{$_SERVER['HTTP_X_FORWARDED_PROTO']}://{$_SERVER['SERVER_NAME']}$image";
            $params['text'] = "<a href='$image'>&#8205;</a>" . $params['text'];
            $params['disable_web_page_preview'] = false;
        }
        /*         var_dump($params);
        echo PHP_EOL; */
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
    public static function sendPhoto($userId, $caption = '', $image = '', $type = 'image/jpeg', $messageId = -1)
    {
        $botToken = self::$botToken;
        $params = self::$params;
        $params['chat_id'] = is_array($userId) ? $userId[0] : $userId; // id получателя сообщения
        if ($caption !== '') {
            $params['caption'] = $caption;
            $params['parse_mode'] = 'HTML';
        }
        if ($messageId !== -1) {
            $params['reply_to_message_id'] = $messageId;
        }
        if ($image !== '') {
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
        $botToken = self::$botToken;

        $params['chat_id'] = $chatId; // id получателя сообщения
        $params['message_id'] = $messageId;

        $options = self::$options;
        $options[CURLOPT_URL] = "https://api.telegram.org/bot$botToken/deleteMessage"; // адрес api телеграмм-бота
        $options[CURLOPT_POSTFIELDS] = $params; // адрес api телеграмм-бота

        $curl = curl_init();

        curl_setopt_array($curl, $options);
        return [json_decode(curl_exec($curl), true)];
    }
    public static function getMe()
    {
        $botToken = self::$botToken;

        $options = self::$options;
        $options[CURLOPT_URL] = "https://api.telegram.org/bot$botToken/getMe"; // адрес api телеграмм-бота
        $curl = curl_init();
        curl_setopt_array($curl, $options);
        return json_decode(curl_exec($curl), true);
    }
    private static function getAuthData()
    {
        if (!empty(self::$botToken))
            return self::$botToken;
        return Settings::getBotToken();
    }
    public static function webhookDelete()
    {
        $botToken = self::$botToken;
        $options = self::$options;
        $options[CURLOPT_URL] = "https://api.telegram.org/bot$botToken/deleteWebhook";

        $curl = curl_init();
        curl_setopt_array($curl, $options);
        $result = json_decode(curl_exec($curl), true);
        if ($result['ok'])
            return true;
        return false;
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
        $botToken = self::$botToken;

        $params = [
            'chat_id' => $chatId, // id чата
            'message_id' => $messageId, // id закрепляемого сообщения
            'disable_notification' => true, // "Тихий" метод закрепления, без оповещения
        ];

        $options = self::$options;
        $options[CURLOPT_URL] = "https://api.telegram.org/bot$botToken/pinChatMessage";
        $options[CURLOPT_POSTFIELDS] = $params;

        $curl = curl_init();
        curl_setopt_array($curl, $options);
        $result = json_decode(curl_exec($curl), true);

        if ($result['ok'])
            return true;
        return false;
    }
    public static function unpinMessage($chatId, $messageId)
    {
        $botToken = self::$botToken;
        $params = [
            'chat_id' => $chatId, // id чата
            'message_id' => $messageId
        ];

        $options = self::$options;
        $options[CURLOPT_URL] = "https://api.telegram.org/bot$botToken/unpinChatMessage";
        $options[CURLOPT_POSTFIELDS] = $params;

        $curl = curl_init();
        curl_setopt_array($curl, $options);
        $result = json_decode(curl_exec($curl), true);

        if ($result['ok'])
            return true;
        return false;
    }
    public static function pinMessageAndSaveItsData($chatId, $messageId)
    {

        // $chatData = Settings::getChat($chatId);

        self::pinMessage($chatId, $messageId);
        // if (!isset($chatData['value']['pinned']) || $messageId !== $chatData['value']['pinned']) {
        //     self::unpinMessage($chatId, $chatData['value']['pinned']);
        //     $data = [
        //         'type' => 'tg-chat',
        //         'short_name' => $chatId,
        //         'name' => 'Чат з користувачем',
        //     ];

        //     if (isset($chatData['name'])) {
        //         $data['name'] = $chatData['name'];
        //     }

        //     if (isset($chatData['value'])) {
        //         $data['value'] = $chatData['value'];
        //     }

        //     $data['value']['pinned'] = $messageId;
        //     $data['value'] = json_encode($data['value'], JSON_UNESCAPED_UNICODE);
        //     Settings::save($data);
        // }
    }
    public static function editMessage($chatId, $messageId, $message)
    {
        $botToken = self::$botToken;
        $params = [
            'chat_id' => $chatId, // id чата
            'message_id' => $messageId, // id сообщения
            'text' => $message, // текст сообщения
            'parse_mode' => 'HTML', // режим отображения сообщения, не обязательный параметр
        ];

        $options = self::$options;
        $options[CURLOPT_URL] = "https://api.telegram.org/bot$botToken/editMessageText";
        $options[CURLOPT_POSTFIELDS] = $params;

        $curl = curl_init();
        curl_setopt_array($curl, $options);
        $result = json_decode(curl_exec($curl), true);

        if ($result['ok'])
            return true;
        return false;
    }
}

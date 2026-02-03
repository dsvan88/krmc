<?php

namespace app\core;

use app\models\Settings;
use Exception;

class TelegramInfoBot extends TelegramBot
{
    private static $botToken = '';
    private static $options = [];
    private static $params = [];

    private static function getAuthData()
    {
        if (!empty(self::$botToken))
            return self::$botToken;
        return Settings::getInfoBotToken();
    }
    public static function webhookSet($botToken)
    {
        if (strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) !== 'https')
            return false;

        $options = self::$options;
        $options[CURLOPT_URL] = "https://api.telegram.org/bot$botToken/setWebhook?url=https://$_SERVER[HTTP_HOST]/api/telegram/info-webhook";

        $curl = curl_init();
        curl_setopt_array($curl, $options);
        static::$result = json_decode(curl_exec($curl), true);

        self::end();

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
        return self::send('editMessageText', $params);
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

        self::send('getUserProfilePhotos', $params);

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

        self::send('getFile', $params);

        return static::$result;
    }

    public static function getUserProfileAvatar(int $userId = 0)
    {
        if (empty($userId)) {
            throw new Exception(__METHOD__ . ': UserID can’t be empty.');
        }

        self::$close = false;
        $profilePhotos = self::getUserProfilePhotos($userId);

        if (!$profilePhotos['ok'] || $profilePhotos['result']['total_count'] < 1) return false;

        $mainPhotoData = self::getFile($profilePhotos['result']['photos'][0][0]['file_id']);

        if (empty($mainPhotoData['result']['file_path'])) return false;

        $botToken = self::$botToken;
        $file_path = $mainPhotoData['result']['file_path'];
        $url = "https://api.telegram.org/file/bot$botToken/$file_path";

        self::$curl = curl_init();

        $options = [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true
        ];
        curl_setopt_array(self::$curl, $options);

        $fileContent = curl_exec(self::$curl);

        self::$close = true;
        self::end();

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
        return self::send('setMessageReaction', $params);
    }
    public static function answerCallbackQuery(int $cqId = 0, string $text = '', bool $alert = false)
    {
        $params = [
            'callback_query_id' => $cqId, // id колл-бек події
            'text' => $text, // Текст повідомлення
            'show_alert' => $alert, // Показати як alert повідомлення, замість зникаючого.
        ];
        return self::send('answerCallbackQuery', $params);
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

        if (empty(self::$curl)) {
            self::$curl = curl_init();
        }
        curl_setopt_array(self::$curl, $options);
        static::$result = json_decode(curl_exec(self::$curl), true);
        // error_log(json_encode(static::$result,JSON_UNESCAPED_UNICODE));


        self::end();

        return !empty(static::$result['ok']);
    }

    public static function end()
    {
        if (self::$close) curl_close(self::$curl);
    }
}

<?php

namespace app\Controllers;

use app\core\Locale;
use app\core\Sender;
use app\core\Telegram\ChatAction;
use app\core\TelegramBot;
use app\core\Validator;
use app\models\Contacts;
use app\models\Settings;
use app\models\TelegramChats;
use app\models\Users;
use app\Repositories\TelegramBotRepository;
use app\Repositories\TelegramChatsRepository;
use Exception;

class TelegramInfoBotController extends TelegramBotController
{
    public static $chatId = null;
    public static $command = '';
    public static $guestCommands = ['help', 'booking', 'nick', 'nickRelink', 'week', 'day', 'today', 'pending'];
    public static $CommandNamespace = '\\app\\Repositories\\TelegramInfoCommands';
    public static $AnswerNamespace = '\\app\\Repositories\\TelegramCbInfoAnswers';

    public static $resultMessage = '';
    public static $reaction = '';
    public static $replyMarkup = [];
    public static $type = '';

    public static function before()
    {
        $contentType = isset($_SERVER['CONTENT_TYPE']) ? strtolower(trim($_SERVER['CONTENT_TYPE'])) : '';
        if (strpos($contentType, 'application/json') ===  false) return false;

        if (APP_LOC !== 'local') {
            $ip = substr($_SERVER['REMOTE_ADDR'], 0, 4) === substr($_SERVER['SERVER_ADDR'], 0, 4) ? $_SERVER['HTTP_X_REAL_IP'] : $_SERVER['REMOTE_ADDR'];
            if (!Validator::validate('telegramIp', $ip) && $ip !== '127.0.0.1') {
                $techTgId = Settings::getTechTelegramId();
                $message = json_encode([
                    'REMOTE_ADDR' => $_SERVER['REMOTE_ADDR'],
                    'SERVER_ADDR' => $_SERVER['SERVER_ADDR'],
                    'HTTP_X_REAL_IP' => $_SERVER['HTTP_X_REAL_IP'],
                ]);
                if (empty($techTgId))
                    error_log($message);
                else
                    Sender::message($techTgId, $message);
                return false;
            }
        }

        $message = json_decode(trim(file_get_contents('php://input')), true);

        static::$type = empty($message['callback_query']) ? 'message' : 'callback_query';

        if (!is_array($message) || empty($message[static::$type]) || (empty($message[static::$type]['text']) && empty($message[static::$type]['data']))) {
            die('{"error":"1","title":"Error!","text":"Error: Nothing to get."}');
        }

        ChatAction::$message = $message;
        if (static::$type === 'message' && empty($message[static::$type]['from']['is_bot'])) {
            TelegramChats::save($message);
        }

        $langCode = 'uk';
        if (isset($message[static::$type]['from']['language_code']) && in_array($message[static::$type]['from']['language_code'], ['en', 'ru'])) {
            $langCode = $message[static::$type]['from']['language_code'];
        }
        Locale::change($langCode);

        self::$chatId = TelegramBotRepository::getChatId();

        $userTelegramId = TelegramBotRepository::getUserTelegramId();
        $userId = Contacts::getUserIdByContact('telegramid', $userTelegramId);

        if (static::$type === 'message') {
            static::$command = TelegramBotRepository::parseChatCommand($message['message']['text']);

            if (empty(static::$command)) {
                if (empty(TelegramChatsRepository::$pending)) return false;

                static::$command = TelegramChatsRepository::$pending;

                TelegramBotRepository::getCommonArguments($message['message']['text']);
            }

            if (empty($userId) && !in_array(self::$command, self::$guestCommands)) {
                Sender::message(self::$chatId, Locale::phrase('{{ Tg_Unknown_Requester }}'), $message['message']['message_id']);
                return false;
            }
        } else {
            ChatAction::$arguments = TelegramBotRepository::replyButtonDecode($message[static::$type]['data']);

            if (empty(ChatAction::$arguments['c'])) return false;

            self::$command =  ChatAction::$arguments['c'];

            if (empty($userId) && !in_array(self::$command, self::$guestCommands)) {
                Sender::callbackAnswer(ChatAction::$message['callback_query']['id'], Locale::phrase('{{ Tg_Unknown_Requester }}'), true);
                return false;
            }
        }

        ChatAction::$requester = Users::find($userId);

        if (!empty(CFG_MAINTENCE) && !empty(self::$command) && !TelegramBotRepository::hasAccess(ChatAction::$requester['privilege']['status'], 'admin')) {
            Sender::message(self::$chatId, Locale::phrase("I offer my deepest apologies, but I’m in the maintance mode 🧑‍💻 right now...\nPlease return to us a little later."));
            return false;
        }

        return true;
    }
}

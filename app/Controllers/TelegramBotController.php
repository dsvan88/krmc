<?php

namespace app\Controllers;

use app\core\Controller;
use app\core\Entities\Requester;
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

class TelegramBotController extends Controller
{
    public static $chatId = null;
    public static $command = '';
    public static $guestCommands = ['help', 'booking', 'nick', 'nickRelink', 'week', 'day', 'today', 'pending'];
    public static $CommandNamespace = '\\app\\Repositories\\TelegramCommands';
    public static $AnswerNamespace = '\\app\\Repositories\\TelegramCbAnswers';

    public static $resultMessage = '';
    public static $reaction = '';
    public static $replyMarkup = [];
    public static $type = '';

    public static function before()
    {
        $contentType = isset($_SERVER['CONTENT_TYPE']) ? strtolower(trim($_SERVER['CONTENT_TYPE'])) : '';
        if (strpos($contentType, 'application/json') ===  false) return false;

        if (APP_LOC !== 'local') {

            if (!Validator::validate('telegramToken', $_SERVER['HTTP_X_TELEGRAM_BOT_API_SECRET_TOKEN'] ?? '')) {
                http_response_code(403);
                return false;
            }

            $ip = substr($_SERVER['REMOTE_ADDR'], 0, 4) === substr($_SERVER['SERVER_ADDR'], 0, 4) ? $_SERVER['HTTP_X_REAL_IP'] : $_SERVER['REMOTE_ADDR'];
            if (!Validator::validate('telegramIp', $ip) && $ip !== '127.0.0.1') {
                $techTgId = Settings::getTechTelegramId();
                $message = "DEBUG:\nWrong Telegram IP:" . PHP_EOL
                    . 'REMOTE_ADDR: ' . (empty($_SERVER['REMOTE_ADDR']) ? '' : $_SERVER['REMOTE_ADDR']) . PHP_EOL
                    . 'SERVER_ADDR: ' . (empty($_SERVER['SERVER_ADDR']) ? '' : $_SERVER['SERVER_ADDR']) . PHP_EOL
                    . 'HTTP_X_REAL_IP: ' . (empty($_SERVER['HTTP_X_REAL_IP']) ? '' : $_SERVER['HTTP_X_REAL_IP']);
                if (empty($techTgId))
                    error_log($message);
                else
                    Sender::message($techTgId, $message);
            }
        }

        $message = json_decode(trim(file_get_contents('php://input')), true);

        static::$type = empty($message['callback_query']) ? 'message' : 'callback_query';

        if (!is_array($message) || empty($message[static::$type]) || (empty($message[static::$type]['text']) && empty($message[static::$type]['data']))) {
            die('{"error":"1","title":"Error!","text":"Error: Nothing to get."}');
        }

        ChatAction::$message = $message;
        if (static::$type === 'message' && empty($message[static::$type]['from']['is_bot'])) {
            TelegramChatsRepository::save($message);
        }

        $langCode = 'uk';
        if (isset($message[static::$type]['from']['language_code']) && in_array($message[static::$type]['from']['language_code'], ['en', 'ru'])) {
            $langCode = $message[static::$type]['from']['language_code'];
        }
        Locale::change($langCode);

        $requester = Requester::create();
        static::$chatId = TelegramBotRepository::getChatId();

        if (static::$type === 'message') {
            static::$command = TelegramBotRepository::parseChatCommand($message['message']['text']);

            if (empty(static::$command)) {
                if (empty(TelegramChatsRepository::isPendingState())) return false;

                static::$command = TelegramChatsRepository::$pending;

                TelegramBotRepository::getCommonArguments($message['message']['text']);
            }

            if (empty($requester->profile) && !in_array(static::$command, static::$guestCommands)) {
                Sender::message(static::$chatId, Locale::phrase('{{ Tg_Unknown_Requester }}'), $message['message']['message_id']);
                return false;
            }
        } else {
            ChatAction::$arguments = TelegramBotRepository::replyButtonDecode($message[static::$type]['data']);

            if (empty(ChatAction::$arguments['c'])) return false;

            static::$command =  ChatAction::$arguments['c'];

            if (empty($requester->profile) && !in_array(static::$command, static::$guestCommands)) {
                Sender::callbackAnswer(ChatAction::$message['callback_query']['id'], Locale::phrase('{{ Tg_Unknown_Requester }}'), true);
                return false;
            }
        }


        if (!empty(CFG_MAINTENCE) && !empty(static::$command) && !TelegramBotRepository::hasAccess($requester->privilege['status'], 'admin')) {
            Sender::message(static::$chatId, Locale::phrase("I offer my deepest apologies, but I’m in the maintance mode 🧑‍💻 right now...\nPlease return to us a little later."));
            return false;
        }

        if (empty($requester)) return true;

        ChatAction::$requester = $requester;

        if (static::$type === 'message') {
            if (static::$command === 'booking' && Users::isBanned('booking', $requester->profile->ban)) {
                Sender::delete(static::$chatId, $message['message']['message_id']);
                Sender::message($requester->chat->uid, Locale::phrase(['string' => "I’m deeply sorry, but you banned for that action:(...\nYour ban will be lifted at: <b>%s</b>", 'vars' => [date('d.m.Y', $requester->ban['expired'] + TIME_MARGE)]]));
                return false;
            }
            if (static::$chatId == Settings::getMainTelegramId() && Users::isBanned('chat', $requester->profile->ban)) {
                Sender::delete(static::$chatId, $message['message']['message_id']);
                Sender::message($requester->chat->uid, Locale::phrase(['string' => "I’m deeply sorry, but you banned for that action:(...\nYour ban will be lifted at: <b>%s</b>", 'vars' => [date('d.m.Y', $requester->ban['ban']['expired'] + TIME_MARGE)]]));
                return false;
            }
        } elseif (static::$command === 'booking' && Users::isBanned('booking', $requester->profile->ban)) {
            Sender::callbackAnswer(ChatAction::$message['callback_query']['id'], Locale::phrase(['string' => "I’m deeply sorry, but you banned for that action:(...\nYour ban will be lifted at: <b>%s</b>", 'vars' => [date('d.m.Y', $requester->ban['ban']['expired'] + TIME_MARGE)]]), true);
            return false;
        }
        return true;
    }
    public static function webhookAction()
    {
        // exit(json_encode(['message' => TelegramBotRepository::$message], JSON_UNESCAPED_UNICODE));
        try {
            if (in_array(static::$command, ['dice', 'd6', 'd64'])) {
                $tbBot = new TelegramBot;
                $tbBot->sendDice(static::$chatId, static::$command === 'd64' ? '🎰' : '🎲');
                return true;
            }
            $result = static::execute();

            if (empty($result)) return false;

            static::resolveResult($result);
        } catch (\Throwable $th) {
            $_SESSION['debug'][] = 'commonError: ' . $th->__toString();
            $_SESSION['debug'][] = 'messageData: ' . json_encode(ChatAction::$message, JSON_UNESCAPED_UNICODE);
            Sender::message(static::$chatId, Locale::phrase("Something went wrong😱!\nWe are deeply sorry for that😢\nI’ve informed our administrators about your situation, and they are fixing it right now!\nThank you for understanding!"));
        } finally {
            if (empty($_SESSION['debug'])) return true;

            $debugMessage = 'DEBUG:' . PHP_EOL . implode(PHP_EOL, $_SESSION['debug']);
            unset($_SESSION['debug']);
            Sender::message(Settings::getTechTelegramId(), $debugMessage);
        }
    }
    public static function execute(string $command = ''): array
    {
        if (empty($command)) {
            $command = static::$command;
        }

        if (static::$type === 'message') {
            $class = str_replace('/', '\\', static::$CommandNamespace . '\\' . ucfirst($command) . 'Command');
        } else {
            $class = str_replace('/', '\\', static::$AnswerNamespace . '\\' . ucfirst($command) . 'Answer');
        }

        if (!class_exists($class)) {
            error_log($class . ' doesnt exists!');
            return [];
        }

        $status = '';
        if (!empty(ChatAction::$requester->profile->status))
            $status = ChatAction::$requester->profile->status;

        if (TelegramBotRepository::hasAccess($status, $class::getAccessLevel()) || ($status === 'admin' && $command === 'chat')) {
            return $class::execute();
        }

        return [];
    }

    public static function resolveResult(array $result = []): int
    {
        if (empty($result)) {
            throw new Exception(__METHOD__ . ': $result can’t be empty!');
        }

        if (!empty($result['reaction']) && APP_LOC !== 'local') {
            Sender::setMessageReaction(static::$chatId, TelegramBotRepository::getMessageId(), $result['reaction']);
        }

        if (!empty($result['answer'])) {
            Sender::callbackAnswer(ChatAction::$message['callback_query']['id'], Locale::phrase($result['answer']), $result['alert']);
        }

        if (!empty($result['update'])) {
            foreach ($result['update'] as $item) {
                if (empty($item)) continue;
                if (!empty($item['replyMarkup']['inline_keyboard']))
                    TelegramBotRepository::encodeInlineKeyboard($item['replyMarkup']['inline_keyboard']);
                Sender::edit(
                    empty($item['chatId']) ? static::$chatId : $item['chatId'],
                    empty($item['messageId']) ? TelegramBotRepository::getMessageId() : $item['messageId'],
                    empty($item['message']) ? '' : Locale::phrase($item['message']),
                    empty($item['replyMarkup']) ? [] : $item['replyMarkup']
                );
            }
        }
        if (!empty($result['send'])) {
            foreach ($result['send'] as $item) {
                if (empty($item)) continue;
                if (!empty($item['replyMarkup']['inline_keyboard']))
                    TelegramBotRepository::encodeInlineKeyboard($item['replyMarkup']['inline_keyboard']);
                if (empty($item['message'])) {
                    Sender::message(Settings::getTechTelegramId(), 'I don’t know why, but a Chat Action returned an empty message.');
                } else {
                    $botResult = Sender::message(
                        empty($item['chatId']) ? static::$chatId : $item['chatId'],
                        Locale::phrase($item['message']),
                        // empty($item['replyOn']) ? 0 : $item['replyOn'],
                        0,
                        empty($item['replyMarkup']) ? [] : $item['replyMarkup']
                    );
                }
            }
        }
        if (!empty(ChatAction::$report)) {
            Sender::message(Settings::getAdminChatTelegramId(), Locale::phrase(ChatAction::$report));
        }

        return empty($botResult[0]['result']['message_id']) ? 0 : $botResult[0]['result']['message_id'];
    }
}

<?php

namespace app\Controllers;

use app\core\Controller;
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
    public static $guestCommands = ['help', 'booking', 'nick', 'nickRelink', 'week', 'day', 'today'];
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

        $data = trim(file_get_contents('php://input'));
        $message = json_decode($data, true);

        static::$type = empty($message['callback_query']) ? 'message' : 'callback_query';

        if (!is_array($message) || empty($message[static::$type]) || (empty($message[static::$type]['text']) && empty($message[static::$type]['data']))) {
            die('{"error":"1","title":"Error!","text":"Error: Nothing to get."}');
        }

        if (static::$type === 'message' && empty($message[static::$type]['from']['is_bot'])) {
            TelegramChats::save($message);
        }

        $langCode = 'uk';
        if (isset($message[static::$type]['from']['language_code']) && in_array($message[static::$type]['from']['language_code'], ['en', 'ru'])) {
            $langCode = $message[static::$type]['from']['language_code'];
        }
        Locale::change($langCode);
        ChatAction::$message = $message;

        self::$chatId = TelegramBotRepository::getChatId();

        $userTelegramId = TelegramBotRepository::getUserTelegramId();
        $userId = Contacts::getUserIdByContact('telegramid', $userTelegramId);

        if (static::$type === 'message') {
            static::$command = TelegramBotRepository::parseChatCommand($message['message']['text']);

            if (empty(static::$command)) {
                $pending = TelegramChatsRepository::isPendingState();

                if (empty($pending)) return false;

                static::$command = $pending;

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
                Sender::callbackAnswer($message[static::$type]['id'], Locale::phrase('{{ Tg_Unknown_Requester }}'), true);
                return false;
            }
        }

        ChatAction::$requester = Users::find($userId);

        if (!empty(CFG_MAINTENCE) && !empty(self::$command) && !TelegramBotRepository::hasAccess(ChatAction::$requester['privilege']['status'], 'admin')) {
            Sender::message(self::$chatId, Locale::phrase("I offer my deepest apologies, but I’m in the maintance mode 🧑‍💻 right now...\nPlease return to us a little later."));
            return false;
        }

        if (empty(ChatAction::$requester)) return true;

        if (static::$type === 'message' && empty(ChatAction::$requester)) {
            if (self::$command === 'booking' && Users::isBanned('booking', ChatAction::$requester['ban'])) {
                Sender::delete(self::$chatId, $message['message']['message_id']);
                Sender::message($userTelegramId, Locale::phrase(['string' => "I’m deeply sorry, but you banned for that action:(...\nYour ban will be lifted at: <b>%s</b>", 'vars' => [date('d.m.Y', ChatAction::$requester['ban']['expired'] + TIME_MARGE)]]));
                return false;
            }
            if (self::$chatId == Settings::getMainTelegramId() && Users::isBanned('chat', ChatAction::$requester['ban'])) {
                Sender::delete(self::$chatId, $message['message']['message_id']);
                Sender::message($userTelegramId, Locale::phrase(['string' => "I’m deeply sorry, but you banned for that action:(...\nYour ban will be lifted at: <b>%s</b>", 'vars' => [date('d.m.Y', ChatAction::$requester['ban']['expired'] + TIME_MARGE)]]));
                return false;
            }
        } elseif (self::$command === 'booking' && Users::isBanned('booking', ChatAction::$requester['ban'])) {
            Sender::callbackAnswer($message[static::$type]['id'], Locale::phrase(['string' => "I’m deeply sorry, but you banned for that action:(...\nYour ban will be lifted at: <b>%s</b>", 'vars' => [date('d.m.Y', ChatAction::$requester['ban']['expired'] + TIME_MARGE)]]), true);
            return false;
        }
        return true;
    }
    public static function webhookAction()
    {
        // exit(json_encode(['message' => TelegramBotRepository::$message], JSON_UNESCAPED_UNICODE));
        try {
            if (in_array(self::$command, ['dice', 'd6', 'd64'])) {
                $tbBot = new TelegramBot;
                $tbBot->sendDice(self::$chatId, self::$command === 'd64' ? '🎰' : '🎲');
                return true;
            }
            $result = static::execute();

            if (empty($result)) return false;

            static::resolveResult($result);
        } catch (\Throwable $th) {
            $_SESSION['debug'][] = 'commonError: ' . $th->__toString();
            $_SESSION['debug'][] = 'messageData: ' . json_encode(ChatAction::$message, JSON_UNESCAPED_UNICODE);
            Sender::message(self::$chatId, Locale::phrase("Something went wrong😱!\nWe are deeply sorry for that😢\nI’ve informed our administrators about your situation, and they are fixing it right now!\nThank you for understanding!"));
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
        if (!empty(ChatAction::$requester['privilege']['status']))
            $status = ChatAction::$requester['privilege']['status'];

        if (TelegramBotRepository::hasAccess($status, $class::getAccessLevel()) || ($status === 'admin' && $command === 'chat')) {
            return $class::execute();
        }

        return [];
    }

    public static function resolveResult(array $result = []): void
    {
        if (empty($result)) {
            throw new Exception(__METHOD__ . ': $result can’t be empty!');
        }

        if (!empty($result['reaction']) && APP_LOC !== 'local') {
            Sender::setMessageReaction(self::$chatId, TelegramBotRepository::getMessageId(), $result['reaction']);
        }

        if (!empty($result['answer'])) {
            Sender::callbackAnswer(ChatAction::$message['callback_query']['id'], Locale::phrase($result['answer']));
        }

        if (!empty($result['update'])) {
            foreach ($result['update'] as $item) {
                if (!empty($item['replyMarkup']['inline_keyboard']))
                    TelegramBotRepository::encodeInlineKeyboard($item['replyMarkup']['inline_keyboard']);
                Sender::edit(
                    empty($item['chatId']) ? self::$chatId : $item['chatId'],
                    empty($item['messageId']) ? TelegramBotRepository::getMessageId() : $item['messageId'],
                    empty($item['message']) ? '' : Locale::phrase($item['message']),
                    empty($item['replyMarkup']) ? [] : $item['replyMarkup']
                );
            }
        }
        if (!empty($result['send'])) {
            foreach ($result['send'] as $item) {
                if (!empty($item['replyMarkup']['inline_keyboard']))
                    TelegramBotRepository::encodeInlineKeyboard($item['replyMarkup']['inline_keyboard']);
                if (empty($item['message'])) {
                    Sender::message(Settings::getTechTelegramId(), 'I don’t know why, but a Chat Action returned an empty message.');
                } else {
                    $botResult = Sender::message(
                        empty($item['chatId']) ? self::$chatId : $item['chatId'],
                        Locale::phrase($item['message']),
                        // empty($item['replyOn']) ? 0 : $item['replyOn'],
                        0,
                        empty($item['replyMarkup']) ? [] : $item['replyMarkup']
                    );
                }
            }
        }
        // $report = ChatAction::getReport();
        if (!empty(ChatAction::$report)) {
            Sender::message(Settings::getAdminChatTelegramId(), Locale::phrase(ChatAction::$report));
        }

        if (self::$command === 'week') {
            self::unpinWeekMessage();
            self::pinMessage($botResult[0]['result']['message_id']);
        } elseif (in_array(self::$command, ['booking', 'reg', 'set', 'recall', 'promo'], true)) {
            self::updateWeekMessages();
        }
    }
    public static function updateWeekMessages(): bool
    {
        static::$type = 'message';
        $result = static::execute('week');

        if (!$result['result']) return false;

        $message = $result['send'][0]['message'];
        $chatData = TelegramChats::getChatsWithPinned();
        foreach ($chatData as $chatId => $pinned) {
            Sender::edit($chatId, $pinned, $message);

            if (Sender::$operator::$result['ok'] || Sender::$operator::$result['error_code'] != 400) continue;

            // Clear saved pinned message if not found in the chat.
            TelegramChats::clearPinned($chatId);
        }

        return true;
    }
    public static function pinMessage(int $messageId = 0)
    {
        if (empty($messageId)) return false;

        Sender::pin(self::$chatId, $messageId);
        TelegramChats::savePinned(ChatAction::$message, $messageId);

        return true;
    }
    public static function unpinWeekMessage()
    {
        $pinned = TelegramChats::getPinnedMessage(self::$chatId);

        if (empty($pinned)) return false;

        Sender::unpin(self::$chatId, $pinned);

        return true;
    }
}

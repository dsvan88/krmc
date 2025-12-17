<?php

namespace app\Controllers;

use app\core\Controller;
use app\core\Locale;
use app\core\Router;
use app\core\Sender;
use app\core\Tech;
use app\core\TelegramBot;
use app\core\Validator;
use app\models\Contacts;
use app\models\Days;
use app\models\Settings;
use app\models\TelegramChats;
use app\models\Users;
use app\Repositories\DayRepository;
use app\Repositories\TelegramBotRepository;

class TelegramBotController extends Controller
{
    public static $requester = [];
    public static $incomeMessage = [];
    public static $chatId = null;
    public static $isDirect = false;
    public static $command = '';
    public static $commandArguments = [];
    public static $guestCommands = ['help', 'nick', 'nickRelink', 'week', 'day', 'today'];
    public static $CommandNamespace = '\\app\\Repositories\\TelegramCommands';

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
        self::$incomeMessage = $message;
        self::$chatId = static::$type === 'message' ? self::$incomeMessage[static::$type]['chat']['id'] : self::$incomeMessage[static::$type]['message']['chat']['id'];

        $userTelegramId = self::$incomeMessage[static::$type]['from']['id'];
        $userId = Contacts::getUserIdByContact('telegramid', $userTelegramId);

        if (static::$type === 'message') {
            static::$command = TelegramBotRepository::parseChatCommand(trim(self::$incomeMessage['message']['text']));
            if (empty($userId) && !empty(static::$command) && !in_array(self::$command, self::$guestCommands)) {
                Sender::message(self::$chatId, Locale::phrase('{{ Tg_Unknown_Requester }}'), self::$incomeMessage['message']['message_id']);
                return false;
            }
        } else {
            self::$commandArguments = TelegramBotRepository::replyButtonDecode(self::$incomeMessage[static::$type]['data']);

            if (empty(self::$commandArguments['c'])) return false;

            self::$command =  self::$commandArguments['c'];

            if (empty($userId) && !in_array(self::$command, self::$guestCommands)) {
                Sender::callbackAnswer(self::$incomeMessage[static::$type]['id'], Locale::phrase('{{ Tg_Unknown_Requester }}'), true);
                return false;
            }
        }

        self::$requester = Users::find($userId);

        if (!empty(CFG_MAINTENCE) && !empty(self::$command) && !TelegramBotRepository::hasAccess(self::$requester['privilege']['status'], 'admin')) {
            Sender::message(self::$chatId, Locale::phrase("I offer my deepest apologies, but I’m in the maintance mode 🧑‍💻 right now...\nPlease return to us a little later."));
            return false;
        }

        if (static::$type === 'message') {
            if (self::$command === 'booking' && Users::isBanned('booking', self::$requester['ban'])) {
                Sender::delete(self::$chatId, self::$incomeMessage['message']['message_id']);
                Sender::message($userTelegramId, Locale::phrase(['string' => "I’m deeply sorry, but you banned for that action:(...\nYour ban will be lifted at: <b>%s</b>", 'vars' => [date('d.m.Y', self::$requester['ban']['expired'] + TIME_MARGE)]]));
                return false;
            }
            if (self::$chatId == Settings::getMainTelegramId() && Users::isBanned('chat', self::$requester['ban'])) {
                Sender::delete(self::$chatId, self::$incomeMessage['message']['message_id']);
                Sender::message($userTelegramId, Locale::phrase(['string' => "I’m deeply sorry, but you banned for that action:(...\nYour ban will be lifted at: <b>%s</b>", 'vars' => [date('d.m.Y', self::$requester['ban']['expired'] + TIME_MARGE)]]));
                return false;
            }
        } elseif (self::$command === 'booking' && Users::isBanned('booking', self::$requester['ban'])) {
            Sender::callbackAnswer(self::$incomeMessage[static::$type]['id'], Locale::phrase(['string' => "I’m deeply sorry, but you banned for that action:(...\nYour ban will be lifted at: <b>%s</b>", 'vars' => [date('d.m.Y', self::$requester['ban']['expired'] + TIME_MARGE)]]), true);
            return false;
        }
        return true;
    }
    public static function webhookAction()
    {
        // exit(json_encode(['message' => self::$incomeMessage], JSON_UNESCAPED_UNICODE));
        try {
            return static::$type === 'callback_query' ? self::executeCallbackQuery() : self::executeChatCommand();
        } catch (\Throwable $th) {
            $_SESSION['debug'][] = 'commonError: ' . $th->__toString();
            $_SESSION['debug'][] = 'messageData: ' . json_encode(self::$incomeMessage, JSON_UNESCAPED_UNICODE);
            Sender::message(self::$chatId, Locale::phrase("Something went wrong😱!\nWe are deeply sorry for that😢\nI’ve informed our administrators about your situation, and they are fixing it right now!\nThank you for understanding!"));
        } finally {
            if (empty($_SESSION['debug'])) return true;

            $debugMessage = 'DEBUG:' . PHP_EOL . implode(PHP_EOL, $_SESSION['debug']);
            unset($_SESSION['debug']);
            Sender::message(Settings::getTechTelegramId(), $debugMessage);
        }
    }
    public static function executeCallbackQuery()
    {
        $command = static::$command;
        TelegramBotRepository::init([
            'message' => static::$incomeMessage,
            'userData' => static::$requester,
            'arguments' => static::$commandArguments,
        ]);
        $result = TelegramBotRepository::$command();
        if (!empty($result['message'])) {
            Sender::callbackAnswer(self::$incomeMessage[static::$type]['id'], Locale::phrase($result['message']));
        }
        if (!empty($result['update'])) {
            foreach ($result['update'] as $item) {
                if (!empty($item['replyMarkup']['inline_keyboard']))
                    TelegramBotRepository::encodeInlineKeyboard($item['replyMarkup']['inline_keyboard']);
                Sender::edit(
                    empty($item['chatId']) ? self::$chatId : $item['chatId'],
                    empty($item['messageId']) ? self::$incomeMessage[static::$type]['message']['message_id'] : $item['messageId'],
                    empty($item['message']) ? '' : Locale::phrase($item['message']),
                    empty($item['replyMarkup']) ? [] : $item['replyMarkup']
                );
            }
        }
        if (!empty($result['send'])) {
            foreach ($result['send'] as $item) {
                if (!empty($send['replyMarkup']['inline_keyboard']))
                    TelegramBotRepository::encodeInlineKeyboard($item['replyMarkup']['inline_keyboard']);
                Sender::message(
                    empty($item['chatId']) ? self::$chatId : $item['chatId'],
                    empty($item['message']) ? '' : Locale::phrase($item['message']),
                    empty($item['replyOn']) ? 0 : $item['replyOn'],
                    empty($item['replyMarkup']) ? [] : $item['replyMarkup']
                );
            }
        }
        if (in_array(self::$command, ['booking'], true)) {
            self::updateWeekMessages();
        }
        return true;
    }
    public static function executeChatCommand()
    {
        if (in_array(self::$command, ['dice', 'd6', 'd64'])) {
            $tbBot = new TelegramBot;
            $tbBot->sendDice(self::$chatId, self::$command === 'd64' ? '🎰' : '🎲');
            return true;
        }
        if (!self::execute()) {
            if (empty(self::$resultMessage)) return false;
            $botResult = Sender::message(
                Settings::getTechTelegramId(),
                'Message: ' . json_encode(self::$incomeMessage, JSON_UNESCAPED_UNICODE), /* self::$requester ,*/
                'Arguments: ' . json_encode(TelegramBotRepository::parseArguments(self::$commandArguments), JSON_UNESCAPED_UNICODE)
            );
        }
        if (!empty(self::$reaction)) {
            Sender::setMessageReaction(self::$chatId, self::$incomeMessage['message']['message_id'], self::$reaction);
        }

        if (!empty(self::$replyMarkup['inline_keyboard']))
            TelegramBotRepository::encodeInlineKeyboard(self::$replyMarkup['inline_keyboard']);

        $botResult = Sender::message(self::$chatId, Locale::phrase(self::$resultMessage), 0, self::$replyMarkup);

        if ($botResult[0]['ok']) {
            if (self::$command === 'week') {
                self::unpinWeekMessage();
                self::pinMessage($botResult[0]['result']['message_id']);
            }
            if (in_array(self::$command, ['booking', 'reg', 'set', 'recall', 'promo'], true)) {
                self::updateWeekMessages();
            }
        }
        return true;
    }
    public static function execute(string $command = ''): array
    {

        if (empty($command)) {
            $command = self::$command;
        }

        $class = ucfirst($command) . 'Command';
        $class = str_replace('/', '\\', self::$CommandNamespace . '\\' . $class);

        if (!class_exists($class)) {
            return [];
        }

        $ready = $class::set([
            // 'operatorClass' => self::class,
            'requester' => self::$requester,
            'message' => self::$incomeMessage,
            'argumets' => self::$commandArguments,
        ]);

        if (empty($ready)) return [
            'message' => $class::$status,
        ];

        $status = empty(self::$requester['privilege']['status']) ? '' : self::$requester['privilege']['status'];
        if (!TelegramBotRepository::hasAccess($status, $class::getAccessLevel())) {
            return [];
        }

        return $class::execute(self::$commandArguments, self::$resultMessage, self::$reaction, self::$replyMarkup);
    }
    // public static function checkAccess(string $level = 'all')
    // {
    //     $levels = Router::$accessLevels;
    //     $status = 'all';

    //     if (!empty(self::$requester['privilege']['status']))
    //         $status = self::$requester['privilege']['status'];

    //     if (!empty(self::$requester) && $status === 'all')
    //         $status = 'user';

    //     if (!self::$isDirect && self::$chatId != Settings::getTechTelegramId()) {
    //         $status = $levels[$status] > 1 ? 'trusted' : 'user';
    //     }
    //     return $levels[$level] <= $levels[$status];
    // }
    public static function updateWeekMessages(): bool
    {
        self::execute('week');

        $chatData = TelegramChats::getChatsWithPinned();
        foreach ($chatData as $chatId => $pinned) {
            Sender::edit($chatId, $pinned, self::$resultMessage);

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
        TelegramChats::savePinned(self::$incomeMessage, $messageId);

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

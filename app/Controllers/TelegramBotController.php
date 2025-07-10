<?php

namespace app\Controllers;

use app\core\Controller;
use app\core\Locale;
use app\core\Sender;
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
    private static $techTelegramId = null;
    private static $mainGroupTelegramId = null;

    public static $requester = [];
    public static $incomeMessage = [];
    public static $chatId = null;
    public static $command = '';
    public static $commandArguments = [];
    public static $guestCommands = ['help', 'nick', 'week', 'day', 'today'];
    public static $CommandNamespace = '\\app\\Repositories\\TelegramCommands';

    public static $resultMessage = '';
    public static $reaction = '';

    public static function before()
    {
        $contentType = isset($_SERVER['CONTENT_TYPE']) ? strtolower(trim($_SERVER['CONTENT_TYPE'])) : '';
        if (strpos($contentType, 'application/json') ===  false) return false;

        if (APP_LOC !== 'local') {
            $ip = substr($_SERVER['REMOTE_ADDR'], 0, 4) === substr($_SERVER['SERVER_ADDR'], 0, 4) ? $_SERVER['HTTP_X_REAL_IP'] : $_SERVER['REMOTE_ADDR'];
            // if (!Validator::validate('telegramIp', $ip) && $ip !== '127.0.0.1') {
            if (!Validator::validate('telegramIp', $ip)) {
                self::$techTelegramId = Settings::getTechTelegramId();
                $message = json_encode([
                    'REMOTE_ADDR' => $_SERVER['REMOTE_ADDR'],
                    'SERVER_ADDR' => $_SERVER['SERVER_ADDR'],
                    'HTTP_X_REAL_IP' => $_SERVER['HTTP_X_REAL_IP'],
                ]);
                if (empty(self::$techTelegramId))
                    error_log($message);
                else
                    Sender::message(self::$techTelegramId, $message);
                return false;
            }
        }

        $data = trim(file_get_contents('php://input'));
        $message = json_decode($data, true);

        if (!is_array($message) || empty($message['message']) || empty($message['message']['text'])) {
            die('{"error":"1","title":"Error!","text":"Error: Nothing to get."}');
        }

        if (empty($message['message']['from']['is_bot'])) {
            TelegramChats::save($message);
        }

        $langCode = 'uk';
        if (isset($message['message']['from']['language_code']) && in_array($message['message']['from']['language_code'], ['en', 'ru'])) {
            $langCode = $message['message']['from']['language_code'];
        }
        Locale::change($langCode);

        $text = trim($message['message']['text']);

        $command = self::parseCommand($text);
        self::$incomeMessage = $message;
        self::$techTelegramId = Settings::getTechTelegramId();
        self::$mainGroupTelegramId = Settings::getMainTelegramId();
        self::$chatId = $message['message']['chat']['id'];

        $userTelegramId = $message['message']['from']['id'];

        $userId = Contacts::getUserIdByContact('telegramid', $userTelegramId);

        if (empty($userId) && $command && !in_array(self::$command, self::$guestCommands)) {
            Sender::message(self::$chatId, Locale::phrase('{{ Tg_Unknown_Requester }}'), self::$incomeMessage['message']['message_id']);
            return false;
        }
        self::$requester = Users::find($userId);

        if (self::$command === 'booking' && Users::isBanned('booking', self::$requester['ban'])) {
            Sender::delete(self::$chatId, $message['message']['message_id']);
            Sender::message($userTelegramId, Locale::phrase(['string' => "I’m deeply sorry, but you banned for that action:(...\nYour ban will be lifted at: <b>%s</b>", 'vars' => [date('d.m.Y', self::$requester['ban']['expired'] + TIME_MARGE)]]));
            return false;
        }

        if (self::$chatId == self::$mainGroupTelegramId && Users::isBanned('chat', self::$requester['ban'])) {
            Sender::delete(self::$chatId, $message['message']['message_id']);
            Sender::message($userTelegramId, Locale::phrase(['string' => "I’m deeply sorry, but you banned for that action:(...\nYour ban will be lifted at: <b>%s</b>", 'vars' => [date('d.m.Y', self::$requester['ban']['expired'] + TIME_MARGE)]]));
            return false;
        }
        return $command;
    }
    public static function webhookAction()
    {
        // exit(json_encode(['message' => self::$incomeMessage], JSON_UNESCAPED_UNICODE));
        try {
            if (!self::execute()) {
                if (empty(self::$resultMessage)) return false;
                if (!empty($_SESSION['debug'])) {
                    self::$incomeMessage['debug'] = PHP_EOL . 'DEBUG:' . PHP_EOL . PHP_EOL . implode(PHP_EOL, $_SESSION['debug']);
                    unset($_SESSION['debug']);
                }
                $botResult = Sender::message(self::$techTelegramId, json_encode([self::$incomeMessage, /* self::$requester ,*/ self::parseArguments(self::$commandArguments)], JSON_UNESCAPED_UNICODE));
            }

            if (!empty(self::$reaction)) {
                // https://core.telegram.org/bots/api#setmessagereaction
                // https://core.telegram.org/bots/api#reactiontype
                // Sender::message(self::$chatId, self::$reaction, self::$incomeMessage['message']['message_id']);
                Sender::setMessageReaction(self::$chatId, self::$incomeMessage['message']['message_id'], self::$reaction);
            }

            $botResult = Sender::message(self::$chatId, Locale::phrase(self::$resultMessage));

            if ($botResult[0]['ok']) {
                if (self::$command === 'week') {
                    self::unpinWeekMessage();
                    self::pinMessage($botResult[0]['result']['message_id']);
                }
                /*                 if (in_array($command, ['reg', 'set', 'week', 'recall', 'today', 'day', 'promo'], true) && self::$chatId !== self::$techTelegramId) {
                    Sender::delete(self::$chatId, self::$incomeMessage['message']['message_id']);
                } */
                if (in_array(self::$command, ['booking', 'reg', 'set', 'recall', 'promo'], true)) {
                    self::updateWeekMessages();
                }
            }
        } catch (\Throwable $th) {
            $debugMessage = [
                'commonError' => $th->__toString(),
                'messageData' => self::$incomeMessage,
            ];
            if (!empty($_SESSION['debug'])) {
                $debugMessage['debug'] = PHP_EOL . 'DEBUG:' . PHP_EOL . implode(PHP_EOL, $_SESSION['debug']);
                unset($_SESSION['debug']);
            }
            Sender::message(self::$techTelegramId, json_encode($debugMessage, JSON_UNESCAPED_UNICODE));
            Sender::message(self::$chatId, Locale::phrase("Something went wrong😱!\nWe are deeply sorry for that😢\nI’ve informed our administrators about your situation, and they are fixing it right now!\nThank you for understanding!"));
        }
    }
    public static function parseCommand(string $text): bool
    {
        $_text = mb_strtolower(str_replace('на ', '', $text), 'UTF-8');
        $days = DayRepository::getDayNamesForCommand();
        if (preg_match("/^([+-])\s{0,3}($days)/ui", $_text, $match) === 1) {
            $arguments['method'] = $match[1];
            $arguments['dayName'] = $match[2];
            if (preg_match('/([0-2][0-9])(:[0-5][0-9]){0,1}/', str_replace('.', ':', $_text), $match) === 1) {
                $arguments['arrive'] = $match[0];
                if (strlen($arguments['arrive']) < 3) {
                    $arguments['arrive'] .= ':00';
                }
            }
            if (preg_match('/\([^)]+\)/', $text, $prim) === 1) {
                $arguments['prim'] = mb_substr($prim[0], 1, -1, 'UTF-8');
            } elseif (preg_match('/\?/', $_text) === 1) {
                $arguments['prim'] = '?';
            }
            self::$command = 'booking';
            self::$commandArguments = $arguments;
            return true;
        }
        // if (preg_match('/^[+-]\s{0,3}(пн|пон|вт|вів|ср|сер|чт|чет|пт|пят|п’ят|сб|суб|вс|вос|нед|нд|сг|сег|сьо|зав|mon|tue|wed|thu|fri|sat|sun|tod|tom)/ui', $_text) === 1) {
        //     preg_match_all('/[+-]\s{0,3}(пн|пон|вт|вів|ср|сер|чт|чет|пт|пят|п’ят|сб|суб|вс|вос|нед|нд|сг|сег|сьо|зав|mon|tue|wed|thu|fri|sat|sun|tod|tom)/ui', str_replace('.', ':', $_text), $matches);
        //     $arguments = $matches[0];
        //     if (preg_match('/\([^)]+\)/', $text, $prim) === 1) {
        //         $arguments['prim'] = mb_substr($prim[0], 1, -1, 'UTF-8');
        //     }
        //     self::$command = 'booking';
        //     self::$commandArguments = $arguments;
        //     return true;
        // }
        if (preg_match('/^[+]\s{0,3}[0-2]{0,1}[0-9]/', $_text) === 1) {
            preg_match('/^(\+)\s{0,3}([0-2]{0,1}[0-9])(:[0-5][0-9]){0,1}/i', mb_strtolower(str_replace('.', ':', $_text), 'UTF-8'), $matches);

            if ($matches[2] < 8 || $matches[2] > 23) return false;
            if (empty($matches[3])) $matches[3] = ':00';

            $arguments['method'] = '+';
            $arguments['dayName'] = 'tod';
            $arguments['arrive'] = $matches[2] . $matches[3];

            if (preg_match('/\([^)]+\)/', $text, $prim) === 1) {
                $arguments['prim'] = mb_substr($prim[0], 1, -1, 'UTF-8');
            }
            self::$command = 'booking';
            self::$commandArguments = $arguments;
            return true;
        }
        if ($text[0] === '/') {
            $command = mb_substr($text, 1, NULL, 'UTF-8');

            $spacePos = mb_strpos($command, ' ', 0, 'UTF-8');
            if ($spacePos !== false) {
                $command = mb_substr($command, 0, $spacePos, 'UTF-8');
            }
            $commandLen = mb_strlen($command);
            $atPos = mb_strpos($command, '@', 0, 'UTF-8'); // at = @ in English context
            if ($atPos !== false) {
                $command = mb_substr($command, 0, $atPos, 'UTF-8');
                $commandLen = $atPos;
            }
            $command = strtolower($command);

            if (in_array($command, ['?', 'help', 'start'])) {
                self::$command = 'help';
                return true;
            }

            if (in_array($command, ['reg', 'set'], true)) {
                $text = mb_substr($text, $commandLen + 1, NULL, 'UTF-8');
                $arguments = explode(',', mb_strtolower(str_replace('на ', '', $text)));
                if (preg_match('/\([^)]+\)/', $text, $prim) === 1) {
                    $arguments['prim'] = mb_substr($prim[0], 1, -1, 'UTF-8');
                }
                self::$command = $command;
                self::$commandArguments = $arguments;
                return true;
            }
            // preg_match_all('/([a-zA-Zа-яА-ЯрРсСтТуУфФчЧхХШшЩщЪъЫыЬьЭэЮюЄєІіЇїҐґ.0-9]+)/', trim(mb_substr($text, $commandLen + 1, NULL, 'UTF-8')), $matches);
            preg_match_all('/([a-zа-яєіїґ.0-9#-]+)/ui', trim(mb_substr($text, $commandLen + 1, NULL, 'UTF-8')), $matches);

            self::$command = $command;
            self::$commandArguments = $matches[0];
            return true;
        }
        return false;
    }
    public static function parseArguments($arguments)
    {
        $requestData = [
            'method' => '+',
            'arrive' => '',
            'prim' => '',
            'dayNum' => -1,
            'userId' => 0,
        ];
        if (isset($arguments['prim'])) {
            $requestData['prim'] = $arguments['prim'];
            unset($arguments['prim']);
        }

        foreach ($arguments as $value) {
            $value = trim($value);
            if (preg_match('/^(\+|-)[^0-9]/', $value)) {

                $requestData['method'] = $value[0];
                $withoutMethod = trim(mb_substr($value, 1, 6, 'UTF-8'));
                $dayName = mb_strtolower(mb_substr($withoutMethod, 0, 3, 'UTF-8'), 'UTF-8');

                self::parseDayNum($dayName, $requestData);
            } elseif (preg_match('/^\d{2}:\d{2}$/', $value) === 1 && empty($requestData['arrive'])) {
                $requestData['arrive'] = $value;
            } elseif (preg_match('/^(\+|-)\d{1,2}/', $value, $match) === 1) {
                $requestData['nonames'] = substr($match[0], 1);
            } elseif ($requestData['userId'] < 2) {
                $value = str_ireplace(['m', 'c', 'o', 'p', 'x', 'a'], ['м', 'с', 'о', 'р', 'х', 'а'], $value);
                $userRegData = Users::getDataByName($value);
                if ($userRegData) {
                    $requestData['userId'] = $userRegData['id'];
                    $requestData['userName'] = $userRegData['name'];
                } else
                    $requestData['probableUserName'] = $value;
            }
        }

        if (empty($requestData['currentDay']))  self::parseDayNum('tod', $requestData);

        return $requestData;
    }
    public static function parseDayNum(string $daySlug, array &$requestData): bool
    {

        $requestData['currentDay'] = Days::current();

        $daySlug = mb_strtolower($daySlug, 'UTF-8');
        if (mb_strlen($daySlug, 'UTF-8') > 3) {
            $daySlug = mb_substr($daySlug, 0, 3);
        }
        if (in_array($daySlug, DayRepository::$techDaysArray['today'], true)) {
            $requestData['dayNum'] = $requestData['currentDay'];
            return true;
        } elseif (in_array($daySlug, DayRepository::$techDaysArray['tomorrow'], true)) {
            $dayNum = $requestData['currentDay'] + 1;
            if ($dayNum === 7)
                $dayNum = 0;
            $requestData['dayNum'] = $dayNum;
            return true;
        } else {
            foreach (DayRepository::$daysArray as $num => $daysNames) {
                if (in_array($daySlug, $daysNames, true)) {
                    $requestData['dayNum'] = $num;
                    return true;
                }
            }
        }
        return false;
    }
    public static function execute($command = null)
    {

        if (empty($command)) {
            $command = self::$command;
        }

        $class = ucfirst($command) . 'Command';
        $class = str_replace('/', '\\', self::$CommandNamespace . '\\' . $class);

        if (!class_exists($class)) {
            return false;
        }

        $ready = $class::set([
            'operatorClass' => self::class,
            'requester' => self::$requester,
            'message' => self::$incomeMessage
        ]);

        if (!$ready) return false;

        $accessLevel = $class::getAccessLevel();

        if (!self::checkAccess($accessLevel)) {
            return false;
        }

        return $class::execute(self::$commandArguments);
    }
    public static function checkAccess(string $level = 'guest')
    {
        $levels = ['guest' => 0, 'user' => 1, 'trusted' => 2, 'manager' => 3, 'admin' => 4, 'root' => 5];
        $status = 'guest';

        if (!empty(self::$requester['privilege']['status']))
            $status = self::$requester['privilege']['status'];

        if (!empty(self::$requester) && $status === 'guest')
            $status = 'user';

        if (self::$incomeMessage['message']['chat']['type'] !== 'private' && self::$chatId != self::$techTelegramId && $levels[$status] > 1) {
            $status = 'user';
        }
        return $levels[$level] <= $levels[$status];
    }
    public static function updateWeekMessages(): bool
    {
        self::execute('week');

        $chatData = TelegramChats::getChatsWithPinned();
        foreach ($chatData as $chatId => $pinned) {
            Sender::edit($chatId, $pinned, self::$resultMessage);

            if (Sender::$operator::$result['ok'] || Sender::$operator::$result['error_code'] != 400) continue;

            Sender::message(
                self::$techTelegramId,
                'chatId = ' . $chatId . PHP_EOL
                    . 'incomeMessage = ' . self::$incomeMessage['message']['chat']['id'] . PHP_EOL
                    . 'pinned = ' . $pinned . PHP_EOL
                    . 'ok = ' . Sender::$operator::$result['ok'] . PHP_EOL
                    . 'error_code = ' . Sender::$operator::$result['error_code'] . PHP_EOL
                    . 'result: ' . PHP_EOL
                    . json_encode(Sender::$operator::$result, JSON_UNESCAPED_UNICODE)
            );

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

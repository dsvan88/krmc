<?php

namespace app\Controllers;

use app\core\Controller;
use app\core\Locale;
use app\core\Sender;
use app\core\Validator;
use app\core\View;
use app\models\Contacts;
use app\models\Settings;
use app\models\TelegramChats;
use app\models\Users;

class TelegramBotController extends Controller
{
    private static $techTelegramId = null;
    private static $mainGroupTelegramId = null;

    public static $requester = [];
    public static $message = [];
    public static $chatId = null;
    public static $command = '';
    public static $commandArguments = [];
    public static $guestCommands = ['help', 'nick', 'week', 'day', 'today'];
    public static $CommandNamespace = '\\app\\Repositories\\TelegramCommands';

    public static $resultMessage = '';
    public static $resultPreMessage = '';

    public static function before()
    {
        $contentType = isset($_SERVER['CONTENT_TYPE']) ? strtolower(trim($_SERVER['CONTENT_TYPE'])) : '';
        if (strpos($contentType, 'application/json') ===  false) return false;

        $ip = substr($_SERVER['REMOTE_ADDR'], 0, 4) === substr($_SERVER['SERVER_ADDR'], 0, 4) ? $_SERVER['HTTP_X_REAL_IP'] : $_SERVER['REMOTE_ADDR'];
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
        self::$message = $message;
        self::$techTelegramId = Settings::getTechTelegramId();
        self::$mainGroupTelegramId = Settings::getMainTelegramId();
        self::$chatId = $message['message']['chat']['id'];

        $userTelegramId = $message['message']['from']['id'];

        $userId = Contacts::getUserIdByContact('telegramid', $userTelegramId);

        if (empty($userId) && $command && !in_array(self::$command, self::$guestCommands)) {
            Sender::message(self::$chatId, Locale::phrase('{{ Tg_Unknown_Requester }}'), self::$message['message']['message_id']);
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
        // exit(json_encode(['message' => self::$message], JSON_UNESCAPED_UNICODE));
        try {
            if (!self::execute()) {
                if (empty(self::$resultMessage)) return false;
                $botResult = Sender::message(self::$techTelegramId, json_encode([self::$message, /* self::$requester ,*/ self::parseArguments(self::$commandArguments)], JSON_UNESCAPED_UNICODE));
            }

            if (!empty(self::$resultPreMessage)) {
                Sender::message(self::$chatId, self::$resultPreMessage, self::$message['message']['message_id']);
            }

            $botResult = Sender::message(self::$chatId, Locale::phrase(self::$resultMessage));

            if ($botResult[0]['ok']) {
                if (self::$command === 'week') {
                    self::pinMessage($botResult[0]['result']['message_id']);
                }
                /*                 if (in_array($command, ['reg', 'set', 'week', 'recall', 'today', 'day', 'promo'], true) && self::$chatId !== self::$techTelegramId) {
                    Sender::delete(self::$chatId, self::$message['message']['message_id']);
                } */
                if (in_array(self::$command, ['booking', 'reg', 'set', 'recall', 'promo'], true)) {
                    self::updateWeekMessages();
                }
            }
        } catch (\Throwable $th) {
            $debugMessage = [
                'commonError' => $th->__toString(),
                'messageData' => self::$message,
            ];
            Sender::message(self::$techTelegramId, json_encode($debugMessage, JSON_UNESCAPED_UNICODE));
            Sender::message(self::$chatId, Locale::phrase("Something went wrong😱!\nWe are deeply sorry for that😢\nI’ve informed our administrators about your situation, and they are fixing it right now!\nThank you for understanding!"));
        }
    }
    public static function parseCommand(string $text): bool
    {
        $_text = mb_strtolower(str_replace('на ', '', $text), 'UTF-8');
        if (preg_match('/^[+-]\s{0,3}(пн|пон|вт|вів|ср|сер|чт|чет|пт|пят|п’ят|сб|суб|вс|вос|нед|нд|сг|сег|сьо|зав|mon|tue|wed|thu|fri|sat|sun|tod|tom)/ui', $_text) === 1) {
            preg_match_all('/[+-]\s{0,3}(пн|пон|вт|вів|ср|сер|чт|чет|пт|пят|п’ят|сб|суб|вс|вос|нед|нд|сг|сег|сьо|зав|mon|tue|wed|thu|fri|sat|sun|tod|tom)/ui', str_replace('.', ':', $_text), $matches);
            $arguments = $matches[0];
            if (preg_match('/\([^)]+\)/', $text, $prim) === 1) {
                $arguments['prim'] = mb_substr($prim[0], 1, -1, 'UTF-8');
            }
            self::$command = 'booking';
            self::$commandArguments = $arguments;
            return true;
        }
        if (preg_match('/^[+]\s{0,3}[0-2]{0,1}[0-9]/', $_text) === 1) {
            preg_match('/^(\+)\s{0,3}([0-2]{0,1}[0-9])(:[0-5][0-9]){0,1}/i', mb_strtolower(str_replace('.', ':', $_text), 'UTF-8'), $matches);

            if ($matches[2] < 8 || $matches[2] > 23) return false;
            if (empty($matches[3])) $matches[3] = ':00';

            $arguments = [
                '+tod',
                $matches[2] . $matches[3],
            ];
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
            preg_match_all('/([a-zа-я.0-9]+)/ui', trim(mb_substr($text, $commandLen + 1, NULL, 'UTF-8')), $matches);

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

        $requestData['currentDay'] = getdate()['wday'] - 1;

        if ($requestData['currentDay'] === -1)
            $requestData['currentDay'] = 6;

        foreach ($arguments as $value) {
            $value = trim($value);
            if (preg_match('/^(\+|-)[^0-9]/', $value)) {

                $requestData['method'] = $value[0];
                $withoutMethod = trim(mb_substr($value, 1, 6, 'UTF-8'));
                $dayName = mb_strtolower(mb_substr($withoutMethod, 0, 3, 'UTF-8'), 'UTF-8');

                $requestData['dayNum'] = self::parseDayNum($dayName, $requestData['currentDay']);
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
        return $requestData;
    }
    public static function parseDayNum($dayName, $today)
    {
        $dayName = mb_strtolower($dayName, 'UTF-8');
        if (mb_strlen($dayName, 'UTF-8') > 3) {
            $dayName = mb_substr($dayName, 0, 3);
        }
        if (in_array($dayName, ['сг', 'сег', 'сьо', 'tod'], true)) {
            return $today;
        } elseif (in_array($dayName, ['зав', 'tom'], true)) {
            $dayNum = $today + 1;
            if ($dayNum === 7)
                $dayNum = 0;
            return $dayNum;
        } else {
            $daysArray = [
                ['пн', 'пон', 'mon'],
                ['вт', 'вто', 'вів', 'tue'],
                ['ср', 'сре', 'сер', 'wed'],
                ['чт', 'чтв', 'чет', 'thu'],
                ['пт', 'пят', 'п’ят', 'fri'],
                ['сб', 'суб', 'sat'],
                ['вс', 'вос', 'нед', 'нд', 'sun']
            ];

            foreach ($daysArray as $num => $daysNames) {
                if (in_array($dayName, $daysNames, true)) {
                    return $num;
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
            'message' => self::$message
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

        if (self::$message['message']['chat']['type'] !== 'private' && self::$chatId != self::$techTelegramId && $levels[$status] > 1) {
            $status = 'user';
        }
        return $levels[$level] <= $levels[$status];
    }
    public static function updateWeekMessages(): array
    {
        $result = [];
        self::execute('week');

        $chatData = TelegramChats::getChatsWithPinned();
        foreach ($chatData as $chatId => $pinned) {
            $result[] = Sender::edit($chatId, $pinned, self::$resultMessage);
        }

        return $result;
    }
    public static function pinMessage($messageId = null)
    {
        if (empty($messageId)) return false;

        Sender::pin(self::$chatId, $messageId);
        TelegramChats::savePinned(self::$message, $messageId);

        return true;
    }
}

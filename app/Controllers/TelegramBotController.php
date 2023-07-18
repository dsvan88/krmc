<?php

namespace app\Controllers;

use app\core\Controller;
use app\core\Locale;
use app\core\TelegramBot;
use app\core\View;
use app\models\Contacts;
use app\models\Settings;
use app\models\TelegramChats;
use app\models\Users;

class TelegramBotController extends Controller
{
    private static $bot = null;
    private static $techTelegramId = null;

    public static $requester = [];
    public static $message = [];
    public static $chatId = null;
    public static $command = '';
    public static $commandArguments = [];
    public static $guestCommands = ['help', 'nick', 'week'];

    public static $resultMessage = '';
    public static $resultPreMessage = '';

    public static function before()
    {
        $contentType = isset($_SERVER['CONTENT_TYPE']) ? strtolower(trim($_SERVER['CONTENT_TYPE'])) : '';
        if (strpos($contentType, 'application/json') ===  false) return true;
        $data = trim(file_get_contents('php://input'));
        $message = json_decode($data, true);

        if (!is_array($message)) {
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

        if (!self::parseCommand($text)) exit();

        self::$message = $message;
        self::$bot = new TelegramBot();
        self::$techTelegramId = Settings::getTechTelegramId();
        self::$chatId = $message['message']['chat']['id'];

        $userTelegramId = $message['message']['from']['id'];

        $userId = Contacts::getUserIdByContact('telegramid', $userTelegramId);

        self::$requester = empty($userId) ? $requester = Users::getDataByTelegramId($userTelegramId) : Users::getDataById($userId);

        if (empty(self::$requester) && !in_array(self::$command, self::$guestCommands)) {
            self::$bot->sendMessage(self::$chatId, Locale::phrase('{{ Tg_Unknown_Requester }}'));
            exit();
        }
    }
    public static function webhookAction()
    {
        // exit(json_encode(['message' => self::$message], JSON_UNESCAPED_UNICODE));
        try {
            if (!self::execute()) {
                if (empty(self::$resultMessage))
                    exit();
                $botResult = self::$bot->sendMessage(self::$techTelegramId, json_encode([self::$message, /*self::$requester,*/ self::parseArguments(self::$commandArguments)], JSON_UNESCAPED_UNICODE));
            }

            if (!empty(self::$resultPreMessage)) {
                self::$bot->sendMessage(self::$chatId, self::$resultPreMessage, self::$message['message']['message_id']);
            }

            $botResult = self::$bot->sendMessage(self::$chatId, Locale::phrase(self::$resultMessage));

            if ($botResult[0]['ok']) {
                if (self::$command === 'week') {
                    self::pinMessage($botResult[0]['result']['message_id']);
                }
                /*                 if (in_array($command, ['reg', 'set', 'week', 'recall', 'today', 'day', 'promo'], true) && self::$chatId !== self::$techTelegramId) {
                    $bot->deleteMessage(self::$chatId, self::$message['message']['message_id']);
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
            self::$bot->sendMessage(self::$techTelegramId, json_encode($debugMessage, JSON_UNESCAPED_UNICODE));
            self::$bot->sendMessage(self::$chatId, Locale::phrase("Something went wrong😱!\nWe are deeply sorry for that😢\nI’ve informed our administrators about your situation, and they are fixing it right now!\nThank you for understanding!"));
        }
    }
    public static function parseCommand(string $text): bool
    {
        if (preg_match('/^[+-]\s{0,3}(пн|пон|вт|вів|ср|сер|чт|чет|пт|пят|п’ят|сб|суб|вс|вос|нед|нд|сг|сег|сьо|зав|mon|tue|wed|thu|fri|sat|sun|tod|tom)/', mb_strtolower(str_replace('на ', '', $text), 'UTF-8')) === 1) {
            preg_match_all('/[+-]\s{0,3}(пн|пон|вт|вів|ср|сер|чт|чет|пт|пят|п’ят|сб|суб|вс|вос|нед|нд|сг|сег|сьо|зав|mon|tue|wed|thu|fri|sat|sun|tod|tom)|([0-2]{0,1}[0-9]\:[0-5][0-9])/i', mb_strtolower(str_replace('на ', '', $text), 'UTF-8'), $matches);
            $arguments = $matches[0];
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
            preg_match_all('/([a-zA-Zа-яА-ЯрРсСтТуУфФчЧхХШшЩщЪъЫыЬьЭэЮюЄєІіЇїҐґ.0-9]+)/', trim(mb_substr($text, $commandLen + 1, NULL, 'UTF-8')), $matches);

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
            } elseif (strpos($value, ':') !== false) {
                $requestData['arrive'] = $value;
            } elseif (preg_match('/^(\+|-)\d{1,2}/', $value, $match) === 1) {
                $requestData['nonames'] = substr($match[0], 1);
            } elseif ($requestData['userId'] < 2) {
                $value = str_ireplace(['m', 'c', 'o', 'p', 'x', 'a'], ['м', 'с', 'о', 'р', 'х', 'а',], $value);
                $userRegData = Users::getDataByName($value);
                if ($userRegData) {
                    $requestData['userId'] = $userRegData['id'];
                    $requestData['userName'] = $userRegData['name'];
                }
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
                ['чт', 'чет', 'thu'],
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
    }
    public static function execute($command = null)
    {

        if (empty($command)) {
            $command = self::$command;
        }

        $class = ucfirst($command) . 'Command';
        $class = str_replace('/', '\\', "\\app\\Repositories\\TelegramCommands\\{$class}");

        if (!class_exists($class)) {
            self::$resultMessage = $class . ' Telegram command isn`t found!';
            return false;
        }

        $class::$operatorClass = self::class;
        $class::$requester = self::$requester;
        $class::$message = self::$message;

        if (!self::checkAccess($class::$accessLevel)) {
            return false;
        }

        return $class::execute(self::$commandArguments);
    }
    public static function chatsListAction()
    {
        $vars = [
            'title' => '{{ Chats_List_Title }}',
            'chatsData' => TelegramChats::getChatsList()
        ];
        View::render($vars);
    }
    public static function sendAction()
    {
        if (!empty($_POST)) {
            $bot = new TelegramBot();
            $message =  preg_replace('/(<((?!b|u|s|strong|em|i|\/b|\/u|\/s|\/strong|\/em|\/i)[^>]+)>)/i', '', str_replace(['<br />', '<br/>', '<br>', '</p>'], "\n", trim($_POST['html'])));
            if (is_numeric($_POST['target'])) {
                $targets = $_POST['target'];
            } else {
                switch ($_POST['target']) {
                    case 'main':
                        $targets = Settings::getMainTelegramId();
                        break;
                    case 'groups':
                        $chats = TelegramChats::getGroupChatsList();
                        $count = count($chats);
                        for ($i = 0; $i < $count; $i++) {
                            $targets[] = $chats[$i]['uid'];
                        }
                        break;
                    default:
                        $chats = TelegramChats::getChatsList();
                        $count = count($chats);
                        for ($i = 0; $i < $count; $i++) {
                            $targets[] = $chats[$i]['uid'];
                        }
                        break;
                }
            }
            if ($_FILES['logo']['size'] > 0 && $_FILES['logo']['size'] < 10485760) { // 10 Мб = 10 * 1024 *1024
                $result = $bot->sendPhoto($targets, $message, $_FILES['logo']['tmp_name']);
            } else {
                $result = $bot->sendMessage($targets, $message);
            }
            $message = 'Success!';
            if (!$result[0]['ok']) {
                $message = 'Fail!';
            }

            View::message(['error' => 0, 'message' => $message]);
        }
        $groupChats = TelegramChats::getGroupChatsList();
        $directChats = TelegramChats::getDirectChats();
        $chats = array_merge($groupChats, $directChats);
        $vars = [
            'title' => '{{ HEADER_ASIDE_MENU_CHAT_SEND }}',
            'texts' => [
                'blockTitle' => '{{ HEADER_ASIDE_MENU_CHAT_SEND }}',
                'submitTitle' => 'Send',
                'sendAll' => '{{ Send_To_All }}',
                'sendGroups' => '{{ Send_To_Groups }}',
                'sendMain' => '{{ Send_To_Main }}',
            ],
            'chats' => $chats,
            'scripts' => [
                '/public/scripts/plugins/ckeditor.js?v=' . $_SERVER['REQUEST_TIME'],
                '/public/scripts/forms-admin-funcs.js?v=' . $_SERVER['REQUEST_TIME'],
            ],
        ];
        View::render($vars);
    }
    public static function checkAccess(string $level = 'guest')
    {
        $levels = ['guest' => 0, 'user' => 1, 'manager' => 2, 'admin' => 3, 'root' => 4];
        $status = 'guest';

        if (!empty(self::$requester['privilege']['status']))
            $status = self::$requester['privilege']['status'];

        return $levels[$level] <= $levels[$status];
    }
    public static function updateWeekMessages(): array
    {
        $result = [];
        self::execute('week');

        $chatData = TelegramChats::getChatsWithPinned();
        foreach ($chatData as $chatId => $pinned) {
            $result[] = self::$bot->editMessage($chatId, $pinned, self::$resultMessage);
        }

        return $result;
    }
    public static function pinMessage($messageId = null)
    {
        if (empty($messageId)) return false;

        self::$bot->pinMessage(self::$chatId, $messageId);
        TelegramChats::savePinned(self::$message, $messageId);

        return true;
    }
}

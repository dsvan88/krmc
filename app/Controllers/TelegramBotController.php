<?php

namespace app\Controllers;

use app\core\Controller;
use app\core\Locale;
use app\core\TelegramBot;
use app\core\View;
use app\models\Days;
use app\models\News;
use app\models\Settings;
use app\models\TelegramChats;
use app\models\Users;
use app\models\Weeks;

class TelegramBotController extends Controller
{
    public static $requester = [];
    public static $message = [];
    public static $command = [];
    public static $adminCommands = ['reg', 'recall', 'set', 'users', 'promo', 'newuser', 'clear'];
    public static $guestCommands = ['help', 'nick', 'week'];

    public static function before(){
        $contentType = isset($_SERVER['CONTENT_TYPE']) ? trim($_SERVER['CONTENT_TYPE']) : '';
        if (strpos($contentType, 'application/json') ===  false) return true;
        $data = trim(file_get_contents('php://input'));
        $message = json_decode($data, true);

        if (!is_array($message)) {
            die('{"error":"1","title":"Error!","text":"Error: Nothing to get."}');
        }
    

        if (isset($message['message']) && empty($message['message']['from']['is_bot'])) {
            TelegramChats::save($message);
        }

        $langCode = 'uk';
        if (isset($message['message']['from']['language_code']) && in_array($message['message']['from']['language_code'], ['uk', 'en', 'ru'])) {
            $langCode = $message['message']['from']['language_code'];
        }
        Locale::change($langCode);

        self::$message = $message;
    }
    public static function webhookAction()
    {
        $bot = new TelegramBot();
        $techTelegramId = Settings::getTechTelegramId();
        try {

            $text = trim(self::$message['message']['text']);
            $command = self::parseCommand($text);

            if (!$command) return false;

            $telegramId = self::$message['message']['from']['id'];

            $userData = Users::getDataByTelegramId($telegramId);

            if (in_array($command['command'], self::$adminCommands, true)) {
                if (empty($userData) || !in_array($userData['privilege']['status'], ['manager', 'admin'], true))
                    return false;
            }

            if (empty($userData) && !in_array($command['command'], self::$guestCommands)) {
                $bot->sendMessage(self::$message['message']['chat']['id'], Locale::phrase('{{ Tg_Unknown_Requester }}'));
                exit();
            }

            self::$requester = $userData;
            self::$command = $command;

            $result = self::execute();

            $botResult = $bot->sendMessage($techTelegramId, json_encode([self::parseArguments($command['arguments']), $result],JSON_UNESCAPED_UNICODE));

            if (isset($result['pre-message'])) {
                $bot->sendMessage(self::$message['message']['chat']['id'], $result['pre-message'], self::$message['message']['message_id']);
            }

            $botResult = $bot->sendMessage(self::$message['message']['chat']['id'], Locale::phrase($result['message']));
            if ($botResult[0]['ok']) {
                if ($command['command'] === 'week') {
                    $bot->pinMessage(self::$message['message']['chat']['id'], $botResult[0]['result']['message_id']);
                    TelegramChats::savePinned(self::$message, $botResult[0]['result']['message_id']);
                }
                /*                 if (in_array($command['command'], ['reg', 'set', 'week', 'recall', 'today', 'day', 'promo'], true) && self::$message['message']['chat']['id'] !== $techTelegramId) {
                    $bot->deleteMessage(self::$message['message']['chat']['id'], self::$message['message']['message_id']);
                } */
                if (in_array($command['command'], ['booking', 'reg', 'set', 'recall', 'promo'], true)) {
                    $chatData = TelegramChats::getChatsWithPinned();
                    $weekData = self::execute('week');
                    foreach ($chatData as $chatId => $pinned) {
                        $result[] = $bot->editMessage($chatId, $pinned, $weekData['message']);
                    }
                }
            }
        } catch (\Throwable $th) {
            $debugMessage = [
                'commonError' => $th->__toString(),
                'messageData' => self::$message,
            ];
            $bot->sendMessage($techTelegramId, json_encode($debugMessage, JSON_UNESCAPED_UNICODE));
        }
    }
    public static function parseCommand($text)
    {
        if (preg_match('/^[+-]\s{0,3}(пн|пон|вт|ср|чт|чет|пт|пят|сб|суб|вс|вос|сг|сег|зав)/', mb_strtolower(str_replace('на ', '', $text), 'UTF-8')) === 1) {
            preg_match_all('/[+-]\s{0,3}(пн|пон|вт|ср|чт|чет|пт|пят|сб|суб|вс|вос|сг|сег|зав)|([0-2]{0,1}[0-9]\:[0-5][0-9])/i', mb_strtolower(str_replace('на ', '', $text), 'UTF-8'), $matches);
            $arguments = $matches[0];
            if (preg_match('/\([^)]+\)/', $text, $prim) === 1) {
                $arguments['prim'] = mb_substr($prim[0], 1, -1, 'UTF-8');
            }
            return ['command' => 'booking', 'arguments' => $arguments];
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

            if (in_array($command, ['?', 'help'])) {
                return ['command' => 'help'];
            }
            if (in_array($command, ['reg', 'set'], true)) {
                $text = mb_substr($text, $commandLen + 1, NULL, 'UTF-8');
                $arguments = explode(',', mb_strtolower(str_replace('на ', '', $text)));
                if (preg_match('/\([^)]+\)/', $text, $prim) === 1) {
                    $arguments['prim'] = mb_substr($prim[0], 1, -1, 'UTF-8');
                }
                return ['command' => $command, 'arguments' => $arguments];
            }
            preg_match_all('/([a-zA-Zа-яА-ЯрРсСтТуУфФчЧхХШшЩщЪъЫыЬьЭэЮюЄєІіЇїҐґ.0-9]+)/', trim(mb_substr($text, $commandLen + 1, NULL, 'UTF-8')), $matches);
            $arguments = $matches[0];
            return ['command' => $command, 'arguments' => $arguments];
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
        if (in_array($dayName, ['сг', 'сег'], true)) {
            return $today;
        } elseif ($dayName === 'зав') {
            $dayNum = $today + 1;
            if ($dayNum === 7)
                $dayNum = 0;
            return $dayNum;
        } else {
            $daysArray = [
                ['пн', 'пон'],
                ['вт', 'вто'],
                ['ср', 'сре'],
                ['чт', 'чет'],
                ['пт', 'пят'],
                ['сб', 'суб'],
                ['вс', 'вос']
            ];

            foreach ($daysArray as $num => $daysNames) {
                if (in_array($dayName, $daysNames, true)) {
                    return $num;
                }
            }
        }
    }
    public static function execute($command = null){

        $command = $command ? $command : self::$command['command'];
        $path = $_SERVER['DOCUMENT_ROOT']."\\app\\Controllers\\TelegramCommands\\$command.php";
        
        if (!file_exists($path)) 
            return ['result' => false, 'message' => 'Telegram command isn`t found!'.$path];
        
        extract(self::$command);
        
        $result = false;
        $message = '';

        require $path;

        $return = [
            'result' => $result,
            'message' => $message
        ];

        if (isset($preMessage)){
            $return['pre-message'] = $preMessage;
        }

        return $return;
    }
    public static function chatsListAction()
    {
        $vars = [
            'title' => '{{ Chats_List_Title }}',
            'chatsData' => TelegramChats::getChatsList()
        ];
        View::render($vars);
    }
    public static function testCommand()
    {
        $weekData = Weeks::weekDataByTime();
        return ['result' => true, 'message' => json_encode($weekData, JSON_UNESCAPED_UNICODE)];
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
}

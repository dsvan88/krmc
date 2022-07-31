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
    public static $requesterData = [];
    public static $messageData = [];
    public static function webhookAction()
    {
        $bot = new TelegramBot();
        $techTelegramId = Settings::getTechTelegramId();
        try {
            $contentType = isset($_SERVER['CONTENT_TYPE']) ? trim($_SERVER['CONTENT_TYPE']) : '';
            if (strpos($contentType, 'application/json') !==  false) {
                $data = trim(file_get_contents('php://input'));
                $messageArray = json_decode($data, true);

                if (!is_array($messageArray)) {
                    die('{"error":"1","title":"Error!","text":"Error: Nothing to get."}');
                }
            }
            if (isset($messageArray['message']) && empty($messageArray['message']['from']['is_bot'])) {
                TelegramChats::save($messageArray);
            }

            $langCode = 'uk';
            if (isset($messageArray['message']['from']['language_code']) && in_array($messageArray['message']['from']['language_code'], ['uk', 'en', 'ru'])) {
                $langCode = $messageArray['message']['from']['language_code'];
            }
            Locale::change($langCode);

            $text = trim($messageArray['message']['text']);

            $command = self::parseCommand($text);

            if (!$command) return false;

            $telegramId = $messageArray['message']['from']['id'];

            $userData = Users::getDataByTelegramId($telegramId);

            if (in_array($command['command'], ['reg', 'recall', 'set', 'users', 'promo', 'newuser'], true)) {
                if (empty($userData) || !in_array($userData['privilege']['status'], ['manager', 'admin'], true))
                    return false;
            }

            $bot = new TelegramBot();
            if (empty($userData) && !in_array($command['command'], ['help', 'nick', 'week'])) {
                $bot->sendMessage($messageArray['message']['chat']['id'], Locale::applySingle('{{ Tg_Unknown_Requester }}'));
                exit();
            }
            self::$requesterData = $userData;
            self::$messageData = $messageArray;

            $commandMethod = $command['command'] . 'Command';
            $result = self::$commandMethod($command);

            $botResult = $bot->sendMessage($techTelegramId, json_encode([self::parseArguments($command['arguments']), $result]));
            if (isset($result['pre-message'])) {
                $bot->sendMessage($messageArray['message']['chat']['id'], $result['pre-message'], $messageArray['message']['message_id']);
            }
            $botResult = $bot->sendMessage($messageArray['message']['chat']['id'], Locale::applySingle($result['message']));
            if ($botResult[0]['ok']) {
                if ($command['command'] === 'week') {
                    $bot->pinMessage($messageArray['message']['chat']['id'], $botResult[0]['result']['message_id']);
                    TelegramChats::savePinned($messageArray, $botResult[0]['result']['message_id']);
                }
                /*                 if (in_array($command['command'], ['reg', 'set', 'week', 'recall', 'today', 'day', 'promo'], true) && $messageArray['message']['chat']['id'] !== $techTelegramId) {
                    $bot->deleteMessage($messageArray['message']['chat']['id'], $messageArray['message']['message_id']);
                } */
                if (in_array($command['command'], ['booking', 'reg', 'set', 'recall', 'promo'], true)) {
                    $chatData = TelegramChats::getChatsWithPinned();
                    $weekData = self::weekCommand();
                    foreach ($chatData as $chatId => $pinned) {
                        $result[] = $bot->editMessage($chatId, $pinned, $weekData['message']);
                    }
                }
            }
        } catch (\Throwable $th) {
            $debugMessage = [
                'commonError' => $th->__toString(),
                'messageArray' => $messageArray,
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
            if (method_exists(__CLASS__, $command . 'Command')) {
                preg_match_all('/([a-zA-Zа-яА-ЯрРсСтТуУфФчЧхХШшЩщЪъЫыЬьЭэЮюЄєІіЇїҐґ.0-9]+)/', trim(mb_substr($text, $commandLen + 1, NULL, 'UTF-8')), $matches);
                $arguments = $matches[0];
                return ['command' => $command, 'arguments' => $arguments];
            }
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
    public static function weekCommand($data = [])
    {
        $weeksData = Weeks::nearWeeksDataByTime();

        $message = '';
        if (!empty($weeksData)) {
            foreach ($weeksData as $weekData) {

                for ($i = 0; $i < 7; $i++) {

                    if (!isset($weekData['data'][$i]) || in_array($weekData['data'][$i]['status'], ['', 'recalled'])) {
                        continue;
                    }
                    $dayDescription = Days::getFullDescription($weekData, $i);
                    if ($dayDescription !== '')
                        $message .=  $dayDescription .
                            "___________________________\n";
                }
            }
        } else {
            if ($message === '') {
                $message = Locale::applySingle('{{ Tg_Command_Games_Not_Set }}');
            }
        }

        $promoData = News::getPromoData();
        if ($promoData) {
            if ($promoData['title'] !== '') {
                $message .= "<u><b>$promoData[title]</b></u>\n<i>$promoData[subtitle]</i>\n\n";
                $message .= preg_replace('/(<((?!b|u|s|strong|em|i|\/b|\/u|\/s|\/strong|\/em|\/i)[^>]+)>)/i', '', str_replace(['<br />', '<br/>', '<br>', '</p>'], "\n", trim($promoData['html'])));
            }
        }
        return ['result' => true, 'message' => $message];
    }
    public static function todayCommand($data)
    {
        $weekData = Weeks::weekDataByTime();

        $currentDayNum = getdate()['wday'] - 1;

        if ($currentDayNum === -1)
            $currentDayNum = 6;

        $dayDescription = Days::getFullDescription($weekData, $currentDayNum);
        if ($dayDescription === '') {
            $dayDescription = Locale::applySingle('{{ Tg_Command_Games_Not_Set }}'); //В ближайшее время, игры не запланированны!\nОбратитесь к нам позднее.\n
        }
        return ['result' => true, 'message' => $dayDescription];
    }
    public static function dayCommand($data)
    {
        $weekId = Weeks::currentId();

        $currentDayNum = getdate()['wday'] - 1;

        if ($currentDayNum === -1)
            $currentDayNum = 6;

        extract($data);
        if (isset($arguments[0])) {
            $dayNum = self::parseDayNum($arguments[0], $currentDayNum);
            if ($dayNum < $currentDayNum)
                $weekId++;
        } else {
            $dayNum = $currentDayNum;
        }

        $weekData = Weeks::weekDataById($weekId);
        $dayDescription = Days::getFullDescription($weekData, $dayNum);
        if ($dayDescription === '') {
            $dayDescription = Locale::applySingle('{{ Tg_Command_Games_Not_Set }}'); //В ближайшее время, игры не запланированны!\nОбратитесь к нам позднее.\n
        }
        return ['result' => true, 'message' => $dayDescription];
    }
    public static function bookingCommand($data)
    {
        extract($data);
        $requestData = self::parseArguments($arguments);
        $requestData['userId'] = self::$requesterData['id'];
        $requestData['userName'] = self::$requesterData['name'];
        $requestData['userStatus'] = self::$requesterData['privilege']['status'];

        $weekId = Weeks::currentId();
        if ($requestData['currentDay'] > $requestData['dayNum']) {
            ++$weekId;
        }

        $weekData = Weeks::weekDataById($weekId);

        $participantId = $slot = -1;
        if ($weekData['data'][$requestData['dayNum']]['status'] !== 'set') {
            if (!in_array($requestData['userStatus'], ['manager', 'admin']))
                return ['result' => false, 'message' => '{{ Tg_Gameday_Not_Set }}'];

            if (!isset($weekData['data'][$requestData['dayNum']]['game']))
                $weekData['data'][$requestData['dayNum']] = Days::$dayDataDefault;

            if ($requestData['arrive'] !== '')
                $weekData['data'][$requestData['dayNum']]['time'] = $requestData['arrive'];
            $requestData['arrive'] = '';
            $weekData['data'][$requestData['dayNum']]['status'] = 'set';
        }

        foreach ($weekData['data'][$requestData['dayNum']]['participants'] as $index => $userData) {
            if ($userData['id'] === $requestData['userId']) {
                if ($requestData['arrive'] !== '' && $requestData['arrive'] !== $userData['arrive']) {
                    $slot = $index;
                    break;
                }
                $participantId = $index;
                break;
            }
        }

        $newDayData = $weekData['data'][$requestData['dayNum']];
        if ($requestData['method'] === '+') {
            if ($participantId !== -1) {
                return ['result' => false, 'message' => '{{ Tg_Command_Requester_Already_Booked }}'];
            }
            $newDayData = Days::addParticipantToDayData($newDayData, $slot, $requestData);
            $reactions = [
                '🤩',
                '🥰',
                '🥳',
                '😻',
            ];
        } else {
            if ($participantId === -1) {
                return ['result' => false, 'message' => '{{ Tg_Command_Requester_Not_Booked }}'];
            }
            unset($newDayData['participants'][$participantId]);
            $newDayData['participants'] = array_values($newDayData['participants']);
            $reactions = [
                '😥',
                '😭',
                '😱',
                '😿',
            ];
        }

        $result = Days::setDayData($weekId, $requestData['dayNum'], $newDayData);

        $botReaction = '';
        if (isset($reactions)) {
            $botReaction = $reactions[mt_rand(0, count($reactions) - 1)];
        }
        if (!$result) {
            return ['result' => false, 'message' => json_encode($newData, JSON_UNESCAPED_UNICODE), 'pre-message' => $botReaction];
        }

        $weekData['data'][$requestData['dayNum']] = $newDayData;
        return ['result' => true, 'message' => Days::getFullDescription($weekData, $requestData['dayNum']), 'pre-message' => $botReaction];
    }
    public static function regCommand($data)
    {
        extract($data);
        if (empty($arguments)) {
            return ['result' => false, 'message' => '{{ Tg_Command_Without_Arguments}}'];
        }

        $requestData = self::parseArguments($arguments);

        if (!isset($requestData['nonames']) && $requestData['userId'] < 2) {
            return ['result' => false, 'message' => '{{ Tg_Command_User_Not_Found }}'];
        }

        $weekId = Weeks::currentId();
        if ($requestData['dayNum'] < 0) {
            $requestData['dayNum'] = $requestData['currentDay'];
        } else {
            if ($requestData['currentDay'] > $requestData['dayNum']) {
                ++$weekId;
            }
        }
        $weekData = Weeks::weekDataById($weekId);

        $participantId = $slot = -1;

        if ($weekData['data'][$requestData['dayNum']]['status'] !== 'set') {
            if (!isset($weekData['data'][$requestData['dayNum']]['game']))
                $weekData['data'][$requestData['dayNum']] = Days::$dayDataDefault;

            if ($requestData['arrive'] !== '')
                $weekData['data'][$requestData['dayNum']]['time'] = $requestData['arrive'];
            $requestData['arrive'] = '';
            $weekData['data'][$requestData['dayNum']]['status'] = 'set';
        }

        if (isset($requestData['nonames'])) {
            $slot = count($weekData['data'][$requestData['dayNum']]['participants']);
        } else {
            foreach ($weekData['data'][$requestData['dayNum']]['participants'] as $index => $userData) {
                if ($userData['id'] === $requestData['userId']) {
                    if ($requestData['arrive'] !== '' && $requestData['arrive'] !== $userData['arrive']) {
                        $slot = $index;
                        break;
                    }
                    $participantId = $index;
                    break;
                }
            }
        }
        $newDayData = $weekData['data'][$requestData['dayNum']];
        if ($requestData['method'] === '+') {
            if ($participantId !== -1) {
                return ['result' => false, 'message' => '{{ Tg_Command_User_Already_Booked }}'];
            }
            if (isset($requestData['nonames'])) {
                $newDayData = Days::addNonamesToDayData($newDayData, $slot, $requestData['nonames'], $requestData['prim']);
            } else {
                $newDayData = Days::addParticipantToDayData($newDayData, $slot, $requestData);
            }
        } else {
            if (isset($requestData['nonames'])) {
                $newDayData = Days::removeNonamesFromDayData($newDayData, $requestData['nonames']);
            } else {
                if ($participantId === -1) {
                    return ['result' => false, 'message' => '{{ Tg_Command_User_Not_Booked }}'];
                }
                unset($newDayData['participants'][$participantId]);
                $newDayData['participants'] = array_values($newDayData['participants']);
            }
        }

        $result = Days::setDayData($weekId, $requestData['dayNum'], $newDayData);

        if (!$result) {
            return ['result' => false, 'message' => json_encode($newData, JSON_UNESCAPED_UNICODE)];
        }

        $weekData['data'][$requestData['dayNum']] = $newDayData;
        return ['result' => true, 'message' => Days::getFullDescription($weekData, $requestData['dayNum'])];
    }
    public static function setCommand($data)
    {
        extract($data);
        $dayName = '';
        $dayNum = -1;
        $currentDayNum = getdate()['wday'] - 1;

        if ($currentDayNum === -1)
            $currentDayNum = 6;

        if (empty($arguments)) {
            return ['result' => false, 'message' => '{{ Tg_Command_Without_Arguments}}'];
        }

        $gameName = $dayName = $time = '';
        $tournament = false;

        foreach ($arguments as $value) {
            $value = trim($value);
            if ($gameName === '' && preg_match('/^(maf|маф|пок|pok|наст|board|table|кеш|кєш|кэш|cash|інш|другое|etc)/', mb_strtolower($value, 'UTF-8'), $gamesPattern) > 0) {
                $gameName = $gamesPattern[0];
                if ($tournament === false && preg_match('/(тур|tour)/', mb_strtolower($value, 'UTF-8')) > 0) {
                    $tournament = true;
                }
                continue;
            }
            if ($time === '' && preg_match('/^([0-2]{0,1}[0-9]\:[0-5][0-9])/', mb_strtolower($value, 'UTF-8'), $timesPattern) > 0) {
                $time = $timesPattern[0];
                continue;
            }
            if ($dayName === '' && preg_match('/^[+-]{0,1}(пн|пон|вт|ср|чт|чет|пт|пят|сб|суб|вс|вос|сг|сег|зав)/', mb_strtolower($value, 'UTF-8'), $daysPattern) > 0) {
                $dayName = $daysPattern[0];
                continue;
            }
        }
        if ($dayName === '')
            $dayName = 'сг';

        $method = '+';
        if ($dayName[0] === '+' || $dayName[0] === '-') {
            $method = $dayName[0];
            $dayName = mb_substr($dayName, 1, null, 'UTF-8');
        }
        $dayNum = self::parseDayNum($dayName, $currentDayNum);

        if ($gameName !== '') {
            $gamesArray = [
                'mafia' => ['maf', 'маф'],
                'poker' => ['пок', 'pok'],
                'board' => ['наст', 'board', 'table'],
                'cash' => ['кеш', 'кєш', 'кэш', 'cash'],
                'etc' => ['інш', 'другое', 'etc'],
            ];

            foreach ($gamesArray as $name => $gameNames) {
                if (in_array($gameName, $gameNames, true)) {
                    $gameName = $name;
                    break;
                }
            }
        }

        $weekId = Weeks::currentId();

        if ($dayNum < $currentDayNum) {
            ++$weekId;
        }
        $weekData = Weeks::weekDataById($weekId);

        $weekData['data'][$dayNum]['status'] = 'set';

        if ($method === '-') {
            $weekData['data'][$dayNum]['status'] = 'recalled';
        }

        if ($gameName !== '') {
            $weekData['data'][$dayNum]['game'] = $gameName;
        }

        if ($time !== '') {
            $weekData['data'][$dayNum]['time'] = $time;
        }

        if (isset($arguments['prim'])) {
            $weekData['data'][$dayNum]['day_prim'] = $arguments['prim'];
        }

        if ($tournament) {
            if (empty($weekData['data'][$dayNum]['mods']) || !in_array('tournament', $weekData['data'][$dayNum]['mods'])) {
                $weekData['data'][$dayNum]['mods'][] = 'tournament';
            }
        } elseif (!empty($weekData['data'][$dayNum]['mods'])) {
            $index = array_search('tournament', $weekData['data'][$dayNum]['mods'], true);
            if ($index !== false) {
                unset($weekData['data'][$dayNum]['mods'][$index]);
                $weekData['data'][$dayNum]['mods'][$index] = array_values($weekData['data'][$dayNum]['mods']);
            }
        }

        $result = Days::setDayData($weekId, $dayNum, $weekData['data'][$dayNum]);

        if (!$result) {
            return ['result' => false, 'message' => json_encode($weekData['data'][$dayNum], JSON_UNESCAPED_UNICODE)];
        }
        return ['result' => true, 'message' => $method === '-' ? '{{ Tg_Command_Successfully_Canceled }}' : Days::getFullDescription($weekData, $dayNum)];
    }
    public static function recallCommand($data)
    {
        extract($data);
        $dayName = '';
        $dayNum = -1;
        $currentDayNum = getdate()['wday'] - 1;

        if ($currentDayNum === -1)
            $currentDayNum = 6;

        if (!empty($arguments)) {
            if (preg_match('/^(пн|пон|вт|ср|чт|чет|пт|пят|сб|суб|вс|вос|сг|сег|зав)/', mb_strtolower($arguments[0], 'UTF-8'), $daysPattern) === 1) {
                $dayName = $daysPattern[0];
            }
        }
        if ($dayName === '')
            $dayName = 'сг';

        $dayNum = self::parseDayNum($dayName, $currentDayNum);

        $currentWeekId = Weeks::currentId();

        if ($dayNum < $currentDayNum) {
            ++$currentWeekId;
        }

        $result = Days::recall($currentWeekId, $dayNum);

        if (!$result)
            return ['result' => false, 'message' => '{{ Tg_Command_Set_Day_Not_Found }}'];

        return ['result' => true, 'message' => '{{ Tg_Command_Successfully_Canceled }}'];
    }
    public static function nickCommand($data)
    {
        extract($data);

        if (!empty(self::$requesterData)) {
            return ['result' => false, 'message' => ['string' => '{{ Tg_Command_Name_Already_Set }}', 'vars' => [self::$requesterData['name']]]];
        }

        $username = '';
        foreach ($arguments as $string) {
            $username .= Locale::mb_ucfirst($string) . ' ';
        }

        $username = mb_substr($username, 0, -1, 'UTF-8');
        if (mb_strlen(trim($username), 'UTF-8') < 2) {
            return ['result' => false, 'message' => '{{ Tg_Command_Name_Too_Short }}'];
        }
        if (preg_match('/([^а-яА-ЯрРсСтТуУфФчЧхХШшЩщЪъЫыЬьЭэЮюЄєІіЇїҐґ .0-9])/', $username) === 1) {
            return ['result' => false, 'message' => '{{ Tg_Command_Name_Wrong_Format }}'];
        }

        $telegramId = self::$messageData['message']['from']['id'];
        $telegram = self::$messageData['message']['from']['username'];

        $userExistsData = Users::getDataByName($username);

        if (!empty($userExistsData['id'])) {
            if ($userExistsData['contacts']['telegramid'] !== '') {
                if ($userExistsData['contacts']['telegramid'] !== $telegramId) {
                    return ['result' => false, 'message' => ['string' => '{{ Tg_Command_Name_Already_Set_By_Other }}', 'vars' => [$username]]];
                }
                return ['result' => false, 'message' => '{{ Tg_Command_Name_You_Have_One }}'];
            } else {
                $userExistsData['contacts']['telegramid'] = $telegramId;
                $userExistsData['contacts']['telegram'] = $telegram;
                $userExistsData['contacts']['email'] = '';
                Users::edit(['contacts' => $userExistsData['contacts']], ['id' => $userExistsData['id']]);
                return ['result' => true, 'message' => ['string' => '{{ Tg_Command_Name_Save_Success }}', 'vars' => [$username]]];
            }
        } else {
            $id = Users::add($username);
            Users::edit(['contacts' => ['telegram' => $telegram, 'telegramid' => $telegramId]], ['id' => $id]);
            return ['result' => true, 'message' => ['string' => '{{ Tg_Command_Name_Save_Success }}', 'vars' => [$username]]];
        }
    }
    public static function newuserCommand($data)
    {
        extract($data);
        $username = '';
        foreach ($arguments as $string) {
            $username .= Locale::mb_ucfirst($string) . ' ';
        }
        $username = mb_substr($username, 0, -1, 'UTF-8');

        if (mb_strlen(trim($username), 'UTF-8') < 2) {
            return ['result' => false, 'message' => '{{ Tg_Command_Name_Too_Short }}'];
        }
        if (preg_match('/([^а-яА-ЯрРсСтТуУфФчЧхХШшЩщЪъЫыЬьЭэЮюЄєІіЇїҐґ .0-9])/', $username) === 1) {
            return ['result' => false, 'message' => '{{ Tg_Command_Name_Wrong_Format }}'];
        }

        if (Users::getId($username) > 0) {
            return ['result' => false, 'message' => ['string' => '{{ Tg_Command_New_User_Already_Set }}', 'vars' => [$username]]];
        }

        Users::add($username);
        return ['result' => true, 'message' => ['string' => '{{ Tg_Command_New_User_Save_Success }}', 'vars' => [$username]]];
    }
    public static function usersCommand()
    {
        $usersList = Users::getList();
        $message = '';
        $x = 0;
        for ($i = 0; $i < count($usersList); $i++) {
            if ($usersList[$i]['name'] === '') continue;
            $message .= (++$x) . ". <b>{$usersList[$i]['name']}</b>";
            if ($usersList[$i]['contacts']['telegram'] !== '') {
                $message .= " (@{$usersList[$i]['contacts']['telegram']})";
            }
            if ($usersList[$i]['contacts']['telegramid'] !== '') {
                $message .= ' ✅';
            }
            $message .= "\n";
        }
        $message .= "______________________________\n✅ - " . Locale::applySingle('{{ Tg_User_With_Telegramid }}');
        return ['result' => true, 'message' => $message];
    }
    public static function promoCommand()
    {
        $text = self::$messageData['message']['text'];
        $promoText = trim(mb_substr($text, mb_strpos($text, ' ', 0, 'UTF-8') + 1, NULL, 'UTF-8'));

        if (isset(self::$messageData['message']['entities'])) {

            $newString = '';
            $offset = 0;
            $formattings = [
                'bold' => 'b',
                'italic' => 'i',
                'strikethrough' => 's',
                'spoiler' => 'tg-spoiler',
            ];
            for ($i = 0; $i < count(self::$messageData['message']['entities']); $i++) {
                if (self::$messageData['message']['entities'][$i]['type'] === 'bot_command') {
                    $offset = self::$messageData['message']['entities'][$i]['offset'] + self::$messageData['message']['entities'][$i]['length'];
                    continue;
                }
                $newString .= mb_substr($text, $offset, self::$messageData['message']['entities'][$i]['offset'] - $offset, 'UTF-8');
                $newString .= "<{$formattings[self::$messageData['message']['entities'][$i]['type']]}>" . mb_substr($text, self::$messageData['message']['entities'][$i]['offset'], self::$messageData['message']['entities'][$i]['length'], 'UTF-8') . "</{$formattings[self::$messageData['message']['entities'][$i]['type']]}>";
                $offset = self::$messageData['message']['entities'][$i]['offset'] + self::$messageData['message']['entities'][$i]['length'];
            }
            $newString .= mb_substr($text, $offset, null, 'UTF-8');

            $newString = preg_replace(['/\-\-(.*)\-\-/'], ['<u>$1</u>'], $newString);

            if ($newString !== '')
                $promoText = $newString;
        }
        preg_match('/(.*?)\n(.*?)\n([^`]*)/', $promoText, $matches);

        $data = [
            'title' => isset($matches[1]) ? trim($matches[1]) : '',
            'subtitle' => isset($matches[2]) ? trim($matches[2]) : '',
            'html' => isset($matches[3]) ? str_replace("\n", '</br>', $matches[3]) : '',
        ];

        News::edit($data, 'promo');

        return ['result' => true, 'message' => '{{ Tg_Command_Promo_Saved }}'];
    }
    public static function helpCommand()
    {
        $message = Locale::applySingle('{{ Tg_Command_Help }}');

        if (self::$messageData['message']['chat']['type'] === 'private' && in_array(self::$requesterData['privilege']['status'], ['manager', 'admin'])) {
            $message .= Locale::applySingle('{{ Tg_Command_Help_Admin }}');
        }
        return ['result' => true, 'message' => $message];
    }
    public static function chatsListAction()
    {
        $vars = [
            'formTitle' => '{{ Chats_List_Title }}',
            'chatsData' => TelegramChats::getChatsList()
        ];
        View::render('{{ Chats_List_Title }}', $vars);
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
            if ($_FILES['logo']['size'] > 0) {
                $image = FILE_MAINGALL . sha1_file($_FILES['logo']['tmp_name']) . mb_substr($_FILES['logo']['name'], mb_strripos($_FILES['logo']['name'], '.', 0, 'UTF-8'), NULL, 'UTF-8');
                copy($_FILES['logo']['tmp_name'], $_SERVER['DOCUMENT_ROOT'] . $image);
                $result = $bot->sendPhoto($targets, $image, $message);
            } else {
                $result = $bot->sendMessage($targets, $message);
            }
            var_dump($result);
            $message = '{{ Action_Success }}';
            if (!$result[0]['ok']) {
                $message = '{{ Action_Failure }}';
            }

            View::message(['error' => 0, 'message' => $message]);
        }
        $groupChats = TelegramChats::getGroupChatsList();
        $directChats = TelegramChats::getDirectChats();
        $chats = array_merge($groupChats, $directChats);
        $vars = [
            'texts' => [
                'blockTitle' => '{{ HEADER_ASIDE_MENU_CHAT_SEND }}',
                'submitTitle' => '{{ Send_Label }}',
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
        View::render('{{ HEADER_ASIDE_MENU_CHAT_SEND }}', $vars);
    }
}

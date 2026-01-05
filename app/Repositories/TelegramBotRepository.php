<?

namespace app\Repositories;

use app\core\Telegram\ChatCommand;
use app\core\Locale;
use app\core\Router;
use app\models\Contacts;
use app\models\Days;
use app\models\Users;
use app\models\Weeks;
use app\models\Settings;
use app\models\TelegramChats;
use Exception;

class TelegramBotRepository
{
    public static function parseChatCommand(string $text): string
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
            ChatCommand::$arguments = $arguments;
            return 'booking';
        }

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
            ChatCommand::$arguments = $arguments;
            return 'booking';
        }

        if ($text[0] !== '/') return '';

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
            return 'help';
        }

        if (in_array($command, ['reg', 'set'], true)) {
            $_text = mb_substr($_text, $commandLen + 1, NULL, 'UTF-8');
            $arguments = explode(',', $_text);
            if (preg_match('/\([^)]+\)/', $text, $prim) === 1) {
                $arguments['prim'] = mb_substr($prim[0], 1, -1, 'UTF-8');
            }
            ChatCommand::$arguments = $arguments;
            return $command;
        }
        $symbols = Locale::$cyrillicPattern;
        preg_match_all("/([a-z$symbols.0-9#-]+)/ui", trim(mb_substr($text, $commandLen + 1, NULL, 'UTF-8')), $matches);

        ChatCommand::$arguments = $matches[0];
        return $command;
    }
    public static function parseArguments($arguments): void
    {
        if (isset($arguments['prim'])) {
            ChatCommand::$arguments['prim'] = $arguments['prim'];
            unset($arguments['prim']);
        }
        foreach ($arguments as $value) {
            $value = trim($value);
            if (preg_match('/^[+-][^0-9]/', $value)) {

                ChatCommand::$arguments['method'] = $value[0];
                $withoutMethod = trim(mb_substr($value, 1, 6, 'UTF-8'));
                $dayName = mb_strtolower(mb_substr($withoutMethod, 0, 3, 'UTF-8'), 'UTF-8');

                static::parseDayNum($dayName, ChatCommand::$arguments);
            } elseif (preg_match('/^\d{2}:\d{2}$/', $value) === 1 && empty(ChatCommand::$arguments['arrive'])) {
                ChatCommand::$arguments['arrive'] = $value;
            } elseif (preg_match('/\#(\d)*$/', $value, $match) === 1) {
                $userRegData = Users::find($match[0]);
                if ($userRegData) {
                    ChatCommand::$arguments['userId'] = $userRegData['id'];
                    ChatCommand::$arguments['userName'] = $userRegData['name'];
                }
            } elseif (preg_match('/^(\+|-)\d{1,2}/', $value, $match) === 1) {
                ChatCommand::$arguments['nonames'] = substr($match[0], 1);
            } elseif (ChatCommand::$arguments['userId'] < 2) {
                // $value = str_ireplace(['m', 'c', 'o', 'p', 'x', 'a'], ['м', 'с', 'о', 'р', 'х', 'а'], $value);
                $value = Users::formatName($value);

                if (empty($value)) continue;

                $userRegData = Users::getDataByName($value);
                ChatCommand::$arguments['probableUserName'] = $value;
                if (!empty($userRegData)) {
                    ChatCommand::$arguments['userId'] = $userRegData['id'];
                    ChatCommand::$arguments['userName'] = $userRegData['name'];
                }
            }
        }

        if (empty(ChatCommand::$arguments['currentDay']))
            static::parseDayNum('tod', ChatCommand::$arguments);
    }
    public static function parseDayNum(string $daySlug): bool
    {
        ChatCommand::$arguments['currentDay'] = Days::current();

        $daySlug = mb_strtolower($daySlug, 'UTF-8');
        if (mb_strlen($daySlug, 'UTF-8') > 3) {
            $daySlug = mb_substr($daySlug, 0, 3);
        }
        if (in_array($daySlug, DayRepository::$techDaysArray['today'], true)) {
            ChatCommand::$arguments['dayNum'] = ChatCommand::$arguments['currentDay'];
            return true;
        } elseif (in_array($daySlug, DayRepository::$techDaysArray['tomorrow'], true)) {
            ChatCommand::$arguments['dayNum'] = ChatCommand::$arguments['currentDay'] + 1;

            if (ChatCommand::$arguments['dayNum'] === 7) ChatCommand::$arguments['dayNum'] = 0;

            return true;
        } else {
            foreach (DayRepository::$daysArray as $num => $daysNames) {
                if (in_array($daySlug, $daysNames, true)) {
                    ChatCommand::$arguments['dayNum'] = $num;
                    return true;
                }
            }
        }
        return false;
    }

    public static function hasAccess(string $status = '', string $level = 'all'): bool
    {
        $levels = array_flip(Router::$accessLevels);

        if (empty($status)) $status = 'user';

        if (!static::isDirect() && static::getChatId() != Settings::getTechTelegramId()) {
            $status = $levels[$status] > 1 ? 'trusted' : 'user';
        }
        return $levels[$level] <= $levels[$status];
    }
    public static function getMessageId(array $message = []): int
    {
        if (empty($message))
            $message = ChatCommand::$message;

        if (empty($message))
            throw new Exception(__METHOD__ . ': $message can\'t be empty!');

        return empty($message['callback_query']) ?
            $message['message']['message_id'] :
            $message['callback_query']['message']['message_id'];
    }
    public static function getChatId(array $message = []): int
    {
        if (empty($message))
            $message = ChatCommand::$message;

        if (empty($message))
            throw new Exception(__METHOD__ . ': $message can\'t be empty!');

        return empty($message['callback_query']) ?
            $message['message']['chat']['id'] :
            $message['callback_query']['message']['chat']['id'];
    }
    public static function isDirect(array $message = []): bool
    {
        if (empty($message))
            $message = ChatCommand::$message;

        if (empty($message))
            throw new Exception(__METHOD__ . ': $message can\'t be empty!');

        return empty($message['callback_query']) ?
            $message['message']['chat']['type'] === 'private' :
            $message['callback_query']['message']['chat']['type'] === 'private';
    }
    public static function encodeInlineKeyboard(array &$data): void
    {
        foreach ($data as $i => $row) {
            foreach ($row as $k => $v) {
                $data[$i][$k]['text'] = Locale::phrase($v['text']);
                $data[$i][$k]['callback_data'] = static::replyButtonEncode($v['callback_data']);
            }
        }
    }
    public static function replyButtonEncode(array $data): string
    {
        return base64_encode(http_build_query($data));
    }
    public static function replyButtonDecode(string $data): array
    {
        parse_str(base64_decode($data), $result);
        return $result;
    }
}

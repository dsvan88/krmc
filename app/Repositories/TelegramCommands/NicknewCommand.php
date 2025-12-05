<?

namespace app\Repositories\TelegramCommands;

use app\core\ChatCommand;
use app\core\Locale;
use app\core\Sender;
use app\models\Contacts;
use app\models\Settings;
use app\models\TelegramChats;
use app\models\Users;
use app\Repositories\ContactRepository;
use app\Repositories\TelegramChatsRepository;

class NickCommand extends ChatCommand
{
    public static function description()
    {
        return self::locale('<u>/nicknew Your nickname</u> (Cyrillic) <i>// Register your nickname</i>');
    }
    public static function execute(array $arguments = [], string &$message = '', string &$reaction = '', array &$replyMarkup = [])
    {
        // if (!empty(self::$requester)) {
        //     $message = self::locale(['string' => '{{ Tg_Command_Name_Already_Set }}', 'vars' => [self::$requester['name']]]);
        //     return false;
        // }

        $_username = implode(' ', $arguments);

        if (empty(trim($_username))) {
            $message = self::locale("Your nickname can't be empty!\nPlease, use that format:\n/nick <b>Your nickname</b>");
            return false;
        }

        $lun = Users::formatName($_username, 'latin');
        $cun = Users::formatName($_username, 'cyrillic');

        $username = strlen($lun) > strlen($cun) ? $lun : $cun;

        if (mb_strlen($username, 'UTF-8') < 2) {
            $message = self::locale("Your nickname is too short!\nPlease use at least <b>2</b> symbols, so people can recognize you!");
            return false;
        }

        // if (empty($username)) {
        //     $message = self::locale("Invalid nickname format!\nPlease use only <b>Cyrillic</b> and <b>spaces</b> in the nickname!");
        //     return false;
        // }

        // $symbols = Locale::$cyrillicPattern;
        // if (preg_match_all("/[^$symbols .0-9]/ui", $_username, $matches)) {
        //     $wrong = implode('</i>", "<i>', $matches[0]);
        //     $message = self::locale(['string' => "Invalid nickname format!\nPlease use only <b>Cyrillic</b> and <b>spaces</b> in the nickname!\nWrong simbols: %s", 'vars' => ["\"<i>$wrong</i>\""]]);
        //     return false;
        // }

        $telegramId = self::$message['message']['from']['id'];
        $telegram = self::$message['message']['from']['username'];

        $userExistsData = Users::getDataByName($username);

        if (empty($userExistsData['id'])) {
            // $userId = Users::add($username);
            $userId = self::$message['message']['from']['id'];

            // Contacts::new(['telegramid' => $telegramId, 'telegram' => $telegram], $userId);
            // TelegramChats::save(self::$message);
            // TelegramChatsRepository::getAndSaveTgAvatar($userId, true);

            $message = self::locale(['string' => '{{ Tg_Command_Name_Save_Success }}', 'vars' => [$username]]);

            $replyMarkup = [
                'inline_keyboard' => [ 
                        [
                            ['text' => self::locale('Yes'), 'callback_data' => base64_encode(json_encode(['cmd' => 'nick', 'uid' => $userId, 'save' => true]))],
                            ['text' => self::locale('No'), 'callback_data' => base64_encode(json_encode(['cmd' => 'nick', 'uid' => $userId, 'save' => false]))],
                        ],
                    ],
                ];
            return true;
        }

        $userContacts = Contacts::getByUserId($userExistsData['id']);
        // TelegramChats::save(self::$message);

        if (empty($userContacts)) {
            // Contacts::new(['telegramid' => $telegramId, 'telegram' => $telegram], $userExistsData['id']);
            // TelegramChatsRepository::getAndSaveTgAvatar($userExistsData['id'], true);
            $message = self::locale(['string' => '{{ Tg_Command_Name_Save_Success }}', 'vars' => [$username]]);
            return true;
        }

        $userContacts = ContactRepository::formatUserContacts($userContacts);
        if ($userContacts['telegramid'] !== $telegramId) {
            $message = self::locale(['string' => '{{ Tg_Command_Name_Already_Set_By_Other }}', 'vars' => [$username]]);
            return false;
        }

        $reaction = '👌';
        $message = self::locale('{{ Tg_Command_Name_You_Have_One }}');
        return false;
    }
}

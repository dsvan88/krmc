<?

namespace app\Repositories\TelegramCommands;

use app\core\ChatCommand;
use app\models\Contacts;
use app\models\TelegramChats;
use app\models\Users;
use app\Repositories\AccountRepository;
use app\Repositories\ContactRepository;
use app\Repositories\TelegramChatsRepository;

class TestCommand extends ChatCommand
{
    public static $accessLevel = 'admin';
    public static function description()
    {
        return self::locale('<u>/test</u> //<i>Command for testing new functions.</i>');
    }
    public static function execute(array $arguments = [], string &$message = '', string &$reaction = '', array &$replyMarkup = [])
    {
        if (!empty(self::$requester)) {
            $message = self::locale(['string' => '{{ Tg_Command_Name_Already_Set }}', 'vars' => [self::$requester['name']]]);
            return false;
        }

        $_username = implode(' ', $arguments);

        if (empty(trim($_username))) {
            $message = self::locale("Your nickname can’t be empty!\nPlease, use that format:\n/nick <b>Your nickname</b>");
            return false;
        }

        $username = Users::formatName($_username);

        if (mb_strlen($username, 'UTF-8') < 2) {
            $message = self::locale("Your nickname is too short!\nPlease use at least <b>2</b> symbols, so people can recognize you!");
            return false;
        }

        if (empty($username)) {
            $message = self::locale("Invalid nickname format!\nPlease use <b>Cyrillic</b> or <b>Latin</b> alphabet, <b>spaces</b> and <b>digits</b> in the nickname!");
            return false;
        }

        $telegramId = self::$message['message']['from']['id'];
        $telegram = self::$message['message']['from']['username'];

        $userExistsData = Users::getDataByName($username);

        if (empty($userExistsData['id'])) {
            $userId = Users::add($username);

            Contacts::new(['telegramid' => $telegramId, 'telegram' => $telegram], $userId);
            TelegramChats::save(self::$message);
            TelegramChatsRepository::getAndSaveTgAvatar($userId, true);

            $message = self::locale(['string' => "So... we remember you under the nickname <b>%s</b>. Right?\nNice to meet you!", 'vars' => [$username]]);
            $message .= PHP_EOL . PHP_EOL;
            $message .= self::locale("If you made a mistake, don’t worry, tell the administrator about it and he will quickly fix it😏");

            $replyMarkup = [
                'inline_keyboard' => [
                    [
                        ['text' => '✅' . self::locale('Yes'), 'callback_data' => ['c' => 'nick', 'u' => $userId, 'y' => 1]],
                        ['text' => '❌' . self::locale('No'), 'callback_data' => ['c' => 'nick', 'u' => $userId]],
                    ],
                ],
            ];
            return true;
        }

        $userContacts = Contacts::getByUserId($userExistsData['id']);

        if (empty($userContacts)) {
            Contacts::new(['telegramid' => $telegramId, 'telegram' => $telegram], $userExistsData['id']);
            TelegramChatsRepository::getAndSaveTgAvatar($userExistsData['id'], true);
            $message = self::locale(['string' => "So... we remember you under the nickname <b>%s</b>. Right?\nNice to meet you!", 'vars' => [$username]]);
            $message .= PHP_EOL . PHP_EOL;
            $message .= self::locale('If you made a mistake, don’t worry, tell the Administrator about it and he will quickly fix it😏');
            $reaction = '👌';
            return true;
        }

        $userContacts = ContactRepository::formatUserContacts($userContacts);

        $message = self::locale(['string' => '{{ Tg_Command_Name_Already_Set_By_Other }}', 'vars' => [$username]]);

        $isChatExists = TelegramChatsRepository::isChatExists($userContacts['telegramid']);
        $isAvailable = AccountRepository::checkAvailable($userExistsData['id']);

        if (!$isAvailable || $isChatExists) {
            $message .= PHP_EOL;
            $message .= self::locale('If it is your, then contact the Administrators to make changes!');
            $reaction = '🤷‍♂️';
            return false;
        }

        if (!$isChatExists) {
            $message = self::locale(['string' => 'The nickname <b>%s</b> is already registered by another member of the group!', 'vars' => [$username]]);
            $message .= PHP_EOL;
            $message .= self::locale('But... I can’t find his TelegramID🤷‍♂️');
            $message .= PHP_EOL;
            $message .= self::locale('Is it your?*');
            $message .= PHP_EOL . PHP_EOL;
            $message .= '<i>' . self::locale("*This nickname will be your, after Administrators’s approve.") . '</i>';
            $replyMarkup = [
                'inline_keyboard' => [
                    [
                        ['text' => '✅' . self::locale('Yes'), 'callback_data' => ['c' => 'nickRelink', 'u' => $userExistsData['id'], 't' => $telegramId, 'y' => 1]],
                        ['text' => '❌' . self::locale('No'), 'callback_data' => ['c' => 'nickRelink', 'u' => $userExistsData['id'], 't' => $telegramId]],
                    ],
                ],
            ];
            $reaction = '🤔';
            return true;
        }

        $message = self::locale(['string' => 'The nickname <b>%s</b> is already registered by another member of the group!', 'vars' => [$username]]);
        $message .= PHP_EOL;
        $message .= self::locale("But... We didn’t saw him for quite time🤷‍♂️");
        $message .= PHP_EOL;
        $message .= self::locale("Do you wanna to make it your?*");
        $message .= PHP_EOL . PHP_EOL;
        $message .= '<i>' . self::locale("*This nickname will be your, after Administrators’s approve.") . '</i>';
        $replyMarkup = [
            'inline_keyboard' => [
                [
                    ['text' => '✅' . self::locale('Yes'), 'callback_data' => ['c' => 'nickRelink', 'u' => $userExistsData['id'], 't' => $telegramId, 'y' => 1]],
                    ['text' => '❌' . self::locale('No'), 'callback_data' => ['c' => 'nickRelink', 'u' => $userExistsData['id'], 't' => $telegramId]],
                ],
            ],
        ];
        $reaction = '🤔';
        return true;
    }
}

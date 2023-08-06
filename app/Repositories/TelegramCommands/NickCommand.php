<?

namespace app\Repositories\TelegramCommands;

use app\core\ChatCommand;
use app\core\Locale;
use app\models\Contacts;
use app\models\TelegramChats;
use app\models\Users;
use app\Repositories\ContactRepository;

class NickCommand extends ChatCommand
{
    public static function description()
    {
        return self::locale('<u>/nick Your nickname</u> (Cyrillic) <i>// Register your nickname</i>');
    }
    public static function execute(array $arguments = [])
    {
        if (!empty(self::$requester)) {
            self::$operatorClass::$resultMessage = self::locale(['string' => '{{ Tg_Command_Name_Already_Set }}', 'vars' => [self::$requester['name']]]);
            return false;
        }

        $username = Users::formatName(implode(' ',$arguments));

        if (empty($username)) {
            self::$operatorClass::$resultMessage = self::locale('{{ Tg_Command_Name_Wrong_Format }}');
            return false;
        }

        if (mb_strlen($username, 'UTF-8') < 2) {
            self::$operatorClass::$resultMessage = self::locale('{{ Tg_Command_Name_Too_Short }}');
            return false;
        }


        $telegramId = self::$message['message']['from']['id'];
        $telegram = self::$message['message']['from']['username'];

        $userExistsData = Users::getDataByName($username);

        if (empty($userExistsData['id'])) {
            $userId = Users::add($username);

            Contacts::new(['telegramid'=> $telegramId, 'telegram' => $telegram], $userId);
            TelegramChats::save(self::$message);

            self::$operatorClass::$resultMessage = self::locale(['string' => '{{ Tg_Command_Name_Save_Success }}', 'vars' => [$username]]);
            return true;
        }

        $userContacts = Contacts::getByUserId($userExistsData['id']);
        TelegramChats::save(self::$message);

        if (empty($userContacts)){
            Contacts::new(['telegramid'=> $telegramId, 'telegram' => $telegram], $userExistsData['id']);
            self::$operatorClass::$resultMessage = self::locale(['string' => '{{ Tg_Command_Name_Save_Success }}', 'vars' => [$username]]);
            return true;
        }

        $userContacts = ContactRepository::formatUserContacts($userContacts);
        if ($userContacts['telegramid'] !== $telegramId){
            self::$operatorClass::$resultMessage = self::locale(['string' => '{{ Tg_Command_Name_Already_Set_By_Other }}', 'vars' => [$username]]);
            return false;
        }
        
        self::$operatorClass::$resultMessage = self::locale('{{ Tg_Command_Name_You_Have_One }}');
        return false;
    }
}

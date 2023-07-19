<?

namespace app\Repositories\TelegramCommands;

use app\core\ChatCommand;
use app\core\Locale;
use app\models\Users;

class NewuserCommand extends ChatCommand
{
    public static $accessLevel = 'manager';
    public static function description()
    {
        return self::locale('<u>/newuser Player’s nickname</u> (in Cyrillic) <i>// Register a new nickname in the system.</i>');
    }
    public static function execute(array $arguments = [])
    {
        $username = '';
        foreach ($arguments as $string) {
            $username .= Locale::mb_ucfirst($string) . ' ';
        }
        $username = mb_substr($username, 0, -1, 'UTF-8');

        if (mb_strlen(trim($username), 'UTF-8') < 2) {
            self::$operatorClass::$resultMessage = self::locale('{{ Tg_Command_Name_Too_Short }}');
            return false;
        }
        if (preg_match('/([^а-яА-ЯрРсСтТуУфФчЧхХШшЩщЪъЫыЬьЭэЮюЄєІіЇїҐґ .0-9])/', $username) === 1) {
            self::$operatorClass::$resultMessage = self::locale('{{ Tg_Command_Name_Wrong_Format }}');
            return false;
        }

        if (Users::getId($username) > 0) {
            self::$operatorClass::$resultMessage = self::locale(['string' => '{{ Tg_Command_New_User_Already_Set }}', 'vars' => [$username]]);
            return false;
        }

        Users::add($username);

        self::$operatorClass::$resultMessage = self::locale(['string' => '{{ Tg_Command_New_User_Save_Success }}', 'vars' => [$username]]);
        return true;
    }
}

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
    public static function execute(array $arguments = [], string &$message = '', string &$reaction = '', array &$replyMarkup = [])
    {
        $username = '';
        foreach ($arguments as $string) {
            $username .= Locale::mb_ucfirst($string) . ' ';
        }
        $username = mb_substr($username, 0, -1, 'UTF-8');

        if (mb_strlen(trim($username), 'UTF-8') < 2) {
            $message = self::locale('{{ Tg_Command_Name_Too_Short }}');
            return false;
        }

        $symbols = Locale::$cyrillicPattern;
        if (preg_match_all("/[^$symbols .0-9]/ui", $username, $matches)) {
            $wrong = implode("</i>', '<i>", $matches[0]);
            $message = self::locale(['string' => "Invalid nickname format!\nPlease use only <b>Cyrillic</b> and <b>spaces</b> in the nickname!\nWrong simbols: %s", 'vars' => ["'<i>$wrong</i>'"]]);
            return false;
        }

        if (Users::getId($username) > 0) {
            $message = self::locale(['string' => '{{ Tg_Command_New_User_Already_Set }}', 'vars' => [$username]]);
            return false;
        }

        Users::add($username);
        
        $reaction = '👌';
        $message = self::locale(['string' => '{{ Tg_Command_New_User_Save_Success }}', 'vars' => [$username]]);
        return true;
    }
}

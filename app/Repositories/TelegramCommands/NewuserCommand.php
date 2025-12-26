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
    public static function execute()
    {
        $username = '';
        foreach (static::$arguments as $string) {
            $username .= Locale::mb_ucfirst($string) . ' ';
        }

        $username = Users::formatName(mb_substr($username, 0, -1, 'UTF-8'));

        if (mb_strlen(trim($username), 'UTF-8') < 2) {
            return static::result('{{ Tg_Command_Name_Too_Short }}');
        }

        if (Users::getId($username) > 0) {
            return static::result(['string' => '{{ Tg_Command_New_User_Already_Set }}', 'vars' => [$username]]);
        }

        Users::add($username);

        return static::result(['string' => '{{ Tg_Command_New_User_Save_Success }}', 'vars' => [$username]], '👌', true);
    }
}

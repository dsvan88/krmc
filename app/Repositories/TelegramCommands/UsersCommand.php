<?

namespace app\Repositories\TelegramCommands;

use app\core\ChatCommand;
use app\models\Users;

class UsersCommand extends ChatCommand
{
    public static $accessLevel = 'manager';
    public static function description()
    {
        return self::locale('<u>/users</u> <i>// Users list, registered in system.</i>');
    }
    public static function execute(array $arguments = [])
    {
        $usersList = Users::getList();
        $usersList = Users::contacts($usersList);
        $message = '';
        $x = 0;
        $count = min(count($usersList), 100);
        for ($i = 0; $i < $count; $i++) {
            if (empty($usersList[$i]['name'])) continue;
            $message .= (++$x) . ". <b>{$usersList[$i]['name']}</b>";
            if (!empty($usersList[$i]['contacts']['telegram'])) {
                $message .= " (@{$usersList[$i]['contacts']['telegram']})";
            }
            if (!empty($usersList[$i]['contacts']['telegramid'])) {
                $message .= ' ✅';
            }
            $message .= "\n";
        }
        $message .= "______________________________\n✅ - " . self::locale('{{ Tg_User_With_Telegramid }}');

        self::$operatorClass::$resultMessage = $message;
        return true;
    }
}

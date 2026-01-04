<?

namespace app\Repositories\TelegramCommands;

use app\core\Telegram\ChatCommand;
use app\models\Users;

class UsersCommand extends ChatCommand
{
    public static $accessLevel = 'manager';
    public static function description()
    {
        return self::locale("<u>/users range|#userId|nickname</u> <i>// Users list, registered in system (first 100).</i>. Examples:</i>\n\t/users 0-100\n\t/users #14\n\t/users Example");
    }
    public static function execute()
    {
        $usersList = self::getUsersList(static::$arguments);
        $usersList = Users::contacts($usersList);

        $message = '';
        $count = min(count($usersList), 100);
        for ($i = 0; $i < $count; $i++) {
            if (empty($usersList[$i]['name'])) continue;
            $message .= "<i>#{$usersList[$i]['id']}</i>. <b>{$usersList[$i]['name']}</b>";
            if (!empty($usersList[$i]['contacts']['telegram'])) {
                $message .= " (@{$usersList[$i]['contacts']['telegram']})";
            }
            if (!empty($usersList[$i]['contacts']['telegramid'])) {
                $message .= ' ✅';
            }
            $message .= "\n";
        }
        $message .= "______________________________\n✅ - " . self::locale('{{ Tg_User_With_Telegramid }}');
        $reaction = '👌';
        return static::result($message, $reaction, true);
    }
    public static function getUsersList(array $arguments = []): array
    {
        $options = $arguments[0];

        if (empty($options))
            return Users::getList(0, 100);

        if (preg_match('/(\d+)\-(\d+)/', $options, $match) === 1) {
            $offset = $match[1];
            $limit = $match[2];
            return Users::getList($offset, $limit - $offset + 1);
        }

        if (preg_match('/\#(\d+)/', $options, $match) === 1) {
            $id = $match[1];
            return [Users::find($id)];
        }

        if (preg_match('/([а-я0-9]+)/ui', $options, $match) === 1) {
            $pattern = $match[1];
            return Users::ilike('name', $pattern);
        }

        return Users::getList(0, 100);
    }
}

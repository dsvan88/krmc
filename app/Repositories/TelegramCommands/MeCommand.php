<?

namespace app\Repositories\TelegramCommands;

use app\core\Telegram\ChatCommand;
use app\models\SocialPoints;

class RenickCommand extends ChatCommand
{
    public static $accessLevel = 'user';

    public static function description()
    {
        return self::locale('<u>/me</u> <i>// Get information about your profile.</i>');
    }
    public static function execute()
    {
        return static::result(['string' => 'Your summ of social points is: <b>%s</b>', 'vars' => [SocialPoints::get(static::$requester['id'])]]);
    }
}

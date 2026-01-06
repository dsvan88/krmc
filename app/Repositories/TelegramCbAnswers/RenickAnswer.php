<?

namespace app\Repositories\TelegramCommands;

use app\core\Telegram\ChatAnswer;
use app\models\Users;
use Exception;

class RenickAnswer extends ChatAnswer
{
    public static function execute():array
    {
        if (empty(static::$arguments))
            throw new Exception(__METHOD__ . ': Arguments is empty');

        if (empty(static::$requester)) {
            return static::result('You don’t have enough rights to change information about other users!');
        }

        $uId = (int) trim(static::$arguments['u']);

        if (empty($uId))
            throw new Exception(__METHOD__ . ': UserID can’t be empty!');

        if (static::$requester['id'] != $uId && (empty(static::$requester['privilege']['status']) || !in_array(static::$requester['privilege']['status'], ['manager', 'admin', 'root'], true)))
            return static::result('You don’t have enough rights to change information about other users!');

        $update = [];
        if (empty(static::$arguments['y'])) {

            if (empty(static::$requester['personal']['newName']))
                return static::result("I don't know why, but I can't find your new nick name... I'm deeply sorry...");

            $personal = static::$requester['personal'];
            unset($personal['newName']);
            Users::edit(['personal'=> $personal], ['id' => static::$requester['id']]);

            $update = [
                'message' => static::locale('Okay! Let’s try again later!')
            ];
        } else {
            $name = static::$requester['personal']['newName'];
            $personal = static::$requester['personal'];
            unset($personal['newName']);
            Users::edit(['name' => $name, 'personal'=> $personal], ['id' => static::$requester['id']]);

            $update = [
                'message' =>
                static::locale(['string' => "<b>%s</b>, nice to meet you, again!\nYou successfully changed your nickname!", 'vars' => [$name]]) .
                    PHP_EOL . PHP_EOL .
                    static::locale('If you made a mistake - don’t worry! Just tell the Administrator about it and he will quickly fix it😏'),
            ];
            self::$report = ['string' => "User <b>%s</b>, successfully changed a nickname to <b>%s</b>.", 'vars' => [static::$requester['name'], $name]];
        }

        return array_merge(static::result('Okay', true), ['update' => [$update]]);
    }
}
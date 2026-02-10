<?php

namespace app\Repositories\TelegramCbAnswers;

use app\core\Telegram\ChatAnswer;
use app\models\Users;
use Exception;

class NewuserAnswer extends ChatAnswer
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

        if (!in_array(static::$requester->profile->status, ['manager', 'admin', 'root'], true))
            return static::result('You don’t have enough rights to change information about other users!');

        if (empty(static::$arguments['y'])) {

            Users::delete($uId);

            $update = [
                'message' => static::locale(['string' => "Okay! Let’s try again!\nUse the next command to register a new user:\n/newuser <b>%s</b>\n\nTry to avoid characters of different languages.", 'vars' => [static::$requester->profile->name]])
            ];
        } else {
            $userData = Users::find($uId);
            $update = [
                'message' =>
                static::locale(['string' => 'The player under the nickname <b>%s</b> is successfully registered in the system!', 'vars' => [$userData['name']]]) .
                    PHP_EOL . PHP_EOL .
                    static::locale('*Hint him about the desirability of registering this alias, or do it manually in the admin panel😏'),
            ];
        }

        return array_merge(static::result('Okay', true), ['update' => [$update]]);
    }
}
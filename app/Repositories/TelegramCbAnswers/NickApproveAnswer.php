<?php

namespace app\Repositories\TelegramCbAnswers;

use app\core\Entities\Chat;
use app\core\Entities\User;
use app\core\Telegram\ChatAnswer;
use app\models\Contacts;
use app\models\Settings;
use app\Repositories\TelegramChatsRepository;
use Exception;

class NickAnswer extends ChatAnswer
{
    public static function execute(): array
    {
        if (empty(static::$requester) || empty(static::$arguments))
            throw new Exception(__METHOD__ . ': Requester or Arguments is empty!');

        if (!in_array(static::$requester->profile->status, ['admin', 'root'], true))
            return static::result('You don’t have enough rights to change information about other users!');

        if (empty(static::$arguments['u']) || empty(static::$arguments['t'])) {

            if (static::$arguments['ci'] != Settings::getMainTelegramId()) {
                $update['message'] = static::locale('Okay! I get it.');
                $update['message'] .= PHP_EOL;
                $update['message'] .= static::locale('I’ll inform the user about your decision😔');
            }

            $message = static::locale('I offer my deepest apologies, but the Administrator has rejected your request.');
            $message .= PHP_EOL;
            $message .= static::locale('Just come up with a new nickname for yourself!');

            $update2 = [
                'chatId' => (int) static::$arguments['ci'],
                'messageId' => (int) static::$arguments['mi'],
                'message' => $message,
            ];

            return array_merge(static::result('Success', true), ['update' => [$update, $update2]]);
        }

        $uId = (int) trim(static::$arguments['u']);
        $tId = (int) trim(static::$arguments['t']);

        if (empty($uId) || empty($tId))
            throw new Exception(__METHOD__ . ': UserID or TelegramID can’t be empty!');

        $target = Chat::create($tId);
        $oldUser = User::create($uId);

        $contacts = ['telegramid' => $tId, 'telegram' => $target->username];
        Contacts::reLink($contacts, $uId);
        TelegramChatsRepository::getAndSaveTgAvatar($uId, true);

        if (static::$arguments['ci'] != Settings::getMainTelegramId()) {
            $update['message'] = static::locale('Okay! I get it.');
            $update['message'] .= PHP_EOL;
            $update['message'] .= static::locale('I’ll inform the user about your decision😊');
        }

        $message = static::locale('The administrator has approved your request!');
        $message .= PHP_EOL;
        $message .= static::locale(['string' => 'I’m remember you under nickname <b>%s</b>', 'vars' => [$oldUser->name]]);
        $message .= PHP_EOL;
        $message .= static::locale('Nice to meet you!');

        $update2 = [
            'chatId' => (int) static::$arguments['ci'],
            'messageId' => (int) static::$arguments['mi'],
            'message' => $message,
        ];

        return array_merge(static::result('Success', true), ['update' => [$update, $update2]]);
    }
}

<?php

namespace app\Repositories\TelegramCbAnswers;

use app\core\Telegram\ChatAnswer;
use app\models\Contacts;
use app\models\Settings;
use app\models\TelegramChats;
use app\models\Users;
use app\Repositories\TelegramBotRepository;
use app\Repositories\TelegramChatsRepository;
use Exception;

class NickAnswer extends ChatAnswer
{
    public static function execute():array
    {
        if (empty(static::$arguments))
            throw new Exception(__METHOD__ . ': Arguments is empty!');

        if (empty(static::$requester))
            return static::result('You don’t have enough rights to edit information about other users!');

        $uId = (int) trim(static::$arguments['u']);
        $tId = (int) trim(static::$arguments['t']);

        if (empty($uId) || empty($tId))
            throw new Exception(__METHOD__ . ': UserID or TelegramID can’t be empty!');

        if (static::$requester['id'] != $uId) {
            if (empty(static::$requester['privilege']['status']) || !in_array(static::$requester['privilege']['status'], ['manager', 'admin', 'root'], true))
                return static::result('You don’t have enough rights to change information about other users!');
            static::$arguments['ci'] = TelegramBotRepository::getChatId();
            static::$arguments['mi'] = TelegramBotRepository::getMessageId();
            
            return static::nickApprove();
        }

        $userData = Users::find($uId);

        if (static::$message['callback_query']['from']['id'] != $tId) {
            return static::result('You don’t have enough rights to change information about other users!');
        }

        if (empty(static::$arguments['y'])) {
            $update['message'] = static::locale(['string' => 'The nickname <b>%s</b> is already registered by another member of the group!', 'vars' => [$userData['name']]]);
            $update['message'] .= PHP_EOL;
            $update['message'] .= static::locale('Just come up with a new nickname for yourself!');
            return array_merge(static::result('Success', true), ['update' => [$update]]);
        }
        $update['message'] = static::locale(['string' => 'The nickname <b>%s</b> is already registered by another member of the group!', 'vars' => [$userData['name']]]) . PHP_EOL;
        $update['message'] .= static::locale('But... I can’t find his TelegramID🤷‍♂️') . PHP_EOL;
        $update['message'] .= static::locale('Is it your?*') . PHP_EOL;
        $update['message'] .= PHP_EOL . '⏳<i>' . static::locale('*Just wait a little for Administrators’s approve.') . '</i>';
        $update['replyMarkup'] = [
            'inline_keyboard' => [
                [
                    ['text' => '✅' . static::locale('Yes'), 'callback_data' => ['c' => 'nickRelink', 'u' => $uId, 't' => $tId, 'y' => 1]],
                    ['text' => '❌' . static::locale('No'), 'callback_data' => ['c' => 'nickRelink', 'u' => $uId, 't' => $tId]],
                ],
            ],
        ];

        $cId = static::$message['callback_query']['message']['chat']['id'];
        $mId = static::$message['callback_query']['message']['id'];
        $send = [];
        if ($cId !== Settings::getMainTelegramId()) {
            $send['message'] = static::locale(['string' => 'Telegram user with ID <b>%s</b> trying to register the nickname <b>%s</b>.', 'vars' => [$tId, $userData['name']]]) . PHP_EOL;
            $send['message'] .= static::locale('It’s already registered in our system with another TelegramID, but his TelegramID doesn’t exists anymore or owner didn’t play for quite time.') . PHP_EOL;
            $send['message'] .= static::locale('Do you agree to pass an ownership of the nickname to a new user?');
            $send['replyMarkup'] = [
                'inline_keyboard' => [
                    [
                        ['text' => '✅' . static::locale('Yes'), 'callback_data' => ['c' => 'nickApprove', 'u' => $uId, 't' => $tId, 'ci' => $cId, 'mi' => $mId]],
                        ['text' => '❌' . static::locale('No'), 'callback_data' => ['c' => 'nickApprove', 'ci' => $cId, 'mi' => $mId]],
                    ],
                ],
            ];
        }
        return array_merge(static::result('Success', true), ['update' => [$update]], ['send' => [$send]]);
    }

    public static function nickApprove(){
        
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

        $userData = Users::find($uId);
        $thChat = TelegramChats::getChat($tId);
        $contacts = ['telegramid' => $tId, 'telegram' => $thChat['personal']['username']];
        Contacts::reLink($contacts, $uId);
        TelegramChatsRepository::getAndSaveTgAvatar($uId, true);

        if (static::$arguments['ci'] != Settings::getMainTelegramId()) {
            $update['message'] = static::locale('Okay! I get it.');
            $update['message'] .= PHP_EOL;
            $update['message'] .= static::locale('I’ll inform the user about your decision😊');
        }

        $message = static::locale('The administrator has approved your request!');
        $message .= PHP_EOL;
        $message .= static::locale(['string' => 'I’m remember you under nickname <b>%s</b>', 'vars' => [$userData['name']]]);
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
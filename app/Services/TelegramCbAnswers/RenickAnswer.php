<?php

namespace app\Services\TelegramCbAnswers;

use app\core\Telegram\ChatAnswer;
use app\mappers\SocialPoints;
use app\mappers\Users;
use app\Services\TelegramCommands\RenickCommand;
use Exception;

class RenickAnswer extends ChatAnswer
{

    public static $accessLevel = 'user';
    public static function execute(): array
    {
        if (empty(static::$arguments['u']))
            throw new Exception(__METHOD__ . ': Arguments is empty');

        $uId = (int) trim(static::$arguments['u']);

        if (empty($uId))
            throw new Exception(__METHOD__ . ': UserID can’t be empty!');

        if (static::$requester->profile->id != $uId) {
            return static::result('You don’t have enough rights to change information about other users!');
        }

        if (SocialPoints::get($uId) < RenickCommand::$costs) {
            return static::result(['string' => 'I’m deeply sorry, but you can’t do this action yet! Social Points isn’t enough. Need <b>%s</b>.', 'vars' => [static::$costs]]);
        }

        if (empty(static::$arguments['y'])) {

            if (empty(static::$requester->profile->newName))
                return static::result("I don't know why, but I can't find your new nick name... I'm deeply sorry...");

            $personal = static::$requester->profile->personal;
            unset($personal['newName']);
            Users::edit(['personal' => $personal], ['id' => static::$requester->profile->id]);

            $update = [
                'message' => static::locale('Okay! Let’s try again later!')
            ];
            return array_merge(static::result('Okay', true), ['update' => [$update]]);
        }

        $name = static::$requester->profile->newName;
        $personal = static::$requester->profile->personal;
        unset($personal['newName']);
        Users::edit(['name' => $name, 'personal' => $personal], ['id' => static::$requester->profile->id]);

        $update = [
            'message' =>
            static::locale(['string' => "<b>%s</b>, nice to meet you, again!\nYou successfully changed your nickname!", 'vars' => [$name]]) .
                PHP_EOL . PHP_EOL .
                static::locale('If you made a mistake - don’t worry! Just tell the Administrator about it and he will quickly fix it😏'),
        ];
        self::$report = ['string' => "User <b>%s</b>, successfully changed a nickname to <b>%s</b>.", 'vars' => [static::$requester->profile->name, $name]];

        SocialPoints::minus(static::$requester->profile->id, RenickCommand::$costs);

        return array_merge(static::result('Okay', true), ['update' => [$update]]);
    }
}

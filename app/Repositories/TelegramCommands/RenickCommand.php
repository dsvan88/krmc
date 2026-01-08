<?

namespace app\Repositories\TelegramCommands;

use app\core\Telegram\ChatCommand;
use app\core\Locale;
use app\models\SocialPoints;
use app\models\Users;

class RenickCommand extends ChatCommand
{
    public static $accessLevel = 'user';
    public static $costs = 50;

    public static function description()
    {
        return self::locale('<u>/renick Your nickname</u> <i>// Change your nickname</i>');
    }
    public static function execute()
    {
        if (SocialPoints::get(static::$requester['id']) < static::$costs){
            return static::result(['string'=> 'I’m deeply sorry, but you can’t do this action yet! Social Points isn’t enough. Need <b>%s</b>.', 'vars' => [static::$costs]]);
        }
        
        $_username = implode(' ', static::$arguments);

        if (empty(trim($_username))) {
            return static::result("Your nickname can’t be empty!\nPlease, use that format:\n/nick <b>Your nickname</b>");
        }

        $username = Users::formatName($_username);

        if (empty($username)) {
            return static::result("Invalid nickname format!\nPlease use <b>Cyrillic</b> or <b>Latin</b> alphabet, <b>spaces</b> and <b>digits</b> in the nickname!");
        }

        if (mb_strlen($username, 'UTF-8') < 2) {
            return static::result("Your nickname is too short!\nPlease use at least <b>2</b> symbols, so people can recognize you!");
        }

        $symbols = Locale::$cyrillicPattern;
        if (preg_match_all("/[^a-z$symbols .0-9]/ui", $_username, $matches)) {
            $wrong = implode('</i>", "<i>', $matches[0]);
            return static::result(['string' => "Invalid nickname format!\nPlease use <b>Cyrillic</b> or <b>Latin</b> alphabet, <b>spaces</b> and <b>digits</b>!\nWrong simbols: %s", 'vars' => ["\"<i>$wrong</i>\""]]);
        }

        $userExistsData = Users::getDataByName($username);

        if (empty($userExistsData['id'])) {

            $personal = static::$requester['personal'];
            $personal['newName'] = $username;
            Users::edit(['personal'=> $personal], ['id' => static::$requester['id']]);

            $message = self::locale(['string' => "So... you wanna to change your nickname <b>%s</b> to a nickname <b>%s</b>. Right?", 'vars' => [static::$requester['name'], $username]]);

            $replyMarkup = [
                'inline_keyboard' => [
                    [
                        ['text' => '✅' . self::locale('Yes'), 'callback_data' => ['c' => 'renick', 'u' => static::$requester['id'], 'y' => 1]],
                        ['text' => '❌' . self::locale('No'), 'callback_data' => ['c' => 'renick', 'u' => static::$requester['id']]],
                    ],
                ],
            ];
            return [
                'result' => true,
                'reaction' => '👌',
                'send' => [
                    [
                        'message' => $message,
                        'replyMarkup' => $replyMarkup,
                    ]
                ]
            ];
        }

        $message = self::locale(['string' => '{{ Tg_Command_New_User_Already_Set }}', 'vars' => [$username]]) . PHP_EOL;
        $message .= self::locale('If it is your, then contact the Administrators to make changes!');
        
        return [
            'result' => false,
            'reaction' => '🤷‍♂️',
            'send' => [
                [
                    'message' => $message,
                ]
            ]
        ];
    }
}

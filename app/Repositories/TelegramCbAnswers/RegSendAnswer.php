<?

namespace app\Repositories\TelegramCbAnswers;

use app\core\Telegram\ChatAnswer;
use app\models\Days;
use app\models\Settings;
use app\models\Weeks;
use Exception;

class RegSendAnswer extends ChatAnswer
{
    public static function execute():array
    {
        if (empty(static::$requester) || empty(static::$arguments))
            throw new Exception(__METHOD__ . ': UserData or Arguments is empty!');

        if (empty(static::$requester['privilege']['status']) || !in_array(static::$requester['privilege']['status'], ['manager', 'admin', 'root'], true))
            return static::result('You don’t have enough rights!');

        $weekId = (int) trim(static::$arguments['w']);
        $dayNum = (int) trim(static::$arguments['d']);

        $message = Days::getFullDescription(Weeks::weekDataById($weekId), $dayNum);

        $replyMarkup = [
            'inline_keyboard' => [
                [
                    ['text' => '🙋' . static::locale('I will!'), 'callback_data' => ['c' => 'booking', 'w' => $weekId, 'd' => $dayNum]],
                    ['text' => static::locale('I want!') . '🥹', 'callback_data' => ['c' => 'booking', 'w' => $weekId, 'd' => $dayNum, 'p' => '?']],
                    ['text' => '⛔️', 'callback_data' => ['c' => 'booking', 'w' => $weekId, 'd' => $dayNum, 'r' => '1']],
                ],
            ],
        ];

        $send = [
            'chatId' => Settings::getMainTelegramId(),
            'message' => $message,
            'replyMarkup' => $replyMarkup,
        ];
        return array_merge(static::result('Success', true), ['send' => [$send]]);
    }
}
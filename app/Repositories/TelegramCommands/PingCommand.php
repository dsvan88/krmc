<?

namespace app\Repositories\TelegramCommands;

use app\core\Telegram\ChatCommand;
use app\core\Locale;
use app\core\Tech;
use app\models\Contacts;
use app\models\GameTypes;
use app\models\Weeks;
use app\Repositories\TelegramBotRepository;

class PingCommand extends ChatCommand
{
    public static $accessLevel = 'trusted';
    private static $weeksOffset = 4;
    public static function description()
    {
        return self::locale('<u>/ping (week day)</u> <i>// Ping users from current activity to get their attention.</i>');
    }
    public static function execute()
    {
        $weekId = Weeks::currentId();

        $daySlug = isset(static::$arguments[0]) ? static::$arguments[0] : 'tod';

        TelegramBotRepository::parseDayNum($daySlug, static::$arguments);
        if (static::$arguments['dayNum'] < static::$arguments['currentDay'])
            $weekId++;

        $weekData = Weeks::weekDataById($weekId);
        $currentDay = $weekData['data'][static::$arguments['dayNum']];
        $existsIds = array_column($currentDay['participants'], 'id');
        $game = $currentDay['game'];

        $bookedIds = [];
        foreach ($weekData['data'] as $num => $day) {
            if ($num == static::$arguments['dayNum'] || $day['game'] !== $game) continue;
            $bookedIds = array_column($day['participants'], 'id');
        }

        $dayTimestamp = $weekData['start'] + (TIMESTAMP_DAY * static::$arguments['dayNum']);
        $format = 'd.m.Y ' . $weekData['data'][static::$arguments['dayNum']]['time'];
        $dayDate = strtotime(date($format, $dayTimestamp));
        $date = date('d.m.Y', $dayDate);

        $offset = self::$weeksOffset;
        do {
            $weekData = Weeks::find($weekId - $offset);
            foreach ($weekData['data'] as $num => $day) {
                if ($num == static::$arguments['dayNum'] || $day['game'] !== $game) continue;
                $bookedIds = array_merge($bookedIds, array_column($day['participants'], 'id'));
            }
        } while (--$offset > 0);

        if (empty($bookedIds))
            return static::result('');

        $userIds = [];
        foreach ($bookedIds as $userId) {
            if (empty($userId) || in_array($userId, $userIds, true) || in_array($userId, $existsIds, true)) continue;
            $userIds[] = $userId;
        }

        $contacts = Contacts::findGroup('user_id', $userIds);
        $tgNames = [];
        foreach ($contacts as $contact) {
            if ($contact['type'] !== 'telegram') continue;
            $tgNames[] = $contact['contact'];
        }

        if (empty($tgNames)) {
            return static::result('');;
        }

        $gameNames = GameTypes::names();

        $lang = Locale::$langCode;
        $proto = Tech::getRequestProtocol();
        $link = "<a href='$proto://{$_SERVER['SERVER_NAME']}/game/{$game}/?lang=$lang'>{$gameNames[$game]}</a>";

        $list = '@' . implode(', @', $tgNames);

        $message =  self::locale(['string' => "Dear players: %s!\n%s at %s we’re going to play in %s!\nAre you in?😉", 'vars' => [$list, $date, $currentDay['time'], $link]]);
        return [
            'result' => true,
            'reaction' => '👌',
            'send' => [
                [
                    'message' => $message,
                ]
            ]
        ];
    }
}

<?

namespace app\Repositories\TelegramCommands;

use app\core\ChatCommand;
use app\core\Locale;
use app\core\Tech;
use app\models\Contacts;
use app\models\Days;
use app\models\GameTypes;
use app\models\Weeks;

class PingCommand extends ChatCommand
{
    public static $accessLevel = 'trusted';
    private static $weeksOffset = 4;
    public static function description()
    {
        return self::locale('<u>/ping (week day)</u> <i>// Ping users from current activity to get their attention.</i>');
    }
    public static function execute(array $arguments = [])
    {
        $weekId = Weeks::currentId();
        $requestData = $arguments;

        $daySlug = isset($requestData[0]) ? $requestData[0] : 'tod';
        self::$operatorClass::parseDayNum($daySlug, $requestData);
        if ($requestData['dayNum'] < $requestData['currentDay'])
            $weekId++;

        $weekData = Weeks::weekDataById($weekId);
        $currentDay = $weekData['data'][$requestData['dayNum']];
        $existsIds = array_column($currentDay['participants'], 'id');
        $game = $currentDay['game'];

        foreach ($weekData['data'] as $num => $day) {
            if ($num == $requestData['dayNum'] || $day['game'] !== $game) continue;
            $bookedIds = array_column($day['participants'], 'id');
        }

        $offset = self::$weeksOffset;
        do {
            $weekData = Weeks::find($weekId - $offset);
            foreach ($weekData['data'] as $num => $day) {
                if ($num == $requestData['dayNum'] || $day['game'] !== $game) continue;
                $bookedIds = array_merge($bookedIds, array_column($day['participants'], 'id'));
            }
        } while (--$offset > 0);

        $userIds = [];
        foreach($bookedIds as $userId){
            if (in_array($userId, $userIds) || in_array($userId, $existsIds)) continue;
            $userIds[] = $userId;
        }

        $contacts = Contacts::findGroup('user_id', $userIds);
        $tgNames = [];
        foreach ($contacts as $contact) {
            if ($contact['type'] !== 'telegram') continue;
            $tgNames[] = $contact['contact'];
        }
        
        $_SESSION['debug'][] = count($userIds);
        $_SESSION['debug'][] = implode(PHP_EOL,$userIds);
        $_SESSION['debug'][] = implode(PHP_EOL,$tgNames);

        if (empty($tgNames)) {
            // self::$operatorClass::$resultMessage = self::locale('{{ Tg_Command_Games_Not_Set }}');
            return false;
        }

        $dayTimestamp = $weekData['start'] + (TIMESTAMP_DAY * $requestData['dayNum']);
        $format = 'd.m.Y ' . $weekData['data'][$requestData['dayNum']]['time'];
        $dayDate = strtotime(date($format, $dayTimestamp));
        // $date = date('d.m.Y', $dayDate) . ' (<b>' . self::locale(Days::$days[$requestData['dayNum']]) . '</b>) ' . $currentDay['time'];
        $date = date('d.m.Y', $dayDate) . ' at ' . $currentDay['time'];

        $gameNames = GameTypes::names();

        $lang = Locale::$langCode;
        $proto = Tech::getRequestProtocol();
        $link = "<a href='$proto://{$_SERVER['SERVER_NAME']}/game/{$game}/?lang=$lang'>{$gameNames[$game]}</a>";

        $list = '@' . implode(', @', $tgNames);
        self::$operatorClass::$resultMessage =  self::locale(['string' => "Dear players: %s!\n%s we're going to play in %s!\nAre you in?😉", 'vars' => [$list, $date, $link]]);
        return true;
    }
}

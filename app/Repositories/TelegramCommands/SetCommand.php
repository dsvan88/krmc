<?
namespace app\Repositories\TelegramCommands;

use app\core\ChatCommand;
use app\models\Days;
use app\models\Weeks;

class RegCommand extends ChatCommand {
    public static function description(){
        return self::locale('<u>/day (week day)</u> <i>// Booking information for a specific day. Without specifying the day - for today</i>');
    }
    public static function execute(array $arguments=[]){
        if (empty($arguments)) {
            return [false, self::locale('{{ Tg_Command_Without_Arguments }}')];
        }
        
        $dayName = '';
        $dayNum = -1;
        $currentDayNum = Days::current();
        
        $gameName = $dayName = $time = '';
        $tournament = false;
        
        foreach ($arguments as $value) {
            $value = trim($value);
            if ($gameName === '' && preg_match('/^(maf|маф|наст|board|table|пок|pok|nlh|інш|другое|etc)/', mb_strtolower($value, 'UTF-8'), $gamesPattern) > 0) {
                $gameName = $gamesPattern[0];
                if ($tournament === false && preg_match('/(тур|tour)/', mb_strtolower($value, 'UTF-8')) > 0) {
                    $tournament = true;
                }
                continue;
            }
            if ($time === '' && preg_match('/^([0-2]{0,1}[0-9]\:[0-5][0-9])/', mb_strtolower($value, 'UTF-8'), $timesPattern) > 0) {
                $time = $timesPattern[0];
                continue;
            }
            if ($dayName === '' && preg_match('/^[+-]{0,1}(пн|пон|вт|ср|чт|чет|пт|пят|сб|суб|вс|вос|сг|сег|зав)/', mb_strtolower($value, 'UTF-8'), $daysPattern) > 0) {
                $dayName = $daysPattern[0];
                continue;
            }
        }
        
        if ($dayName === '')
            $dayName = 'сг';
        
        $method = '+';
        if ($dayName[0] === '+' || $dayName[0] === '-') {
            $method = $dayName[0];
            $dayName = mb_substr($dayName, 1, null, 'UTF-8');
        }
        $dayNum = self::$operatorClass::parseDayNum($dayName, $currentDayNum);
        
        if ($gameName !== '') {
            $gamesArray = [
                'mafia' => ['maf', 'маф'],
                'board' => ['наст', 'board', 'table'],
                'nlh' => ['пок', 'pok', 'nlh'],
                'etc' => ['інш', 'другое', 'etc'],
            ];
        
            foreach ($gamesArray as $name => $gameNames) {
                if (in_array($gameName, $gameNames, true)) {
                    $gameName = $name;
                    break;
                }
            }
        }
        
        $weekId = Weeks::currentId();
        
        if ($dayNum < $currentDayNum) {
            ++$weekId;
        }
        $weekData = Weeks::weekDataById($weekId);
        
        $weekData['data'][$dayNum]['status'] = 'set';
        
        if ($method === '-') {
            $weekData['data'][$dayNum]['status'] = 'recalled';
        }
        
        if ($gameName !== '') {
            $weekData['data'][$dayNum]['game'] = $gameName;
        }
        
        if ($time !== '') {
            $weekData['data'][$dayNum]['time'] = $time;
        }
        
        if (isset($arguments['prim'])) {
            $weekData['data'][$dayNum]['day_prim'] = $arguments['prim'];
        }
        
        if ($tournament) {
            if (empty($weekData['data'][$dayNum]['mods']) || !in_array('tournament', $weekData['data'][$dayNum]['mods'])) {
                $weekData['data'][$dayNum]['mods'][] = 'tournament';
            }
        } elseif (!empty($weekData['data'][$dayNum]['mods'])) {
            $index = array_search('tournament', $weekData['data'][$dayNum]['mods'], true);
            if ($index !== false) {
                unset($weekData['data'][$dayNum]['mods'][$index]);
                $weekData['data'][$dayNum]['mods'][$index] = array_values($weekData['data'][$dayNum]['mods']);
            }
        }
        
        $result = Days::setDayData($weekId, $dayNum, $weekData['data'][$dayNum]);
        
        if (!$result) {
            return [false, json_encode($weekData['data'][$dayNum], JSON_UNESCAPED_UNICODE)];
        }
        
        $message = $method === '-' ? self::locale('{{ Tg_Command_Successfully_Canceled }}') : Days::getFullDescription($weekData, $dayNum);

        return [$result, $message];
    }
}
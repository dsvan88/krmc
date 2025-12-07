<?

namespace app\Repositories;

use app\core\Locale;
use app\models\Days;
use app\models\Weeks;

class DayRepository
{
    public static $daysArray = [
        ['пн', 'пон', 'mon'],
        ['вт', 'вто', 'вів', 'tue'],
        ['ср', 'сре', 'сер', 'wed'],
        ['чт', 'чтв', 'чет', 'thu'],
        ['пт', 'пят', 'п’ят', 'fri'],
        ['сб', 'суб', 'sat'],
        ['вс', 'вос', 'нед', 'нд', 'sun']
    ];

    public static $dayDefaultModsArray = [
        'beginners' => '',
        'tournament' => '',
        'night' => '',
        'close' => '',
        'theme' => '',
        'funs' => ''
    ];
    public static $techDaysArray = [
        'today' => ['tod', 'td', 'сг', 'сег', 'сьо'],
        'tomorrow' => ['tom', 'tm', 'зав'],
    ];



    public static function renamePlayer(int $userId, string $name): void
    {
        $weeks = Weeks::getAll();
        foreach ($weeks as $week) {
            foreach ($week['data'] as $dayNum => $day) {
                foreach ($day['participants'] as $participantNum => $participant) {
                    if ($participant['id'] !== $userId) continue;
                    $week['data'][$dayNum]['participants'][$participantNum]['name'] = $name;
                }
            }
            Weeks::setWeekData($week['id'], ['data' => $week['data']]);
        }
    }
    public static function dayDescription(array $day): string
    {
        if (empty($day)) return false;
        $result = $day['date'] . ' - ' .  $day['gameName'] . "\n" . Locale::phrase('Already registered players') . ': ' . count($day['participants']) . PHP_EOL;
        return preg_replace('/<.*?>/', '', $result);
    }
    public static function findNearSetDay(int $weekId, int $dayId)
    {
        $dayData = [];
        do {
            ++$dayId;
            if ($dayId > 6) {
                if (!Weeks::checkNextWeek($weekId, true)) return [$weekId, false];
                $dayId = 0;
                ++$weekId;
            }
            $dayData = Days::weekDayData($weekId, $dayId);
        } while ($dayData['status'] !== 'set');

        return [$weekId, $dayId];
    }
    public static function getDayNamesForCommand(): string
    {
        $days = [];
        foreach (static::$daysArray as $dayNames) {
            $days = array_merge($days, $dayNames);
        }
        foreach (static::$techDaysArray as $dayNames) {
            $days = array_merge($days, $dayNames);
        }
        return implode('|', $days);
    }
    public static function getModsTexts(array $mods = []): string
    {
        if (empty($mods)) return '';

        $result = '';
        if (in_array('funs', $mods, true))
            $result .= Locale::phrase("*<b>Fun game</b>!\nFewer rules, more emotions, additional roles and moves!\nHave a good time and have fun!\n");
        if (in_array('beginners', $mods, true))
            $result .= Locale::phrase("*<b>Begginers</b>!\nLess strict, more explanatory, friendly atmosphere!\nIt’s time to try something new in safest way!😉\n");
        if (in_array('night', $mods, true))
            $result .= Locale::phrase("*<b>Nights</b>!\nAll night long! Don’t stop!😉\n");
        if (in_array('theme', $mods, true))
            $result .= Locale::phrase("*<b>Themes</b>!\nPrepeare yourself and your image!\nIt’s time to dive into a different world!😁\n");
        if (in_array('close', $mods, true))
            $result .= Locale::phrase("*<b>Close</b>!\nOn invitation only!\n");
        if (in_array('tournament', $mods, true))
            $result .= Locale::phrase("<b>Tournament</b>!\nBecome a champion in a glorious and fair competition!\n");
        return $result;
    }
    public static function findLastGameOfPlayer(int $userId = 0)
    {
        if (empty($userId)) return 0;

        $weeks = Weeks::getAll();
        $weeks = array_reverse($weeks);
        foreach($weeks as $week){
            foreach($week['data'] as $num=>$day)
            {
                if ($day['status'] !== 'set') continue;
                foreach($day['participants'] as $player){
                    if ($player['id'] == $userId) 
                        return $week['start']+TIME_MARGE*($num+1);
                }
            }
        }

        return 0;
    }
}

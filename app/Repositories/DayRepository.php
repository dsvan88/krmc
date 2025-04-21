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

    public static $techDaysArray = [
        ['tod', 'td', 'сг', 'сег', 'сьо'],
        ['tom', 'tm', 'зав'],
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
}

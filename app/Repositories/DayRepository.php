<?

namespace app\Repositories;

use app\models\Games;
use app\models\Weeks;

class DayRepository
{
    public static function renamePlayer(int $userId, string $name): void
    {
        $weeks = Weeks::getAll();
        foreach ($weeks as $num => $week) {
            if (!strpos($week['data'], '"id":' . $userId . ',')) continue;
            $days = json_decode($week['data'], true);
            foreach ($days as $dayNum => $day) {
                foreach ($day['participants'] as $participantNum => $participant) {
                    if ($participant['id'] !== $userId) continue;
                    $days[$dayNum]['participants'][$participantNum]['name'] = $name;
                }
            }
            Weeks::setWeekData($week['id'], ['data' => $days]);
        }
    }
}

<?

namespace app\Repositories;

use app\core\Locale;
use app\core\Paginator;
use app\models\Days;
use app\models\GameTypes;
use app\models\Users;
use app\models\Weeks;

class WeekRepository
{
    public static function getShowData(int $weekId = 0): array
    {
        $weekCurrentId = Weeks::currentId();
        if (empty($weekId))
            $weekId = $weekCurrentId;

        $weeksIds = Weeks::getIds();
        $weeksCount = count($weeksIds);
        $weekCurrentIndexInList = array_search($weekCurrentId, $weeksIds);

        $weekData = Weeks::weekDataById($weekId);

        $dayId = Days::current();

        $prevWeek = $nextWeek = false;

        $selectedWeekIndex = array_search($weekId, $weeksIds);

        if (isset($weeksIds[$selectedWeekIndex - 1]))
            $prevWeek = Weeks::weekDataById($weeksIds[$selectedWeekIndex - 1]);
        if (isset($weeksIds[$selectedWeekIndex + 1]))
            $nextWeek = Weeks::weekDataById($weeksIds[$selectedWeekIndex + 1]);

        $dayNames = Locale::apply([
            'Monday',
            'Tuesday',
            'Wednesday',
            'Thursday',
            'Friday',
            'Saturday',
            'Sunday'
        ]);

        $games = GameTypes::names();
        $days = [];

        for ($i = 0; $i < 7; $i++) {

            $days[$i] = $weekData['data'][$i];
            $days[$i]['timestamp'] = $weekData['start'] + TIMESTAMP_DAY * $i;
            $days[$i]['date'] = date('d.m.Y', $days[$i]['timestamp']) . ' (<strong>' . $dayNames[$i] . '</strong>) ' . $days[$i]['time'];

            $days[$i]['gameName'] = $games[$days[$i]['game']];



            $days[$i]['class'] = 'day-future';
            if ($selectedWeekIndex < $weekCurrentIndexInList) {
                $days[$i]['class'] = 'day-expire';
            } elseif ($selectedWeekIndex === $weekCurrentIndexInList) {
                if ($dayId > $i) {
                    $days[$i]['class'] = 'day-expire';
                } elseif ($dayId === $i) {
                    $days[$i]['class'] = 'day-current';
                }
            }

            $days[$i]['participants'] = Users::addNames($days[$i]['participants']);
            $days[$i]['playersCount'] = min(count($days[$i]['participants']), 10);
            for ($x = 0; $x < $days[$i]['playersCount']; $x++) {
                if (!empty($days[$i]['participants'][$x]['id'])) continue;
                $days[$i]['participants'][$x]['name'] = '+1';
            }
        }
        $paginator = Paginator::weekly(['weeksIds' => $weeksIds, 'currentIndex' => $weekCurrentIndexInList, 'selectedIndex' => $selectedWeekIndex]);

        $isManager = !empty($_SESSION['privilege']['status']) && in_array($_SESSION['privilege']['status'], ['manager', 'admin']);

        return compact(
            'weekId',
            'weeksIds',
            'weeksCount',
            'weekCurrentId',
            'weekCurrentIndexInList',
            'weekData',
            'dayId',
            'prevWeek',
            'nextWeek',
            'selectedWeekIndex',
            'dayNames',
            'games',
            'days',
            'isManager',
            'paginator'
        );
    }
}

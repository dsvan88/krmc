<?

namespace app\Repositories;

use app\core\Locale;
use app\core\Paginator;
use app\core\Tech;
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
            $prevWeek = Weeks::find($weeksIds[$selectedWeekIndex - 1]);
        if (isset($weeksIds[$selectedWeekIndex + 1]))
            $nextWeek = Weeks::find($weeksIds[$selectedWeekIndex + 1]);

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

            $days[$i]['class'] = 'future';
            if ($selectedWeekIndex < $weekCurrentIndexInList) {
                $days[$i]['class'] = 'expire';
            } elseif ($selectedWeekIndex === $weekCurrentIndexInList) {
                if ($dayId > $i) {
                    $days[$i]['class'] = 'expire';
                } elseif ($dayId === $i) {
                    $days[$i]['class'] = 'current';
                }
            }

            $days[$i]['participants'] = Users::addNames($days[$i]['participants']);
            $days[$i]['playersCount'] = min(count($days[$i]['participants']), 10);
            for ($x = 0; $x < $days[$i]['playersCount']; $x++) {
                if (!empty($days[$i]['participants'][$x]['id'])) continue;
                $days[$i]['participants'][$x]['name'] = '+1';
            }
        }
        $description = self::scheludeDescription($days);

        $paginator = Paginator::weekly(['weeksIds' => $weeksIds, 'currentIndex' => $weekCurrentIndexInList, 'selectedIndex' => $selectedWeekIndex]);

        $isManager = Users::checkAccess('manager');

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
            'paginator',
            'description',
        );
    }
    public static function scheludeDescription(array $days): string
    {
        if (empty($days)) return false;
        $result = Locale::phrase("Our schelude") . ':' . PHP_EOL;
        foreach ($days as $index => $day) {
            $result .= $day['date'] . ' - ' .  $day['gameName'] . ';' . PHP_EOL;
        }
        $result .= Locale::phrase("Welcome to our club") . '!';
        return preg_replace('/<.*?>/', '', $result);
    }
    public static function formWeekOG(array $data = [])
    {
        $url = Tech::getRequestProtocol() . "://{$_SERVER['SERVER_NAME']}";
        $logo = empty($data['logo']) ? '/public/images/club-logo-w-city.jpg' : $data['logo'];
        $imageSize = getimagesize($_SERVER['DOCUMENT_ROOT'] . $logo);
        $image = "$url/$logo";
        $data['title'] = Locale::phrase($data['title']);
        $result = [
            'title' => $data['title'],
            'type' => 'article',
            'url' => "$url/weeks/{$data['weekId']}",
            'image' => $image,
            'og:image:width' => $imageSize[0],
            'og:image:height' => $imageSize[1],
            'description' => $data['description'],
            'site_name' => $data['title'] . ' | ' . CLUB_NAME,
            'twitter' => [
                'card' => 'summary_large_image',
                'image' => $image,
            ],
        ];
        return $result;
    }
}

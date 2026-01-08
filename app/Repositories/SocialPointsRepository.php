<?

namespace app\Repositories;

use app\models\SocialPoints;
use app\models\Weeks;
use app\models\Days;

class SocialPointsRepository
{
    public static function applyBookingPoints(int $weekId = 0): void
    {

        if (empty($weekId)){
            $weekId = Weeks::currentId();

            if (!Weeks::checkPrevWeek($weekId)) return;

            --$weekId;
        }

        $weekData = Weeks::weekDataById($weekId);

        foreach($weekData['data'] as $num=>$day){

            if ($day['status'] !== 'set') continue;
            
            $count = count($day['participants']);

            if ($day['game'] === 'mafia' && $count < 11 || $count < 5) continue;

            array_walk(
                $day['participants'],
                function (array $user){
                    $points = empty($user['prim']) || strpos($user['prim'], '?') === false ? 5 : 3; 
                    SocialPoints::add($user['id'], $points);
                }
            );
            Days::setStatus($weekId, $num, 'finished');
        }
    }
    public static function evaluateMessage(int $userId, string $message): void
    {
        if (mb_strlen($message, 'UTF-8') < 100) return;

        SocialPoints::add($userId, 1);
    }
    public static function evaluateBooking(int $userId, string $message): void
    {
        SocialPoints::add($userId, 1);
    }
}

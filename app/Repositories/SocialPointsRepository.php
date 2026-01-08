<?

namespace app\Repositories;

use app\models\SocialPoints;
use app\models\Weeks;
use Exception;

class SocialPointsRepository
{
    public static function applyBookingPoints(): void
    {
        $weekData = Weeks::current();
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

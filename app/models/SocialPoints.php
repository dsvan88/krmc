<?

namespace app\models;

use app\core\Model;
use Exception;

class SocialPoints extends Model
{

    public static $table = 'users';

    public static function set(int $userId = 0, int $point = 0): int
    {
        if (empty($userId))
            throw new Exception(__METHOD__ . ' UserID can’t be empty.');

        $userData = Users::find($userId);

        if (empty($userData)) {
            throw new Exception(__METHOD__ . ' UserData can’t be empty.');
        }

        $privelege = $userData['privilege'];
        $privelege['points'] = $point;
        static::update(['privilege' => $privelege], ['id' => $userId]);

        return $point;
    }
    public static function get(int $userId = 0)
    {
        if (empty($userId))
            throw new Exception(__METHOD__ . ' UserID can’t be empty.');

        $userData = Users::find($userId);
        if (empty($userData['privilege']['points'])) {
            $userData['privilege']['points'] = 0;
            return static::set($userId, 0);
        }

        return $userData['privilege']['points'];
    }
    public static function add(int $userId = 0, int $point = 0)
    {
        if (empty($userId))
            throw new Exception(__METHOD__ . ' UserID can’t be empty.');

        $userData = Users::find($userId);
        if (empty($userData['privilege']['points'])) {
            return static::set($userId, $point);
        }

        if ($point < 0 && $userData['privilege']['points'] < abs($point)) {
            return false;
        }
        return $userData['privilege']['points'] += $point;
    }
    public static function minus(int $userId = 0, int $point = 0)
    {
        if (empty($userId))
            throw new Exception(__METHOD__ . ' UserID can’t be empty.');

        $userData = Users::find($userId);
        if (empty($userData['privilege']['points'])) {
            static::set($userId, 0);
            return false;
        }

        if ($userData['privilege']['points'] < $point) return false;

        return $userData['privilege']['points'] -= $point;
    }
}

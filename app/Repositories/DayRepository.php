<?

namespace app\Repositories;

use app\models\Contacts;
use app\models\Weeks;

class DayRepository
{
    public static function renamePlayer($userId, $name){
        $table = Weeks::$table;
        $search = '"name":"'.$name.'"';
        $result = Weeks::query("SELECT * FROM $table WHERE data ILIKE ?", [$search], 'Assoc');
        var_dump($result);
    }
}
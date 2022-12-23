<?

use app\core\Locale;
use app\models\Days;
use app\models\Weeks;

$weekData = Weeks::weekDataByTime();

$currentDayNum = Days::current();

$message = Days::getFullDescription($weekData, $currentDayNum);

if ($message === '') {
    $message = Locale::phrase('{{ Tg_Command_Games_Not_Set }}'); //В ближайшее время, игры не запланированны!\nОбратитесь к нам позднее.\n
}
else {
    $result = true;
}
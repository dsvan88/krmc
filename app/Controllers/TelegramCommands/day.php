<?

use app\core\Locale;
use app\models\Days;
use app\models\Weeks;

$weekId = Weeks::currentId();
$currentDayNum = Days::current();

if (isset($arguments[0])) {
    $dayNum = self::parseDayNum($arguments[0], $currentDayNum);
    if ($dayNum < $currentDayNum)
        $weekId++;
} else {
    $dayNum = $currentDayNum;
}

$weekData = Weeks::weekDataById($weekId);
$message = Days::getFullDescription($weekData, $dayNum);

if ($message === '') {
    $message = Locale::phrase('{{ Tg_Command_Games_Not_Set }}'); //В ближайшее время, игры не запланированны!\nОбратитесь к нам позднее.\n
}
else {
    $result = true;
}
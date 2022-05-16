<?php
if (!isset($_POST['message'])) {
    require_once $_SERVER['DOCUMENT_ROOT'] . '/engine/class.action.php';
    $GLOBALS['CommonActionObject'] = new Action;
}
require_once $_SERVER['DOCUMENT_ROOT'] . '/engine/class.weeks.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/engine/class.news.php';

$weeks = new Weeks;
$news = new News;

$weeksData = $weeks->getNearWeeksDataByTime();

$output['message'] = '';
foreach ($weeksData as $weekData) {

    for ($i = 0; $i < 7; $i++) {

        if (!isset($weekData['data'][$i])) {
            continue;
        }
        $dayDescription = $weeks->getDayFullDescription($weekData, $i);
        if ($dayDescription !== '')
            $output['message'] .=  $dayDescription .
                "___________________________\r\n";
    }
}

if ($output['message'] === '') {
    $output['message'] = "В ближайшее время, игры не запланированны!\r\nОбратитесь к нам позднее.\r\n";
}

$promoData = $news->newsGetAllByType('tg-promo');
if ($promoData) {
    if ($promoData[0]['title'] !== '') {
        $promoData = $promoData[0];
        $message = "<u><b>$promoData[title]</b></u>\r\n<i>$promoData[subtitle]</i>\r\n\r\n";
        $message .= preg_replace('/(<((?!b|u|s|strong|em|i|\/b|\/u|\/s|\/strong|\/em|\/i)[^>]+)>)/i', '', str_replace(['<br />', '<br/>', '<br>', '</p>'], "\r\n", trim($promoData['html'])));
        $output['message'] .= "\r\n$message";
    }
}

<?php

use app\core\Locale;
use app\models\Days;
use app\models\News;
use app\models\Weeks;

$weeksData = Weeks::nearWeeksDataByTime();

$message = '';
if (!empty($weeksData)) {
    foreach ($weeksData as $weekData) {

        for ($i = 0; $i < 7; $i++) {

            if (!isset($weekData['data'][$i]) || in_array($weekData['data'][$i]['status'], ['', 'recalled'])) {
                continue;
            }
            $dayDescription = Days::getFullDescription($weekData, $i);
            if ($dayDescription !== '')
                $message .=  $dayDescription .
                    "___________________________\n";
        }
    }
} else {
    if ($message === '') {
        $message = Locale::phrase('{{ Tg_Command_Games_Not_Set }}');
    }
    else {
        $result = true;
    }
}

$promoData = News::getPromoData();
if ($promoData) {
    if ($promoData['title'] !== '') {
        $message .= "<u><b>$promoData[title]</b></u>\n<i>$promoData[subtitle]</i>\n\n";
        $message .= preg_replace('/(<((?!b|u|s|strong|em|i|\/b|\/u|\/s|\/strong|\/em|\/i)[^>]+)>)/i', '', str_replace(['<br />', '<br/>', '<br>', '</p>'], "\n", trim($promoData['html'])));
    }
}
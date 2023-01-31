<?php

use app\models\Days;
use app\models\Weeks;

$dayName = '';
$dayNum = -1;
$currentDayNum = Days::current();
$message = "Не можу очистити цей день.😥\nВін й досі запланований! Я можу очистити лише дні, по яким стався \"відбій\"";

if (!empty($arguments)) {
    if (preg_match('/^(пн|пон|вт|ср|чт|чет|пт|пят|сб|суб|вс|вос|сг|сег|зав)/', mb_strtolower($arguments[0], 'UTF-8'), $daysPattern) === 1) {
        $dayName = $daysPattern[0];
    }
}
if ($dayName === '')
    $dayName = 'сг';

$dayNum = self::parseDayNum($dayName, $currentDayNum);

$weekId = Weeks::currentId();

if ($dayNum < $currentDayNum) {
    ++$weekId;
}

$result = Days::clear($weekId, $dayNum);

if ($result){
    $message = 'Налаштування обраного дня очищені';
}

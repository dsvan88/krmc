<?php

use app\models\Days;
use app\models\Weeks;

if (empty($arguments)) {
    $message = '{{ Tg_Command_Without_Arguments}}';
    goto commandEnd;
}

$requestData = self::parseArguments($arguments);

if (!isset($requestData['nonames']) && $requestData['userId'] < 2) {
    $message = '{{ Tg_Command_User_Not_Found }}';
    goto commandEnd;
}

$weekId = Weeks::currentId();
if ($requestData['dayNum'] < 0) {
    $requestData['dayNum'] = $requestData['currentDay'];
} else {
    if ($requestData['currentDay'] > $requestData['dayNum']) {
        ++$weekId;
    }
}
$weekData = Weeks::weekDataById($weekId);

$participantId = $slot = -1;

if ($weekData['data'][$requestData['dayNum']]['status'] !== 'set') {
    if (!isset($weekData['data'][$requestData['dayNum']]['game']))
        $weekData['data'][$requestData['dayNum']] = Days::$dayDataDefault;

    if ($requestData['arrive'] !== '')
        $weekData['data'][$requestData['dayNum']]['time'] = $requestData['arrive'];
    $requestData['arrive'] = '';
    $weekData['data'][$requestData['dayNum']]['status'] = 'set';
}

if (isset($requestData['nonames'])) {
    $slot = count($weekData['data'][$requestData['dayNum']]['participants']);
} else {
    foreach ($weekData['data'][$requestData['dayNum']]['participants'] as $index => $userData) {
        if ($userData['id'] === $requestData['userId']) {
            if ($requestData['arrive'] !== '' && $requestData['arrive'] !== $userData['arrive']) {
                $slot = $index;
                break;
            }
            $participantId = $index;
            break;
        }
    }
}
$newDayData = $weekData['data'][$requestData['dayNum']];
if ($requestData['method'] === '+') {
    if ($participantId !== -1) {
        $message = '{{ Tg_Command_User_Already_Booked }}';
        goto commandEnd;
    }
    if (isset($requestData['nonames'])) {
        $newDayData = Days::addNonamesToDayData($newDayData, $slot, $requestData['nonames'], $requestData['prim']);
    } else {
        $newDayData = Days::addParticipantToDayData($newDayData, $slot, $requestData);
    }
} else {
    if (isset($requestData['nonames'])) {
        $newDayData = Days::removeNonamesFromDayData($newDayData, $requestData['nonames']);
    } else {
        if ($participantId === -1) {
            $message = '{{ Tg_Command_User_Not_Booked }}';
            goto commandEnd;
        }
        unset($newDayData['participants'][$participantId]);
        $newDayData['participants'] = array_values($newDayData['participants']);
    }
}

$result = Days::setDayData($weekId, $requestData['dayNum'], $newDayData);

if (!$result) {
    $message = json_encode($newData, JSON_UNESCAPED_UNICODE);
    goto commandEnd;
}

$weekData['data'][$requestData['dayNum']] = $newDayData;

$message = Days::getFullDescription($weekData, $requestData['dayNum']);

commandEnd:
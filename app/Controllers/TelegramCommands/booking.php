<?php

use app\models\Days;
use app\models\Weeks;

$requestData = self::parseArguments($arguments);
$requestData['userId'] = self::$requester['id'];
$requestData['userName'] = self::$requester['name'];
$requestData['userStatus'] = self::$requester['privilege']['status'];

$weekId = Weeks::currentId();
if ($requestData['currentDay'] > $requestData['dayNum']) {
    ++$weekId;
}

$weekData = Weeks::weekDataById($weekId);

$participantId = $slot = -1;
if ($weekData['data'][$requestData['dayNum']]['status'] !== 'set') {
    if (!in_array($requestData['userStatus'], ['manager', 'admin'])){
        $message = '{{ Tg_Gameday_Not_Set }}';
        goto commandEnd;
    }
    if (!isset($weekData['data'][$requestData['dayNum']]['game']))
        $weekData['data'][$requestData['dayNum']] = Days::$dayDataDefault;

    if ($requestData['arrive'] !== '')
        $weekData['data'][$requestData['dayNum']]['time'] = $requestData['arrive'];
        
    $requestData['arrive'] = '';
    $weekData['data'][$requestData['dayNum']]['status'] = 'set';
}

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

$newDayData = $weekData['data'][$requestData['dayNum']];
if ($requestData['method'] === '+') {
    if ($participantId !== -1) {
        $message = '{{ Tg_Command_Requester_Already_Booked }}';
        goto commandEnd;
    }
    $newDayData = Days::addParticipantToDayData($newDayData, $slot, $requestData);
    $reactions = [
        '🤩',
        '🥰',
        '🥳',
        '😻',
    ];
} else {
    if ($participantId === -1) {
        $message = '{{ Tg_Command_Requester_Not_Booked }}';
        goto commandEnd;
    }
    unset($newDayData['participants'][$participantId]);
    $newDayData['participants'] = array_values($newDayData['participants']);
    $reactions = [
        '😥',
        '😭',
        '😱',
        '😿',
    ];
}

$result = Days::setDayData($weekId, $requestData['dayNum'], $newDayData);

$botReaction = '';
if (isset($reactions)) {
    $botReaction = $reactions[mt_rand(0, count($reactions) - 1)];
}
if (!$result) {
    $message = json_encode($newData, JSON_UNESCAPED_UNICODE);
    $preMessage = $botReaction;
    goto commandEnd;
}

$weekData['data'][$requestData['dayNum']] = $newDayData;

$result = true;
$message = Days::getFullDescription($weekData, $requestData['dayNum']);
$preMessage = $botReaction;

commandEnd:
<?php

use app\core\Locale;
use app\models\Users;

$usersList = Users::getList();
$message = '';
$x = 0;
for ($i = 0; $i < count($usersList); $i++) {
    if ($usersList[$i]['name'] === '') continue;
    $message .= (++$x) . ". <b>{$usersList[$i]['name']}</b>";
    if ($usersList[$i]['contacts']['telegram'] !== '') {
        $message .= " (@{$usersList[$i]['contacts']['telegram']})";
    }
    if ($usersList[$i]['contacts']['telegramid'] !== '') {
        $message .= ' ✅';
    }
    $message .= "\n";
}

$result = true;
$message .= "______________________________\n✅ - " . Locale::phrase('{{ Tg_User_With_Telegramid }}');
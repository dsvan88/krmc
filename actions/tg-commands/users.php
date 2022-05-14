<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/engine/functions.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/engine/class.users.php';

$users = new Users;

$telegram = isset($_POST['message']['from']['username']) ? $_POST['message']['from']['username'] : '';

$telegramId = $_POST['message']['from']['id'];

$userData = $users->usersGetData(['name', 'telegram']);
$output['message'] = '';
for ($i = 0; $i < count($userData); $i++) {
    $output['message'] .= ($i + 1) . " <b>{$userData[$i]['name']}</b>!";
    if ($userData[$i]['telegram'] !== '')
        $output['message'] .= "(@{$userData[$i]['telegram']})";
    $output['message'] .= "\n";
}

<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/engine/functions.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/engine/class.users.php';

$users = new Users;

$telegram = isset($_POST['message']['from']['username']) ? $_POST['message']['from']['username'] : '';

$telegramId = $_POST['message']['from']['id'];

$userData = $users->usersGetData(['id', 'name', 'telegram', 'status'], ['telegramid' => $telegramId]);

if (!isset($userData['id'])) {
    $output['message'] = "Извините! Не узнаю вас в гриме:(\r\nСкажите Ваш псевдоним в игре, что бы я вас запомнил! Напишите: /nick Ваш псевдоним (кириллицей)";
} elseif (!in_array($userData['status'], ['manager', 'admin'], true)) {
    $output['message'] = "Команда не знайдена";
} else {
    $usersList = $users->usersGetData(['name', 'telegram'], '', 0);
    for ($i = 0; $i < count($usersList); $i++) {
        if ($usersList[$i]['name'] === '') continue;
        $output['message'] .= ($i + 1) . ". <b>{$usersList[$i]['name']}</b>";
        if ($usersList[$i]['telegram'] !== '')
            $output['message'] .= " (@{$usersList[$i]['telegram']})";
        $output['message'] .= "\n";
    }
}

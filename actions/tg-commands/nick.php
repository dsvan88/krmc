<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/engine/functions.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/engine/class.users.php';

$users = new Users;

$telegram = isset($_POST['message']['from']['username']) ? $_POST['message']['from']['username'] : '';

$telegramId = $_POST['message']['from']['id'];

$userData = $users->usersGetData(['id', 'name', 'telegram'], ['telegramid' => $telegramId]);

if (isset($userData['name']) && $userData['name'] !== 'tmp_telegram_user') {

    if ($telegram !== '' && $userData['telegram'] !== $telegram) {
        $users->userUpdateData(['telegram' => $telegram], ['id' => $userExistsData['id']]);
    }

    $output['message'] = "Я уже запомнил Вас под именем <b>$userData[name]</b>!\r\nИсправления лишь через администраторов:(!";
} else {

    $username = '';
    foreach ($args as $string) {
        $username .= mb_ucfirst($string) . ' ';
    }
    $username = mb_substr($username, 0, -1, 'UTF-8');

    if (preg_match('/([^а-яА-ЯрРсСтТуУфФчЧхХШшЩщЪъЫыЬьЭэЮюЄєІіЇїҐґ .])/', $username) === 1) {
        $output['message'] = "Не верный формат псевдонима!\nПожалуйста, используйте только <b>кириллицу</b> и <b>пробелы</b> в Вашем псевдониме!";
    } elseif (mb_strlen(trim($username), 'UTF-8') < 2) {
        $output['message'] = "Слишком короткий псевдоним!\nПожалуйста, используйте, минимум <b>2</b> символа, что бы люди смогли Вас узнать!";
    } else {
        $userId = $users->userGetId($username);
        $userExistsData = $users->usersGetData(['id', 'name', 'telegramid'], ['id' => $userId]);
        if (isset($userExistsData['id'])) {
            if ($userExistsData['telegramid'] !== '') {
                if ($userExistsData['telegramid'] !== $telegramId) {
                    $output['message'] = "Извините, но псевдонимом <b>$username</b> - уже <b>зарезервирован</b> другим участником группы.\nЕсли это Ваш псевдоним - обратитесь к администраторам!";
                } else {
                    $output['message'] = 'Ваша информация - уже успешно сохранена!';
                }
            } else {
                $users->userUpdateData(['telegram' => $telegram, 'telegramid' => $telegramId], ['id' => $userExistsData['id']]);
                $output['message'] = "Так... записываем Вас под именем <b>$username</b>. Верно?\nПриятно познакомиться!\n\nЕсли допустили ошибку - не переживайте, сообщите об этом администратору и он быстренько это исправит😏";
            }
            if (isset($userData['id'])) {
                $users->userDelete($userData['id']);
            }
        } else {
            if (isset($userData['id'])) {
                $users->userUpdateData(['name' => $username], ['id' => $userData['id']]);
            } else {
                $users->usersSaveNameFromTelegram(['name' => $username, 'telegram' => $telegram, 'telegramid' => $telegramId]);
            }
            $output['message'] = "Так... записываем Вас под именем <b>$username</b>. Верно?\nПриятно познакомиться!\n\nЕсли допустили ошибку - не переживайте, сообщите об этом администратору и он быстренько это исправит😏";
        }
    }
}

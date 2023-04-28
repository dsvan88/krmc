<?php

use app\core\Locale;
use app\models\Contacts;
use app\models\Users;

if (!empty(self::$requester)) {
    $message = ['string' => '{{ Tg_Command_Name_Already_Set }}', 'vars' => [self::$requester['name']]];
    goto commandEnd;
}

$username = '';
foreach ($arguments as $string) {
    $username .= Locale::mb_ucfirst($string) . ' ';
}
$username = mb_substr($username, 0, -1, 'UTF-8');

if (mb_strlen(trim($username), 'UTF-8') < 2) {
    $message = '{{ Tg_Command_Name_Too_Short }}';
    goto commandEnd;
}
if (preg_match('/([^а-яА-ЯрРсСтТуУфФчЧхХШшЩщЪъЫыЬьЭэЮюЄєІіЇїҐґ .0-9])/', $username) === 1) {
    $message = '{{ Tg_Command_Name_Wrong_Format }}';
    goto commandEnd;
}

$telegramId = self::$message['message']['from']['id'];
$telegram = self::$message['message']['from']['username'];

$userExistsData = Users::getDataByName($username);

if (empty($userExistsData['id'])) {
    $id = Users::add($username);
    Users::edit([
        'contacts' => [
            'telegram' => $telegram, 
            'telegramid' => $telegramId
        ]
    ],
    ['id' => $id]);

    Contacts::add([
        'user_id' => $id,
        'type' => 'telegramid',
        'contact' => $telegramId,
    ]);
    if (!empty($telegram)){
        Contacts::add([
            'user_id' => $id,
            'type' => 'telegram',
            'contact' => $telegram,
        ]);
    }
    
    $result = true;
    $message = ['string' => '{{ Tg_Command_Name_Save_Success }}', 'vars' => [$username]];
    goto commandEnd;
}

if ($userExistsData['contacts']['telegramid'] !== '') {
    if ($userExistsData['contacts']['telegramid'] !== $telegramId) {
        $message = ['string' => '{{ Tg_Command_Name_Already_Set_By_Other }}', 'vars' => [$username]];
        goto commandEnd;
    }
    $message = '{{ Tg_Command_Name_You_Have_One }}';
    goto commandEnd;
}

$userExistsData['contacts']['telegramid'] = $telegramId;
$userExistsData['contacts']['telegram'] = $telegram;
$userExistsData['contacts']['email'] = isset($userExistsData['contacts']['email']) ? $userExistsData['contacts']['email'] : '';

Users::edit(['contacts' => $userExistsData['contacts']], ['id' => $userExistsData['id']]);

$oldTgContacts = Contacts::findBy('user_id', $userExistsData['id']);
if (empty($oldTgContacts)){
    Contacts::add([
        'user_id' => $id,
        'type' => 'telegramid',
        'contact' => $telegramId,
    ]);
    Contacts::add([
        'user_id' => $id,
        'type' => 'telegram',
        'contact' => $telegram,
    ]);
}

$message = ['string' => '{{ Tg_Command_Name_Save_Success }}', 'vars' => [$username]];

commandEnd:
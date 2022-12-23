<?php

use app\core\Locale;
use app\models\Users;

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

if (Users::getId($username) > 0) {
    $message = ['string' => '{{ Tg_Command_New_User_Already_Set }}', 'vars' => [$username]];
    goto commandEnd;
}

Users::add($username);

$result = true;
$message = ['string' => '{{ Tg_Command_New_User_Save_Success }}', 'vars' => [$username]];

commandEnd:
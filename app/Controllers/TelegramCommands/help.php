<?php
use app\core\Locale;

$message = Locale::phrase('{{ Tg_Command_Help }}');

if (self::$message['message']['chat']['type'] === 'private' && in_array(self::$requester['privilege']['status'], ['manager', 'admin'])) {
    $message .= Locale::phrase('{{ Tg_Command_Help_Admin }}');
}
<?php

namespace app\core;

use app\models\Settings;

class TelegramInfoBot extends TelegramBot
{
    public static $webhookLink = 'api/telegram/info-webhook';

    private static function getAuthData()
    {
        return Settings::getInfoBotToken();
    }
}

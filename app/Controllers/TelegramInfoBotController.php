<?php

namespace app\Controllers;

class TelegramInfoBotController extends TelegramBotController
{
    public static $guestCommands = ['help', 'booking', 'nick', 'nickRelink', 'week', 'day', 'today', 'pending'];
    public static $CommandNamespace = '\\app\\Services\\TelegramInfoCommands';
    public static $AnswerNamespace = '\\app\\Services\\TelegramInfoCbAnswers';
}

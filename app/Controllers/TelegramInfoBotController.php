<?php

namespace app\Controllers;

class TelegramInfoBotController extends TelegramBotController
{
    public static $guestCommands = ['help', 'booking', 'nick', 'nickRelink', 'week', 'day', 'today', 'pending'];
    public static $CommandNamespace = '\\app\\Repositories\\TelegramInfoCommands';
    public static $AnswerNamespace = '\\app\\Repositories\\TelegramCbInfoAnswers';
}

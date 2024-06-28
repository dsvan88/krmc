<?php

namespace app\Controllers;

use app\core\Controller;
use app\core\View;

class TelegramAppController extends Controller
{
    private static $techTelegramId = null;
    private static $mainGroupTelegramId = null;

    public static $requester = [];
    public static $message = [];
    public static $chatId = null;
    public static $command = '';
    public static $commandArguments = [];
    public static $guestCommands = ['help', 'nick', 'week', 'day', 'today'];
    public static $CommandNamespace = '\\app\\Repositories\\TelegramCommands';

    public static $resultMessage = '';
    public static $resultPreMessage = '';

    public static function before()
    {
        View::$layout = 'telegram';
    }
    public static function homeAction()
    {
        $vars = [
            'title' => 'Telegram App',
            'scripts' => [
                'telegram/main.js',
            ],
        ];
        View::$route['vars'] = array_merge(View::$route['vars'], $vars);
        View::render();
    }
}

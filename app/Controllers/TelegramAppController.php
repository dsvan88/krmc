<?php

namespace app\Controllers;

use app\core\Controller;
use app\core\View;

class TelegramAppController extends Controller
{
    private static $techTelegramId = null;
    private static $mainGroupTelegramId = null;

    public static function before()
    {
        View::$layout = 'telegram';
    }
    public static function authAction()
    {
        $vars = [
            'title' => 'Telegram App',
            'scripts' => [
                'telegram/auth.js',
            ],
        ];
        View::$route['vars'] = array_merge(View::$route['vars'], $vars);
        View::render();
    }
}

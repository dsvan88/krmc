<?php

namespace app\Controllers;

use app\core\Controller;
use app\core\View;

class TelegramAppController extends Controller
{
    public static function before()
    {
        View::$layout = 'telegram';
        return true;
    }
    public static function authAction()
    {
        View::$layout = 'empty';
        $vars = [
            'title' => 'Telegram App',
            'scripts' => [
                'telegram/auth.js',
            ],
        ];
        View::$route['vars'] = array_merge(View::$route['vars'], $vars);
        return View::render();
    }
}

<?php

namespace app\core;

use app\models\Settings;
use app\Repositories\TechRepository;
use Throwable;

class Router
{
    protected static $routes = [];
    protected static $params = [];
    protected static $accessLevels = ['all' => 0, 'user' => 1, 'trusted' => 2, 'manager' => 3, 'admin' => 4, 'root' => 5];
    public static function before()
    {
        if (!empty(self::$routes)) {
            error_log('$routes is not empty');
            return true;
        }
        $path = self::getRoutesPath();

        $arr = require $path;
        foreach ($arr as $key => $val) {
            self::add($key, $val);
        }
    }
    public static function add($route, $params)
    {
        preg_match_all('/{([^\/]+)}/', $route, $matchAll);
        $route = preg_replace(['/\//', '/{([^\/]+)}/'], ['\\/', '([^\/]+)'], $route);
        $route = "/^$route$/";
        $params['varNames'] = $matchAll[1];
        self::$routes[$route] = $params;
    }
    public static function isMatch()
    {
        $offset = strpos($_SERVER['REQUEST_URI'], '?');
        $url = trim(empty($offset) ? $_SERVER['REQUEST_URI'] : substr($_SERVER['REQUEST_URI'], 0, $offset), '/');
        foreach (self::$routes as $route => $params) {
            if (!preg_match($route, $url, $match)) continue;

            if (isset($params['redirect'])) {
                return View::redirect('/' . $params['redirect']);
            }
            if ($params['access']['category'] !== 'all') {
                if (!self::checkAccessLevel($params)) {
                    if (isset($params['access']['redirect'])) {
                        return View::redirect('/' . $params['access']['redirect']);
                    }
                    return View::redirect('/');
                }
            }

            $params['url'] = $url;
            $params['vars'] = [];
            $count = count($match);
            for ($i = 1; $i < $count; $i++) {
                $params['vars'][$params['varNames'][$i - 1]] = $match[$i];
            }
            self::$params = $params;

            return true;
        }
        return false;
    }
    public static function run()
    {
        self::before();
        if (empty($_SESION['id']) && strpos($_SERVER['REQUEST_URI'], 'api/') === false) {
            self::savePath();
        }
        if (self::isMatch()) {
            $path = 'app\Controllers\\' . ucfirst(self::$params['controller']) . 'Controller';
            if (class_exists($path)) {
                $action = self::$params['action'] . 'Action';
                $message = '';
                if (method_exists($path, $action)) {
                    try {
                        $controller = new $path(self::$params);
                        if ($controller::$ready) {
                            $controller->$action();
                        }
                    } catch (Throwable $error) {
                        $message = $error->__toString();
                        if (APP_LOC === 'local') {
                            error_log($message);
                            return false;
                        }
                    }
                    TechRepository::scheduleBackup();
                    if (!empty($_SESSION['debug'])) {
                        $message .= PHP_EOL . 'DEBUG:' . PHP_EOL;
                        $message .= PHP_EOL . implode(PHP_EOL, $_SESSION['debug']);
                        unset($_SESSION['debug']);
                    }
                    if (!empty($message)) {
                        return Sender::message(Settings::getTechTelegramId(), $message);
                    }
                } else {
                    return View::errorCode(404, ['message' => "Action $action isn’t found in Controller $path!"]);
                }
            } else {
                return View::errorCode(404, ['message' => "Controller $path isn't found!"]);
            }
        } else {
            return View::errorCode(404, ['message' => 'Route isn’t found!']);
        }
    }
    public static function checkAccessLevel($params)
    {
        if (empty($_SESSION['privilege'])) return false;

        if (self::$accessLevels[$params['access']['category']] > self::$accessLevels[$_SESSION['privilege']['status']]) {
            return false;
        }
        return true;
    }
    public static function savePath(): void
    {
        $url = trim($_SERVER['REQUEST_URI'], '/');
        if (empty($url)) return;

        $_SESSION['path'] = '/' . $url;
    }
    public static function getRoutesPath(): string
    {
        $default = 'app/config/routes/http.php';
        $uri = $_SERVER['REQUEST_URI'];

        if ($uri[0] === '/')
            $uri = mb_substr($uri, 1, null, 'UTF-8');

        if (empty($uri) || strlen($uri) < 3)
            return $default;

        $end = mb_strpos($uri, '/', 1, 'UTF-8');

        $method = mb_substr($uri, 0, $end, 'UTF-8');

        $path = "app/config/routes/$method.php";

        return file_exists("{$_SERVER['DOCUMENT_ROOT']}/$path") ? $path : $default;
    }
}

<?php

namespace  app\core;

class Controller
{

    public static $route;
    public static $view;
    public static $model;
    public function __construct($route)
    {
        self::$route = $route;
        View::set($route);
        self::$model = self::loadModal($route['controller']);
        self::before();
    }
    public static function set($route)
    {
        self::$route = $route;
        View::set($route);
        self::$model = self::loadModal($route['controller']);
        self::before();
    }
    public static function before()
    {
        return false;
    }
    public static function loadModal($name)
    {
        $path = 'app\models\\' . ucfirst($name);
        if (class_exists($path)) {
            return new $path;
        }
    }
}

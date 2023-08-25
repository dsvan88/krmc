<?php

namespace  app\core;

class Controller
{

    public static $route;
    public static $view;
    public static $model;
    public function __construct($route)
    {
        self::set($route);
    }
    public static function set($route)
    {
        self::$route = $route;
        View::set($route);
        self::$model = self::loadModel($route['controller']);
        static::before();
    }
    public static function before()
    {
        return false;
    }
    public static function loadModel($name)
    {
        $path = 'app/models/' . ucfirst($name);
        if (class_exists($path)) {
            return new $path;
        }
    }
}

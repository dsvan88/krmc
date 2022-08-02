<?php

namespace  app\core;

class Tech
{
    public static function getClientIP()
    {
        $keys = ['HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR', 'HTTP_CLIENT_IP'];
        foreach ($keys as $key) {
            if (array_key_exists($key, $_SERVER)) {
                return $_SERVER[$key];
            }
        }
        return '';
    }
}

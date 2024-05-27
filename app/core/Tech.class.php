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
    public static function dump($data)
    {
        echo '<pre>';
        var_dump($data);
        echo '</pre>';
    }
    public static function getCode(string $seed): string
    {
        $code = '';

        do {
            $code = preg_replace('/[^0-9]/', '', sha1($seed . microtime()));
        } while (strlen($code) < 5);

        if (strlen($code) < 8)
            $code = str_pad($code, 8, '0');
        else
            $code = substr($code, 0, 8);

        return $code;
    }
    public static function modifyAssocArray(array &$array): void
    {
        if (empty($array)) return;

        $result = [];
        array_walk($array, function ($element) use (&$result) {
            if (empty($element['id'])) return false;
            $result[$element['id']] = $element;
        });
        $array = $result;
    }
}

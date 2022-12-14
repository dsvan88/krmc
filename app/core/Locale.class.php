<?php

namespace app\core;

class Locale
{
    public static $langCode = 'uk';
    public static $dictionary = [];
    public static function apply($vars)
    {
        self::loadDictionary();
        foreach ($vars as $key => $value) {
            if (is_array($value)) {
                if (isset($value['string']) && isset($value['vars'])) {
                    $vars[$key] = sprintf(self::$dictionary[$value['string']], ...$value['vars']);
                    continue;
                }
                $vars[$key] = self::apply($value);
            } elseif (is_string($vars[$key]) && substr($vars[$key], 0, 2) === '{{' && isset(self::$dictionary[$value])) {
                $vars[$key] = self::$dictionary[$value];
            }
        }
        return $vars;
    }
    public static function loadDictionary()
    {
        if (empty(self::$dictionary)) {
            self::$dictionary = require $_SERVER['DOCUMENT_ROOT'] . '/app/locale/' . self::$langCode . '.php';
        }
    }
    public static function change($code)
    {
        self::$langCode = $code;
        self::$dictionary = require "{$_SERVER['DOCUMENT_ROOT']}/app/locale/$code.php";
    }
    public static function applySingle($key)
    {
        self::loadDictionary();

        if (is_array($key)) {
            if (isset($key['string']) && isset($key['vars']) && isset(self::$dictionary[$key['string']])) {
                return sprintf(self::$dictionary[$key['string']], ...$key['vars']);
            } else return $key;
        }
        if (isset(self::$dictionary[$key]))
            return self::$dictionary[$key];

        return $key;
    }
    public static function mb_ucfirst($string, $encoding = 'UTF8')
    {
        if (strpos($string, ' ') !== false) {
            $words = explode(' ', $string);
            $newString = '';
            foreach ($words as $word) {
                $newString .= self::mb_ucfirst($word, $encoding) . ' ';
            }
            return mb_substr($newString, 0, -1, $encoding);
        }
        return mb_strtoupper(mb_substr($string, 0, 1, $encoding), $encoding) . mb_substr($string, 1, mb_strlen($string, $encoding), $encoding);
    }
    public static function translitization($string)
    {
        $string = (string) $string;
        preg_match_all('([-0-9??-????-?????????????????????????????????????????????????????????????????????????? ]+)', $string, $matches);
        $string = implode('', $matches[0]);
        $string = trim($string);
        $string = function_exists('mb_strtolower') ? mb_strtolower($string, 'UTF-8') : strtolower($string);
        $string = strtr($string, array('??' => 'a', '??' => 'b', '??' => 'v', '??' => 'g', '??' => 'd', '??' => 'e', '??' => 'e', '??' => 'j', '??' => 'z', '??' => 'i', '??' => 'y', '??' => 'k', '??' => 'l', '??' => 'm', '??' => 'n', '??' => 'o', '??' => 'p', '??' => 'r', '??' => 's', '??' => 't', '??' => 'u', '??' => 'f', '??' => 'h', '??' => 'c', '??' => 'ch', '??' => 'sh', '??' => 'shch', '??' => 'y', '??' => 'e', '??' => 'yu', '??' => 'ya', '??' => '', '??' => '', '??' => 'e', '??' => 'i', '??' => 'i', '??' => 'g', ' ' => '_'));
        return $string;
    }
    public static function findUnsetText()
    {
        $directory = $_SERVER['DOCUMENT_ROOT'] . '/app/Controllers';
        $files = scandir($directory);
        $values = [];
        foreach ($files as $file) {
            if (strpos($file, '.php') === false) continue;
            $fullpath = "$directory/$file";
            $content = file_get_contents($fullpath);
            preg_match_all('/\{\{\s{1}[0-9a-zA-Z_]+\s{1}\}\}/', $content, $matches);
            $values = array_merge($values, self::apply($matches[0]));
        }
        $result = [];
        foreach ($values as $value) {
            if (strpos($value, '{{ ') === false) continue;
            $result[] = $value;
        }
        return $result;
    }
}

<?php

namespace app\core;

class Locale
{
    public static $langCode = 'uk';
    public static $langCodes = ['uk', 'ru', 'en'];
    public static $dictionary = [];
    public static function apply($vars)
    {
        self::loadDictionary();
        foreach ($vars as $key => $value) {
            if (is_array($value)) {
                if (isset($value['string']) && isset($value['vars'])) {
                    if (empty(self::$dictionary[$value['string']])) {
                        $vars[$key] = sprintf($value['string'], ...$value['vars']);
                        continue;
                    }
                    $vars[$key] = sprintf(self::$dictionary[$value['string']], ...$value['vars']);
                    continue;
                }
                $vars[$key] = self::apply($value);
            } elseif (is_string($vars[$key]) && isset(self::$dictionary[$value])) {
                $vars[$key] = self::$dictionary[$value];
            }
        }
        return $vars;
    }
    public static function loadDictionary()
    {
        if (!empty(self::$dictionary)) return false;
        self::$dictionary = require $_SERVER['DOCUMENT_ROOT'] . '/app/locale/' . self::$langCode . '.php';
    }
    public static function change($code)
    {
        self::$langCode = $code;
        self::$dictionary = require "{$_SERVER['DOCUMENT_ROOT']}/app/locale/$code.php";
    }
    public static function phrase($key)
    {
        self::loadDictionary();

        if (is_array($key)) {
            if (isset($key['string']) && isset($key['vars'])) {
                if (empty(self::$dictionary[$key['string']]))
                    return sprintf($key['string'], ...$key['vars']);
                return sprintf(self::$dictionary[$key['string']], ...$key['vars']);
            }
            return $key;
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
    public static function translitization(string $string)
    {
        $string = function_exists('mb_strtolower') ? mb_strtolower($string, 'UTF-8') : strtolower($string);
        /*         preg_match_all('/([-0-9а-яА-ЯрРсСтТуУфФчЧхХШшЩщЪъЫыЬьЭэЮюЄєІіЇїҐґa-z ]+)/', $string, $matches);
        $string = implode('', $matches[0]); */
        $string = preg_replace('/[^-0-9а-яА-ЯрРсСтТуУфФчЧхХШшЩщЪъЫыЬьЭэЮюЄєІіЇїҐґa-z ]/', '', $string);
        $string = trim($string);
        $string = strtr($string, array('а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e', 'ё' => 'e', 'ж' => 'j', 'з' => 'z', 'и' => 'i', 'й' => 'y', 'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n', 'о' => 'o', 'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't', 'у' => 'u', 'ф' => 'f', 'х' => 'h', 'ц' => 'c', 'ч' => 'ch', 'ш' => 'sh', 'щ' => 'shch', 'ы' => 'y', 'э' => 'e', 'ю' => 'yu', 'я' => 'ya', 'ъ' => '', 'ь' => '', 'є' => 'e', 'і' => 'i', 'ї' => 'i', 'ґ' => 'g', ' ' => '_'));
        return $string;
    }
    public static function camelize(string $string)
    {
        if (!strpos($string, '-')) return $string;

        $array = explode('-', $string);
        $result = $array[0];
        $count = count($array);

        for ($x = 1; $x < $count; ++$x) {
            $result .= self::mb_ucfirst($array[$x]);
        }

        return $result;
    }
    public static function setLocale()
    {
        $langCode = '';

        if (!empty($_COOKIE['lang']))
            $langCode = $_COOKIE['lang'];

        if (!empty($_GET['lang']))
            $langCode = $_GET['lang'];

        if (!in_array($langCode, self::$langCodes, true)) return false;

        self::$langCode = $langCode;

        if (empty($_COOKIE['lang']) || $_COOKIE['lang'] !== self::$langCode) {
            setcookie('lang', $langCode, $_SERVER['REQUEST_TIME'] + CFG_MAX_SESSION_AGE, '/');
            $_COOKIE['lang'] = $langCode;
        }
        return true;
    }
    public static function getLocaledLinks()
    {

        $offset = strpos($_SERVER['REQUEST_URI'], '?');
        $url = trim(empty($offset) ? $_SERVER['REQUEST_URI'] : substr($_SERVER['REQUEST_URI'], 0, $offset), '/') . '/';

        $https = empty($_SERVER['HTTP_X_FORWARDED_PROTO']) ? 'https' : $_SERVER['HTTP_X_FORWARDED_PROTO'];
        $url = "$https://{$_SERVER['SERVER_NAME']}/" . ($url === '/' ? '' : $url);

        $codes = self::$langCodes;
        $count = count($codes);
        $result = '';
        for ($x = 0; $x < $count; $x++) {
            $result .= "<link rel='alternate' href='$url?lang={$codes[$x]}' hreflang='{$codes[$x]}'>" . PHP_EOL;
        }
        $result .= "<link rel='alternate' href='$url' hreflang='x-default'>" . PHP_EOL;
        return $result;
    }
    public static function decamelize(string $string)
    {
        return strtolower(preg_replace('/([A-Z])/', ' $1', $string));
    }
}

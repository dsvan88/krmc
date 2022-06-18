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
        return mb_strtoupper(mb_substr($string, 0, 1, $encoding), $encoding) . mb_substr($string, 1, mb_strlen($string, $encoding), $encoding);
    }
    public static function translitization($string)
    {
        $string = (string) $string;
        preg_match_all('([-0-9а-яА-ЯрРсСтТуУфФчЧхХШшЩщЪъЫыЬьЭэЮюЄєІіЇїҐґ ]+)', $string, $matches);
        $string = implode('', $matches[0]);
        $string = trim($string);
        $string = function_exists('mb_strtolower') ? mb_strtolower($string, 'UTF-8') : strtolower($string);
        $string = strtr($string, array('а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e', 'ё' => 'e', 'ж' => 'j', 'з' => 'z', 'и' => 'i', 'й' => 'y', 'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n', 'о' => 'o', 'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't', 'у' => 'u', 'ф' => 'f', 'х' => 'h', 'ц' => 'c', 'ч' => 'ch', 'ш' => 'sh', 'щ' => 'shch', 'ы' => 'y', 'э' => 'e', 'ю' => 'yu', 'я' => 'ya', 'ъ' => '', 'ь' => '', 'є' => 'e', 'і' => 'i', 'ї' => 'i', 'ґ' => 'g', ' ' => '_'));
        return $string;
    }
}

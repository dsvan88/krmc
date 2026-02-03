<?php
namespace  app\core;

class Env
{
    public static function init(){
        $directory = $_SERVER['DOCUMENT_ROOT'];
        $files = glob("$directory/.env*");
        $count = count($files);

        if (empty($count)) return false;

        for ($x=0; $x < $count; $x++) { 
            self::parse($files[$x]);
        }
    }
    public static function parse($path){
        $file = fopen($path, 'r');
        while(!feof($file)){
            $string = trim(fgets($file));
            if (empty($string) || $string[0] === '#' || $string[0] === ';' || strpos($string, '=') === false) continue;
            [$key, $value] = explode('=',$string);
            $key = trim($key);
            $value = trim($value);
            if (empty($value)) continue;
            $_ENV[$key] = $value;
            putenv("$key=$value");
        }
        fclose($file);
    }
}
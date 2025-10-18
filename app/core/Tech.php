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
    public static function getRequestProtocol()
    {
        if (empty($_SERVER['HTTP_X_FORWARDED_PROTO']))
            return empty($_SERVER['HTTPS']) ? "http" : "https";
        return strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']);
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
    public static function json_validate(string $string): bool
    {
        json_decode($string);

        return json_last_error() === JSON_ERROR_NONE;
    }
    public static function encrypt(string $string): string
    {
        if (empty($string)) return '';

        $cipher = "AES-256-CBC";
        // $key = md5(ROOT_PASS_DEFAULT);
        $key = hash('xxh3', ROOT_PASS_DEFAULT); //fastest modern algoritm

        $compressed = gzcompress($string, 9);
        $ivLength = openssl_cipher_iv_length($cipher);
        $iv = openssl_random_pseudo_bytes($ivLength);
        $encrypted = openssl_encrypt($compressed, $cipher, $key, 0, $iv);

        return base64_encode($iv . $encrypted);
    }
    public static function decrypt(string $encryptedData): string
    {
        if (empty($encryptedData)) return '';

        $cipher = "AES-256-CBC";
        // $key = md5(ROOT_PASS_DEFAULT);
        $key = hash('xxh3', ROOT_PASS_DEFAULT); //fastest modern algoritm
        $ivLength = openssl_cipher_iv_length($cipher);
        $decoded = base64_decode($encryptedData);
        $extractedIV = substr($decoded, 0, $ivLength);
        $encryptedText = substr($decoded, $ivLength);

        $decryptedCompressed = openssl_decrypt($encryptedText, $cipher, $key, 0, $extractedIV);

        return gzuncompress($decryptedCompressed);
    }
}

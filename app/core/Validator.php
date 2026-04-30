<?php

namespace app\core;

use app\core\Entities\Coupon;
use app\mappers\Coupons;
use app\mappers\Pages;
use app\mappers\Settings;
use app\mappers\Users;

class Validator
{
    public static function validate(string $method, mixed $value)
    {
        return self::$method($value);
    }
    public static function csrfCheck(): bool
    {
        return !empty($_POST[CSRF_NAME]) && self::validate('csrf', $_POST[CSRF_NAME]);
    }
    private static function int(string $string): ?int
    {
        $string = strtolower(trim($string));

        return is_numeric($string) ? (int) $string : null;
    }
    private static function float(string $string): ?float
    {
        $string = strtolower(trim($string));

        return is_numeric($string) ? (float) $string : null;
    }
    private static function rootpass(string $value): string
    {
        $value = trim($value);
        if (empty($value)) return false;
        $rootUser = Users::find(1, true);
        return password_verify(sha1($value), $rootUser['password']);
    }
    private static function csrf(string $value): string
    {
        $value = trim($value);
        if (empty($value)) return false;
        return hash_equals($_SESSION['csrf'], $value);
        // return $value === $_SESSION['csrf'] && $value === sha1($_SERVER['HTTP_USER_AGENT'] . session_id());
    }
    private static function blocks(string $value)
    {
        $value = trim($value);
        if (empty($value)) return false;
        return in_array($value, Pages::$blocks, true) ? $value : false;
    }
    private static function email(string $value)
    {
        $value = trim($value);
        if (empty($value)) return false;
        return filter_var($value, FILTER_VALIDATE_EMAIL) ? $value : false;
    }
    private static function date(string $value, string $format = 'Y-m-d')
    {
        $value = trim($value);
        $dateTime = \DateTime::createFromFormat($format, $value);
        return ($dateTime && $dateTime->format($format) === $value) ? $value : false;
    }
    private static function telegram(string $value)
    {
        $value = trim($value);
        if (strlen($value) < 5)
            return false;
        if (preg_match('/[^a-z0-9_]/i', $value) !== 0)
            return false;
        return $value;
    }
    private static function phone(string $value)
    {
        $value = trim($value);
        if (empty($value)) return false;
        $_clearPhone = preg_replace('/[^+0-9]/', '', $value);
        $pattern = "/^(\+38){0,1}\d{10}$/";
        return preg_match($pattern, $_clearPhone) === 1 ? $value : false;
    }
    private static function gender(string $value): string
    {
        $value = trim($value);
        if (empty($value)) return '';
        return in_array($value, ['male', 'female', 'secret'], true) ? $value : false;
    }
    private static function telegramToken(string $token = ''): bool
    {
        $secret = $_ENV['TG_SECRET_TOKEN'] ?? '';

        if (empty($secret)) return true;

        return hash_equals($secret, $token);
    }
    private static function telegramIp(string $ip): string
    {
        $ranges = [
            [
                'min' => 1533805568, //ip2long('91.108.4.0'),
                'max' => 1533806591, //ip2long('91.108.7.255'),
            ],
            [
                'min' => 2509938688, //ip2long('149.154.160.0')
                'max' => 2509942783, //ip2long('149.154.175.255'),
            ],
        ];
        $ip = sprintf('%u', ip2long($ip)); // Protect from x32 Systems (result can be negative)
        foreach ($ranges as $range) {
            if ($ip >= $range['min'] && $ip <= $range['max']) return true;
        }
        return false;
    }
    private static function telegramHMAC(string $string): bool
    {
        parse_str($string, $array);
        $hash = $array['hash'];
        unset($array['hash']);
        ksort($array);

        $check_string = '';
        foreach ($array as $k => $v)
            $check_string .= "$k=$v\n";
        $check_string = rtrim($check_string, "\n");

        $hmac = hash_hmac('sha256', Settings::getBotToken(), 'WebAppData', true);
        return hash_hmac('sha256', $check_string, $hmac) === $hash;
    }
    private static function name(string $name = '')
    {
        $name = trim($name);
        if (empty($name)) return null;

        $symbols = 'a-z';
        $lun = preg_replace(['/\s+/', "/[^$symbols.0-9 ]+/ui"], [' ', ''], $name);
        $symbols = Locale::$cyrillicPattern;
        $cun = preg_replace(['/\s+/', "/[^$symbols.0-9 ]+/ui"], [' ', ''], $name);

        $name = strlen($lun) > strlen($cun) ? $lun : $cun;

        if (empty($name)) return null;

        $nickname = '';
        $_name = explode(' ', $name);

        foreach ($_name as $slug) {
            $nickname .= Locale::mb_ucfirst($slug) . ' ';
        }

        return mb_substr($nickname, 0, -1, 'UTF-8');
    }
    private static function localeModule(string $string): ?string
    {
        $string = trim($string);

        if (empty($string)) return null;

        return in_array($string, ['mafia', 'poker'], true)
            ? $string
            : null;
    }
    private static function couponType(string $string): ?string
    {
        $string = strtolower(trim($string));

        if (empty($string) || $string === 'han') return null;

        return in_array($string, Coupons::$types, true)
            ? $string
            : null;
    }
    private static function discountType(string $string): ?string
    {
        $string = strtolower(trim($string));

        if (empty($string)) return null;

        return in_array($string, Coupons::$discount_types, true)
            ? $string
            : null;
    }
    private static function couponStatus(string $string): ?string
    {
        $string = strtolower(trim($string));

        if (empty($string)) return null;

        return in_array($string, Coupons::$statuses, true)
            ? $string
            : null;
    }
    private static function couponId(string $id): ?string
    {
        $id = strtolower(trim($id));

        if (empty($id)) return null;

        $coupon = Coupon::create($id);

        return $coupon->id ?? null;
    }
}

<?

namespace app\core;

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
    private static function csrf(string $value): string
    {
        $value = trim($value);
        if (empty($value)) return false;
        return $value === $_SESSION['csrf'];
    }
    private static function email(string $value)
    {
        $value = trim($value);
        if (empty($value)) return false;
        return filter_var($value, FILTER_VALIDATE_EMAIL) ? $value : false;
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
        return in_array($value, ['male', 'female', 'secret'], true) ? $value : '';
    }
}

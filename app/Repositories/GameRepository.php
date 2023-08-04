<?

namespace app\Repositories;

use app\core\Locale;

class GameRepository
{
    public static $bools = ['courtAfterFouls'];
    public static $strings = ['voteType'];
    public static function formConfig(array $data): array
    {
        $config = [];
        foreach ($data as $key => $value) {
            if (in_array($key, ['player', 'manager', 'role'])) continue;
            $key = Locale::camelize($key);

            if (in_array($key, self::$bools)) {
                $config[$key] = (bool) $value;
                continue;
            }
            if (in_array($key, self::$strings)) {
                $config[$key] = $value;
                continue;
            }
            if (is_array($value)) {
                $config[$key] = self::formConfig($value);
                continue;
            }
            $config[$key] = self::floatValues($value);
        }
        return $config;
    }
    public static function floatValues(string $value)
    {
        if (empty($value)) return $value;
        if (!strpos($value, ', ')) return (float) str_replace(',', '.', $value);

        $array = explode(', ', $value);
        $count = count($array);
        for ($x = 0; $x < $count; $x++) {
            $array[$x] = (float) str_replace(',', '.', $array[$x]);
        }
        return $array;
    }
}

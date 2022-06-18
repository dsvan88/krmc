<?

namespace app\models;

use app\core\Model;

class Settings extends Model
{
    public static $settings = [];

    public static function load($type)
    {
        self::$settings[$type] = self::query('SELECT * FROM ' . SQL_TBL_SETTINGS . ' WHERE type = ?', [$type], 'Assoc');
        return self::$settings[$type];
    }
    public static function getById($id)
    {
        $result = self::query('SELECT * FROM ' . SQL_TBL_SETTINGS . ' WHERE id = ?', [$id], 'Assoc');
        if (empty($result))
            return false;
        return $result[0];
    }
    public static function getImage($name)
    {
        if (!isset(self::$settings['img'])) {
            self::load('img');
        }
        for ($i = 0; $i < count(self::$settings['img']); $i++) {
            if (self::$settings['img'][$i]['short_name'] === $name) {
                return self::$settings['img'][$i];
            }
        }
        return false;
    }
    public static function getPage($name)
    {
        if (!isset(self::$settings['pages'])) {
            self::load('pages');
        }
        for ($i = 0; $i < count(self::$settings['pages']); $i++) {
            if (self::$settings['pages'][$i]['short_name'] === $name) {
                return self::$settings['pages'][$i];
            }
        }
        return false;
    }
    public static function getPageById($id)
    {
        if (!isset(self::$settings['pages'])) {
            self::load('pages');
        }
        for ($i = 0; $i < count(self::$settings['pages']); $i++) {
            if (self::$settings['pages'][$i]['id'] == $id) {
                return self::$settings['pages'][$i];
            }
        }
        return false;
    }
    public static function getBotToken()
    {
        if (!isset(self::$settings['tg-bot'])) {
            self::load('tg-bot');
        }

        if (empty(self::$settings['tg-bot']))
            return false;

        return self::$settings['tg-bot'][0]['value'];
    }
    public static function getTechTelegramId()
    {
        if (!isset(self::$settings['tg-tech'])) {
            self::load('tg-tech');
        }

        if (empty(self::$settings['tg-tech']))
            return false;

        return self::$settings['tg-tech'][0]['value'];
    }
    public static function getMainTelegramId()
    {
        if (!isset(self::$settings['tg-main'])) {
            self::load('tg-main');
        }

        if (empty(self::$settings['tg-main']))
            return false;

        return self::$settings['tg-main'][0]['value'];
    }
    public static function getChat($chatId)
    {
        if (!isset(self::$settings['tg-chat'])) {
            self::load('tg-chat');
        }
        for ($i = 0; $i < count(self::$settings['tg-chat']); $i++) {
            if (!is_array(self::$settings['tg-chat'][$i]['value'])) {
                self::$settings['tg-chat'][$i]['value'] = json_decode(self::$settings['tg-chat'][$i]['value'], true);
            }
            if (self::$settings['tg-chat'][$i]['value']['chatId'] == $chatId) {
                return self::$settings['tg-chat'][$i];
            }
        }
        return false;
    }
    public static function getChats()
    {
        if (!isset(self::$settings['tg-chat'])) {
            self::load('tg-chat');
        }
        for ($i = 0; $i < count(self::$settings['tg-chat']); $i++) {
            if (!is_array(self::$settings['tg-chat'][$i]['value'])) {
                self::$settings['tg-chat'][$i]['value'] = json_decode(self::$settings['tg-chat'][$i]['value'], true);
            }
        }
        return self::$settings['tg-chat'];
    }
    public static function save($data)
    {
        try {
            $queryCond = ['type' => $data['type'], 'short_name' => $data['short_name']];
            $id = self::query('SELECT id FROM ' . SQL_TBL_SETTINGS . ' WHERE type = :type AND short_name = :short_name', $queryCond, 'Column');
            if (!$id) {
                return self::insert($data, SQL_TBL_SETTINGS);
            }
            self::update($data, ['id' => $id], SQL_TBL_SETTINGS);
            return true;
        } catch (\Throwable $th) {
            error_log($th->__toString());
            return false;
        }
    }
    public static function remove($id)
    {
        self::delete($id, SQL_TBL_SETTINGS);
    }
    public static function getList($types = [])
    {
        $table = SQL_TBL_SETTINGS;
        $where = '';
        if (!empty($types)) {
            $where = ' WHERE type IN (' . implode(',', array_fill(0, count($types), '?')) . ')';
        }
        return self::query("SELECT * FROM $table $where", $types, 'Assoc');
    }
}

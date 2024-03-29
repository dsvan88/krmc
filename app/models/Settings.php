<?

namespace app\models;

use app\core\Model;

class Settings extends Model
{
    public static $table = SQL_TBL_SETTINGS;
    public static $settings = [];
    public static $jsonFields = ['options'];

    public static function load(string $type)
    {
        $result = self::getAll(['type' => $type]);
        if (!$result || empty($result)) return false;

        self::$settings[$type] = [];
        foreach ($result as $setting) {
            self::$settings[$type][$setting['slug']] = [
                'id' => $setting['id'],
                'type' => $setting['type'],
                'slug' => $setting['slug'],
                'name' => $setting['name'],
                'value' => $setting['value'],
                'options' => $setting['options'],
            ];
        }
        return self::$settings[$type];
    }
    public static function getImage(string $name)
    {
        if (!isset(self::$settings['img'])) {
            self::load('img');
        }

        if (isset(self::$settings['img'][$name])) {
            return self::$settings['img'][$name];
        }

        return false;
    }
    public static function getGroup(string $group = 'img')
    {
        if (isset(self::$settings[$group])) {
            return self::$settings[$group];
        }
        self::load($group);

        if (empty(self::$settings[$group]))
            return false;

        return self::$settings[$group];
    }
    public static function getBotToken()
    {
        if (!isset(self::$settings['telegram'])) {
            self::load('telegram');
        }

        if (empty(self::$settings['telegram']))
            return false;

        return self::$settings['telegram']['bot_token']['value'];
    }
    public static function getTechTelegramId()
    {
        if (!isset(self::$settings['telegram'])) {
            self::load('telegram');
        }

        if (empty(self::$settings['telegram']))
            return false;

        return self::$settings['telegram']['tech_chat']['value'];
    }
    /* 
    Get main telegram group ID
    @return string|false
     */
    public static function getMainTelegramId()
    {
        if (!isset(self::$settings['telegram'])) {
            self::load('telegram');
        }

        if (empty(self::$settings['telegram']))
            return false;

        return self::$settings['telegram']['main_group_chat']['value'];
    }
    public static function edit(int $settingId, array $array)
    {
        foreach ($array as $column => $value) {
            if (!is_array($value)) continue;
            $array[$column] = json_encode($value, JSON_UNESCAPED_UNICODE);
        }
        return self::update($array, ['id' => $settingId]);
    }
    public static function save($data)
    {
        $table = self::$table;
        try {
            $queryCond = ['type' => $data['type'], 'slug' => $data['slug']];
            foreach ($data as $column => $value) {
                if (!is_array($value)) continue;
                $data[$column] = json_encode($value, JSON_UNESCAPED_UNICODE);
            }
            $id = self::query("SELECT id FROM $table WHERE type = :type AND slug = :slug", $queryCond, 'Column');
            if (!$id) {
                return self::insert($data);
            }
            self::update($data, ['id' => $id]);
            return true;
        } catch (\Throwable $th) {
            error_log($th->__toString());
            return false;
        }
    }
    public static function remove($id)
    {
        self::delete($id, self::$table);
    }
    public static function getList($types = [])
    {
        $table = self::$table;
        $where = '';
        if (!empty($types)) {
            $where = ' WHERE type IN (' . implode(',', array_fill(0, count($types), '?')) . ')';
        }
        return self::query("SELECT * FROM $table $where", $types, 'Assoc');
    }
    public static function init()
    {
        $table = self::$table;
    
        self::query(
            "CREATE TABLE IF NOT EXISTS $table (
                id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
                type CHARACTER VARYING(30) NOT NULL DEFAULT 'img',
                slug CHARACTER VARYING(200) NOT NULL DEFAULT '',
                name CHARACTER VARYING(250) NOT NULL DEFAULT '',
                value CHARACTER VARYING(500) NOT NULL DEFAULT '',
                options JSON DEFAULT NULL,
                default_value CHARACTER VARYING(500) NOT NULL DEFAULT ''
            );"
        );
        if (self::isExists(['id' => 1])) return true;

        $settings = [
            ['img', 'MainLogo', 'Основний логотип', '/public/images/club_logo.png'],
            ['img', 'MainFullLogo', 'Основний логотип', '/public/images/club_logo-full.png'],
            ['img', 'MainLogoMini', 'Основний логотип', '/public/images/club_logo-mini.png'],
            ['img', 'profile', 'Профиль', '/public/images/profile.png'],
            ['img', 'male', 'Профиль', '/public/images/male.png'],
            ['img', 'female', 'Профиль', '/public/images/female.png'],
            ['img', 'empty_avatar', 'Нет аватара', '/public/images/empty_avatar.png'],
            ['img', 'news_default', 'Новость', '/public/images/news_default.png'],
            ['telegram', 'bot_token', 'Токен Бота', ''],
            ['telegram', 'tech_chat', 'Технический чат (лог ошибок)', ''],
            ['telegram', 'main_group_chat', 'Основной груповой чат', ''],
            ['points', 'win', 'Балы за победу', 1.0],
            ['points', 'bm', 'Лучший ход', [0.0, 0.0, 0.25, 0.4]],
            ['points', 'fk_sheriff', 'Отстрел шерифа первым',  0.3],
            ['points', 'maf_dops', 'Допы живым мафам', [0.0, 0.3, 0.15, 0.3]],
            ['points', 'mir_dops', 'Допы живым мирным', [0.0, 0.0, 0.15, 0.1]],
            ['points', 'fouls', 'Штраф за дискв. фол', 0.3],
            ['email', 'host', 'Email SMTP Server',  'smtp.gmail.com'],
            ['email', 'username', 'Email Login', ''],
            ['email', 'password', 'Email Password', ''],
            ['email', 'secure', 'Email Secure Type', 'ssl'],
            ['email', 'port', 'Email SMTP Port', '465'],
            ['contacts', 'email', 'Contacts Email', 'kr.mafia.club@gmail.com'],
            ['contacts', 'phone', 'Contacts Phone', '+380987654321'],
            ['contacts', 'telegram', 'Telegram Group', 'https://t.me/+ymO2QrwKoQgzODhi'],
            ['contacts', 'tg-chatbot', 'Telegram Bot Username', 'KRMafiaClubBot'],
            ['contacts', 'tg-name', 'Telegram Group Name', CLUB_NAME],
            ['contacts', 'adress', 'Adress', 'Україна  Дніпропетровська обл.  м. Кривий Ріг  пр. Миру, 35'],
            ['contacts', 'gmap_link', 'Google Map Link', 'https://goo.gl/maps/ig4UybMfbdeRLDYHA'],
            ['contacts', 'gmap_widget', 'Google Map Widget Link', 'https://google.com/maps/embed?pb=!1m18!1m12!1m3!1d668.5851682189962!2d33.39026844441126!3d47.91044536621998!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x40db20bcd47525ff%3A0xf39a0145fefa1e96!2z0L_RgNC-0YHQvy4g0JzQuNGA0LAsIDM1!5e0!3m2!1sru!2sua!4v1682765272064!5m2!1sru!2sua'],
            ['socials', 'youtube', 'Youtube Channel', ''],
            ['socials', 'instagram', 'Instagram', ''],
            ['socials', 'facebook', 'Facebook', ''],

            ['backup', 'email', 'Backup Email', ''],
            ['backup', 'last', 'Last backup', ''],
            // ['socials', 'tiktok', 'Tik-Tok Channel',  ''],
        ];

        $array = [];
        $keys = ['type', 'slug', 'name', 'value', 'default_value'];
        for ($i = 0; $i < count($settings); $i++) {
            foreach ($settings[$i] as $num => $setting) {
                if (!is_array($setting)) continue;
                $settings[$i][$num] = json_encode($setting, JSON_UNESCAPED_UNICODE);
            }
            $settings[$i][] = $settings[$i][3];
            $array[] = array_combine($keys, $settings[$i]);
        }
        self::insert($array, $table);

        $mafiaConfig = json_encode([
            "voteType" => "enum",
            "courtAfterFouls" => false,
            "getOutHalfPlayersMin" => 4,
            "mutedSpeakMaxCount" => 5,
            "bestMovePlayersMin" => 9,
            "timerMax" => 6000,
            "lastWillTime" => 6000,
            "debateTime" => 3000,
            "mutedSpeakTime" => 3000,
            "wakeUpRoles" => 2000,
            "points" => [
                "winner" => 1,
                "sherifFirstStaticKill" => 0.1,
                "sherifFirstDynamicKill" => 0.3,
                "bestMove" => [0, 0, 0.25, 0.4],
                "aliveMafs" => [0, 0, 0.25, 0.4],
                "aliveReds" => [0, 0, 0.15, 0.1],
                "fourFouls" => -0.1,
                "disqualified" => -0.3,
                "voteInSherif" => -0.1,
            ],
        ]);

        self::insert(['type'=>'mafia_config', 'slug'=>'mafia-config', 'options'=>$mafiaConfig], $table);
    }
}

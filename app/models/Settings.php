<?

namespace app\models;

use app\core\Model;

class Settings extends Model
{
    public static $table = SQL_TBL_SETTINGS;
    public static $settings = [];
    public static $jsonFields = ['setting'];

    public static function load(string $type = ''): array
    {
        $result = empty($type) ? self::getAll() : self::getAll(['type' => $type]);

        if (empty($result)) return [];

        foreach ($result as $setting) {
            $_setting = [];
            foreach ($setting['setting'] as $set) {
                $_setting[$set['slug']] = $set;
            }
            self::$settings[$setting['type']] = $_setting;
        }
        return empty($type) ? self::$settings : self::$settings[$type];
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
    public static function get(string $slug)
    {

        if (empty($slug)) return false;

        if (isset(self::$settings[$slug])) {
            return self::$settings[$slug];
        }
        self::load($slug);

        if (empty(self::$settings[$slug]))
            return false;

        return self::$settings[$slug];
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
    public static function getTechTelegramId(): int
    {
        if (!isset(self::$settings['telegram'])) {
            self::load('telegram');
        }

        if (empty(self::$settings['telegram']))
            return false;

        return self::$settings['telegram']['tech_chat']['value'];
    }
    /**
     * Get main telegram group ID
     * 
     * @return string|false
     */
    public static function getAdminChatTelegramId(): int
    {
        if (!isset(self::$settings['telegram'])) {
            self::load('telegram');
        }

        if (empty(self::$settings['telegram']['admin']['value']))
            return false;

        return self::$settings['telegram']['admin']['value'];
    }

    /**
     * Get main telegram group ID
     * 
     * @return string|false
     */
    public static function getMainTelegramId(): int
    {
        if (!isset(self::$settings['telegram'])) {
            self::load('telegram');
        }

        if (empty(self::$settings['telegram']['main_group_chat']['value']))
            return false;

        return self::$settings['telegram']['main_group_chat']['value'];
    }
    public static function edit(int $settingId, array $array)
    {
        $setting = [];
        foreach ($array as $column => $value) {
            if (is_array($value)) {
                $setting[$column] = json_encode($value, JSON_UNESCAPED_UNICODE);
            }
        }
        return self::update($setting, ['id' => $settingId]);
    }
    public static function save(string $type = '', string $slug = '', $value = ''): bool
    {
        if (is_array($value)) $value = json_encode($value, JSON_UNESCAPED_UNICODE);
        $setting = [
            'slug' => $slug,
            'name' => $slug,
            'value' => $value,
        ];
        try {
            $prevSetting = self::findBy('type', $type)[0];
            if (empty($prevSetting)) {
                return self::insert(['type' => $type, 'setting' => json_encode([$setting], JSON_UNESCAPED_UNICODE)]);
            }

            $index = array_search($slug, array_column($prevSetting['setting'], 'slug'), true);
            if ($index === false) $prevSetting['setting'][] = $setting;
            else $prevSetting['setting'][$index]['value'] = $value;

            self::update(['setting' => json_encode($prevSetting['setting'], JSON_UNESCAPED_UNICODE)], ['id' => $prevSetting['id']]);
        } catch (\Throwable $th) {
            error_log($th->__toString());
            return false;
        }
        return true;
    }
    public static function remove($id)
    {
        self::delete($id);
    }
    public static function init()
    {
        $table = self::$table;

        self::query(
            "CREATE TABLE IF NOT EXISTS $table (
                id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
                type CHARACTER VARYING(30) NOT NULL DEFAULT 'img',
                setting JSON DEFAULT NULL
            );"
        );
        if (self::isExists(['id' => 1])) return true;


        $settings = [
            [
                'img',
                [
                    ['slug' => 'MainLogo', 'name' => 'Основний логотип', 'value' => '/public/images/club_logo.png'],
                    ['slug' => 'MainFullLogo', 'name' => 'Основний логотип', 'value' => '/public/images/club_logo-full.png'],
                    ['slug' => 'MainLogoMini', 'name' => 'Основний логотип', 'value' => '/public/images/club_logo-mini.png'],
                    ['slug' => 'profile', 'name' => 'Профиль', 'value' => '/public/images/profile.png'],
                    ['slug' => 'male', 'name' => 'Профиль', 'value' => '/public/images/male.png'],
                    ['slug' => 'female', 'name' => 'Профиль', 'value' => '/public/images/female.png'],
                    ['slug' => 'empty_avatar', 'name' => 'Нет аватара', 'value' => '/public/images/empty_avatar.png'],
                    ['slug' => 'news_default', 'name' => 'Новость', 'value' => '/public/images/news_default.png'],
                    ['slug' => 'background', 'name' => 'Загальний фон', 'value' => []],
                ]
            ],
            [
                'telegram',
                [
                    ['slug' => 'bot_token', 'name' => 'Токен Бота', 'value' => ''],
                    ['slug' => 'tech_chat', 'name' => 'Технический чат (лог ошибок)', 'value' => ''],
                    ['slug' => 'admin_chat', 'name' => 'Чат адмінів', 'value' => ''],
                    ['slug' => 'main_group_chat', 'name' => 'Основной груповой чат', 'value' => ''],
                ]
            ],
            [
                'email',
                [
                    ['slug' => 'host', 'name' => 'Email SMTP Server',  'value' => 'smtp.gmail.com'],
                    ['slug' => 'username', 'name' => 'Email Login', 'value' => ''],
                    ['slug' => 'password', 'name' => 'Email Password', 'value' => ''],
                    ['slug' => 'secure', 'name' => 'Email Secure Type', 'value' => 'ssl'],
                    ['slug' => 'port', 'name' => 'Email SMTP Port', 'value' => '465'],
                ]
            ],
            [
                'gdrive',
                [
                    ['slug' => 'type', 'name' => 'Type', 'value' => 'service_account'],
                    ['slug' => 'project_id', 'name' => 'Project ID', 'value' => ''],
                    ['slug' => 'private_key_id', 'name' => 'Private Key ID', 'value' => ''],
                    ['slug' => 'private_key', 'name' => 'Private Key', 'value' => ''],
                    ['slug' => 'client_email', 'name' => 'Client E-mail', 'value' => ''],
                    ['slug' => 'client_id', 'name' => 'Client ID', 'value' => ''],
                    ['slug' => 'auth_uri', 'name' => 'Auth URI', 'value' => 'https://accounts.google.com/o/oauth2/auth'],
                    ['slug' => 'token_uri', 'name' => 'Token URI', 'value' => 'https://oauth2.googleapis.com/token'],
                    ['slug' => 'auth_provider_x509_cert_url', 'name' => 'Auth Provider Cert URL', 'value' => 'https://www.googleapis.com/oauth2/v1/certs'],
                    ['slug' => 'client_x509_cert_url', 'name' => 'Client Cert URL', 'value' => 'https://www.googleapis.com/robot/v1/metadata/x509/dev-krmc%40dev-krmc.iam.gserviceaccount.com'],
                    ['slug' => 'universe_domain', 'name' => 'Universe Domain', 'value' => 'googleapis.com'],
                ]
            ],
            [
                'contacts',
                [
                    ['slug' => 'email', 'name' => 'Contacts Email', 'value' => 'kr.mafia.club@gmail.com'],
                    ['slug' => 'phone', 'name' => 'Contacts Phone', 'value' => '+380987654321'],
                    ['slug' => 'telegram', 'name' => 'Telegram Group', 'value' => 'https://t.me/+ymO2QrwKoQgzODhi'],
                    ['slug' => 'tg-chatbot', 'name' => 'Telegram Bot Username', 'value' => 'KRMafiaClubBot'],
                    ['slug' => 'tg-name', 'name' => 'Telegram Group Name', 'value' => CLUB_NAME],
                    ['slug' => 'adress', 'name' => 'Adress', 'value' => 'Україна  Дніпропетровська обл.  м. Кривий Ріг  пр. Миру, 35'],
                    ['slug' => 'gmap_link', 'name' => 'Google Map Link', 'value' => 'https://goo.gl/maps/ig4UybMfbdeRLDYHA'],
                    ['slug' => 'gmap_widget', 'name' => 'Google Map Widget Link', 'value' => 'https://google.com/maps/embed?pb=!1m18!1m12!1m3!1d668.5851682189962!2d33.39026844441126!3d47.91044536621998!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x40db20bcd47525ff%3A0xf39a0145fefa1e96!2z0L_RgNC-0YHQvy4g0JzQuNGA0LAsIDM1!5e0!3m2!1sru!2sua!4v1682765272064!5m2!1sru!2sua'],
                ]
            ],
            [
                'socials',
                [
                    ['slug' => 'youtube', 'name' => 'Youtube Channel', 'value' => ''],
                    ['slug' => 'instagram', 'name' => 'Instagram', 'value' => ''],
                    ['slug' => 'facebook', 'name' => 'Facebook', 'value' => ''],
                    // ['tiktok', 'Tik-Tok Channel',  ''],
                ]
            ],
            [
                'backup',
                [
                    ['slug' => 'email', 'name' => 'Backup Email', 'value' => ''],
                    ['slug' => 'last', 'name' => 'Last backup', 'value' => ''],
                ]
            ],
        ];

        $array = [];
        $keys = ['type', 'setting'];
        for ($i = 0; $i < count($settings); $i++) {
            foreach ($settings[$i] as $num => $setting) {
                if (!is_array($setting)) continue;
                $settings[$i][$num] = json_encode($setting, JSON_UNESCAPED_UNICODE);
            }
            $array[] = array_combine($keys, $settings[$i]);
        }
        self::insert($array);

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

        self::insert([
            'type' => 'config',
            'setting' => json_encode([
                ['slug' => 'mafia', 'name' => 'Mafia Game config', 'value' => $mafiaConfig]
            ], JSON_UNESCAPED_UNICODE)
        ]);
    }
}

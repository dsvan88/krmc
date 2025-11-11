<?

namespace app\Controllers;

use app\core\Controller;
use app\core\GoogleDrive;
use app\core\ImageProcessing;
use app\core\Locale;
use app\core\Sender;
use app\core\Tech;
use app\core\View;
use app\libs\Db;
use app\models\Contacts;
use app\models\Settings;
use app\models\Users;
use app\Repositories\TechRepository;
use app\Repositories\TelegramChatsRepository;
use Exception;

class TechController extends Controller
{
    public static function sqlAction()
    {
        if (!empty($_POST)) {
            if (isset($_POST['sql_query'])) {
                $result = Db::query($_POST['sql_query'], [], 'Assoc');
                return View::message(['error' => 0, 'sql-result' => $result]);
            }
        }
        $vars = [
            'title' => 'Execute SQL-query',
            'texts' => [
                'SubmitLabel' => 'Execute',
            ],
            'scripts' => [
                'plugins/ckeditor.js',
                'forms-admin-funcs.js',
                'images-pad.js',
            ],
        ];
        View::$route['vars'] = array_merge(View::$route['vars'], $vars);

        return View::render();
    }
    public static function backupAction()
    {
        if (!empty($_POST)) {
            if (empty($_POST['table']))
                return View::message(['error' => 1, 'message' => 'Something wrong with your query!']);

            $table = trim($_POST['table']);
            $result = TechRepository::backup($table);
            $archiveName = CLUB_SNAME . ' backup ' . date('d.m.Y', $_SERVER['REQUEST_TIME']);
            if ($table !== 'all') {
                $result = [$table => array_values($result)];
                $archiveName = "$table $archiveName";
            }
            $archive = TechRepository::archive($archiveName, $result);
            return View::file($archive, basename($archive));
        }
        $vars = [
            'title' => 'DB Backup form',
            'texts' => [
                'SubmitLabel' => 'Execute',
            ],
            'scripts' => [
                'plugins/ckeditor.js',
                'forms-admin-funcs.js',
                'images-pad.js',
            ],
        ];
        View::$route['vars'] = array_merge(View::$route['vars'], $vars);

        return View::render();
    }
    public static function backupSaveAction()
    {
        ignore_user_abort(true);
        error_reporting(0);
        set_time_limit(90);

        $settings = Settings::findBy('type', 'backup')[0];

        foreach ($settings['setting'] as $index => $setting) {
            $backup[$setting['slug']] = [
                'index' => $index,
                'value' => $setting['value']
            ];
        }
        if (empty($backup['email']['value']) || $backup['last']['value'] > $_SERVER['REQUEST_TIME'] - BACKUP_FREQ) exit();

        $settings['setting'][$backup['last']['index']]['value'] = $_SERVER['REQUEST_TIME'];
        Settings::edit($settings['id'], ['setting' => $settings['setting']]);

        header("Connection: close", true);
        header("Content-Encoding: none" . PHP_EOL);
        header("Content-Length: 0", true);
        flush();

        TechRepository::sendBackup($backup['email']['value']);

        exit();
    }
    public static function restoreAction()
    {
        // View::redirect('/');
        ini_set('max_execution_time', 300);
        if (!empty($_POST)) {
            if (TechRepository::restore())
                return View::message('Done!');
            return View::message(['error' => true, 'message' => 'Something wrong with your datafile!']);
        }
        $vars = [
            'title' => 'DB Restore Form',
            'texts' => [
                'SubmitLabel' => 'Execute',
            ],
            'scripts' => [
                'plugins/ckeditor.js',
                'forms-admin-funcs.js',
                'images-pad.js',
            ],
        ];
        View::$route['vars'] = array_merge(View::$route['vars'], $vars);

        return View::render();
    }
    public static function dbrebuildAction()
    {
        return View::redirect('/');

        // $contacts = Contacts::getAll();

        // $result = [];
        // $exists = [];
        // usort($contacts, function ($elemA, $elemB){
        //     return $elemA['id'] > $elemB['id'] ? 1 : -1;
        // });

        // foreach($contacts as $contact){
        //     if ($contact['type'] !== 'telegram'){
        //         unset($contact['id']);
        //         $result[] = $contact;
        //         continue;
        //     }
        //     if (array_search($contact['contact'], $exists, true) !== false) continue;
        //     $exists[] = $contact['contact'];

        //     unset($contact['id']);
        //     $result[] = $contact;
        // }
        // Contacts::dbDropTables(Contacts::$table);
        // Contacts::init();
        // Contacts::insert($result);

        // $settings = [
        //     ['gdrive', 'credentials', 'Credentials JSON',  ''],
        // ];

        // $array = [];
        // $keys = ['type', 'slug', 'name', 'value', 'default_value'];
        // for ($i = 0; $i < count($settings); $i++) {
        //     foreach ($settings[$i] as $num => $setting) {
        //         if (!is_array($setting)) continue;
        //         $settings[$i][$num] = json_encode($setting, JSON_UNESCAPED_UNICODE);
        //     }
        //     $settings[$i][] = $settings[$i][3];
        //     $array[] = array_combine($keys, $settings[$i]);
        // }
        // Settings::insert($array);

        // $table = Pages::$table;
        // Users::query("ALTER TABLE $table ADD COLUMN lang CHARACTER VARYING(5) DEFAULT NULL");

        /*         return View::redirect('/');
        $table = Games::$table;
        $games = Games::getAll();
        Games::query("ALTER TABLE $table ALTER COLUMN manager TYPE CHARACTER VARYING(300)");
        foreach ($games as $index => $game) {
            if (empty($game['manager']) || is_numeric($game['manager'])) continue;
            $game['manager'] = json_decode($game['manager'], true);
            Games::update([ 'manager' => empty($game['manager']['id']) ? 1 : (int) $game['manager']['id'] ], ['id'=>$game['id']]);
        } */

        /* 
        $table = Pages::$table;
        Pages::query("ALTER TABLE $table ADD COLUMN description CHARACTER VARYING(300) NOT NULL DEFAULT ''");
 */
        /* $chatsData = TelegramChats::getChatsList();
        foreach ($chatsData as $index => $chat) {
            if (empty($chat['personal']['nickname'])) continue;
            $userData = Users::getDataByName($chat['personal']['nickname']);
            if (empty($userData['id'])) continue;
            unset($chat['personal']['nickname']);
            TelegramChats::edit(['user_id' => $userData['id'], 'personal' => $chat['personal']], $chat['id']);
        }


        if (!Settings::isExists(['type' => 'backup'])){
            $settings = [
                ['backup', 'email', 'Backup Email', ''],
                ['backup', 'last', 'Last backup', ''],
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
            Settings::insert($array);
        }


        $weeksIds = Weeks::getIds();
        foreach ($weeksIds as $weekId) {
            $weekData = Weeks::weekDataById($weekId);
            foreach ($weekData['data'] as $dayNum => $dayData) {
                foreach($dayData['participants'] as $playerNum => $player){
                    unset($weekData['data'][$dayNum]['participants'][$playerNum]['name']);
                    if ($player['id'] === null || $player['id'] > 0 || !empty($player['name']) && strpos($player['name'], 'tmp_user') === false) continue;
                    $weekData['data'][$dayNum]['participants'][$playerNum]['id'] = null;
                }
            }
            Weeks::update(['data' => json_encode($weekData['data'], JSON_UNESCAPED_UNICODE)], ['id' => $weekId]);
        } */
        echo 'Done!';
    }
    public static function selfTestTelegramAction()
    {
        // View::redirect('/');
        $params = [
            'message' => [
                'message_id' => 189,
                'from' => [
                    'id' => 900669168,
                    // 'id' => 412223734,
                    'is_bot' => false,
                    'first_name' => 'Dmytro',
                    'last_name' => 'Vankevych',
                    'username' => 'dsvan88',
                    'language_code' => 'uk',
                ],
                'chat' => [
                    'id' => 900669168,
                    // 'id' => -684025311,
                    'first_name' => 'Dmytro',
                    'last_name' => 'Vankevych',
                    'username' => 'dsvan88',
                    'type' => 'private',
                    // 'type' => 'group',
                ],
                'date' => 1652025484,
                // 'text' => '+ на 18',
                'text' => '/newuser Мавка',
                // 'text' => '+пн',
                // 'text' => '+пт?',
                // 'text' => '+ на четвер, десь на 18:45, звісно, що підстрахую, але поки що (під ?)',
                // 'text' => '/?',
            ]
        ];

        $url = strtolower(Tech::getRequestProtocol()) . '://' . $_SERVER['HTTP_HOST'] . '/api/telegram/webhook';
        $options = [
            CURLOPT_RETURNTRANSFER => false,
            // CURLOPT_RETURNTRANSFER => true,
            // CURLOPT_FRESH_CONNECT => true,
            CURLOPT_POST => true,       // отправка данных методом POST
            CURLOPT_TIMEOUT => 10,      // максимальное время выполнения запроса
            CURLOPT_FAILONERROR => true,
            CURLOPT_HTTPHEADER => ['Content-Type:application/json'],
            CURLOPT_URL => $url,
            CURLOPT_POSTFIELDS => json_encode($params, JSON_UNESCAPED_UNICODE),
        ];
        $curl = curl_init();

        curl_setopt_array($curl, $options);
        $result = json_decode(curl_exec($curl), true);
        var_dump($result);
        return $result;
    }
    public static function testAction()
    {
        // $gDrive = new GoogleDrive;
        // echo $gDrive->getFolderId('avatars');

        $users = Users::getAll();
        foreach($users as $user){
            try {
                if (!empty($user['personal']['avatar'])) continue;
                TelegramChatsRepository::getAndSaveTgAvatar($user['id'], true);
            } catch (\Throwable $th) {
                Tech::dump($th);
            }
        }
    }
}

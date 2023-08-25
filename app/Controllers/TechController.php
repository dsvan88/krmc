<?

namespace app\Controllers;

use app\core\Controller;
use app\core\PHPMailer\PHPMailer;
use app\core\View;
use app\libs\Db;
use app\models\Contacts;
use app\models\Pages;
use app\models\Settings;
use app\models\TelegramChats;
use app\models\Users;
use app\models\Weeks;
use app\Repositories\TechRepository;

class TechController extends Controller
{
    public static function sqlAction()
    {
        if (!empty($_POST)) {
            if (isset($_POST['sql_query'])) {
                $result = Db::query($_POST['sql_query'], [], 'Assoc');
                View::message(['error' => 0, 'sql-result' => $result]);
            }
        }
        $vars = [
            'title' => 'Execute SQL-query',
            'texts' => [
                'SubmitLabel' => 'Execute',
            ],
            'scripts' => [
                '/public/scripts/plugins/ckeditor.js?v=' . $_SERVER['REQUEST_TIME'],
                '/public/scripts/forms-admin-funcs.js?v=' . $_SERVER['REQUEST_TIME'],
            ],
        ];
        View::render($vars);
    }
    public static function backupAction()
    {
        if (!empty($_POST)) {
            if (empty($_POST['table']))
                View::message(['error' => 1, 'message' => 'Something wrong with your query!']);

            $table = trim($_POST['table']);
            $result = TechRepository::backup($table);
            $archiveName = MAFCLUB_SNAME . ' backup ' . date('d.m.Y', $_SERVER['REQUEST_TIME']);
            if ($table !== 'all') {
                $result = [$table => array_values($result)];
                $archiveName = "$table $archiveName";
            }
            $archive = TechRepository::archive($archiveName, $result);
            View::file($archive, basename($archive));
        }
        $vars = [
            'title' => 'DB Backup form',
            'texts' => [
                'SubmitLabel' => 'Execute',
            ],
            'scripts' => [
                '/public/scripts/plugins/ckeditor.js?v=' . $_SERVER['REQUEST_TIME'],
                '/public/scripts/forms-admin-funcs.js?v=' . $_SERVER['REQUEST_TIME'],
            ],
        ];
        View::render($vars);
    }
    public static function migrationAction()
    {
        // View::redirect('/');
        if (!empty($_POST)) {
            $table = substr($_FILES['data']['name'], strpos($_FILES['data']['name'], '-') + 1);
            $table = substr($table, 0, strrpos($table, '.'));

            if (!in_array($table, ['settings', 'tgchats', 'users', 'weeks', 'pages', 'games', 'contacts']))
                View::message(['error' => true, 'message' => 'Something wrong with your datafile!']);

            $data = json_decode(trim(file_get_contents($_FILES['data']['tmp_name'])), true);

            if (!is_array($data))
                View::message(['error' => true, 'message' => 'Something wrong with your datafile!']);

            DB::tableTruncate($table);
            DB::insert($data, $table);
            DB::query("SELECT setval(pg_get_serial_sequence('$table', 'id'), coalesce(max(id)+1, 1), false) FROM $table;");

            View::message('Done!');
        }
        $vars = [
            'title' => 'DB Migration Form',
            'texts' => [
                'SubmitLabel' => 'Execute',
            ],
            'scripts' => [
                '/public/scripts/plugins/ckeditor.js?v=' . $_SERVER['REQUEST_TIME'],
                '/public/scripts/forms-admin-funcs.js?v=' . $_SERVER['REQUEST_TIME'],
            ],
        ];
        View::render($vars);
    }
    public static function dbrebuildAction()
    {
        // View::redirect('/');

        $table = Pages::$table;
        Pages::query("ALTER TABLE $table ADD COLUMN description CHARACTER VARYING(300) NOT NULL DEFAULT ''");

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
                    'type' => 'group',
                ],
                'date' => 1652025484,
                'text' => '+ на 18',
                // 'text' => '/nick Думатель',
                // 'text' => '/day',
                // 'text' => '+tod 18:15',
                // 'text' => '/users',
                // 'text' => '/?',
            ]
        ];

        $url = strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) . '://' . $_SERVER['HTTP_HOST'] . '/api/telegram/webhook';
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

        $directory = 'testDir';
        mkdir($directory);

        var_dump(file_exists($directory) ? 'Exists' : 'Error');
        View::errorCode(404, ['message' => 'Result is Ok']);
    }
    public static function sendMailAction()
    {
        $mailer = new PHPMailer();

        $mailer->isSMTP();
        $mailer->CharSet = "UTF-8";
        $mailer->SMTPAuth   = true;
        // $mailer->SMTPDebug = 4;
        // $mailer->Debugoutput = function ($str, $level) {
        //     $GLOBALS['status'][] = $str;
        // };

        $mailer->Host       = 'smtp.gmail.com';
        $mailer->Username   = '';
        $mailer->Password   = '';
        $mailer->SMTPSecure = 'ssl';
        $mailer->Port       = 465;

        $mailer->Subject    = 'Test mail';
        $mailer->Body       = "That's my second try to send simple email";

        // if (isset($this->senderData['email']))
        //     $mailer->setFrom($this->senderData['email'], $this->senderData['name']);
        // else
        //     $mailer->setFrom($authData['email'], $authData['name']);
        // // $mailer->addAddress('yourMainEmail@gmail.com'); // If you need send message from your main tech email to your main email - you can change it here
        $mailer->isHTML(true);

        $mailer->addAddress('');
        $mailer->send();
    }
}
